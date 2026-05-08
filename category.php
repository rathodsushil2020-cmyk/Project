<?php

include 'components/connect.php';

include 'components/wishlist_cart.php';

// Fetch products from the database 
$category = isset($_GET['category']) ? $_GET['category'] : '';

if (!empty($category)) {
   $select_products = $conn->prepare("SELECT * FROM `products` WHERE category = ?");
   $select_products->execute([$category]);
} else {
   $select_products = $conn->prepare("SELECT * FROM `products`");
   $select_products->execute();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Category</title>
   
   <!-- Font Awesome CDN -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- Custom CSS -->
   <link rel="stylesheet" href="css/style.css">

</head>
<body>
   
<?php include 'components/user_header.php'; ?>

<section class="products">

   <h1 class="heading">Category: <?= htmlspecialchars($category) ?></h1>

   <div class="box-container">
   <?php
     if ($select_products->rowCount() > 0) {
        while ($fetch_product = $select_products->fetch(PDO::FETCH_ASSOC)) {
   ?>
   <form action="" method="post" class="box">
      <input type="hidden" name="pid" value="<?= $fetch_product['id']; ?>">
      <input type="hidden" name="name" value="<?= htmlspecialchars($fetch_product['name']); ?>">
      <input type="hidden" name="price" value="<?= $fetch_product['price']; ?>">
      <input type="hidden" name="image" value="<?= $fetch_product['image_01']; ?>">
      <button class="fas fa-heart" type="submit" name="add_to_wishlist"></button>
      <a href="quick_view.php?pid=<?= $fetch_product['id']; ?>" class="fas fa-eye"></a>
      <img src="uploaded_img/<?= $fetch_product['image_01']; ?>" alt="">
      <div class="name"><?= htmlspecialchars($fetch_product['name']); ?></div>
      <div class="category">Category: <?= htmlspecialchars($fetch_product['category']); ?></div>
      <div class="price">Rs. <?= $fetch_product['price']; ?></div>
      <input type="submit" value="Add to Cart" class="btn" name="add_to_cart">
   </form>
   <?php
      }
   } else {
      echo '<p class="empty">No products found!</p>';
   }
   ?>
   </div>

</section>
<?php include 'components/footer.php'; ?>
</body>
</html>
