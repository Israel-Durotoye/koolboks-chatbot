import streamlit as st
import requests
import os
import uuid
from datetime import datetime

# Page Configuration (must be the first Streamlit call)
st.set_page_config(
    page_title="Koolboks Chat Assistant",
    layout="wide",
    initial_sidebar_state="expanded",
    menu_items={
        "About": "Koolboks Chat Assistant developed by Israel Durotoye"
    }
)

# API Configuration
API_URL = "http://localhost:8000"

# Custom CSS
st.markdown(
    """
    <style>
    .stApp {
        max-width: 1200px;
        margin: 0 auto;
    }
    .chat-message {
        padding: 1.5rem;
        border-radius: 0.5rem;
        margin-bottom: 1rem;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    .user-message {
        background-color: #f0f2f6;
    }
    .assistant-message {
        background-color: #e8f0fe;
    }
    .message-timestamp {
        font-size: 0.8rem;
        color: #666;
        margin-bottom: 0.5rem;
    }
    .citation-link {
        font-size: 0.9rem;
        color: #0066cc;
        text-decoration: underline;
        cursor: pointer;
    }
    .document-status {
        padding: 1rem;
        border-radius: 0.5rem;
        margin: 1rem 0;
        background-color: #f8f9fa;
        border: 1px solid #dee2e6;
    }
    </style>
    """,
    unsafe_allow_html=True,
)

# Initialize session state variables
if "session_id" not in st.session_state:
    st.session_state.session_id = str(uuid.uuid4())
if "messages" not in st.session_state:
    st.session_state.messages = []
if "document_loaded" not in st.session_state:
    st.session_state.document_loaded = False
if "chat_history" not in st.session_state:
    st.session_state.chat_history = []
if "document_info" not in st.session_state:
    st.session_state.document_info = {
        "name": None,
        "upload_time": None,
        "chunks": 0
    }
if "show_settings" not in st.session_state:
    st.session_state.show_settings = False
if "temperature" not in st.session_state:
    st.session_state.temperature = 0.7
if "max_tokens" not in st.session_state:
    st.session_state.max_tokens = 500
if "show_citations" not in st.session_state:
    st.session_state.show_citations = True
if "show_lead_form" not in st.session_state:
    st.session_state.show_lead_form = False
if "lead_captured" not in st.session_state:
    st.session_state.lead_captured = False

# Sidebar for document upload and settings
with st.sidebar:
    st.title("📚 Knowledge Base")
    
    # Document upload section
    uploaded_file = st.file_uploader(
        "Upload your document",
        type=["pdf"],
        help="Upload a PDF document to start chatting about its contents",
        key="doc_uploader"
    )

    if uploaded_file and not st.session_state.document_loaded:
        file_path = os.path.join("uploads", uploaded_file.name)
        os.makedirs("uploads", exist_ok=True)

        with st.spinner("🔄 Processing document... Please wait."):
            try:
                with open(file_path, "wb") as f:
                    f.write(uploaded_file.getbuffer())

                # Send to API for processing
                with open(file_path, "rb") as f:
                    response = requests.post(
                        f"{API_URL}/upload/",
                        files={"file": f},
                        timeout=30,
                    )

                if response.status_code == 200:
                    result = response.json()
                    st.session_state.document_info = {
                        "name": uploaded_file.name,
                        "upload_time": datetime.now().strftime("%Y-%m-%d %H:%M:%S"),
                        "chunks": result.get("chunks", 0),
                    }
                    st.session_state.document_loaded = True
                    st.session_state.messages = []
                    st.session_state.chat_history = []
                    st.success("✨ Document processed successfully!")

                    # Display document info
                    st.markdown("### Document Information")
                    st.markdown(
                        f"""
                        - **Name**: {uploaded_file.name}
                        - **Uploaded**: {st.session_state.document_info['upload_time']}
                        - **Chunks**: {st.session_state.document_info['chunks']}
                        """
                    )
                else:
                    st.error(f"❌ Error processing document: {response.text}")
            except requests.exceptions.Timeout:
                st.error("⚠️ Request timed out. The document might be too large.")
            except Exception as e:
                st.error(f"❌ Error: {str(e)}")
            finally:
                if os.path.exists(file_path):
                    os.remove(file_path)

    # Settings section
    st.markdown("---")
    st.markdown("### ⚙️ Settings")
    
    # Chat settings
    with st.expander("Chat Settings", expanded=False):
        st.slider("Response Temperature", 0.0, 1.0, 0.7, 0.1, key="temperature")
        st.number_input("Max Tokens", 100, 2000, 500, 50, key="max_tokens")
        st.checkbox("Show citations", value=True, key="show_citations")
    
    # Lead capture section
    st.markdown("---")
    st.markdown("### 💼 Get in Touch")
    
    if not st.session_state.lead_captured:
        if st.button("📞 Request Callback", use_container_width=True):
            st.session_state.show_lead_form = True
        
        if st.session_state.show_lead_form:
            with st.form("lead_form"):
                st.markdown("**Share your details and we'll contact you:**")
                name = st.text_input("Full Name *", placeholder="John Doe")
                email = st.text_input("Email *", placeholder="john@example.com")
                phone = st.text_input("Phone Number *", placeholder="+234 800 000 0000")
                message = st.text_area("Message (optional)", placeholder="I'm interested in...")
                
                # Extract products mentioned in chat
                interested_products = []
                for msg in st.session_state.messages:
                    if msg["role"] == "user":
                        content = msg["content"].lower()
                        if any(word in content for word in ["freezer", "fridge", "refrigerat"]):
                            interested_products.append("Solar Freezers")
                            break
                
                submitted = st.form_submit_button("Submit", use_container_width=True)
                
                if submitted:
                    if name and email and phone:
                        try:
                            response = requests.post(
                                f"{API_URL}/capture-lead/",
                                json={
                                    "name": name,
                                    "email": email,
                                    "phone": phone,
                                    "message": message,
                                    "interested_products": interested_products,
                                    "session_id": st.session_state.session_id,
                                    "chat_history": [
                                        {"role": m["role"], "content": m["content"], "timestamp": m.get("timestamp", "")}
                                        for m in st.session_state.messages[-10:]
                                    ]
                                },
                                timeout=30  # Increased timeout
                            )
                            
                            if response.status_code == 200:
                                st.success("✅ Thank you! We'll contact you shortly.")
                                st.session_state.lead_captured = True
                                st.session_state.show_lead_form = False
                                st.rerun()
                            else:
                                st.error("Failed to submit. Please try again.")
                        except Exception as e:
                            st.error(f"Error: {str(e)}")
                    else:
                        st.warning("Please fill in all required fields.")
    else:
        st.success("✅ We have your details. Our team will reach out soon!")
        if st.button("Submit Another Request", use_container_width=True):
            st.session_state.lead_captured = False
            st.session_state.show_lead_form = True
            st.rerun()

