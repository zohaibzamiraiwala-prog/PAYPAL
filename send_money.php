<?php
// send_money.php
include 'db.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "<script>window.location.href = 'login.php';</script>";
    exit;
}

// Fetch current user's balance
$user_id = $_SESSION['user_id'];
$sql_balance = "SELECT balance, username FROM users WHERE id = ?";
$stmt_balance = $conn->prepare($sql_balance);
$stmt_balance->bind_param("i", $user_id);
$stmt_balance->execute();
$result_balance = $stmt_balance->get_result();
if ($result_balance->num_rows > 0) {
    $user_data = $result_balance->fetch_assoc();
    $balance = $user_data['balance'];
    $current_username = $user_data['username'];
} else {
    echo "<script>alert('Error fetching user data'); window.location.href = 'dashboard.php';</script>";
    exit;
}
$stmt_balance->close();

// Handle money transfer
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $recipient = trim(mysqli_real_escape_string($conn, $_POST['recipient']));
    $amount = floatval($_POST['amount']);
    
    // Input validation
    if (empty($recipient)) {
        echo "<script>alert('Please enter a recipient email or username');</script>";
    } elseif ($amount <= 0) {
        echo "<script>alert('Amount must be greater than zero');</script>";
    } elseif ($amount > $balance) {
        echo "<script>alert('Insufficient balance');</script>";
    } else {
        // Find recipient by email or username, excluding the current user
        $sql_rec = "SELECT id, balance, username FROM users WHERE (email = ? OR username = ?) AND id != ?";
        $stmt_rec = $conn->prepare($sql_rec);
        $stmt_rec->bind_param("ssi", $recipient, $recipient, $user_id);
        $stmt_rec->execute();
        $result_rec = $stmt_rec->get_result();
        
        if ($result_rec->num_rows > 0) {
            $recipient_data = $result_rec->fetch_assoc();
            $rec_id = $recipient_data['id'];
            $rec_username = $recipient_data['username'];
            
            // Update sender's balance
            $new_sender_bal = $balance - $amount;
            $sql_sender = "UPDATE users SET balance = ? WHERE id = ?";
            $stmt_sender = $conn->prepare($sql_sender);
            $stmt_sender->bind_param("di", $new_sender_bal, $user_id);
            
            // Update recipient's balance
            $rec_balance = $recipient_data['balance'];
            $new_rec_bal = $rec_balance + $amount;
            $sql_rec_update = "UPDATE users SET balance = ? WHERE id = ?";
            $stmt_rec_update = $conn->prepare($sql_rec_update);
            $stmt_rec_update->bind_param("di", $new_rec_bal, $rec_id);
            
            // Record transactions
            $desc_send = "Sent to $rec_username";
            $desc_rec = "Received from $current_username";
            $sql_trans_send = "INSERT INTO transactions (sender_id, receiver_id, amount, transaction_type, status, description) 
                              VALUES (?, ?, ?, 'send', 'completed', ?)";
            $stmt_trans_send = $conn->prepare($sql_trans_send);
            $stmt_trans_send->bind_param("iids", $user_id, $rec_id, $amount, $desc_send);
            
            $sql_trans_rec = "INSERT INTO transactions (sender_id, receiver_id, amount, transaction_type, status, description) 
                             VALUES (?, ?, ?, 'receive', 'completed', ?)";
            $stmt_trans_rec = $conn->prepare($sql_trans_rec);
            $stmt_trans_rec->bind_param("iids", $user_id, $rec_id, $amount, $desc_rec);
            
            // Execute transaction
            $conn->begin_transaction();
            try {
                $stmt_sender->execute();
                $stmt_rec_update->execute();
                $stmt_trans_send->execute();
                $stmt_trans_rec->execute();
                
                // Update session balance
                $_SESSION['balance'] = $new_sender_bal;
                
                $conn->commit();
                echo "<script>alert('Money sent successfully to $rec_username!'); window.location.href = 'dashboard.php';</script>";
            } catch (Exception $e) {
                $conn->rollback();
                echo "<script>alert('Transaction failed: {$e->getMessage()}');</script>";
            }
            
            $stmt_sender->close();
            $stmt_rec_update->close();
            $stmt_trans_send->close();
            $stmt_trans_rec->close();
        } else {
            echo "<script>alert('Recipient not found. Please check the email or username.');</script>";
        }
        $stmt_rec->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Send Money</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background: linear-gradient(135deg, #0079bf, #0067a3);
            color: #fff;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        .form-container {
            background: rgba(255,255,255,0.15);
            padding: 40px;
            border-radius: 15px;
            width: 100%;
            max-width: 450px;
            text-align: center;
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255,255,255,0.3);
            box-shadow: 0 8px 20px rgba(0,0,0,0.3);
            transition: transform 0.3s ease;
        }
        .form-container:hover {
            transform: translateY(-5px);
        }
        h2 {
            font-size: 32px;
            margin-bottom: 20px;
            text-shadow: 1px 1px 3px rgba(0,0,0,0.3);
        }
        p {
            font-size: 20px;
            margin-bottom: 20px;
            background: rgba(0,0,0,0.3);
            padding: 10px;
            border-radius: 8px;
        }
        input {
            display: block;
            width: 100%;
            padding: 15px;
            margin: 12px 0;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            background: rgba(255,255,255,0.9);
            box-sizing: border-box;
            transition: background 0.3s;
        }
        input:focus {
            outline: none;
            background: #fff;
        }
        button {
            background: #fff;
            color: #0079bf;
            padding: 15px 30px;
            border: none;
            border-radius: 8px;
            font-size: 18px;
            cursor: pointer;
            width: 100%;
            margin-top: 10px;
            transition: all 0.3s ease;
        }
        button:hover {
            background: #f0f0f0;
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(0,0,0,0.2);
        }
        a {
            color: #fff;
            text-decoration: none;
            margin-top: 20px;
            display: block;
            font-size: 16px;
            transition: color 0.3s;
        }
        a:hover {
            color: #f0f0f0;
        }
        @media (max-width: 768px) {
            .form-container {
                width: 90%;
                padding: 30px;
            }
            h2 {
                font-size: 28px;
            }
            input, button {
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h2>Send Money</h2>
        <p>Current Balance: $<?php echo number_format($balance, 2); ?></p>
        <form method="POST">
            <input type="text" name="recipient" placeholder="Recipient Email or Username" required>
            <input type="number" name="amount" placeholder="Amount" min="0.01" step="0.01" required>
            <button type="submit">Send Money</button>
        </form>
        <a href="dashboard.php">Back to Dashboard</a>
    </div>
</body>
</html>
