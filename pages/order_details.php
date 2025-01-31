<?php
// order_details.php
session_start();
include '../includes/db.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Check if the order ID is provided
if (!isset($_GET['order_id']) || empty($_GET['order_id'])) {
    echo "<p>Order ID is missing. Please go back to your orders.</p>";
    exit();
}

$order_id = $_GET['order_id'];

// Fetch the order items
$stmt = $conn->prepare("SELECT oi.product_id, oi.quantity, oi.price, p.name, p.image
                        FROM order_items oi
                        JOIN products p ON oi.product_id = p.id
                        WHERE oi.order_id = ?");
$stmt->execute([$order_id]);
$order_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch the order details
$stmt = $conn->prepare("SELECT id, total_amount, created_at FROM orders WHERE id = ?");
$stmt->execute([$order_id]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    echo "<p>Order not found.</p>";
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Details - Online Store</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        .container {
            width: 80%;
            margin: 20px auto;
            background-color: #fff;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        h2 {
            text-align: center;
            color: #333;
        }
        .order-summary {
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .order-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 100%;
            margin-bottom: 20px;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .order-item img {
            max-width: 100px;
            margin-right: 20px;
        }
        .order-item-details {
            flex-grow: 1;
        }
        .order-item-price {
            font-weight: bold;
        }
        .back-btn {
            display: block;
            width: 200px;
            margin: 20px auto;
            padding: 10px;
            text-align: center;
            background-color: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }
        .back-btn:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Order Details</h2>

    <div class="order-summary">
        <p><strong>Order ID:</strong> <?= htmlspecialchars($order['id']); ?></p>
        <p><strong>Total Amount:</strong> $<?= number_format($order['total_amount'], 2); ?></p>
        <p><strong>Order Date:</strong> <?= htmlspecialchars($order['created_at']); ?></p>

        <h3>Order Items:</h3>
        <?php foreach ($order_items as $item) : ?>
            <div class="order-item">
                <img src="../images/<?= htmlspecialchars($item['image']); ?>" alt="<?= htmlspecialchars($item['name']); ?>">
                <div class="order-item-details">
                    <p><strong><?= htmlspecialchars($item['name']); ?></strong></p>
                    <p>Quantity: <?= $item['quantity']; ?></p>
                </div>
                <p class="order-item-price">$<?= number_format($item['price'] * $item['quantity'], 2); ?></p>
            </div>
        <?php endforeach; ?>
    </div>

    <a href="orders.php" class="back-btn">Back to My Orders</a>
</div>

</body>
</html>
