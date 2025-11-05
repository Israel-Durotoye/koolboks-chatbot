from fastapi import FastAPI, UploadFile, File, HTTPException
from fastapi.middleware.cors import CORSMiddleware
from fastapi.middleware.gzip import GZipMiddleware
from typing import List, Dict, Any, Optional
from pydantic import BaseModel
from pdf_extractor import extract_text_from_pdf, extract_images_from_pdf
from embedder import chunk_text, store_chunks_and_embeddings, hybrid_search
from query_llm import generate_chat_response
from webhook_handler import webhook_handler
import asyncio
import time

app = FastAPI(title="RAG System API")

# Add CORS middleware
app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

# Add Gzip compression
app.add_middleware(GZipMiddleware, minimum_size=1000)

# Store conversation history for each session with TTL
conversations: Dict[str, Dict] = {}

# Cache for retrieved contexts with TTL (seconds)
document_cache: Dict[str, Dict[str, Any]] = {}
CACHE_TTL_SECONDS = 600
CACHE_MAX_ENTRIES = 100

def get_cached_context(cache_key: str):
    """Return cached context if still fresh."""
    entry = document_cache.get(cache_key)
    if not entry:
        return None

    if time.time() - entry["timestamp"] > CACHE_TTL_SECONDS:
        document_cache.pop(cache_key, None)
        return None

    return entry["context"]

def set_cached_context(cache_key: str, context: List[str]):
    """Cache retrieval results with simple LRU eviction."""
    if len(document_cache) >= CACHE_MAX_ENTRIES:
        oldest_key = min(document_cache, key=lambda key: document_cache[key]["timestamp"])
        document_cache.pop(oldest_key, None)

    document_cache[cache_key] = {
        "context": context,
        "timestamp": time.time(),
    }

def cleanup_old_sessions():
    """Remove conversation histories older than 1 hour"""
    current_time = time.time()
    for session_id in list(conversations.keys()):
        if current_time - conversations[session_id]["last_access"] > 3600:
            del conversations[session_id]

class ChatMessage(BaseModel):
    role: str
    content: str

class ChatHistory(BaseModel):
    user: str
    assistant: str

class ChatSettings(BaseModel):
    temperature: float = 0.7
    max_tokens: int = 500
    top_p: float = 0.95

class ChatRequest(BaseModel):
    query: str
    session_id: str
    chat_history: List[ChatHistory] = []
    settings: Optional[ChatSettings] = None

class LeadCapture(BaseModel):
    name: str
    email: str
    phone: str
    message: str = ""
    interested_products: List[str] = []
    session_id: str
    chat_history: List[Dict[str, str]] = []

@app.post("/upload/")
async def upload_pdf(file: UploadFile = File(...)):
    try:
        pdf_bytes = await file.read()
        pdf_text = extract_text_from_pdf(pdf_bytes)
        image_texts = extract_images_from_pdf(pdf_bytes)

        full_text = pdf_text + "\n" + "\n".join(image_texts)
        print(f"📜 Extracted Text Length: {len(full_text)} characters")

        chunks = chunk_text(full_text)
        store_chunks_and_embeddings(chunks)

        # Reset caches so responses align with the new document
        document_cache.clear()
        conversations.clear()
        
        return {
            "message": "Knowledge base uploaded successfully! You can now start chatting.",
            "chunks": len(chunks)
        }

    except Exception as e:
        return {"error": f"Failed to process document: {str(e)}"}

