// chat.js का कोड

function toggleChat() {
    const chatBox = document.getElementById('vwChatBox');
    if (chatBox) {
        chatBox.classList.toggle('active');
    }
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
    chatBody.scrollTop = chatBody.scrollHeight; // Scroll to bottom

    // 2. Show Typing Indicator
    typingIndicator.style.display = 'block';
    chatBody.scrollTop = chatBody.scrollHeight;

    try {
        // 3. Send data to PHP
        const response = await fetch('chat.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                message: message,
                context: document.title
            })
        });

        const data = await response.json();

        // 4. Show Bot Reply
        typingIndicator.style.display = 'none';
        chatBody.innerHTML += `<div class="msg bot">${data.reply}</div>`;
        chatBody.scrollTop = chatBody.scrollHeight;

    } catch (error) {
        typingIndicator.style.display = 'none';
        chatBody.innerHTML += `<div class="msg bot">Sorry, network issue. Please connect on WhatsApp!</div>`;
    }
}