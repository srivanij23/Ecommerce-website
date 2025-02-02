# **Login Setup for E-commerce Website (XAMPP)**

This guide will walk you through setting up the **Login** functionality in your e-commerce website using **XAMPP** and **PDO**.

## **Step 1: Database Table Setup**

Before using the login functionality, ensure that your **users** table in the database has the necessary structure. Use **phpMyAdmin** to execute the following SQL query in your **ecommerce_website** database:

```sql
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('customer', 'admin') DEFAULT 'customer'
);
This query creates the necessary columns like id, email, password, and role for storing the user information.
