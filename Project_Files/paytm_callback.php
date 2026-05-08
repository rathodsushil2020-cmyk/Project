<?php
include 'components/connect.php';
session_start();

if (isset($_SESSION['pending_order'])) {
    $order = $_SESSION['pending_order'];

    // Insert order into the database
    $placed_on = date('Y-m-d');
    $order_status = 'pending';  // Initially, the order is marked as 'pending'
    $insert_order = $conn->prepare("INSERT INTO orders (user_id, name, number, email, method, address, total_products, total_price, placed_on, order_status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $insert_order->execute([
        $order['user_id'],
        $order['name'],
        $order['number'],
        $order['email'],
        $order['method'],
        $order['address'],
        $order['total_products'],
        $order['total_price'],
        $placed_on,
        $order_status
    ]);

    // Clear the cart
    $delete_cart = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
    $delete_cart->execute([$order['user_id']]);

    // Clear pending order session
    unset($_SESSION['pending_order']);

    // Redirect to orders page with success status
    $_SESSION['order_success'] = true;
    header('Location: orders.php');
    exit();
} else {
    // If no pending order found, redirect to checkout with error
    $_SESSION['order_error'] = 'No pending order found.';
    header('Location: checkout.php');
    exit();
}
?>