# Main chat interface
st.title("💬 AI Document Assistant")

# Document status bar
if st.session_state.document_loaded:
    st.markdown(
        f"""
        <div class="document-status">
            📄 Current document: <strong>{st.session_state.document_info['name']}</strong> 
            | Uploaded: {st.session_state.document_info['upload_time']}
            | Chunks: {st.session_state.document_info['chunks']}
        </div>
        """,
        unsafe_allow_html=True
    )

# Display chat messages with enhanced UI
for message in st.session_state.messages:
    with st.chat_message(message["role"]):
        # Add timestamp
        st.markdown(
            f"<div class='message-timestamp'>{message.get('timestamp', 'No timestamp')}</div>",
            unsafe_allow_html=True
        )
        
        # Display message content
        st.markdown(message["content"])
        
        # Show context and citations for assistant messages
        if "context" in message and message["role"] == "assistant":
            with st.expander("📑 View Sources & Context"):
                st.info(message["context"])
                
                # Add copy button for citations
                if st.button("📋 Copy Citations", key=f"copy_{message.get('timestamp', '')}"):
                    st.toast("Citations copied to clipboard! ✨")

# Chat input with enhanced error handling
if prompt := st.chat_input("Ask me about Koolboks products...", key="chat_input"):
    # Add user message to chat
    timestamp = datetime.now().strftime("%Y-%m-%d %H:%M:%S")
    st.session_state.messages.append({
        "role": "user",
        "content": prompt,
        "timestamp": timestamp
    })
    
    with st.chat_message("user"):
        st.markdown(f"<div class='message-timestamp'>{timestamp}</div>", unsafe_allow_html=True)
        st.markdown(prompt)

        try:
            # Send request to API with chat history and settings
            with st.chat_message("assistant"):
                with st.spinner("🤔 Thinking..."):
                    response = requests.post(
                        f"{API_URL}/chat/",
                        json={
                            "query": prompt,
                            "session_id": st.session_state.session_id,
                            "chat_history": st.session_state.chat_history,
                            "settings": {
                                "temperature": st.session_state.temperature,
                                "max_tokens": st.session_state.max_tokens
                            }
                        },
                        timeout=60  # Increased timeout to 60 seconds
                    )

                    if response.status_code == 200:
                        result = response.json()
                        answer = result["response"]
                        context = result.get("context", "No source context available")
                        timestamp = datetime.now().strftime("%Y-%m-%d %H:%M:%S")
                        
                        # Update chat history (limit to last 10 messages for performance)
                        st.session_state.chat_history.append({
                            "user": prompt,
                            "assistant": answer
                        })
                        if len(st.session_state.chat_history) > 10:
                            st.session_state.chat_history = st.session_state.chat_history[-10:]
                        
                        # Add assistant response to messages
                        st.session_state.messages.append({
                            "role": "assistant",
                            "content": answer,
                            "context": context,
                            "timestamp": timestamp
                        })
                        
                        # Display the response with enhanced UI
                        st.markdown(f"<div class='message-timestamp'>{timestamp}</div>", unsafe_allow_html=True)
                        st.markdown(answer)
                        
                        # Show context with better formatting
                        if st.session_state.show_citations:
                            with st.expander("📑 Source Context", expanded=False):
                                sources = context.split("\n\n")
                                for idx, source in enumerate(sources[1:], 1):  # Skip the "Sources:" header
                                    st.markdown(f"**Source {idx}**")
                                    st.info(source.strip())
                    else:
                        st.error(f"❌ Failed to get response: {response.text}")
        
        except requests.exceptions.Timeout:
            st.error("⚠️ Request timed out. Please try again.")
        except requests.exceptions.ConnectionError:
            st.error("🔌 Cannot connect to the server. Please make sure it's running!")
        except Exception as e:
            st.error(f"❌ An error occurred: {str(e)}")

# Show helpful tips at the bottom
st.markdown("---")
with st.expander("💡 About Koolboks Chatbot"):
    st.markdown("""
    **Koolboks Product Specialist AI Assistant**
    
    Get instant answers about:
    - Solar-powered refrigeration products
    - Buy Now Pay Later (BNPL) financing options
    - Product specifications and pricing
    - Payment plans and installment options
    
    Upload product catalogs (optional) to enhance responses with detailed information.
    """)

# Footer with system status
col1, col2, col3 = st.columns(3)
with col1:
    st.markdown("Developed by **Israel Durotoye**")
with col2:
    status_message = "🟢 Chatbot Active"
    st.markdown(status_message)
with col3:
    st.markdown("Powered by Koolboks AI")