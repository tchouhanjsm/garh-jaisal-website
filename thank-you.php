<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
$enquiryReceived = !empty($_SESSION['enquiry_received']);
unset($_SESSION['enquiry_received']);
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Thank you | Garh Jaisal Haveli</title>
  <meta name="robots" content="noindex, nofollow">
  <link rel="icon" type="image/png" href="img/jaisalmerlogo.png">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Source+Serif+4:opsz,wght@8..60,400;8..60,600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/garh-v2.css">
</head>
<body>
<?php
$activePage = '';
include __DIR__ . '/include/header-v2.php';
?>

<main class="section">
  <div class="site-shell">
    <p class="eyebrow">Garh Jaisal Haveli</p>
    <h1>Thank you.</h1>
    <?php if ($enquiryReceived): ?>
      <p>Your enquiry was received. If you need to follow up, call <a href="tel:+917727047567">+91 77270 47567</a>.</p>
    <?php else: ?>
      <p>Thank you for visiting Hotel Garh Jaisal Haveli in Jaisalmer Fort.</p>
    <?php endif; ?>
    <p><a class="button button-primary" href="index.php">Back to home</a></p>
    <p><a class="button button-primary" href="contact.php">Contact the hotel</a></p>
  </div>
</main>

<?php include __DIR__ . '/include/footer-v2.php'; ?>
<script src="js/garh-v2.js"></script>
</body>
</html>
