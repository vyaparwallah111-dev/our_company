// ==========================================
// 🚀 VYAPAR AI - CHATBOT LOGIC
// ==========================================

// 1. Open/Close Chat Box
function toggleChat() {
    const chatBox = document.getElementById('vwChatBox');
    if (chatBox) chatBox.classList.toggle('active');
}

// 2. Send on Enter Key
function handleEnter(e) {
    if (e.key === 'Enter') sendMessage();
}

// 3. 🚀 Handle Suggestion Chips Clicks
function sendSuggestion(text) {
    const inputField = document.getElementById('vwChatInput');
    inputField.value = text;
    sendMessage(); // Auto-send the message
}

// 4. Main Send Message Function
async function sendMessage() {
    const inputField = document.getElementById('vwChatInput');
    const message = inputField.value.trim();
    if (!message) return;

    const chatBody = document.getElementById('vwChatBody');
    const typingIndicator = document.getElementById('vwTyping');
    const suggestionsBox = document.getElementById('vwSuggestionsBox');

    // Hide suggestions once user sends any message
    if (suggestionsBox) suggestionsBox.style.display = 'none';

    // Show User Message
    chatBody.innerHTML += `<div class="msg user">${message}</div>`;
    inputField.value = '';
    chatBody.scrollTop = chatBody.scrollHeight;

    // Show Typing Indicator
    typingIndicator.style.display = 'block';
    chatBody.scrollTop = chatBody.scrollHeight;

    try {
        // 🚀 CRITICAL FIX: Absolute path '/chat.php' ensures it works from any page (Blog, Services, etc.)
        const response = await fetch('/chat.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                message: message,
                context: document.title // Sends page name to AI
            })
        });

        if (!response.ok) throw new Error("Network Issue");

        const data = await response.json();
        let aiReply = data.reply;

        // WhatsApp Button Formatter
        if (aiReply.includes("||WA_BUTTON||")) {
            aiReply = aiReply.replace("||WA_BUTTON||", "<br><br><a href='https://wa.me/9187641492' target='_blank' style='display:inline-block; background:#25D366; color:white; padding:10px 15px; border-radius:8px; text-decoration:none; font-weight:700; font-size:0.9rem; box-shadow: 0 4px 10px rgba(37, 211, 102, 0.3);'>Chat on WhatsApp</a>");
        }

        // Show Bot Reply
        typingIndicator.style.display = 'none';
        chatBody.innerHTML += `<div class="msg bot">${aiReply}</div>`;
        chatBody.scrollTop = chatBody.scrollHeight;

    } catch (error) {
        // Safe Fallback if PHP fails
        typingIndicator.style.display = 'none';
        chatBody.innerHTML += `<div class="msg bot">Network thoda slow hai. Kripya humse direct WhatsApp par judein! <br><br><a href='https://wa.me/9187641492' target='_blank' style='display:inline-block; background:#25D366; color:white; padding:10px 15px; border-radius:8px; text-decoration:none; font-weight:700; font-size:0.9rem;'>Chat on WhatsApp</a></div>`;
        chatBody.scrollTop = chatBody.scrollHeight;
    }
}