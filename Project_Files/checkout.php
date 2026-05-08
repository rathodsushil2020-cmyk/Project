<?php
include 'components/connect.php';
session_start();

// Redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
   header('location:user_login.php');
   exit();
}

$user_id = $_SESSION['user_id'];
$paymentFail = false;

// Check if the user has items in their cart
$check_cart = $conn->prepare("SELECT * FROM cart WHERE user_id = ?");
$check_cart->execute([$user_id]);

// Show SweetAlert if Paytm payment failed previously
if (isset($_SESSION['payment_status']) && $_SESSION['payment_status'] == 'fail') {
    $paymentFail = true;
    unset($_SESSION['payment_status']);
}

// When order form is submitted
if (isset($_POST['order'])) {
   // Sanitize and prepare input data
   $name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
   $number = filter_var($_POST['number'], FILTER_SANITIZE_STRING);
   $email = filter_var($_POST['email'], FILTER_SANITIZE_STRING);
   $method = filter_var($_POST['method'], FILTER_SANITIZE_STRING);
   $address = 'Flat No. ' . $_POST['flat'] . ', ' . $_POST['street'] . ', ' . $_POST['city'] . ', ' . $_POST['state'] . ', ' . $_POST['country'] . ' - ' . $_POST['pin_code'];
   $address = filter_var($address, FILTER_SANITIZE_STRING);
   $total_products = $_POST['total_products'];
   $total_price = $_POST['total_price'];

   // Check if cart has items
   $check_cart = $conn->prepare("SELECT * FROM cart WHERE user_id = ?");
   $check_cart->execute([$user_id]);

   if ($check_cart->rowCount() > 0) {
      if ($method === 'paytm') {
         // -----------------------------
         // 💳 PAYTM PAYMENT SELECTED
         // -----------------------------
         // Store order data in session to be used in paytm_callback.php
         $_SESSION['pending_order'] = [
            'user_id'        => $user_id,
            'name'           => $name,
            'number'         => $number,
            'email'          => $email,
            'method'         => $method,
            'address'        => $address,
            'total_products' => $total_products,
            'total_price'    => $total_price
         ];

         // Redirect to fake Paytm gateway page
         header("Location: paytm.php");
         exit();
      } else {
         // -----------------------------
         // 🛒 CASH ON DELIVERY SELECTED 
         // -----------------------------
         // Insert order into the database immediately
         $insert_order = $conn->prepare("INSERT INTO orders(user_id, name, number, email, method, address, total_products, total_price) VALUES(?,?,?,?,?,?,?,?)");
         $insert_order->execute([$user_id, $name, $number, $email, $method, $address, $total_products, $total_price]);

         // Delete items from cart after successful order
         $delete_cart = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
         $delete_cart->execute([$user_id]);

         $message[] = 'Order placed successfully!';
      }
   } else {
      $message[] = 'Your cart is empty!';
   }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <title>Checkout</title>
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <!-- Font Awesome CDN -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   <!-- Custom CSS -->
   <link rel="stylesheet" href="css/style.css">
   <!-- SweetAlert2 -->
   <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

<?php if ($paymentFail): ?>
<script>
   Swal.fire({
      icon: 'error',
      title: 'Payment Failed!',
      text: 'Your payment was not completed. Please try again.',
      confirmButtonColor: '#ff5252'
   });
</script>
<?php endif; ?>

<?php include 'components/user_header.php'; ?>

<section class="checkout-orders">
   <form action="" method="POST">
      <h3>Your Orders</h3>
      <div class="display-orders">
         <?php
         $grand_total = 0;
         $cart_items = [];
         $select_cart = $conn->prepare("SELECT * FROM cart WHERE user_id = ?");
         $select_cart->execute([$user_id]);

         if ($select_cart->rowCount() > 0) {
            while ($fetch_cart = $select_cart->fetch(PDO::FETCH_ASSOC)) {
               $cart_items[] = $fetch_cart['name'] . ' (' . $fetch_cart['price'] . ' x ' . $fetch_cart['quantity'] . ')';
               $grand_total += ($fetch_cart['price'] * $fetch_cart['quantity']);
         ?>
         <p><?= $fetch_cart['name']; ?> <span>(<?= '₹' . $fetch_cart['price'] . '/- x ' . $fetch_cart['quantity']; ?>)</span></p>
         <?php
            }
         } else {
            echo '<p class="empty">Your cart is empty!</p>';
         }
         $total_products = implode(', ', $cart_items);
         ?>
         <input type="hidden" name="total_products" value="<?= $total_products; ?>">
         <input type="hidden" name="total_price" value="<?= $grand_total; ?>">
         <div class="grand-total">Grand Total : <span>₹<?= $grand_total; ?>/-</span></div>
      </div>

      <h3>Place Your Order</h3>
      <div class="flex">
         <div class="inputBox">
            <span>Your Name:</span>
            <input type="text" name="name" placeholder="Enter your name" class="box" maxlength="20" required>
         </div>
         <div class="inputBox">
            <span>Your Number:</span>
            <input type="number" name="number" placeholder="Enter your number" class="box" oninput="this.value=this.value.slice(0,10);" required>
         </div>
         <div class="inputBox">
            <span>Your Email:</span>
            <input type="email" name="email" placeholder="Enter your email" class="box" maxlength="50" required>
         </div>
         <div class="inputBox">
            <span>Payment Method:</span>
            <select name="method" class="box" required>
               <option value="cash on delivery">Cash On Delivery</option>
               <option value="paytm">Paytm</option>
            </select>
         </div>
         <div class="inputBox">
            <span>Address Line 1:</span>
            <input type="text" name="flat" placeholder="e.g. Flat number" class="box" maxlength="50" required>
         </div>
         <div class="inputBox">
            <span>Address Line 2:</span>
            <input type="text" name="street" placeholder="Street name" class="box" maxlength="50" required>
         </div>
         <div class="inputBox">
            <span>City:</span>
            <input type="text" name="city" placeholder="City name" class="box" maxlength="50" required>
         </div>
         <div class="inputBox">
            <span>State:</span>
            <input type="text" name="state" placeholder="State" class="box" maxlength="50" required>
         </div>
         <div class="inputBox">
            <span>Country:</span>
            <input type="text" name="country" placeholder="Country" class="box" maxlength="50" required>
         </div>
         <div class="inputBox">
            <span>ZIP Code:</span>
            <input type="number" name="pin_code" placeholder="e.g. 364004" oninput="this.value=this.value.slice(0,6);" class="box" required>
         </div>
      </div>

      <input type="submit" name="order" class="btn <?= ($grand_total > 0) ? '' : 'disabled'; ?>" value="Place Order">
   </form>
</section>

<?php include 'components/footer.php'; ?>
<script src="js/script.js"></script>
</body>
</html>
