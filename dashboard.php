<?php
// dashboard.php
include 'db.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "<script>window.location.href = 'login.php';</script>";
    exit;
}

// Fetch user balance and recent transactions
$user_id = $_SESSION['user_id'];
$sql_balance = "SELECT balance FROM users WHERE id=$user_id";
$result_balance = $conn->query($sql_balance);
if ($result_balance->num_rows > 0) {
    $balance = $result_balance->fetch_assoc()['balance'];
} else {
    echo "<script>alert('Error fetching balance'); window.location.href = 'login.php';</script>";
    exit;
}

$sql_trans = "SELECT t.*, u1.username as sender_name, u2.username as receiver_name 
              FROM transactions t 
              LEFT JOIN users u1 ON t.sender_id = u1.id 
              LEFT JOIN users u2 ON t.receiver_id = u2.id 
              WHERE t.sender_id=$user_id OR t.receiver_id=$user_id 
              ORDER BY t.created_at DESC LIMIT 10";
$result_trans = $conn->query($sql_trans);

// Handle add funds (dummy payment gateway simulation)
if (isset($_POST['add_funds'])) {
    $amount = floatval($_POST['amount']);
    if ($amount > 0) {
        $new_balance = $balance + $amount;
        $update = "UPDATE users SET balance=$new_balance WHERE id=$user_id";
        $trans_sql = "INSERT INTO transactions (sender_id, receiver_id, amount, transaction_type, status, description) 
                      VALUES ($user_id, $user_id, $amount, 'add_fund', 'completed', 'Added funds to wallet')";
        
        $conn->begin_transaction();
        try {
            $conn->query($update);
            $conn->query($trans_sql);
            $conn->commit();
            $_SESSION['balance'] = $new_balance;
            echo "<script>alert('Funds added successfully!'); window.location.href = 'dashboard.php';</script>";
        } catch (Exception $e) {
            $conn->rollback();
            echo "<script>alert('Error adding funds: {$e->getMessage()}');</script>";
        }
    } else {
        echo "<script>alert('Invalid amount');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background: linear-gradient(135deg, #0079bf, #0067a3);
            color: #fff;
            min-height: 100vh;
        }
        header {
            background: rgba(0,0,0,0.6);
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 5px rgba(0,0,0,0.3);
        }
        header h1 {
            font-size: 32px;
            margin: 0;
        }
        .balance {
            font-size: 24px;
            font-weight: bold;
        }
        .section {
            padding: 40px 20px;
            max-width: 1200px;
            margin: 0 auto;
        }
        .card {
            background: rgba(255,255,255,0.15);
            padding: 30px;
            border-radius: 15px;
            margin: 20px 0;
            text-align: center;
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255,255,255,0.3);
            box-shadow: 0 8px 20px rgba(0,0,0,0.3);
            transition: transform 0.3s ease;
        }
        .card:hover {
            transform: translateY(-5px);
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background: rgba(255,255,255,0.1);
            border-radius: 10px;
            overflow: hidden;
        }
        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid rgba(255,255,255,0.2);
        }
        th {
            background: rgba(0,0,0,0.3);
            font-size: 18px;
        }
        td {
            font-size: 16px;
        }
        .btn {
            background: #fff;
            color: #0079bf;
            padding: 12px 25px;
            text-decoration: none;
            border-radius: 8px;
            margin: 10px;
            display: inline-block;
            font-weight: bold;
            transition: all 0.3s ease;
        }
        .btn:hover {
            background: #f0f0f0;
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(0,0,0,0.2);
        }
        form {
            margin-top: 20px;
            display: flex;
            justify-content: center;
            gap: 10px;
        }
        input {
            padding: 12px;
            border-radius: 8px;
            border: none;
            font-size: 16px;
            background: rgba(255,255,255,0.9);
            width: 150px;
        }
        input:focus {
            outline: none;
            background: #fff;
        }
        button {
            background: #fff;
            color: #0079bf;
            padding: 12px 25px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        button:hover {
            background: #f0f0f0;
            transform: translateY(-2px);
        }
        @media (max-width: 768px) {
            .section {
                padding: 20px;
            }
            .card {
                margin: 10px;
            }
            table, th, td {
                font-size: 14px;
            }
            header h1 {
                font-size: 24px;
            }
            .balance {
                font-size: 20px;
            }
            form {
                flex-direction: column;
                align-items: center;
            }
            input, button {
                width: 100%;
                max-width: 300px;
            }
        }
    </style>
</head>
<body>
    <header>
        <h1>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></h1>
        <div class="balance">Balance: $<?php echo number_format($balance, 2); ?></div>
        <a href="#" class="btn" onclick="logout()">Log Out</a>
    </header>
    <section class="section">
        <div class="card">
            <h2>Add Funds (Dummy)</h2>
            <form method="POST">
                <input type="number" name="amount" placeholder="Amount" min="0.01" step="0.01" required>
                <button type="submit" name="add_funds">Add Funds</button>
            </form>
        </div>
        <div class="card">
            <h2>Recent Transactions</h2>
            <table>
                <tr>
                    <th>Date</th>
                    <th>Type</th>
                    <th>Amount</th>
                    <th>From/To</th>
                    <th>Status</th>
                </tr>
                <?php while ($row = $result_trans->fetch_assoc()) { ?>
                    <tr>
                        <td><?php echo $row['created_at']; ?></td>
                        <td><?php echo ucfirst($row['transaction_type']); ?></td>
                        <td>$<?php echo number_format($row['amount'], 2); ?></td>
                        <td>
                            <?php 
                            if ($row['transaction_type'] == 'send') {
                                echo htmlspecialchars($row['receiver_name']);
                            } elseif ($row['transaction_type'] == 'receive') {
                                echo htmlspecialchars($row['sender_name']);
                            } else {
                                echo 'Self';
                            }
                            ?>
                        </td>
                        <td><?php echo ucfirst($row['status']); ?></td>
                    </tr>
                <?php } ?>
            </table>
        </div>
        <a href="send_money.php" class="btn">Send Money</a>
    </section>
    <script>
        function logout() {
            // In production, destroy session via PHP
            window.location.href = 'login.php';
        }
    </script>
</body>
</html>
