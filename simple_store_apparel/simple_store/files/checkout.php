<?php
ob_start();
require_once 'users/init.php';

$store_live_check = $db->query("SELECT * FROM simple_store_settings ")->first(); // get store settings
// checks if the store is live, if not, redirect to index.php
if($store_live_check->live == "0"){
header("Location: index.php");
die();
}

//start session and create cart session if one not available
session_start();
if(!isset($_SESSION['cart'])){
   $_SESSION['cart'] = [];
} 

//adds total items in cart
$sum = 0;
foreach($_SESSION['cart'] as $k) {
   $sum += $k['quantity']; 
}
//if there are no items, redirect to home, cant checkout on empty cart
if($sum == 0){
    header("Location: index.php");
    die();
}

// creates items in cart to array for stripe to read. 
$checkout = [];
foreach($_SESSION['cart'] as $prod) {
     $products = $db->query("SELECT price_id FROM simple_store_products_variants WHERE product_id = ? AND size = ?",[$prod['product_id'], $prod['size']])->first(); // get product price_id
     $checkout[] = [
         'price' => $products->price_id,
         'quantity' => $prod['quantity'],
         ];
}

//stripe required documents
require_once 'usersc/plugins/simple_store/assets/stripe/vendor/autoload.php';


//gets stripe keys in your db and checks whether to use live or sandbox keys
$stripe_key_check = $db->query("SELECT * FROM simple_store_stripe_keys")->first(); // get all keys
if($stripe_key_check->is_live == 1){
    $stripeSecretKey = $stripe_key_check->live_secret;
} else {
    $stripeSecretKey = $stripe_key_check->sandbox_secret;
}

if($stripe_key_check->stripe_coupons == 1){
    $stripe_coupon = "true";
} else {
    $stripe_coupon = "false";
}

//Stripe checkout code
\Stripe\Stripe::setApiKey($stripeSecretKey);
header('Content-Type: application/json');

$YOUR_DOMAIN = "https://".$_SERVER['SERVER_NAME']; // your server name. this should work fine, if not change to "www.yourdomain.com" 

$checkout_session = \Stripe\Checkout\Session::create([ // more settings available. check with stripe to see all options
  'line_items' => [[$checkout]], // uses the checkout array we created earlier
  'mode' => 'payment',
  'success_url' => $YOUR_DOMAIN . '/success.php?session_id={CHECKOUT_SESSION_ID}',
  'cancel_url' => $YOUR_DOMAIN . '/cart.php',
  'automatic_tax' => [
    'enabled' => true,
  ],
  'currency' => $stripe_key_check->stripe_currency,
  'allow_promotion_codes' => $stripe_coupon,
]);

header("HTTP/1.1 303 See Other");
header("Location: " . $checkout_session->url);
?>
