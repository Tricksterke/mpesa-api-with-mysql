<?php
// Database connection details
$servername = "localhost";
$username = "globalqa_globalqa_userdb";
$password = "2jRsEEHVSULLpXmpSr2J";
$dbname = "globalqa_globalqa_userdb";

// Create database connection
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check payment status via AJAX
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['check_payment'])) {
    $phone = $_GET['phone'];

    // Format phone number to start with 254
    if (!preg_match('/^254/', $phone)) {
        $phone = '254' . ltrim($phone, '0');
    }

    // Query to fetch the latest payment within the last minute
    $stmt = $conn->prepare('SELECT amount, mpesa_receipt_number FROM payments WHERE phone_number = ? AND created_at >= NOW() - INTERVAL 1 MINUTE ORDER BY created_at DESC LIMIT 1');
    $stmt->bind_param('s', $phone);
    $stmt->execute();
    $stmt->bind_result($amount, $receiptNumber);
    $stmt->fetch();
    $stmt->close();
    $conn->close();

    if ($amount && $receiptNumber) {
        echo json_encode(['success' => true, 'amount' => $amount, 'receipt' => $receiptNumber]);
    } else {
        echo json_encode(['success' => false, 'message' => 'No recent payment found']);
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Check Payment Status</title>
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <style>
        .notification {
            display: none;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
        }
        .success {
            background-color: #28a745;
            color: white;
        }
        .error {
            background-color: #dc3545;
            color: white;
        }
    </style>
</head>
<body>
    <h1>Check Payment Status</h1>

    <form id="checkPaymentForm">
        <label for="phone">Phone Number:</label>
        <input type="text" id="phone" name="phone" placeholder="Enter phone number" required>
        <button type="submit">Check Payment</button>
    </form>

    <div id="notifications">
        <div id="notification-success" class="notification success hidden">Payment found!</div>
        <div id="notification-error" class="notification error hidden">An error occurred.</div>
    </div>

    <div id="payment-details" class="hidden">
        <p><strong>Amount:</strong> <span id="payment-amount"></span></p>
        <p><strong>Receipt Number:</strong> <span id="mpesa-receipt-number"></span></p>
    </div>

    <script>
        function showNotification(type, message) {
            const notification = type === 'success' ? $('#notification-success') : $('#notification-error');
            notification.text(message).fadeIn(400);

            setTimeout(() => {
                notification.fadeOut(400);
            }, 3000);
        }

        $('#checkPaymentForm').submit(function(e) {
            e.preventDefault();

            const phone = $('#phone').val();

            $.ajax({
                url: '',
                type: 'GET',
                data: { check_payment: true, phone },
                success: function(response) {
                    const res = JSON.parse(response);
                    if (res.success) {
                        $('#payment-details').removeClass('hidden');
                        $('#payment-amount').text(res.amount);
                        $('#mpesa-receipt-number').text(res.receipt);
                        showNotification('success', 'Payment found.');
                    } else {
                        $('#payment-details').addClass('hidden');
                        showNotification('error', res.message);
                    }
                },
                error: function() {
                    showNotification('error', 'An error occurred while checking payment.');
                }
            });
        });
    </script>
</body>
</html>
