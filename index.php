<?php
// index.php (Homepage)
session_start();
if (isset($_SESSION['user_id'])) {
    echo "<script>window.location.href = 'dashboard.php';</script>";
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PayPal Clone - Home</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 0; background: linear-gradient(135deg, #0079bf, #0067a3); color: #fff; }
        header { background: rgba(0,0,0,0.5); padding: 20px; text-align: center; }
        .hero { text-align: center; padding: 100px 20px; }
        .hero h1 { font-size: 48px; margin-bottom: 20px; text-shadow: 2px 2px 4px rgba(0,0,0,0.5); }
        .hero p { font-size: 24px; margin-bottom: 40px; }
        .btn { background: #fff; color: #0079bf; padding: 15px 30px; text-decoration: none; border-radius: 5px; font-weight: bold; transition: all 0.3s; box-shadow: 0 4px 8px rgba(0,0,0,0.2); }
        .btn:hover { background: #f0f0f0; transform: translateY(-2px); }
        .features { display: flex; justify-content: space-around; padding: 50px 20px; }
        .feature { background: rgba(255,255,255,0.1); padding: 30px; border-radius: 10px; width: 30%; text-align: center; backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.2); }
        .feature h2 { font-size: 28px; }
        .feature p { font-size: 18px; }
        footer { background: rgba(0,0,0,0.5); padding: 20px; text-align: center; font-size: 14px; }
        @media (max-width: 768px) { .features { flex-direction: column; } .feature { width: 90%; margin: 20px auto; } }
    </style>
</head>
<body>
    <header>
        <h1>Welcome to PayPal Clone</h1>
    </header>
    <section class="hero">
        <h1>Secure Digital Transactions</h1>
        <p>Send, receive, and manage your money easily and securely.</p>
        <a href="signup.php" class="btn">Sign Up</a>
        <a href="login.php" class="btn" style="margin-left: 20px;">Log In</a>
    </section>
    <section class="features">
        <div class="feature">
            <h2>Send & Receive Money</h2>
            <p>Transfer funds instantly to friends and family.</p>
        </div>
        <div class="feature">
            <h2>Wallet Management</h2>
            <p>Store and manage your balance securely.</p>
        </div>
        <div class="feature">
            <h2>Transaction History</h2>
            <p>Track all your payments and receipts.</p>
        </div>
    </section>
    <footer>
        &copy; 2025 PayPal Clone. All rights reserved.
    </footer>
</body>
</html>
