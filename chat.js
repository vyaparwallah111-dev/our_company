function toggleChat() {
    const chatBox = document.getElementById('vwChatBox');
    if (chatBox) chatBox.classList.toggle('active');
}

function handleEnter(e) {
    if (e.key === 'Enter') sendMessage();
}

async function sendMessage() {
    const inputField = document.getElementById('vwChatInput');
    const message = inputField.value.trim();
    if (!message) return;

    const chatBody = document.getElementById('vwChatBody');
    const typingIndicator = document.getElementById('vwTyping');

    // 1. Show User Message
    chatBody.innerHTML += `<div class="msg user">${message}</div>`;
    inputField.value = '';
    chatBody.scrollTop = chatBody.scrollHeight;

    // 2. Show Typing Indicator
    typingIndicator.style.display = 'block';
    chatBody.scrollTop = chatBody.scrollHeight;

    try {
        const response = await fetch('chat.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                message: message,
                context: document.title // Page Context automatically sent
            })
        });

        const data = await response.json();
        let aiReply = data.reply;

        // 🚀 THE MAGIC: Replace the hidden text with an actual HTML WhatsApp Button
        if (aiReply.includes("||WA_BUTTON||")) {
            aiReply = aiReply.replace("||WA_BUTTON||", "<br><a href='https://wa.me/9187641492?text=Hi%20Vyapar%20Wallah,%20I%20need%20human%20assistance.' target='_blank' class='bot-wa-btn'><i class='fa-brands fa-whatsapp'></i> Chat on WhatsApp</a>");
        }

        // 4. Show Bot Reply
        typingIndicator.style.display = 'none';
        chatBody.innerHTML += `<div class="msg bot">${aiReply}</div>`;
        chatBody.scrollTop = chatBody.scrollHeight;

    } catch (error) {
        typingIndicator.style.display = 'none';
        chatBody.innerHTML += `<div class="msg bot">Sorry, network issue. <br><a href='https://wa.me/9187641492' target='_blank' class='bot-wa-btn'><i class='fa-brands fa-whatsapp'></i> Chat on WhatsApp</a></div>`;
        chatBody.scrollTop = chatBody.scrollHeight;
    }
}