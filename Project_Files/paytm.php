<?php
session_start();
if (!isset($_SESSION['pending_order'])) {
    header("Location: checkout.php");
    exit();
}

$order = $_SESSION['pending_order'];
$total = htmlspecialchars($order['total_price']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <title>Paytm Payment</title>
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
   <style>
      * {
         box-sizing: border-box;
         margin: 0;
         padding: 0;
      }
      body {
         font-family: 'Poppins', sans-serif;
         background: #e0e5ec;
         display: flex;
         align-items: center;
         justify-content: center;
         height: 100vh;
         color: #333;
      }
      .container {
         background: #e0e5ec;
         padding: 40px 30px;
         border-radius: 20px;
         box-shadow: 10px 10px 30px #babecc,
                     -10px -10px 30px #ffffff;
         text-align: center;
         width: 100%;
         max-width: 400px;
         transition: all 0.3s ease;
      }
      .container h2 {
         margin-bottom: 10px;
         font-weight: 600;
         color: #222;
      }
      .container p {
         margin-bottom: 20px;
         color: #666;
         font-size: 15px;
      }
      .amount {
         font-size: 28px;
         color: #007aff;
         margin: 15px 0 25px;
         font-weight: bold;
      }
      .btn, .cancel-btn {
         padding: 12px 30px;
         font-size: 16px;
         font-weight: 500;
         border: none;
         border-radius: 30px;
         cursor: pointer;
         margin: 5px;
         transition: background 0.3s;
      }
      .btn {
         background: linear-gradient(145deg, #007aff, #0051cc);
         color: #fff;
      }
      .btn:hover {
         background: linear-gradient(145deg, #0051cc, #007aff);
      }
      .cancel-btn {
         background: #f44336;
         color: white;
      }
      .cancel-btn:hover {
         background: #d32f2f;
      }
      .processing {
         margin-top: 25px;
         font-weight: 500;
         font-size: 16px;
         color: #28a745;
         display: none;
         animation: fadeIn 0.5s ease-in-out;
      }
      @keyframes fadeIn {
         from { opacity: 0; transform: translateY(10px); }
         to { opacity: 1; transform: translateY(0); }
      }
   </style>
</head>
<body>
   <div class="container">
      <h2>Pay with Paytm</h2>
      <p>You're about to pay:</p>
      <div class="amount">Rs. <?= $total ?> /-</div>
      <button class="btn" onclick="startPayment()">Continue to Pay</button>
      <button class="cancel-btn" onclick="cancelPayment()">Cancel</button>
      <div class="processing" id="processing">Processing payment, please wait...</div>
   </div>

   <script>
      function startPayment() {
         document.querySelector('.btn').style.display = 'none';
         document.querySelector('.cancel-btn').style.display = 'none';
         document.getElementById('processing').style.display = 'block';
         setTimeout(() => {
            window.location.href = "paytm_callback.php";
         }, 3000);
      }

      function cancelPayment() {
         window.location.href = "checkout.php";
      }
   </script>
</body>
</html>
