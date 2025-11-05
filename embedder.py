import chromadb
from chromadb.config import Settings
from langchain_text_splitters import RecursiveCharacterTextSplitter
from sentence_transformers import SentenceTransformer
import os
import shutil
from functools import lru_cache

def init_chroma_db():
    """Initialise Chroma using the new settings-style client."""
    persist_dir = "chroma_db"
    settings = Settings(
        anonymized_telemetry=False,
        is_persistent=True,
        persist_directory=persist_dir,
    )

    def _create_client():
        client = chromadb.PersistentClient(path=persist_dir, settings=settings)
        collection = client.get_or_create_collection(name="rag_docs")
        collection.count()
        return client, collection

    try:
        return _create_client()
    except Exception as first_error:
        print(f"Error accessing ChromaDB: {first_error}. Resetting persistence store...")
        shutil.rmtree(persist_dir, ignore_errors=True)
        shutil.rmtree("collection", ignore_errors=True)
        os.makedirs(persist_dir, exist_ok=True)
        try:
            return _create_client()
        except Exception as second_error:
            raise Exception(f"Failed to initialize ChromaDB after reset: {second_error}") from second_error

# Load embedding model once at module initialization (cached)
# Using the same model to match existing embeddings (768 dimensions)
embedding_model = SentenceTransformer("sentence-transformers/multi-qa-mpnet-base-dot-v1")

# Initialize ChromaDB
chroma_client, collection = init_chroma_db()

# Cache embedding computation for frequently accessed queries
@lru_cache(maxsize=500)
def _get_query_embedding(query: str):
    """Cache embeddings for repeated queries to avoid recomputation."""
    return embedding_model.encode([query])[0]

def chunk_text(text, chunk_size=1024, overlap=128):
    """Chunk text into meaningful segments for retrieval - optimized for speed."""
    if not text or not text.strip():
        return []
    
    splitter = RecursiveCharacterTextSplitter(
        chunk_size=chunk_size, 
        chunk_overlap=overlap,
        length_function=len,
        separators=["\n\n", "\n", ". ", " ", ""]
    )
    chunks = splitter.split_text(text)
    print(f"� Created {len(chunks)} chunks from {len(text)} characters")
    return chunks

def store_chunks_and_embeddings(chunks):
    """Store document chunks in ChromaDB with batch processing for speed."""
    if not chunks:
        print("⚠️ No chunks to store")
        return
    
    # Clear existing documents first
    try:
        collection.delete(where={})
    except:
        pass
    
    # Batch encode all chunks at once (much faster than one-by-one)
    print(f"🔄 Encoding {len(chunks)} chunks...")
    embeddings = embedding_model.encode(chunks, show_progress_bar=False, batch_size=32)
    
    # Batch insert into ChromaDB
    print(f"� Storing {len(chunks)} chunks in ChromaDB...")
    collection.add(
        ids=[f"doc_{i}" for i in range(len(chunks))],
        embeddings=embeddings.tolist(),
        documents=chunks
    )
    
    print(f"✅ Stored {len(chunks)} chunks successfully!")

def hybrid_search(query, top_k=5):
    """Retrieve relevant chunks using vector similarity - optimized without reranker."""
    if not query or not query.strip():
        return ["No query provided."]
    
    # Use cached embedding
    query_embedding = _get_query_embedding(query.strip())
    
    # Retrieve relevant chunks from ChromaDB
    results = collection.query(
        query_embeddings=[query_embedding.tolist()],
        n_results=min(top_k, 5)  # Limit results for speed
    )
    
    retrieved_chunks = results["documents"][0] if results["documents"] else []
    
    if not retrieved_chunks:
        return ["No relevant information found in the document."]
    
    # Return top chunks directly (skip slow reranking for speed)
    print(f"✅ Retrieved {len(retrieved_chunks)} chunks for query")
    return retrieved_chunks[:3]  # Return top 3

def check_chromadb_content():
    """Check if ChromaDB contains stored chunks."""
    stored_docs = collection.get()
    print("📌 Stored Document IDs:", stored_docs.get("ids", []))
    print("📄 Stored Documents (First 3):", stored_docs.get("documents", [])[:3]) 

if __name__ == "__main__":
    sample_text = """
                    But sadly, when self-awareness researchers finally had the chance to catch up,
                    they made many of the same mistakes the Mayan archeologists did, spending
                    years focused on surprisingly myopic details at the expense of bigger, more
                    important questions. The result? Piles of disjointed, often peripheral research that
                    no one even bothered trying to stitch together. So when I set out to summarize the
                    current state of scientific knowledge on self-awareness, I initially came up with
                    more questions than answers, starting with the most central question: What was
                    self-awareness, exactly?
                    """
    chunks = chunk_text(sample_text)
    store_chunks_and_embeddings(chunks)
    check_chromadb_content()
    results = hybrid_search("What is the document about?")
    print(f"Top matches: {results}")