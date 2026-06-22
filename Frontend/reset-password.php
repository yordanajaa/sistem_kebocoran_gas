<!-- PHP session removed -->
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password -</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-15px); }
        }
        
        @keyframes slideIn {
            from { transform: translateX(-100%); }
            to { transform: translateX(0); }
        }
        
        @keyframes progressFill {
            from { width: 0; }
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
            padding: 20px;
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
        
        .reset-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 24px;
            padding: 50px 40px;
            width: 480px;
            max-width: 100%;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            animation: fadeInUp 0.8s ease-out;
            position: relative;
            z-index: 10;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            animation: fadeInUp 0.8s ease-out 0.2s both;
        }
        
        .header h1 {
            font-size: 1.75rem;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 8px;
        }
        
        .header p {
            font-size: 0.875rem;
            color: #6b7280;
        }
        
        /* Progress Steps */
        .steps {
            display: flex;
            justify-content: space-between;
            margin-bottom: 40px;
            animation: fadeInUp 0.8s ease-out 0.3s both;
            position: relative;
        }
        
        .step {
            flex: 1;
            text-align: center;
            position: relative;
            z-index: 1;
        }
        
        .step::before {
            content: '';
            position: absolute;
            top: 15px;
            left: 50%;
            width: calc(100% - 32px);
            height: 2px;
            background: #e5e7eb;
            z-index: -1;
        }
        
        .step:last-child::before {
            display: none;
        }
        
        .step-circle {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: #e5e7eb;
            color: #9ca3af;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 0.875rem;
            font-weight: 600;
            margin-bottom: 8px;
            transition: all 0.3s;
        }
        
        .step.active .step-circle {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .step.completed .step-circle {
            background: #10b981;
            color: white;
        }
        
        .step.completed::before {
            background: #10b981;
        }
        
        .step-label {
            font-size: 0.75rem;
            color: #6b7280;
        }
        
        .section {
            display: none;
            animation: fadeInUp 0.6s ease-out;
        }
        
        .section.active {
            display: block;
        }
        
        .form-group {
            margin-bottom: 24px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-size: 0.875rem;
            font-weight: 500;
            color: #374151;
        }
        
        .form-group input {
            width: 100%;
            padding: 14px 16px;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: #fff;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
            transform: translateY(-2px);
        }
        
        .timer {
            text-align: center;
            font-size: 1.25rem;
            font-weight: 600;
            color: #667eea;
            margin: 20px 0;
        }
        
        .timer.warning {
            color: #ef4444;
        }
        
        .btn {
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
            position: relative;
            overflow: hidden;
        }
        
        .btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
            transition: left 0.5s;
        }
        
        .btn:hover::before {
            left: 100%;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
        }
        
        .btn.loading {
            pointer-events: none;
            opacity: 0.7;
        }
        
        .btn.loading::after {
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
        
        .btn-secondary {
            background: transparent;
            border: 2px solid #e5e7eb;
            color: #6b7280;
            margin-top: 12px;
        }
        
        .btn-secondary:hover {
            background: #f9fafb;
            border-color: #d1d5db;
            box-shadow: none;
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
        
        .back-link {
            text-align: center;
            margin-top: 24px;
            font-size: 0.875rem;
        }
        
        .back-link a {
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s;
        }
        
        .back-link a:hover {
            color: #764ba2;
        }
    </style>
</head>
<body>
    <!-- Floating Shapes -->
    <div class="shape shape1"></div>
    <div class="shape shape2"></div>
    <div class="shape shape3"></div>
    
    <div class="reset-container">
        <div class="header">
            <h1>Reset Password</h1>
            <p>Follow the steps to reset your password</p>
        </div>
        
        <!-- Progress Steps -->
        <div class="steps">
            <div class="step active" id="step-indicator-1">
                <div class="step-circle">1</div>
                <div class="step-label">Email</div>
            </div>
            <div class="step" id="step-indicator-2">
                <div class="step-circle">2</div>
                <div class="step-label">Verify</div>
            </div>
            <div class="step" id="step-indicator-3">
                <div class="step-circle">3</div>
                <div class="step-label">Reset</div>
            </div>
        </div>
        
        <div id="message-box"></div>
        
        <!-- Step 1: Email -->
        <div id="step1" class="section active">
            <div class="form-group">
                <label>Email Address</label>
                <input type="email" id="email" placeholder="you@example.com" required>
            </div>
            
            <button class="btn" onclick="sendResetOTP()" id="sendBtn">
                Send Reset Code
            </button>
            
            <div class="back-link">
                <a href="login.php">← Back to login</a>
            </div>
        </div>
        
        <!-- Step 2: OTP -->
        <div id="step2" class="section">
            <div class="form-group">
                <label>Verification Code</label>
                <input type="text" id="otp" placeholder="Enter 6-digit code" maxlength="6" required>
            </div>
            
            <div class="timer" id="timer"></div>
            
            <button class="btn" onclick="verifyResetOTP()" id="verifyBtn">
                Verify Code
            </button>
            <button class="btn btn-secondary" onclick="backToStep1()">
                Back
            </button>
        </div>
        
        <!-- Step 3: New Password -->
        <div id="step3" class="section">
            <div class="form-group">
                <label>New Password</label>
                <input type="password" id="new-password" placeholder="Min 6 characters" required>
            </div>
            
            <div class="form-group">
                <label>Confirm Password</label>
                <input type="password" id="confirm-password" placeholder="Re-enter password" required>
            </div>
            
            <button class="btn" onclick="resetPassword()" id="resetBtn">
                Reset Password
            </button>
        </div>
    </div>
    
    <script>
        let userEmail = '';
        let timerInterval = null;
        let expiryTime = null;
        
        function showMessage(text, type = 'info') {
            const box = document.getElementById('message-box');
            box.innerHTML = `<div class="message ${type}">${text}</div>`;
            setTimeout(() => box.innerHTML = '', 5000);
        }
        
        function showStep(step) {
            document.querySelectorAll('.section').forEach(s => s.classList.remove('active'));
            document.getElementById(`step${step}`).classList.add('active');
            
            // Update step indicators
            for (let i = 1; i <= 3; i++) {
                const indicator = document.getElementById(`step-indicator-${i}`);
                indicator.classList.remove('active', 'completed');
                if (i < step) indicator.classList.add('completed');
                if (i === step) indicator.classList.add('active');
            }
        }
        
        function startTimer(minutes) {
            expiryTime = Date.now() + (minutes * 60 * 1000);
            
            if (timerInterval) clearInterval(timerInterval);
            
            timerInterval = setInterval(() => {
                const remaining = expiryTime - Date.now();
                
                if (remaining <= 0) {
                    clearInterval(timerInterval);
                    document.getElementById('timer').innerHTML = '<span class="warning">Code Expired</span>';
                    return;
                }
                
                const mins = Math.floor(remaining / 60000);
                const secs = Math.floor((remaining % 60000) / 1000);
                const timerEl = document.getElementById('timer');
                timerEl.textContent = `${mins}:${secs.toString().padStart(2, '0')}`;
                
                if (remaining < 60000) {
                    timerEl.classList.add('warning');
                }
            }, 1000);
        }
        
        async function sendResetOTP() {
            const email = document.getElementById('email').value.trim();
            const btn = document.getElementById('sendBtn');
            
            if (!email) {
                showMessage('Please enter your email address', 'error');
                return;
            }
            
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                showMessage('Please enter a valid email address', 'error');
                return;
            }
            
            userEmail = email;
            btn.classList.add('loading');
            btn.textContent = '';
            
            try {
                const response = await fetch('http://localhost:3000/api/auth/send-otp', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ email })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showMessage('Reset code sent to your email', 'success');
                    showStep(2);
                    startTimer(5);
                } else {
                    showMessage(data.message || 'Failed to send code', 'error');
                }
            } catch (error) {
                showMessage('Connection error. Please try again.', 'error');
            } finally {
                btn.classList.remove('loading');
                btn.textContent = 'Send Reset Code';
            }
        }
        
        async function verifyResetOTP() {
            const otp = document.getElementById('otp').value.trim();
            const btn = document.getElementById('verifyBtn');
            
            if (!otp || otp.length !== 6) {
                showMessage('Please enter the 6-digit code', 'error');
                return;
            }
            
            btn.classList.add('loading');
            btn.textContent = '';
            
            try {
                const response = await fetch('http://localhost:3000/api/auth/verify-otp', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ email: userEmail, otp })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showMessage('Code verified successfully', 'success');
                    if (timerInterval) clearInterval(timerInterval);
                    showStep(3);
                } else {
                    showMessage(data.message || 'Invalid code', 'error');
                }
            } catch (error) {
                showMessage('Connection error. Please try again.', 'error');
            } finally {
                btn.classList.remove('loading');
                btn.textContent = 'Verify Code';
            }
        }
        
        async function resetPassword() {
            const newPassword = document.getElementById('new-password').value;
            const confirmPassword = document.getElementById('confirm-password').value;
            const btn = document.getElementById('resetBtn');
            
            if (!newPassword || !confirmPassword) {
                showMessage('Please fill in all fields', 'error');
                return;
            }
            
            if (newPassword.length < 6) {
                showMessage('Password must be at least 6 characters', 'error');
                return;
            }
            
            if (newPassword !== confirmPassword) {
                showMessage('Passwords do not match', 'error');
                return;
            }
            
            btn.classList.add('loading');
            btn.textContent = '';
            
            try {
                const response = await fetch('http://localhost:3000/api/auth/reset-password', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        email: userEmail,
                        password: newPassword
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showMessage('Password reset successful! Redirecting...', 'success');
                    setTimeout(() => {
                        window.location.href = 'login.php';
                    }, 2000);
                } else {
                    showMessage(data.message || 'Failed to reset password', 'error');
                    btn.classList.remove('loading');
                    btn.textContent = 'Reset Password';
                }
            } catch (error) {
                showMessage('Connection error. Please try again.', 'error');
                btn.classList.remove('loading');
                btn.textContent = 'Reset Password';
            }
        }
        
        function backToStep1() {
            showStep(1);
            document.getElementById('otp').value = '';
            if (timerInterval) clearInterval(timerInterval);
        }
    </script>
</body>
</html>
