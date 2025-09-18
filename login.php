<?php
// login.php
include 'db.php';
session_start();
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];
    
    $sql = "SELECT * FROM users WHERE email='$email'";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['balance'] = $user['balance'];
            // For 2FA, assuming simple code to email (dummy, as mail() needs config)
            // Generate code
            $code = rand(100000, 999999);
            $expires = date('Y-m-d H:i:s', strtotime('+5 minutes'));
            $sql_code = "INSERT INTO twofa_codes (user_id, code, expires_at) VALUES ({$user['id']}, '$code', '$expires')";
            $conn->query($sql_code);
            // Dummy email: mail($user['email'], "2FA Code", "Your code is $code");
            echo "<script>alert('2FA Code sent to email (dummy: $code). Enter below.');</script>";
            // In real, redirect to 2fa.php, but for simplicity, add field
        } else {
            echo "<script>alert('Invalid password');</script>";
        }
    } else {
        echo "<script>alert('No user found');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log In</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 0; background: linear-gradient(135deg, #0079bf, #0067a3); color: #fff; display: flex; justify-content: center; align-items: center; height: 100vh; }
        .form-container { background: rgba(255,255,255,0.1); padding: 40px; border-radius: 10px; width: 400px; text-align: center; backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.2); box-shadow: 0 8px 16px rgba(0,0,0,0.3); }
        input { display: block; width: 100%; padding: 15px; margin: 10px 0; border: none; border-radius: 5px; font-size: 16px; }
        button { background: #fff; color: #0079bf; padding: 15px; border: none; border-radius: 5px; font-size: 16px; cursor: pointer; transition: all 0.3s; }
        button:hover { background: #f0f0f0; transform: translateY(-2px); }
        a { color: #fff; text-decoration: none; margin-top: 20px; display: block; }
        @media (max-width: 768px) { .form-container { width: 90%; padding: 20px; } }
    </style>
</head>
<body>
    <div class="form-container">
        <h2>Log In</h2>
        <form method="POST">
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Log In</button>
        </form>
        <a href="signup.php">Don't have an account? Sign Up</a>
        <a href="reset_password.php">Forgot Password?</a>
    </div>
    <script>
        // For 2FA, after login, prompt for code (simple, not production)
        // In real, separate page
        if (window.location.href.includes('login.php')) { // Dummy check
            // Assume after post, add input for code
        }
    </script>
</body>
</html>
