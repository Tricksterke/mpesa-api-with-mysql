<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // M-Pesa API credentials
    $consumerKey = 'PEuLZtXPgmTIU2TZZpAkUb79igQHvDzk';
    $consumerSecret = 'NEjC5xGwLIUI1kqp';
    $tillNumber = '5927587'; // Till Number
    $shortcode = '7428993';
    $passkey = 'e35d6c9712c7c10f6df1ae7f1de061582ab1d54d2926bcff5c4f7a9080fe28db';
    $callbackUrl = 'https://www.investpeak.globalqashadsclicks.com/callback.php';

    // Get inputs
    $phone = $_POST['phone'];
    $amount = $_POST['amount'];

    // Format phone number to start with 254
    if (!preg_match('/^254/', $phone)) {
        $phone = '254' . ltrim($phone, '0');
    }

    // Get access token
    $authUrl = 'https://api.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials';
    $credentials = base64_encode("$consumerKey:$consumerSecret");

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $authUrl);
    curl_setopt($curl, CURLOPT_HTTPHEADER, ["Authorization: Basic $credentials"]);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    $response = json_decode(curl_exec($curl), true);
    curl_close($curl);

    if (!isset($response['access_token'])) {
        echo json_encode(['success' => false, 'message' => 'Failed to get access token']);
        exit;
    }

    $accessToken = $response['access_token'];

    // Initiate STK Push
    $timestamp = date('YmdHis');
    $password = base64_encode($shortcode . $passkey . $timestamp);

    $stkUrl = 'https://api.safaricom.co.ke/mpesa/stkpush/v1/processrequest';
    $stkData = [
        'BusinessShortCode' => $shortcode,
        'Password' => $password,
        'Timestamp' => $timestamp,
        'TransactionType' => 'CustomerBuyGoodsOnline',
        'Amount' => $amount,
        'PartyA' => $phone,
        'PartyB' => $tillNumber,
        'PhoneNumber' => $phone,
        'CallBackURL' => $callbackUrl,
        'AccountReference' => 'Payment',
        'TransactionDesc' => 'STK Push Payment'
    ];

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $stkUrl);
    curl_setopt($curl, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer $accessToken",
        'Content-Type: application/json'
    ]);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($stkData));
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    $stkResponse = json_decode(curl_exec($curl), true);
    curl_close($curl);

    if (isset($stkResponse['ResponseCode']) && $stkResponse['ResponseCode'] === '0') {
        echo json_encode(['success' => true, 'message' => 'STK Push sent successfully']);
    } else {
        echo json_encode([
            'success' => false,
            'message' => $stkResponse['errorMessage'] ?? 'Failed to send STK Push'
        ]);
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>STK Push Request</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <style>
        .spinner {
            border-top-color: #3498db;
            border-left-color: #3498db;
        }
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
        .countdown {
            font-family: 'Courier New', Courier, monospace;
            font-size: 1rem;
            font-weight: bold;
            color: #007bff;
            margin-top: 1rem;
        }
        .button-style {
            display: block;
            width: 100%;
            background-color: #007bff;
            color: white;
            text-align: center;
            padding: 0.75rem;
            border-radius: 4px;
            transition: background-color 0.3s ease-in-out;
        }
        .button-style:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <div class="bg-white p-8 rounded shadow-md w-full max-w-md">
        <h1 class="text-2xl font-bold mb-6 text-gray-800 text-center">Make Payment</h1>

        <!-- Notifications Section -->
        <div id="notifications">
            <div id="notification-success" class="notification success hidden">STK Push sent successfully!</div>
            <div id="notification-error" class="notification error hidden">An error occurred. Please try again.</div>
        </div>

        <form id="stkForm" class="space-y-4">
            <div>
                <label for="phone" class="block text-gray-600 font-medium mb-1">Phone Number</label>
                <input 
                    type="text" 
                    id="phone" 
                    class="w-full px-4 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500" 
                    placeholder="2547XXXXXXXX" 
                    maxlength="12" 
                    required>
            </div>
            <div>
                <label for="amount" class="block text-gray-600 font-medium mb-1">Amount</label>
                <input 
                    type="number" 
                    id="amount" 
                    class="w-full px-4 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500" 
                    placeholder="Enter amount" 
                    required>
            </div>
            <button 
                type="submit" 
                class="w-full bg-green-600 text-white py-2 px-4 rounded hover:bg-green-700 transition">
                Make Payment
            </button>
        </form>

        <!-- Loader -->
        <div id="loader" class="hidden flex items-center justify-center mt-4">
            <div class="spinner-border animate-spin inline-block w-8 h-8 border-4 rounded-full spinner" role="status"></div>
        </div>

        <!-- Countdown and Redirect Section -->
        <div id="redirect-section" class="hidden text-center">
            <p class="countdown">Checking your payment in <span id="countdown">10</span> seconds...</p>
            <div id="redirect-button" class="hidden mt-4">
                <a href="#" id="redirect-link" class="button-style">Click here to validate your payment</a>
            </div>
        </div>
    </div>

    <script>
        function showNotification(type, message) {
            const notification = type === 'success' ? $('#notification-success') : $('#notification-error');
            notification.text(message).fadeIn(400);

            setTimeout(() => {
                notification.fadeOut(400);
            }, 3000);
        }

        $(document).ready(function() {
            $('#stkForm').submit(function(e) {
                e.preventDefault();

                const phone = $('#phone').val();
                const amount = $('#amount').val();
                const loader = $('#loader');

                loader.removeClass('hidden'); // Show loader

                $.ajax({
                    url: '', // Same file for PHP processing
                    type: 'POST',
                    data: { phone, amount },
                    success: function(response) {
                        const res = JSON.parse(response);
                        loader.addClass('hidden'); // Hide loader
                        if (res.success) {
                            showNotification('success', res.message);
                            setTimeout(() => {
                                startCountdown();
                            }, 3000); // Wait 3 seconds before starting the countdown
                        } else {
                            showNotification('error', res.message);
                        }
                    },
                    error: function() {
                        loader.addClass('hidden'); // Hide loader
                        showNotification('error', 'An error occurred. Please try again.');
                    }
                });
            });

            function startCountdown() {
                let countdown = 10; // 10-second countdown
                const countdownElement = $('#countdown');
                const redirectSection = $('#redirect-section');

                setInterval(() => {
                    countdown--;
                    countdownElement.text(countdown);
                    if (countdown <= 0) {
                        countdownElement.parent().addClass('hidden'); // Hide countdown text
                        $('#redirect-button').removeClass('hidden'); // Show redirect button
                    }
                }, 1000);

                redirectSection.removeClass('hidden'); // Show the redirect section
            }

            $('#redirect-link').click(function() {
                window.location.href = 'maincheck.php';
            });
        });
    </script>
</body>
</html>
