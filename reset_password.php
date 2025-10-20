[<?php
// reset_password.php
include 'db.php';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $sql = "SELECT * FROM users WHERE email='$email'";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        // Dummy reset: generate new pass or send email
        $new_pass = rand(100000, 999999);
        $hashed = password_hash($new_pass, PASSWORD_BCRYPT);
        $update = "UPDATE users SET password='$hashed' WHERE email='$email'";
        $conn->query($update);
        // mail($email, "New Password", "Your new password is $new_pass");
        echo "<script>alert('New password sent to email (dummy: $new_pass)'); window.location.href = 'login.php';</script>";
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
    <title>Reset Password</title>
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
        <h2>Reset Password</h2>
        <form method="POST">
            <input type="email" name="email" placeholder="Email" required>
            <button type="submit">Reset</button>
        </form>
        <a href="login.php">Back to Log In</a>
    </div>
</body>
</html>
