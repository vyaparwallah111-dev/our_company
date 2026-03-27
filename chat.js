// ==========================================
// 🚀 VYAPAR AI - ADVANCED CHATBOT LOGIC
// ==========================================

function toggleChat() {
    const chatBox = document.getElementById('vwChatBox');
    if (chatBox) chatBox.classList.toggle('active');
}

function handleEnter(e) {
    if (e.key === 'Enter') sendMessage();
}

function sendSuggestion(text) {
    document.getElementById('vwChatInput').value = text;
    sendMessage();
}

async function sendMessage() {
    const inputField = document.getElementById('vwChatInput');
    const message = inputField.value.trim();
    if (!message) return;

    const chatBody = document.getElementById('vwChatBody');
    const typingIndicator = document.getElementById('vwTyping');

    // 1. पुराने सजेशन चिप्स को हाईड करें
    const oldChips = chatBody.querySelectorAll('.vw-suggestions');
    oldChips.forEach(chip => chip.style.display = 'none');

    // 2. यूज़र का मेसेज दिखाएँ
    chatBody.innerHTML += `<div class="msg user">${message}</div>`;
    inputField.value = '';

    typingIndicator.style.display = 'block';
    chatBody.scrollTop = chatBody.scrollHeight;

    try {
        const response = await fetch('/chat.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ message: message, context: document.title })
        });

        if (!response.ok) throw new Error("Network Issue");

        const data = await response.json();
        let aiReply = data.reply;
        let dynamicChipsHtml = '';

        // 🚀 A. Parse Dynamic Chips [CHIPS: ...]
        const chipMatch = aiReply.match(/\[CHIPS:\s*(.*?)\]/);
        if (chipMatch) {
            const chips = chipMatch[1].split('|').map(c => c.trim());
            aiReply = aiReply.replace(chipMatch[0], ''); // टेक्स्ट से टैग हटा दें

            dynamicChipsHtml = '<div class="vw-suggestions" style="display:flex; flex-wrap:wrap; gap:8px; margin-top:10px;">';
            chips.forEach(chip => {
                dynamicChipsHtml += `<button onclick="sendSuggestion('${chip}')" style="background:white; color:#0A4C95; border:1px solid #0A4C95; padding:6px 12px; border-radius:20px; font-size:0.85rem; cursor:pointer; font-weight:600; box-shadow:0 2px 5px rgba(0,0,0,0.05); transition:0.3s;" onmouseover="this.style.background='#0A4C95'; this.style.color='white'" onmouseout="this.style.background='white'; this.style.color='#0A4C95'">${chip}</button>`;
            });
            dynamicChipsHtml += '</div>';
        }

        // 📞 B. Parse Lead Form ||SHOW_FORM||
        if (aiReply.includes("||SHOW_FORM||")) {
            aiReply = aiReply.replace("||SHOW_FORM||", "");
            aiReply += `
            <div id="vwLeadFormBox" style="background:#f8fafc; padding:15px; border-radius:10px; margin-top:10px; border:1px solid #cbd5e1; box-shadow:0 4px 10px rgba(0,0,0,0.05);">
                <p style="margin:0 0 10px 0; font-size:0.9rem; font-weight:700; color:#0A4C95;">Book Free Strategy Call 🚀</p>
                <input type="text" id="leadName" placeholder="Your Name" style="width:100%; padding:10px; margin-bottom:8px; border:1px solid #cbd5e1; border-radius:5px; box-sizing:border-box; font-family:inherit; outline:none;">
                <input type="tel" id="leadPhone" placeholder="WhatsApp Number" style="width:100%; padding:10px; margin-bottom:12px; border:1px solid #cbd5e1; border-radius:5px; box-sizing:border-box; font-family:inherit; outline:none;">
                <button onclick="submitLead()" style="width:100%; background:#F37021; color:white; border:none; padding:12px; border-radius:5px; font-weight:bold; cursor:pointer; transition:0.3s; box-shadow:0 4px 10px rgba(243, 112, 33, 0.3);">Submit Details</button>
            </div>`;
        }

        // 🟢 C. Parse WhatsApp Fallback ||WA_BUTTON||
        if (aiReply.includes("||WA_BUTTON||")) {
            aiReply = aiReply.replace("||WA_BUTTON||", "<br><br><a href='https://wa.me/9187641492' target='_blank' style='display:inline-block; background:#25D366; color:white; padding:10px 15px; border-radius:8px; text-decoration:none; font-weight:700; font-size:0.9rem; box-shadow: 0 4px 10px rgba(37, 211, 102, 0.3);'>Chat on WhatsApp</a>");
        }

        typingIndicator.style.display = 'none';

        // 3. फाइनल मेसेज दिखाएँ
        chatBody.innerHTML += `<div class="msg bot">${aiReply} ${dynamicChipsHtml}</div>`;
        chatBody.scrollTop = chatBody.scrollHeight;

    } catch (error) {
        // Safe Fallback Server Error
        typingIndicator.style.display = 'none';
        chatBody.innerHTML += `<div class="msg bot">Network thoda slow hai. Kripya humse direct WhatsApp par judein! <br><br><a href='https://wa.me/9187641492' target='_blank' style='display:inline-block; background:#25D366; color:white; padding:10px 15px; border-radius:8px; text-decoration:none; font-weight:700; font-size:0.9rem;'>Chat on WhatsApp</a></div>`;
        chatBody.scrollTop = chatBody.scrollHeight;
    }
}

// ==========================================
// 📧 FORM SUBMIT FUNCTION (Send to PHP)
// ==========================================
async function submitLead() {
    const name = document.getElementById('leadName').value;
    const phone = document.getElementById('leadPhone').value;
    const formBox = document.getElementById('vwLeadFormBox');

    if (!name || !phone) { alert("Please enter both Name and WhatsApp Number."); return; }

    formBox.innerHTML = `<p style="color:#0A4C95; font-weight:bold; margin:0; text-align:center;">Sending details... ⏳</p>`;

    try {
        await fetch('/send_lead.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ name: name, phone: phone })
        });
        formBox.innerHTML = `<p style="color:#10b981; font-weight:bold; margin:0; text-align:center;">✅ Details Sent! Our expert will WhatsApp you in 5 minutes.</p>`;
    } catch (e) {
        formBox.innerHTML = `<p style="color:red; margin:0; text-align:center;">Error sending details. Please use the WhatsApp button.</p>`;
    }
}

// 🚀 Auto-Open Chatbot after 10 Seconds
setTimeout(() => {
    const chatBox = document.getElementById('vwChatBox');
    if (chatBox && !chatBox.classList.contains('active')) {
        chatBox.classList.add('active');
    }
}, 10000);