@app.post("/chat/")
async def chat(request: ChatRequest):
    if not request.query.strip():
        raise HTTPException(status_code=400, detail="Query cannot be empty.")

    try:
        # Clean up stale sessions periodically
        cleanup_old_sessions()

        start_time = time.time()
        session_state = conversations.setdefault(
            request.session_id,
            {"history": [], "last_access": start_time},
        )
        session_state["last_access"] = start_time

        # Retrieve relevant context from uploaded documents (if available)
        normalized_query = request.query.strip().lower()
        cache_key = f"{request.session_id}:{normalized_query}"
        cached_context = get_cached_context(cache_key)
        
        # Try to get context from documents, but don't fail if none exist
        try:
            if cached_context is not None:
                retrieved_context = cached_context
            else:
                retrieved_context = hybrid_search(request.query, top_k=3)
                set_cached_context(cache_key, retrieved_context)
        except Exception as search_error:
            # No documents uploaded yet, that's fine - chatbot works without them
            print(f"No document context available: {search_error}")
            retrieved_context = []

        # Format the recent chat history for the LLM
        trimmed_history = request.chat_history[-5:]
        formatted_history: List[Dict[str, str]] = []
        for msg in trimmed_history:
            formatted_history.extend(
                [
                    {"role": "user", "content": msg.user},
                    {"role": "assistant", "content": msg.assistant},
                ]
            )

        # Generate response using the LLM with timeout protection
        llm_settings = request.settings or ChatSettings()
        try:
            response = await asyncio.wait_for(
                asyncio.to_thread(
                    generate_chat_response,
                    query=request.query,
                    context=retrieved_context,
                    chat_history=formatted_history,
                    temperature=llm_settings.temperature,
                    max_tokens=llm_settings.max_tokens,
                    top_p=llm_settings.top_p,
                ),
                timeout=50,  # Increased to 50 seconds
            )
        except asyncio.TimeoutError as exc:
            raise HTTPException(status_code=504, detail="Response generation timed out") from exc

        # Persist trimmed history for potential server-side analytics
        session_state["history"] = [msg.dict() for msg in trimmed_history]

        processing_time = time.time() - start_time
        session_state["last_access"] = time.time()

        return {
            "response": response,
            "context": "\n\nSources:\n" + "\n\n".join(retrieved_context),
            "processing_time": processing_time,
        }

    except HTTPException:
        raise
    except Exception as exc:
        raise HTTPException(status_code=500, detail=f"Failed to process chat request: {exc}") from exc

@app.post("/capture-lead/")
async def capture_lead(lead: LeadCapture):
    """Capture lead information and send to CRM via webhook."""
    try:
        # Log what we received from WordPress
        print(f"\n{'='*60}")
        print(f"📋 NEW LEAD RECEIVED")
        print(f"{'='*60}")
        print(f"Name: {lead.name}")
        print(f"Email: {lead.email}")
        print(f"Phone: {lead.phone}")
        print(f"Message: {lead.message}")
        print(f"Session ID: {lead.session_id}")
        print(f"{'='*60}\n")
        
        # Send lead to CRM webhook asynchronously (non-blocking)
        asyncio.create_task(
            asyncio.to_thread(
                webhook_handler.send_lead,
                name=lead.name,
                email=lead.email,
                phone=lead.phone,
                message=lead.message,
                interested_products=lead.interested_products,
                session_id=lead.session_id,
                chat_history=lead.chat_history
            )
        )
        
        # Return immediately to user without waiting for webhook
        return {
            "status": "success",
            "message": "Thank you! We'll contact you shortly.",
            "lead_id": lead.session_id
        }
            
    except Exception as e:
        print(f"❌ Lead capture error: {str(e)}")
        # Still return success to user
        return {
            "status": "success",
            "message": "Thank you! We'll get back to you soon.",
            "lead_id": lead.session_id
        }

@app.post("/log-chat/")
async def log_chat(session_id: str, chat_history: List[Dict], user_info: Optional[Dict] = None):
    """Log complete chat session to webhook for analytics."""
    try:
        webhook_handler.send_chat_log(
            session_id=session_id,
            chat_history=chat_history,
            user_info=user_info
        )
        return {"status": "logged"}
    except Exception as e:
        print(f"❌ Chat log error: {str(e)}")
        return {"status": "failed"}

@app.get("/health")
async def health_check():
    """Health check endpoint."""
    return {
        "status": "healthy",
        "webhook_configured": bool(webhook_handler.webhook_url),
        "timestamp": time.time()
    }

if __name__ == "__main__":
    import uvicorn
    uvicorn.run(app, host = "0.0.0.0", port = 8000)