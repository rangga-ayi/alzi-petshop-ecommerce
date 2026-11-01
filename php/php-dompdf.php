<?php

session_start();
require 'dompdf/autoload.inc.php';
require 'php.php';

use Dompdf\Dompdf;

// Mulai output buffering
ob_start();

// Validasi `order_id` dari GET
$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
$user_id = $_SESSION['userid'];

// Pastikan `order_id` valid
if ($order_id <= 0) {
    die("Invalid Order ID.");
}

// Query informasi user
$query_user = "
    SELECT u.nama, u.email, d.alamat, d.no_telepon
    FROM tbluser u
    JOIN user_detail d ON u.id = d.id
    WHERE u.id = ?
";

$stmt_user = $conn->prepare($query_user);
$stmt_user->bind_param("i", $user_id);
$stmt_user->execute();
$result_user = $stmt_user->get_result();
$user = $result_user->fetch_assoc();

if (!$result_user || !$user) {
    die("User data not found.");
}

// Query transaksi
$queryriwayat = "
    SELECT 
        t.*, 
        ss.status_pengiriman AS shipping_status 
    FROM transactions t
    LEFT JOIN tbluser u ON t.user_id = u.id
    LEFT JOIN shipping_detail sd ON t.order_id = sd.order_id
    LEFT JOIN shipping_status ss ON sd.status_pengiriman = ss.id
    WHERE t.order_id = ?
";

$stmt = $conn->prepare($queryriwayat);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$resultriwayat = $stmt->get_result();

if (!$resultriwayat || $resultriwayat->num_rows === 0) {
    die("Order not found.");
}

$transaction = $resultriwayat->fetch_assoc();
$customer_name = htmlspecialchars($user['nama']);
$customer_address = htmlspecialchars($user['alamat']);
$customer_phone = htmlspecialchars($user['no_telepon']);
$transaction_date = htmlspecialchars($transaction['update_time']);
$transaction_status = htmlspecialchars($transaction['transaction_status']);
$item_details = json_decode($transaction['item_details'], true);

$items = [];
if (is_array($item_details)) {
    foreach ($item_details as $item) {
        $items[] = [
            'name' => htmlspecialchars($item['name']),
            'quantity' => intval($item['quantity']),
            'unit_price' => floatval($item['price']),
            'total' => floatval($item['quantity']) * floatval($item['price']),
        ];
    }
}

$total_price = array_sum(array_column($items, 'total'));

// Informasi tambahan
$store_name = "Alzi Petshop";
$logo_url = "https://via.placeholder.com/150x60?text=Petshop+Logo";

// Template HTML
$html = "
<style>
    body {
        font-family: Arial, sans-serif;
        margin: 20px;
        color: #212529;
    }
    .invoice-box {
        max-width: 800px;
        margin: auto;
        padding: 20px;
        border: 1px solid #ddd;
        background: #ffffff;
        box-shadow: 0 2px 3px rgba(0, 0, 0, 0.1);
    }
    .invoice-header {
        display: flex;
        flex-direction: row;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }
    .invoice-header h1 {
        margin: 0;
        font-size: 28px;
        color: #007bff;
    }
    .invoice-header img {
        height: 60px;
    }
    .invoice-details table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
    }
    .invoice-details th, .invoice-details td {
        padding: 10px;
        text-align: left;
        border-bottom: 1px solid #ddd;
    }
    .invoice-details th {
        background: #007bff;
        color: #ffffff;
    }
    .total {
        font-weight: bold;
        text-align: right;
    }
    .footer {
        text-align: center;
        margin-top: 20px;
        font-size: 12px;
        color: #6c757d;
    }
</style>

<div class='invoice-box'>
    <div class='invoice-header'>
        <h1>Invoice</h1>
        <h3><strong>$store_name</strong></h3>
    </div>
    <p>Order ID: $order_id</p>
    <p>Customer Name: $customer_name</p>
    <p>Phone: $customer_phone</p>
    <p>Date: $transaction_date</p>
    <p>Customer Address: $customer_address</p>
    <div class='invoice-details'>
        <table>
            <tr>
                <th>Item</th>
                <th>Quantity</th>
                <th>Unit Price</th>
                <th>Total</th>
            </tr>";

foreach ($items as $item) {
    $html .= "
            <tr>
                <td>{$item['name']}</td>
                <td>{$item['quantity']}</td>
                <td>IDR " . number_format($item['unit_price'], 0, ',', '.') . "</td>
                <td>IDR " . number_format($item['total'], 0, ',', '.') . "</td>
            </tr>";
}

$html .= "
            <tr>
                <td colspan='3' class='total'>Total</td>
                <td class='total'>IDR " . number_format($total_price, 0, ',', '.') . "</td>
            </tr>
        </table>
    </div>
    <p>Status: $transaction_status</p>
    <div class='footer'>
        <p>Thank you for shopping with us!</p>
        <p>Contact us at: support@alzishop.com | +62 831 6107 6087</p>
    </div>
</div>
";

$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

ob_end_clean();
$dompdf->stream("invoice_$order_id.pdf", ["Attachment" => true]);