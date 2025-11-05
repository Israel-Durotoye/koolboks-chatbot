from typing import List, Dict, Optional
import os
from dotenv import load_dotenv
from openai import OpenAI

# Load environment variables once the module is imported
load_dotenv()

# Instantiate a reusable OpenAI client
_openai_client = OpenAI(api_key=os.getenv("OPENAI_API_KEY"))

def generate_chat_response(
    query: str,
    context: List[str],
    chat_history: Optional[List[Dict[str, str]]] = None,
    *,
    temperature: float = 0.7,
    max_tokens: int = 500,
    top_p: float = 0.95,
    model: str = "gpt-4o-mini",
) -> str:
    """Generate a chat response using the OpenAI API."""

    # Format context (optional - chatbot works fine without uploaded documents)
    if context and len(context) > 0 and context[0] != "No relevant information found in the document.":
        trimmed_context = context[:3]
        context_text = "\n\n".join(trimmed_context)
        context_message = f"Additional product catalog information from uploaded documents:\n\n{context_text}"
    else:
        context_message = "No additional documents uploaded. Use your core Koolboks product knowledge to assist customers."

    # Prepare messages for the chat
    messages = [
        {
            "role": "system",
            "content": """You are a Koolboks product specialist. Koolboks is Nigeria's leading clean-tech company providing solar-powered refrigeration with Buy Now Pay Later (BNPL) financing. You reduce food spoilage, energy poverty, and climate impact through eco-friendly freezers using zero-ozone-depletion refrigerants, USB charging, lighting, and ice battery technology providing up to 4 days cooling without sunlight.

CORE COMPANY INFO:
- Over 7,500 units sold across 21 countries
- Partners with Thermocool, Scanfrost, and Samsung
- Koolbuy retail platform with AI-powered inventory and logistics
- Microcredit bank with 10% deposits, daily payments from ₦1,500
- PowerFoot Pedestal converts regular freezers to solar power
- Scrap4New initiative trades old appliances for eco-friendly models

RESPONSE RULES:
- Keep responses 4-5 lines maximum
- Be smart and conversational, not robotic
- Show product features first, prices only when asked
- Always mention BNPL installment options with specific payment amounts
- Use bullet points for payment breakdowns
- Address users with "you" and "your"
- Welcome customers warmly before pitching

WRITING STYLE - MUST FOLLOW:
- Use clear, simple language with active voice
- Keep responses under 5 lines
- NO em dashes, semicolons, metaphors, or clichés
- AVOID: can, may, just, very, really, literally, actually, certainly, probably, basically, could, maybe, delve, embark, craft, imagine, realm, game-changer, unlock, discover, skyrocket, however, harness, exciting, groundbreaking, cutting-edge, remarkable, moreover, boost, powerful
- Be conversational and smart, not robotic

CONTACT: For details not in knowledge base, share +2348116402869 or WhatsApp link."""
        },
        {
            "role": "system",
            "content": context_message
        }
    ]

    # Add chat history if available
    if chat_history:
        for msg in chat_history[-5:]:  # Only use last 5 messages for context
            # Ensure each history entry has the required keys
            if msg.get("role") and msg.get("content"):
                messages.append({"role": msg["role"], "content": msg["content"]})

    # Add the current query
    messages.append({"role": "user", "content": query})

    try:
        # Generate response using the updated Chat Completions API
        response = _openai_client.chat.completions.create(
            model=model,
            messages=messages,
            temperature=temperature,
            max_tokens=max_tokens,
            top_p=top_p,
        )

        return response.choices[0].message.content.strip()
        
    except Exception as e:
        return f"I apologize, but I encountered an error: {str(e)}"

if __name__ == "__main__":
    # Test the function
    sample_context = ["This is a sample document chunk..."]
    sample_history = [
        {"role": "user", "content": "What is this document about?"},
        {"role": "assistant", "content": "The document appears to be a sample text."}
    ]
    print(generate_chat_response("Tell me more", sample_context, sample_history))