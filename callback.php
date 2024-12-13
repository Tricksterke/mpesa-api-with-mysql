<?php
$servername = "localhost";
$username = "globalqa_globalqa_userdb";
$password = "2jRsEEHVSULLpXmpSr2J";
$dbname = "globalqa_globalqa_userdb";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$data = json_decode(file_get_contents('php://input'), true);

if (!empty($data) && isset($data['Body']['stkCallback']['CallbackMetadata']['Item'])) {
    $callbackData = $data['Body']['stkCallback']['CallbackMetadata']['Item'];

    $amount = $callbackData[0]['Value'] ?? null;                     // Amount
    $receiptNumber = $callbackData[1]['Value'] ?? null;              // Mpesa Receipt Number
    $phoneNumber = $callbackData[3]['Value'] ?? null;                // Phone Number
    $transactionDate = $callbackData[4]['Value'] ?? null;            // Transaction Date

    // Insert into the payments table
    $stmt = $conn->prepare("INSERT INTO payments (amount, mpesa_receipt_number, phone_number, transaction_date) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $amount, $receiptNumber, $phoneNumber, $transactionDate);

    if ($stmt->execute()) {
        file_put_contents('check.txt', "Payment successfully inserted: {$receiptNumber} for {$phoneNumber}" . PHP_EOL, FILE_APPEND);
    } else {
        file_put_contents('check.txt', "Database error: " . $stmt->error . PHP_EOL, FILE_APPEND);
    }
} else {
    file_put_contents('check.txt', "Invalid callback data or missing fields." . PHP_EOL, FILE_APPEND);
}

$conn->close();
?>
