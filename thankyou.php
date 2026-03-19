<?php
    // URL से यूज़र का नाम और बिज़नेस का नाम लेना
    $name = isset($_GET['n']) ? htmlspecialchars($_GET['n']) : 'Valued Client';
    $business = isset($_GET['b']) ? htmlspecialchars($_GET['b']) : 'your business';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thank You | Vyapar Wallah</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>

    <style>
        :root {
            --primary-blue: #0A4C95;
            --accent-orange: #F37021;
            --bg-light: #f8fafc;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
        
        body { 
            background: linear-gradient(135deg, #e8f4fd 0%, #f8fafc 100%); 
            min-height: 100vh; 
            display: flex; 
            justify-content: center; 
            align-items: center; 
            text-align: center; 
            overflow: hidden;
            padding: 20px;
        }

        .thankyou-card {
            background: white;
            padding: 50px 40px;
            border-radius: 20px;
            box-shadow: 0 20px 50px rgba(10, 76, 149, 0.1);
            max-width: 600px;
            width: 100%;
            border-top: 6px solid var(--accent-orange);
            position: relative;
            z-index: 10;
            animation: popIn 0.8s cubic-bezier(0.68, -0.55, 0.27, 1.55) forwards;
            opacity: 0;
            transform: scale(0.8);
        }

        @keyframes popIn {
            to { opacity: 1; transform: scale(1); }
        }

        .icon-circle {
            width: 90px;
            height: 90px;
            background: #ebfbf5;
            color: #10b981;
            font-size: 3rem;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0 auto 25px;
            box-shadow: 0 10px 25px rgba(16, 185, 129, 0.15);
            animation: bounceIcon 2s infinite ease-in-out;
        }

        @keyframes bounceIcon {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }

        h1 { color: var(--primary-blue); font-size: 2.2rem; font-weight: 900; margin-bottom: 10px; line-height: 1.2; }
        h1 span { color: var(--accent-orange); }
        
        .user-details {
            background: #f8fafc;
            padding: 20px;
            border-radius: 12px;
            margin: 25px 0 15px;
            border: 1px dashed var(--primary-blue);
            font-size: 1.1rem;
            color: #333;
            line-height: 1.6;
        }
        .user-details strong { color: var(--primary-blue); font-size: 1.15rem; font-weight: 700;}

        p.msg { font-size: 1.05rem; color: #555; margin-bottom: 25px; font-weight: 500; }

        /* 🚀 NEW: DID YOU KNOW SECTION */
        .did-you-know {
            background: linear-gradient(135deg, #fff4ed 0%, #ffffff 100%);
            border-left: 4px solid var(--accent-orange);
            padding: 15px 20px;
            border-radius: 8px;
            margin: 0 0 30px 0;
            text-align: left;
            box-shadow: 0 5px 15px rgba(243, 112, 33, 0.05);
            animation: fadeIn 1s ease 1s forwards; /* Fades in after 1 sec */
            opacity: 0;
        }
        @keyframes fadeIn { to { opacity: 1; } }

        .did-you-know strong {
            color: var(--accent-orange);
            display: block;
            margin-bottom: 5px;
            font-size: 1.05rem;
            font-weight: 800;
        }
        .did-you-know p {
            color: #444;
            font-size: 0.95rem;
            line-height: 1.5;
            margin: 0;
        }

        .btn-home {
            background: var(--primary-blue);
            color: white;
            padding: 14px 35px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 700;
            font-size: 1.1rem;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            transition: 0.3s;
            box-shadow: 0 10px 20px rgba(10, 76, 149, 0.2);
        }
        .btn-home:hover {
            background: #083c75;
            box-shadow: 0 10px 20px rgba(10, 76, 149, 0.3);
            transform: translateY(-2px);
        }

        /* Animated Background Elements */
        .bg-shape { position: absolute; border-radius: 50%; filter: blur(60px); z-index: 1; opacity: 0.5; }
        .shape1 { width: 300px; height: 300px; background: var(--accent-orange); top: -100px; left: -100px; animation: float1 8s infinite alternate;}
        .shape2 { width: 400px; height: 400px; background: var(--primary-blue); bottom: -150px; right: -100px; animation: float2 10s infinite alternate;}

        @keyframes float1 { to { transform: translate(50px, 50px); } }
        @keyframes float2 { to { transform: translate(-50px, -50px); } }

        @media(max-width: 768px) {
            .thankyou-card { padding: 40px 25px; }
            h1 { font-size: 1.8rem; }
        }
    </style>
</head>
<body>

    <div class="bg-shape shape1"></div>
    <div class="bg-shape shape2"></div>

    <audio id="successSound" preload="auto">
        <source src="https://cdn.pixabay.com/download/audio/2021/08/04/audio_bb630cc098.mp3?filename=success-1-6297.mp3" type="audio/mpeg">
    </audio>

    <div class="thankyou-card">
        <div class="icon-circle"><i class="fa-solid fa-check"></i></div>
        
        <h1>Thank You for Choosing <br><span>Vyapar Wallah!</span></h1>
        
        <div class="user-details">
            Hi <strong><?php echo $name; ?></strong>,<br>
            We are excited to help <strong><?php echo $business; ?></strong> grow!
        </div>

        <p class="msg">Our team will connect with you shortly. 🚀</p>

        <div class="did-you-know">
            <strong><i class="fa-solid fa-lightbulb"></i> Did You Know?</strong>
            <p>Vyapar Wallah has helped over 200+ local businesses and 50+ clinics achieve up to 300% growth in their daily inquiries. We are thrilled to write your success story next!</p>
        </div>

        <a href="index.html" class="btn-home"><i class="fa-solid fa-arrow-left"></i> Back to Home</a>
    </div>

    <script>
        // Sound and Confetti
        window.onload = function() {
            let audio = document.getElementById("successSound");
            audio.volume = 0.4;
            let playPromise = audio.play();
            if (playPromise !== undefined) {
                playPromise.catch(error => { console.log("Autoplay blocked. User needs to interact first."); });
            }

            var duration = 3 * 1000;
            var animationEnd = Date.now() + duration;
            var defaults = { startVelocity: 30, spread: 360, ticks: 60, zIndex: 100 };

            function randomInRange(min, max) { return Math.random() * (max - min) + min; }

            var interval = setInterval(function() {
                var timeLeft = animationEnd - Date.now();
                if (timeLeft <= 0) { return clearInterval(interval); }
                var particleCount = 50 * (timeLeft / duration);
                
                confetti(Object.assign({}, defaults, { particleCount, origin: { x: randomInRange(0.1, 0.3), y: Math.random() - 0.2 } }));
                confetti(Object.assign({}, defaults, { particleCount, origin: { x: randomInRange(0.7, 0.9), y: Math.random() - 0.2 } }));
            }, 250);
        };
    </script>
</body>
</html>