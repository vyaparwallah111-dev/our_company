// ==========================================
// 🚀 VYAPAR WALLAH - CUSTOM PUSH NOTIFICATION
// ==========================================

document.addEventListener("DOMContentLoaded", function() {
    
    // 10 सेकंड (10,000 ms) का टाइमर
    setTimeout(() => {
        // चेक करें कि क्या यूजर ने पहले से नोटिफिकेशन Allow या Block तो नहीं कर रखा है
        if (Notification.permission === 'default') {
            showCustomPushPopup();
        }
    }, 10000);

});

// Custom Popup का UI बनाने का फंक्शन
function showCustomPushPopup() {
    // 1. CSS इंजेक्ट करना (ताकि डिज़ाइन काम करे)
    const style = document.createElement('style');
    style.innerHTML = `
        .custom-push-box {
            position: fixed;
            top: 20px;
            right: -400px; /* शुरुआत में छुपा रहेगा */
            width: 340px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 15px 40px rgba(0,0,0,0.2);
            z-index: 999999;
            transition: right 0.5s ease-in-out;
            padding: 20px;
            font-family: 'Poppins', sans-serif;
            border-left: 4px solid var(--accent-orange, #F37021);
        }
        .custom-push-box.show { right: 20px; }
        .push-header { display: flex; align-items: center; gap: 12px; margin-bottom: 10px; }
        .push-icon { width: 40px; height: 40px; background: #e8f0fe; color: var(--primary-blue, #0A4C95); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; }
        .push-title { font-weight: 800; font-size: 1.05rem; color: var(--primary-blue, #0A4C95); line-height: 1.2; }
        .push-desc { font-size: 0.85rem; color: #555; margin-bottom: 15px; line-height: 1.5; }
        .push-actions { display: flex; gap: 10px; }
        .push-btn { flex: 1; padding: 10px; border: none; border-radius: 6px; cursor: pointer; font-weight: 700; font-size: 0.9rem; transition: 0.3s; }
        .btn-allow { background: var(--primary-blue, #0A4C95); color: white; }
        .btn-allow:hover { background: var(--accent-orange, #F37021); transform: translateY(-2px); }
        .btn-deny { background: #f4f4f4; color: #555; }
        .btn-deny:hover { background: #e0e0e0; }

        /* 📱 Mobile Responsive */
        @media(max-width: 768px) {
            .custom-push-box {
                top: -200px;
                right: 5%;
                width: 90%;
                left: 5%;
                border-left: none;
                border-top: 4px solid var(--accent-orange, #F37021);
                transition: top 0.5s ease-in-out;
            }
            .custom-push-box.show { top: 20px; right: auto; }
        }
    `;
    document.head.appendChild(style);

    // 2. HTML इंजेक्ट करना
    const pushDiv = document.createElement('div');
    pushDiv.className = 'custom-push-box';
    pushDiv.id = 'vyaparPushBox';
    pushDiv.innerHTML = `
        <div class="push-header">
            <div class="push-icon"><i class="fa-solid fa-bell"></i></div>
            <div class="push-title">Want Free Growth Secrets? 🚀</div>
        </div>
        <div class="push-desc">Allow notifications to get exclusive marketing strategies, case studies, and surprise discounts directly!</div>
        <div class="push-actions">
            <button class="push-btn btn-deny" onclick="closePushPopup()">Maybe Later</button>
            <button class="push-btn btn-allow" onclick="triggerOneSignal()">Yes, I Want It!</button>
        </div>
    `;
    document.body.appendChild(pushDiv);

    // 3. बाउंस इफ़ेक्ट के साथ दिखाना
    setTimeout(() => {
        pushDiv.classList.add('show');
    }, 100);
}

// "Maybe Later" बटन का फंक्शन
function closePushPopup() {
    const box = document.getElementById('vyaparPushBox');
    if(box) {
        box.classList.remove('show');
        // इसे DOM से हटा दें
        setTimeout(() => box.remove(), 500); 
    }
}

// "Yes, I Want It!" बटन का फंक्शन
function triggerOneSignal() {
    closePushPopup(); // पहले अपना पॉपअप बंद करें
    
    // असली OneSignal का नेटिव पॉपअप ट्रिगर करें
    if (window.OneSignal) {
        window.OneSignal.push(function() {
            OneSignal.registerForPushNotifications();
        });
    } else {
        // अगर OneSignal लोड नहीं हुआ है तो ब्राउज़र का डिफ़ॉल्ट चलाएं
        Notification.requestPermission();
    }
}