<?php
include 'components/connect.php';
session_start();

// Default flag for showing success message
$showSuccess = false; 

// Check if payment was successful and set session status
if (isset($_SESSION['payment_status']) && $_SESSION['payment_status'] == 'success') {
    $showSuccess = true;  // Show success alert
    unset($_SESSION['payment_status']);  // Reset session status to avoid showing it again
}

// Check if user is logged in
$user_id = $_SESSION['user_id'] ?? '';  // Get user_id from session
?>
<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Orders</title>
   
   <!-- Font Awesome CDN -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- Custom CSS File -->
   <link rel="stylesheet" href="css/style.css">

   <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

<?php include 'components/user_header.php'; ?>

<section class="orders">

   <h1 class="heading">Placed Orders.</h1>

   <div class="box-container">
   <?php
   if (empty($user_id)) {
      echo '<p class="empty">Please login to see your orders</p>';
   } else {
      $select_orders = $conn->prepare("SELECT * FROM `orders` WHERE user_id = ?");
      $select_orders->execute([$user_id]);

      if ($select_orders->rowCount() > 0) {
         while ($fetch_orders = $select_orders->fetch(PDO::FETCH_ASSOC)) {
   ?>
         <div class="box">
            <p>Placed on : <span><?= htmlspecialchars($fetch_orders['placed_on']); ?></span></p>
            <p>Name : <span><?= htmlspecialchars($fetch_orders['name']); ?></span></p>
            <p>Email : <span><?= htmlspecialchars($fetch_orders['email']); ?></span></p>
            <p>Phone Number : <span><?= htmlspecialchars($fetch_orders['number']); ?></span></p>
            <p>Address : <span><?= htmlspecialchars($fetch_orders['address']); ?></span></p>
            <p>Payment Method : <span><?= htmlspecialchars($fetch_orders['method']); ?></span></p>
            <p>Your orders : <span><?= htmlspecialchars($fetch_orders['total_products']); ?></span></p>
            <p>Total price : <span>RS.<?= htmlspecialchars($fetch_orders['total_price']); ?>/-</span></p>
            <p>Order status : 
               <span style="color:<?= $fetch_orders['order_status'] == 'pending' ? 'red' : 'green'; ?>">
                  <?= htmlspecialchars($fetch_orders['order_status']); ?>
               </span>
            </p>
         </div>
   <?php
         }
      } else {
         echo '<p class="empty">No orders placed yet!</p>';
      }
   }
   ?>

   <!-- Show success alert if payment was successful -->
   <?php if ($showSuccess): ?>
   <script>
       Swal.fire({
           icon: 'success',
           title: 'Payment Successful!',
           text: 'Your order has been placed successfully 🎉',
           confirmButtonColor: '#00baf2'
       });
   </script>
   <?php endif; ?>

   </div>

</section>

<?php include 'components/footer.php'; ?>

<script src="js/script.js"></script>

</body>
</html>
