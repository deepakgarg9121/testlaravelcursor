<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login - Facebook Clone</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f0f2f5;
            color: #1c1e21;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .container {
            display: flex;
            max-width: 1000px;
            width: 100%;
            padding: 20px;
            gap: 40px;
            align-items: center;
        }

        .brand-section {
            flex: 1;
            max-width: 500px;
        }

        .brand-logo {
            color: #1877f2;
            font-size: 60px;
            font-weight: 700;
            margin-bottom: 10px;
            line-height: 1;
        }

        .brand-tagline {
            font-size: 28px;
            line-height: 1.2;
            color: #1c1e21;
        }

        .login-section {
            flex: 1;
            max-width: 400px;
        }

        .login-card {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.1);
            padding: 20px;
            width: 100%;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-input {
            width: 100%;
            padding: 14px 16px;
            border: 1px solid #dddfe2;
            border-radius: 6px;
            font-size: 17px;
            outline: none;
            transition: border-color 0.2s;
        }

        .form-input:focus {
            border-color: #1877f2;
        }

        .login-btn {
            width: 100%;
            background: #1877f2;
            color: white;
            border: none;
            border-radius: 6px;
            padding: 12px 0;
            font-size: 20px;
            font-weight: 700;
            cursor: pointer;
            transition: background-color 0.2s;
        }

        .login-btn:hover {
            background: #166fe5;
        }

        .login-btn:disabled {
            background: #bcc0c4;
            cursor: not-allowed;
        }

        .forgot-password {
            text-align: center;
            margin: 15px 0;
        }

        .forgot-password a {
            color: #1877f2;
            text-decoration: none;
            font-size: 14px;
        }

        .forgot-password a:hover {
            text-decoration: underline;
        }

        .divider {
            height: 1px;
            background: #dadde1;
            margin: 20px 0;
        }

        .create-account-btn {
            width: 100%;
            background: #42b72a;
            color: white;
            border: none;
            border-radius: 6px;
            padding: 12px 0;
            font-size: 17px;
            font-weight: 700;
            cursor: pointer;
            transition: background-color 0.2s;
        }

        .create-account-btn:hover {
            background: #36a420;
        }

        .message {
            margin-top: 15px;
            padding: 10px;
            border-radius: 6px;
            text-align: center;
            font-size: 14px;
        }

        .message.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .message.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            text-align: center;
            padding: 20px;
            font-size: 12px;
            color: #65676b;
            background: #f0f2f5;
        }

        @media (max-width: 768px) {
            .container {
                flex-direction: column;
                text-align: center;
            }

            .brand-logo {
                font-size: 40px;
            }

            .brand-tagline {
                font-size: 20px;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="brand-section">
            <h1 class="brand-logo">facebook</h1>
            <p class="brand-tagline">Connect with friends and the world around you on Facebook.</p>
        </div>

        <div class="login-section">
            <div class="login-card">
                <form id="loginForm">
                    <div class="form-group">
                        <input type="text" id="username" name="username" class="form-input" placeholder="Username" required>
                    </div>

                    <div class="form-group">
                        <input type="password" id="password" name="password" class="form-input" placeholder="Password" required>
                    </div>

                    <button type="submit" class="login-btn" id="loginBtn">Log In</button>

                    <div class="forgot-password">
                        <a href="#" onclick="alert('Forgot password functionality not implemented')">Forgot Password?</a>
                    </div>

                    <div class="divider"></div>

                    <button type="button" class="create-account-btn" onclick="alert('Create account functionality not implemented')">
                        Create New Account
                    </button>

                    <div id="message" class="message" style="display: none;"></div>
                </form>
            </div>
        </div>
    </div>

    <div class="footer">
        <p>This is a demo Facebook-like login page created with Laravel</p>
    </div>

    <script>
        document.getElementById('loginForm').addEventListener('submit', async function(e) {
            e.preventDefault();

            const username = document.getElementById('username').value;
            const password = document.getElementById('password').value;
            const loginBtn = document.getElementById('loginBtn');
            const messageDiv = document.getElementById('message');

            // Disable button and show loading
            loginBtn.disabled = true;
            loginBtn.textContent = 'Logging in...';
            messageDiv.style.display = 'none';

            try {
                const response = await fetch('/api/login', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    },
                    body: JSON.stringify({
                        username: username,
                        password: password
                    })
                });

                const data = await response.json();

                if (response.ok) {
                    messageDiv.className = 'message success';
                    messageDiv.textContent = `Welcome back, ${data.user.name}! Redirecting...`;
                    messageDiv.style.display = 'block';

                    // Store token and user data for future API calls
                    localStorage.setItem('auth_token', data.token);
                    localStorage.setItem('user_data', JSON.stringify(data.user));

                    // Clear form
                    document.getElementById('username').value = '';
                    document.getElementById('password').value = '';

                    // Redirect to welcome page after 2 seconds
                    setTimeout(() => {
                        window.location.href = '/welcome';
                    }, 2000);
                } else {
                    messageDiv.className = 'message error';
                    messageDiv.textContent = data.message || 'Login failed. Please try again.';
                    messageDiv.style.display = 'block';
                }
            } catch (error) {
                messageDiv.className = 'message error';
                messageDiv.textContent = 'Network error. Please check your connection and try again.';
                messageDiv.style.display = 'block';
            } finally {
                // Re-enable button
                loginBtn.disabled = false;
                loginBtn.textContent = 'Log In';
            }
        });
    </script>
</body>

</html>