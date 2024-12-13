# M-Pesa API + MySQL Integration

## Description
A robust integration of the M-Pesa API with MySQL, designed to facilitate seamless mobile money transactions and data management. This project is created using PHP, providing a reliable backend solution for handling payments, queries, and callbacks efficiently.

## Features
- Integration with M-Pesa API for payment processing.
- Callback handling for real-time transaction updates.
- Secure communication with M-Pesa API using credentials.

## Requirements
- **PHP** (version 7.4 or higher)
- **M-Pesa API credentials**
  - Consumer Key
  - Consumer Secret
  - Shortcode
  - Till Number (Party B)
  - Passkey
  - Callback URL

## Installation
1. Clone the repository:
   ```bash
   git clone https://github.com/yourusername/mpesa-mysql-api.git
   ```

2. Navigate to the project directory:
   ```bash
   cd mpesa-mysql-api
   ```

3. Create the `payments` table in your MySQL database:
   ```sql
   CREATE TABLE payments (
       id INT AUTO_INCREMENT PRIMARY KEY,
       amount DECIMAL(10,2) NOT NULL,
       mpesa_receipt_number VARCHAR(255) NOT NULL,
       transaction_date DATETIME NOT NULL,
       phone_number VARCHAR(15) NOT NULL,
       callback_time DATETIME NOT NULL,
       created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
   ) ENGINE=InnoDB;
   ```

4. Configure the required credentials in `index.php`:
   - Update the following variables with your M-Pesa API details:
     ```php
     $consumerKey = 'your_consumer_key';
     $consumerSecret = 'your_consumer_secret';
     $shortcode = 'your_shortcode';
     $tillNumber = 'your_till_number';
     $passkey = 'your_passkey';
     $callbackUrl = 'your_callback_url';
     ```

5. Start your server and test the integration.

## Usage
- Use the API endpoints provided in the project to initiate and validate transactions.
- Update the `index.php` file to customize your integration.

## License
This project is licensed under the [MIT License](LICENSE).

## Contributing
Contributions are welcome! Please fork this repository and submit a pull request for any improvements or bug fixes.

## Support
If you encounter any issues or have questions, feel free to open an issue in this repository or contact me directly.

---

### Author
**Tricksterke**
