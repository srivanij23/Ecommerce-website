<?php
// confirm_checkout.php
include '../includes/db.php';

// Check if order_id is passed in the URL
if (!isset($_GET['order_id']) || empty($_GET['order_id'])) {
    echo "<p>Order ID is missing. Please <a href='../index.php'>go back to shop</a> and try again.</p>";
    exit();
}

$order_id = $_GET['order_id'];

// Fetch order items from the order_items table
$stmt = $conn->prepare("SELECT oi.product_id, oi.quantity, oi.price, p.name 
                        FROM order_items oi
                        JOIN products p ON oi.product_id = p.id
                        WHERE oi.order_id = ?");
$stmt->execute([$order_id]);
$order_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Check if order items exist
if (empty($order_items)) {
    echo "<p>No items found for this order.</p>";
    exit();
}

// Calculate the total amount by summing up the items
$total_amount = 0;
foreach ($order_items as $item) {
    $total_amount += $item['price'] * $item['quantity'];
}

// Fetch the order details from the orders table (optional)
$stmt = $conn->prepare("SELECT id, user_id, total_amount, created_at FROM orders WHERE id = ?");
$stmt->execute([$order_id]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

// Check if the order exists
if (!$order) {
    echo "<p>Order not found. Please check your order ID.</p>";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 80%;
            margin: 20px auto;
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        h1 {
            color: #28a745;
        }
        .order-details {
            margin-top: 20px;
        }
        .order-details h3, .order-details p {
            color: #343a40;
        }
        .order-items {
            margin-top: 20px;
        }
        .order-items ul {
            list-style-type: none;
            padding: 0;
        }
        .order-items li {
            padding: 10px;
            border-bottom: 1px solid #ddd;
            color: #495057;
        }
        .total-amount {
            font-size: 1.2em;
            font-weight: bold;
            color: #007bff;
            margin-top: 20px;
        }
        .back-to-shop {
            margin-top: 30px;
            text-align: center;
        }
        .back-to-shop a {
            color: #007bff;
            text-decoration: none;
            font-size: 1.1em;
        }
        .back-to-shop a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Order Confirmation</h1>
        <p>Your order has been successfully placed!</p>

        <div class="order-details">
            <h3>Order ID: <?= htmlspecialchars($order['id']); ?></h3>
            <p><strong>Order Placed On:</strong> <?= htmlspecialchars($order['created_at']); ?></p>
        </div>

        <div class="order-items">
            <h4>Order Items:</h4>
            <ul>
                <?php foreach ($order_items as $item): ?>
                    <li><?= htmlspecialchars($item['name']); ?> - $<?= number_format($item['price'], 2); ?> x <?= $item['quantity']; ?></li>
                <?php endforeach; ?>
            </ul>
        </div>

        <div class="total-amount">
            <p>Total Amount: $<?= number_format($total_amount, 2); ?></p>
        </div>

        <div class="back-to-shop">
            <a href="../index.php">Go back to Shop</a>
        </div>
    </div>
</body>
</html>
