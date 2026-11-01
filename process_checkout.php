<?php
session_start();
header('Content-Type: application/json; charset=UTF-8');

if (!isset($_SESSION['userid'])) {
    echo json_encode(['success' => false, 'message' => 'Silakan login terlebih dahulu.']);
    exit;
}

if (!isset($_SESSION['level']) || $_SESSION['level'] !== 'pembeli') {
    echo json_encode(['success' => false, 'message' => 'Hanya pembeli yang dapat melakukan checkout.']);
    exit;
}

require_once 'php/php.php';
require_once 'midtrans_config.php';

$user_id = intval($_SESSION['userid']);

// 1. Ambil detail alamat dan hitung ongkir
$shipping_info = get_shipping_info($user_id, $conn);
$shipping_cost = $shipping_info['cost'];
$shipping_distance = $shipping_info['distance'];

// 2. Ambil data keranjang
$stmt_cart = $conn->prepare("
    SELECT c.product_id, p.name AS product_name, c.quantity, p.price, (c.quantity * p.price) AS total_price 
    FROM cart c
    JOIN products p ON c.product_id = p.id
    WHERE c.user_id = ?
");
$stmt_cart->bind_param("i", $user_id);
$stmt_cart->execute();
$res_cart = $stmt_cart->get_result();

$items = [];
$total_price_midtrans = 0;
while ($rowc = $res_cart->fetch_assoc()) {
    if ($rowc['price'] > 0 && $rowc['quantity'] > 0) {
        $subtotal = (int) $rowc['total_price'];
        $total_price_midtrans += $subtotal;
        $items[] = [
            'id' => (string) $rowc['product_id'],
            'price' => (int) $rowc['price'],
            'quantity' => (int) $rowc['quantity'],
            'name' => $rowc['product_name']
        ];
    }
}
$stmt_cart->close();

if (empty($items) || $total_price_midtrans <= 0) {
    echo json_encode(['success' => false, 'message' => 'Keranjang kosong atau total belanja tidak valid.']);
    exit;
}

if ($shipping_cost > 0) {
    $items[] = [
        'id' => 'shipping',
        'price' => $shipping_cost,
        'quantity' => 1,
        'name' => 'Ongkos Kirim'
    ];
    $total_price_midtrans += $shipping_cost;
}

// 3. Ambil data user
$stmt_u = $conn->prepare("
    SELECT u.nama, u.email, d.alamat, d.no_telepon
    FROM tbluser u
    LEFT JOIN user_detail d ON u.id = d.id
    WHERE u.id = ?
");
$stmt_u->bind_param("i", $user_id);
$stmt_u->execute();
$res_u = $stmt_u->get_result();
$user = $res_u ? $res_u->fetch_assoc() : null;
$stmt_u->close();

$order_id = rand();
$transaction_details = [
    'order_id' => $order_id,
    'gross_amount' => $total_price_midtrans
];

$customer_details = [
    'first_name' => $user ? $user['nama'] : 'Pembeli',
    'email' => $user ? $user['email'] : 'customer@example.com',
    'phone' => $user ? $user['no_telepon'] : '',
    'shipping_address' => $user ? $user['alamat'] : ''
];

$transaction_data = [
    'transaction_details' => $transaction_details,
    'customer_details' => $customer_details,
    'item_details' => $items
];

try {
    $item_details_json = json_encode($items);
    $snap_token = \Midtrans\Snap::getSnapToken($transaction_data);
    $payment_status = 'Pending';

    $stmt_tx = $conn->prepare("INSERT INTO transactions (user_id, order_id, transaction_status, gross_amount, item_details, snap_token) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt_tx->bind_param("issdss", $user_id, $order_id, $payment_status, $total_price_midtrans, $item_details_json, $snap_token);
    $stmt_tx->execute();
    $stmt_tx->close();

    echo json_encode([
        'success' => true,
        'snap_token' => $snap_token,
        'order_id' => $order_id,
        'payment_url' => "https://app.midtrans.com/snap/v2/vtweb/$snap_token"
    ]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->close();
