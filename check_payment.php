<?php
// Database connection details
$servername = 'YOUR_SERVERNAME';       // Replace with your database server name
$username = 'YOUR_DATABASE_USERNAME';   // Replace with your database username
$password = 'YOUR_DATABASE_PASSWORD';   // Replace with your database password
$dbname = 'YOUR_DATABASE_NAME';         // Replace with your database name

// Create a PDO connection
try {
    $db = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['phone'])) {
    $phone = $_POST['phone'];
    $stmt = $db->prepare("SELECT amount, mpesa_receipt_number FROM payments WHERE phone_number = ? AND created_at >= NOW() - INTERVAL 1 MINUTE ORDER BY created_at DESC LIMIT 1");
    $stmt->execute([$phone]);
    $payment = $stmt->fetch(PDO::FETCH_ASSOC);

    // Log debug information
    $logData = "Time: " . date('Y-m-d H:i:s') . "\n";
    $logData .= "Phone: " . $phone . "\n";
    $logData .= "Query: " . $stmt->queryString . "\n";
    $logData .= "Payment Found: " . json_encode($payment) . "\n\n";
    file_put_contents('checkpayment.txt', $logData, FILE_APPEND);

    if ($payment) {
        echo json_encode([
            'success' => true,
            'amount' => $payment['amount'],
            'receipt' => $payment['mpesa_receipt_number']
        ]);
    } else {
        echo json_encode(['success' => false]);
    }
}
?>
