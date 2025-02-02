# **Database Setup for E-commerce Website*

This document guides you through the process of setting up the database for your E-commerce Website project.
Prerequisites

Before you begin, ensure that you have the following installed:

    MySQL or MariaDB (or any other compatible relational database)
    A local server (XAMPP, WAMP, or LAMP)

Step 1: Create a Database

    Open your MySQL command-line interface (CLI) or MySQL Workbench.
    Run the following command to create a new database:

CREATE DATABASE ecommerce_website;

Step 2: Create the Necessary Tables

Now, we will create the necessary tables for storing user and product data. You can execute the following SQL queries in your MySQL console.
```1. Users Table

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'customer') DEFAULT 'customer',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

2. Products Table

CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL,
    stock_quantity INT NOT NULL,
    image_url VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

3. Orders Table

CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    total DECIMAL(10, 2) NOT NULL,
    status ENUM('pending', 'completed', 'shipped') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

4. Order_Items Table

CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT,
    product_id INT,
    quantity INT NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id),
    FOREIGN KEY (product_id) REFERENCES products(id)
);

Step 3: Configure the Database Connection Using PDO

    Open the PHP files in your e-commerce project where the database connection is established.
    Update the database connection details (hostname, username, password, and database name) accordingly.

Example (using PDO):

<?php
$host = 'localhost';  // Database server
$dbname = 'ecommerce_website'; // Database name
$user = 'root';       // Database username
$password = '';       // Database password

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $user, $password);
    // Set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Connected successfully"; 
}
catch(PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
?>

This PDO connection code is more secure and flexible, especially for handling SQL queries with prepared statements.
