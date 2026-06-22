
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - IOT</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }
        
        @keyframes shimmer {
            0% { background-position: -1000px 0; }
            100% { background-position: 1000px 0; }
        }
        
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
        
        @keyframes slideIn {
            from { transform: translateX(-100%); }
            to { transform: translateX(0); }
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            position: relative;
            overflow: hidden;
        }
        
        /* Floating Shapes */
        .shape {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
            animation: float 6s ease-in-out infinite;
        }
        
        .shape1 { width: 80px; height: 80px; top: 10%; left: 10%; animation-delay: 0s; }
        .shape2 { width: 120px; height: 120px; top: 70%; left: 80%; animation-delay: 2s; }
        .shape3 { width: 60px; height: 60px; top: 40%; left: 70%; animation-delay: 4s; }
        .shape4 { width: 100px; height: 100px; top: 60%; left: 20%; animation-delay: 1s; }
        
        .login-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 24px;
            padding: 50px 40px;
            width: 440px;
            max-width: 90%;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            animation: fadeInUp 0.8s ease-out;
            position: relative;
            z-index: 10;
        }
        
        .logo {
            text-align: center;
            margin-bottom: 40px;
            animation: fadeInUp 0.8s ease-out 0.2s both;
        }
        
        .logo h1 {
            font-size: 2rem;
            font-weight: 700;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 8px;
        }
        
        .logo p {
            font-size: 0.9rem;
            color: #6b7280;
        }
        
        .form-group {
            margin-bottom: 24px;
            animation: fadeInUp 0.8s ease-out 0.4s both;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-size: 0.875rem;
            font-weight: 500;
            color: #374151;
        }
        
        .input-wrapper {
            position: relative;
        }
        
        .input-wrapper input {
            width: 100%;
            padding: 14px 16px;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: #fff;
        }
        
        .input-wrapper input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
            transform: translateY(-2px);
        }
        
        .input-wrapper input::placeholder {
            color: #9ca3af;
        }
        
        .btn-login {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 8px;
            animation: fadeInUp 0.8s ease-out 0.6s both;
            position: relative;
            overflow: hidden;
        }
        
        .btn-login::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
            transition: left 0.5s;
        }
        
        .btn-login:hover::before {
            left: 100%;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
        }
        
        .btn-login:active {
            transform: translateY(0);
        }
        
        .message {
            padding: 14px 16px;
            border-radius: 12px;
            margin-bottom: 20px;
            font-size: 0.875rem;
            animation: slideIn 0.5s ease-out;
        }
        
        .message.error {
            background: #fee2e2;
            color: #dc2626;
            border-left: 4px solid #dc2626;
        }
        
        .message.success {
            background: #d1fae5;
            color: #059669;
            border-left: 4px solid #059669;
        }
        
        .links {
            text-align: center;
            margin-top: 24px;
            animation: fadeInUp 0.8s ease-out 0.8s both;
        }
        
        .links a {
            color: #667eea;
            text-decoration: none;
            font-size: 0.875rem;
            font-weight: 500;
            transition: all 0.3s;
            position: relative;
        }
        
        .links a::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 0;
            height: 2px;
            background: #667eea;
            transition: width 0.3s;
        }
        
        .links a:hover::after {
            width: 100%;
        }
        
        .links a:hover {
            color: #764ba2;
        }
        
        .divider {
            margin: 20px 0;
            text-align: center;
            color: #9ca3af;
            font-size: 0.875rem;
        }
        
        /* Loading Animation */
        .btn-login.loading {
            pointer-events: none;
            opacity: 0.7;
        }
        
        .btn-login.loading::after {
            content: '';
            position: absolute;
            width: 16px;
            height: 16px;
            top: 50%;
            left: 50%;
            margin-left: -8px;
            margin-top: -8px;
            border: 2px solid #ffffff;
            border-radius: 50%;
            border-top-color: transparent;
            animation: spin 0.6s linear infinite;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <!-- Floating Shapes -->
    <div class="shape shape1"></div>
    <div class="shape shape2"></div>
    <div class="shape shape3"></div>
    <div class="shape shape4"></div>
    
    <div class="login-container">
        <div class="logo">
            <h1>LOGIN IOT</h1>
            <p>Welcome back! Please login to continue</p>
        </div>
        
        <div id="message-box"></div>
        
        <form onsubmit="login(event)">
            <div class="form-group">
                <label>Email Address</label>
                <div class="input-wrapper">
                    <input type="email" id="email" placeholder="you@example.com" required>
                </div>
            </div>
            
            <div class="form-group">
                <label>Password</label>
                <div class="input-wrapper">
                    <input type="password" id="password" placeholder="Enter your password" required>
                </div>
            </div>
            
            <button type="submit" class="btn-login" id="loginBtn">
                Sign In
            </button>
        </form>
        
        <div class="links">
            <a href="reset-password.php">Forgot password?</a>
        </div>
    </div>
    
    <script>
        // Check if already logged in
        if (localStorage.getItem('token')) {
            window.location.href = 'dashboard.php';
        }

        function showMessage(text, type = 'info') {
            const box = document.getElementById('message-box');
            box.innerHTML = `<div class="message ${type}">${text}</div>`;
            setTimeout(() => box.innerHTML = '', 5000);
        }
        
        async function login(e) {
            e.preventDefault();
            
            const email = document.getElementById('email').value.trim();
            const password = document.getElementById('password').value;
            const btn = document.getElementById('loginBtn');
            
            if (!email || !password) {
                showMessage('Please fill in all fields', 'error');
                return;
            }
            
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                showMessage('Please enter a valid email address', 'error');
                return;
            }
            
            btn.classList.add('loading');
            btn.textContent = '';
            
            try {
                const response = await fetch('https://sistemkebocorangas-production.up.railway.app/api/auth/login', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ 
                        email: email,
                        password: password
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    localStorage.setItem('token', data.token);
                    localStorage.setItem('userEmail', data.email || 'User');
                    showMessage('Login successful! Redirecting...', 'success');
                    setTimeout(() => {
                        window.location.href = 'dashboard.php';
                    }, 1000);
                } else {
                    btn.classList.remove('loading');
                    btn.textContent = 'Sign In';
                    showMessage(data.message || 'Invalid email or password', 'error');
                }
            } catch (error) {
                btn.classList.remove('loading');
                btn.textContent = 'Sign In';
                showMessage('Connection error. Please try again.', 'error');
            }
        }
    </script>
</body>
</html>
