<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include '../includes/db.php';

$user_id = $_SESSION['user_id'];

// Fetch cart items for the user
$stmt = $conn->prepare("SELECT * FROM cart WHERE user_id = ?");
$stmt->execute([$user_id]);
$cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Check if the cart is empty
if (empty($cart_items)) {
    echo "<h2>Your cart is empty. Please add items to proceed with checkout.</h2>";
    echo "<a href='../index.php'>Go to Shop</a>";
    exit();
}

$total_amount = 0;
$valid_items = [];

// Calculate the total amount for the cart and ensure products have prices
foreach ($cart_items as $item) {
    $stmt = $conn->prepare("SELECT price FROM products WHERE id = ?");
    $stmt->execute([$item['product_id']]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($product && isset($product['price'])) {
        $total_amount += $product['price'] * $item['quantity'];
        $valid_items[] = $item;
    } else {
        echo "<p>Price missing for product ID: {$item['product_id']}. Please update the product in your cart.</p>";
    }
}

// If no valid items found, stop checkout
if (empty($valid_items)) {
    echo "<p>No valid products to checkout. Please update your cart.</p>";
    exit();
}

// Handle order placement
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $conn->beginTransaction(); // Start a transaction

        // Insert a new order
        $stmt = $conn->prepare("INSERT INTO orders (user_id, total_amount, created_at) VALUES (?, ?, NOW())");
        $stmt->execute([$user_id, $total_amount]);
        $order_id = $conn->lastInsertId();

        // Add items from the cart to the `order_items` table
        foreach ($valid_items as $item) {
            $stmt = $conn->prepare("SELECT price FROM products WHERE id = ?");
            $stmt->execute([$item['product_id']]);
            $product = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($product && isset($product['price'])) {
                $stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
                $stmt->execute([$order_id, $item['product_id'], $item['quantity'], $product['price']]);
            } else {
                echo "<p>Price missing for product ID: {$item['product_id']}. Unable to place order.</p>";
                exit();
            }
        }

        // Clear the cart after placing the order
        $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
        $stmt->execute([$user_id]);

        $conn->commit(); // Commit the transaction

        // Redirect to the order confirmation page
        header("Location: confirm_checkout.php?order_id=" . $order_id);
        exit();
    } catch (Exception $e) {
        $conn->rollBack(); // Rollback the transaction on error
        echo "Error: " . $e->getMessage();
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f9f9f9;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 1200px;
            margin: 50px auto;
            background-color: white;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }

        h2 {
            text-align: center;
            color: #333;
        }

        .cart-item {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #ccc;
        }

        .cart-item img {
            width: 100px;
            height: 100px;
            object-fit: cover;
        }

        .item-details {
            flex: 1;
            padding-left: 15px;
        }

        .item-name {
            font-size: 16px;
            font-weight: bold;
        }

        .item-price {
            font-size: 14px;
            color: #777;
        }

        .total-cost {
            font-size: 20px;
            font-weight: bold;
            text-align: right;
            margin-top: 20px;
            border-top: 2px solid #ccc;
            padding-top: 10px;
        }

        .checkout-actions {
            text-align: center;
            margin-top: 30px;
        }

        .checkout-actions button {
            background-color: #4CAF50;
            color: white;
            padding: 12px 20px;
            border: none;
            cursor: pointer;
            font-size: 16px;
            border-radius: 5px;
        }

        .checkout-actions button:hover {
            background-color: #45a049;
        }

        .back-to-shop {
            display: block;
            text-align: center;
            margin-top: 20px;
        }

        .back-to-shop a {
            color: #4CAF50;
            font-size: 16px;
            text-decoration: none;
        }

        .back-to-shop a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Checkout</h2>
        
        <?php
        foreach ($valid_items as $item) {
            $stmt = $conn->prepare("SELECT name, price, image FROM products WHERE id = ?");
            $stmt->execute([$item['product_id']]);
            $product = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($product && isset($product['price'])) {
                echo "<div class='cart-item'>
                        <img src='../images/{$product['image']}' alt='{$product['name']}'>
                        <div class='item-details'>
                            <div class='item-name'>{$product['name']}</div>
                            <div class='item-price'>\${$product['price']} x {$item['quantity']}</div>
                        </div>
                    </div>";
            }
        }
        ?>

        <div class="total-cost">
            Total Amount: $<?= number_format($total_amount, 2); ?>
        </div>

        <form method="POST" action="">
            <div class="checkout-actions">
                <button type="submit">Confirm Checkout</button>
            </div>
        </form>

        <div class="back-to-shop">
            <a href="index.php">Go to Shop</a>
        </div>
    </div>
</body>
</html>
