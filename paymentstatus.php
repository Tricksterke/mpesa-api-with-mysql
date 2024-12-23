<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Check Recent Transactions</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .table th, .table td {
            border-color: #dee2e6 !important;
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center">
    <div class="container mx-auto p-6">
        <div class="max-w-lg mx-auto bg-white shadow-lg rounded-lg p-8">
            <h1 class="text-2xl font-bold mb-6 text-center">Check Recent Transactions</h1>
            <form method="post" action="">
                <div class="mb-4">
                    <label for="phone" class="block text-sm font-medium text-gray-700">Phone Number</label>
                    <input type="text" id="phone" name="phone" class="mt-1 p-3 block w-full border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500" required>
                </div>
                <button type="submit" class="w-full bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded-lg">Check Transactions</button>
            </form>

            <?php
            // Database connection
            $servername = "localhost";
            $username = "globalqa_globalqa_userdb";
            $password = "2jRsEEHVSULLpXmpSr2J";
            $dbname = "globalqa_globalqa_userdb";

            // Create database connection
            $conn = new mysqli($servername, $username, $password, $dbname);
            if ($conn->connect_error) {
                die("Connection failed: " . $conn->connect_error);
            }

            // Check if the form is submitted
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['phone'])) {
                $phone_number = $conn->real_escape_string($_POST['phone']);
                $current_time = date('Y-m-d H:i:s');
                $time_limit = date('Y-m-d H:i:s', strtotime('-5 minutes'));

                // Query for recent transactions
                $sql = "SELECT id, amount, mpesa_receipt_number, phone_number, created_at 
                        FROM payments 
                        WHERE phone_number = '$phone_number' 
                        AND created_at >= '$time_limit' 
                        ORDER BY created_at ASC";
                
                $result = $conn->query($sql);

                if ($result->num_rows > 0) {
                    echo "<table class='table-auto border-collapse border border-gray-400 w-full mt-6'>";
                    echo "<thead><tr class='bg-gray-100'>";
                    echo "<th class='border border-gray-400 px-4 py-2'>ID</th>";
                    echo "<th class='border border-gray-400 px-4 py-2'>Amount</th>";
                    echo "<th class='border border-gray-400 px-4 py-2'>Mpesa Receipt Number</th>";
                    echo "<th class='border border-gray-400 px-4 py-2'>Phone Number</th>";
                    echo "<th class='border border-gray-400 px-4 py-2'>Created At</th>";
                    echo "</tr></thead>";
                    echo "<tbody>";
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td class='border border-gray-400 px-4 py-2'>" . $row['id'] . "</td>";
                        echo "<td class='border border-gray-400 px-4 py-2'>" . $row['amount'] . "</td>";
                        echo "<td class='border border-gray-400 px-4 py-2'>" . $row['mpesa_receipt_number'] . "</td>";
                        echo "<td class='border border-gray-400 px-4 py-2'>" . $row['phone_number'] . "</td>";
                        echo "<td class='border border-gray-400 px-4 py-2'>" . $row['created_at'] . "</td>";
                        echo "</tr>";
                    }
                    echo "</tbody>";
                    echo "</table>";
                } else {
                    echo "<p class='mt-4 text-gray-700 text-center'>No recent transactions found for this phone number.</p>";
                }

                $conn->close();
            }
            ?>
        </div>
    </div>
</body>
</html>
