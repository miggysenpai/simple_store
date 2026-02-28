<?php 
ob_start();
require_once 'users/init.php'; 

// start session and create cart session if one not available
session_start();
if(!isset($_SESSION['cart'])){
   $_SESSION['cart'] = [];
   }
   
// this empties cart, as checkout was successfull  
if(isset($_SESSION['cart'])){
     unset($_SESSION['cart']);
}   

$contact_email = $db->query("SELECT * FROM simple_store_settings ")->first(); // get store contact info 
  
// required stripe code
require_once 'usersc/plugins/simple_store/assets/stripe/vendor/autoload.php';

// check for keys and use the the correct one (live or sandbox)
$stripe_key_check = $db->query("SELECT * FROM simple_store_stripe_keys")->first(); // get keys
if($stripe_key_check->is_live == 1){
    $stripeSecretKey = $stripe_key_check->live_secret;
} else {
    $stripeSecretKey = $stripe_key_check->sandbox_secret;
}

// start a stripe connection
$stripe = new \Stripe\StripeClient($stripeSecretKey);


try {
  $session = $stripe->checkout->sessions->retrieve($_GET['session_id']); // retrieves session information
  $items = $stripe->checkout->sessions->allLineItems($_GET['session_id'], []); // retrieves all items purchased
  $payment_intent = $stripe->paymentIntents->retrieve($session->payment_intent , []); // retrieves payment intent info
  $payment_info = $stripe->charges->retrieve($payment_intent->latest_charge, []); // retrieves payment information
  

  //echo '<pre>' , var_dump($session) , '</pre>';
  //echo '<pre>' , var_dump($items) , '</pre>';
  //echo '<pre>' , var_dump($payment_intent) , '</pre>';
  //echo '<pre>' , var_dump($payment_info) , '</pre>';

  
  http_response_code(200);
} catch (Error $e) {
  http_response_code(500);
  echo json_encode(['error' => $e->getMessage()]);
}

// this is incase `address line 2` is empty, so it doesn't crash when inserting into database
$address_line2 = " ";  
$amount_tax = "0";
$amount_shipping = "0";
if(isset($session->customer_details->address->line2)) {
    $address_line2 = $session->customer_details->address->line2;
}
if(isset($session->total_details->amount_shipping)) {
    $amount_shipping = $session->total_details->amount_shipping;
}
if(isset($session->total_details->amount_tax)) {
    $amount_tax = $session->total_details->amount_tax;
}

// the "receipt number" is the first 8 characters of when the order was created (unix timestamp)
// when looking up the order, the customer will need customer email and receipt number to retrieve order
$receipt_number = substr($session->created, 0, 8);

// prepares fields for database
$checkout_fields = [
    'session_id' => $_GET['session_id'],
    'payment_intent_id' => $session->payment_intent,
    'receipt_number' => $receipt_number,
    'amount_total' => $session->amount_total,
    'amount_shipping' => $amount_shipping,
    'amount_tax' => $amount_tax,
    'created' => $session->created,
    'status' => '1',
    'customer_email' => $session->customer_details->email,
    'customer_name' => $session->customer_details->name,
    'address_city' => $session->customer_details->address->city,
    'address_country' => 'United States',
    'address_line1' => $session->customer_details->address->line1,
    'address_line2' => $address_line2,
    'address_postal_code' => $session->customer_details->address->postal_code,
    'address_state' => $session->customer_details->address->state,
    'payment_brand' => $payment_info->payment_method_details->card->brand,
    'payment_last4' => $payment_info->payment_method_details->card->last4,
    'payment_exp_month' => $payment_info->payment_method_details->card->exp_month,
    'payment_exp_year' => $payment_info->payment_method_details->card->exp_year,
];




//checks if already in database(page reloads)
$db_check = $db->query("SELECT * FROM simple_store_stripe_transactions WHERE session_id = ?",[$_GET['session_id']])->first(); 
if(!isset($db_check->id)){
    $result = $db->insert('simple_store_stripe_transactions', $checkout_fields);

    //inserts item purchased
    
    foreach($items['data'] as $itemInfo){
        $item_id = $db->query("SELECT * FROM simple_store_products_variants WHERE price_id = ?",[$itemInfo['price']['id']])->first(); // get item_id info
        
        $ordered_item_loop = [
            'customer_email' => $session->customer_details->email,
            'receipt_number' => $receipt_number,
            'checkout_session_id' => $_GET['session_id'],
            'product_id' => $item_id->product_id,
            'price_id' => $itemInfo['price']['id'],
            'qty' => $itemInfo['quantity'],
            'purchased_date' => $session->created,
            ];
            $result_2 = $db->insert('simple_store_transactions_item', $ordered_item_loop);
            unset($ordered_item_loop);
    }
    
    
    
   ## Send email    

    require_once('usersc/plugins/simple_store/assets/brevo_email/vendor/autoload.php');
    
    $email_loop = [];
    foreach($items['data'] as $productInfo){
                                $product_local_variant = $db->query("SELECT * FROM simple_store_products_variants WHERE price_id = ?",[$productInfo['price']['id']])->first(); // get variant info
                                $product_local = $db->query("SELECT * FROM simple_store_products WHERE id = ?",[$product_local_variant->product_id])->first(); // get product info
                                $product_img = $db->query("SELECT * FROM simple_store_products_images WHERE product_id = ? AND is_primary = ?",[$product_local->id, "1"])->first(); // get primary image info
                               $email_loop[]  = [
                                    'productImage' => $product_img->image,
                                    'productName' => $productInfo['description'],
                                    'productQuantity' => $productInfo['quantity'],
                               ];
                               
    } 
    
    
    $contact_email = $db->query("SELECT * FROM simple_store_settings ")->first(); // get store contact info 
    $brevo_check = $db->query("SELECT * FROM simple_store_brevo")->first(); // get keys  
   
    // Configure API key authorization: api-key
    $config = Brevo\Client\Configuration::getDefaultConfiguration()->setApiKey('api-key', $brevo_check->brevo_key);
    // Uncomment below to setup prefix (e.g. Bearer) for API key, if needed
    // $config = Brevo\Client\Configuration::getDefaultConfiguration()->setApiKeyPrefix('api-key', 'Bearer');
    // Configure API key authorization: partner-key
    $config = Brevo\Client\Configuration::getDefaultConfiguration()->setApiKey('partner-key', $brevo_check->brevo_key);
    // Uncomment below to setup prefix (e.g. Bearer) for API key, if needed
    // $config = Brevo\Client\Configuration::getDefaultConfiguration()->setApiKeyPrefix('partner-key', 'Bearer');
    
    $apiInstance = new Brevo\Client\Api\TransactionalEmailsApi(
        // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
        // This is optional, `GuzzleHttp\Client` will be used as default.
        new GuzzleHttp\Client(),
        $config
    );
    $sendSmtpEmail = new \Brevo\Client\Model\SendSmtpEmail([
     'subject' => $settings->site_name.' - Thanks for your order!',
     'sender' => ['name' => $settings->site_name, 'email' => $brevo_check->sender_email],
     'replyTo' => ['name' => $settings->site_name, 'email' => $brevo_check->sender_email],
     'to' => [[ 'name' => $session->customer_details->name, 'email' => $session->customer_details->email]],
     'htmlContent' => '<html lang="en">
            <head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, inital-scale=1.0"><title>{{params.siteName}} - Thank you for your order</title><link href="https://{{params.domainName}}/usersc/plugins/simple_store/assets/template/css/stylesheet.css" rel="stylesheet" type="text/css">
                <style>
                    @font-face {
                    font-family: "OneLittleFontRegular";
                    font-style: normal;
                    font-weight: 400;
                    src: url(https://{{params.domainName}}/usersc/plugins/simple_store/assets/template/css/one_little_font_regular-webfont.woff2) format("woff2");
                    }
                     div, p, body, button { font-family: "OneLittleFontRegular", Verdana, Helvetica, sans-serif !important; }
        
                    body{
                        font-family:  "OneLittleFontRegular" , sans-serif;
                    }
                    .email-container {
                        max-width: 600px;
                        font-family:  "OneLittleFontRegular", sans-serif;
                    }
                    .hero-content {
                        background: #ECF1FB;
                        border-radius: 5px;
                    }
                    .hero-container {
                        width: 90%;
                    }
                    .order-list {
                        background: #ECF1FB;
                        border-radius: 5px;
                    }
                    .order-list table {
                        background: #ffffff;
                        border-radius: 5px;
                    }
                    .order-information {
                        background: #ECF1FB;
                        border-radius: 5px;
                    }
                    .contact-email {
                        background: #ECF1FB;
                        border-radius: 5px;
                    }
                    .socials a {
                        text-decoration: none;
                        color:#000000;
                    }
                    .footer {
                        background: #ECF1FB;
                        border-radius: 5px;
                    }
                    .footer-container {
                        padding-top: 10px;
                        padding-bottom: 10px;
                    }
                    .product-image {
                        border-radius: 10px;
                    }
                    button {
                        border-radius: 5px;
                        border: none;
                        background-color: #0067FF;
                        color:#ffffff;
                        padding: 15px 32px;
                        text-align: center;
                        text-decoration: none;
                        display: inline-block;
                    }
                </style>
            </head>
            <body>
        
            <center>
                <div class="email-container">
                    <div class="header">
                        <br />
                        <a href="https://{{params.domainName}}" class="logo"><img width="110" height="110" src="https://{{params.domainName}}/{{params.brevoImage}}"></a>
                        <br /><br />
                    </div>
                    <div width="100%" class="hero-content">
                        <div class="hero-container" >
                            <img src="">
                            <p class="title"> Thanks for your order!</p>
                            <p>Great news! Your order is confirmed! We will send you the tracking information once your order ships!</p>
                            <br />
                            <a href="https://{{params.domainName}}/trackorder.php"><button>Track your order</button></a>
                            <br /><br />
                        </div>
                    </div>
                    <br />
                    <div width="100%" class="order-list">
                        <br />
                        <p class="title">Items in this order</p>
                        <p>Order number: #{{params.orderNumber}}</p>
                        <br />
                        
                        
                        {% for item in params.products %}
                        <table  width="90%">
                        <tr>
                            <td width="25%"><img class="product-image" style="border-radius: 10px" width="80" src="https://{{params.domainName}}/{{item.productImage}}"></td>
                            <td width="50%" >{{ item.productName }}</td>
                            <td>x {{ item.productQuantity }}</td>
                        </tr>
                        </table>
                        <br />
                        {% endfor %}
                        
                        
                              
                        
                        <br />
                    </div>
                    <br />
                    <div width="100%" class="order-information">
                     <br />
                        <table  width="90%">
                            <tr>
                                <td ><strong>Order Information</strong></td>
                            </tr>
                            <tr>
                                <td>Order Number : # {{params.orderNumber}}</td>
                            </tr>
                            <tr></tr>
                            <tr>
                                <td ><strong>Shipping Address</strong></td>
                            </tr>
                            <tr>
                                <td>{{params.addressLine1}}</td>
                            </tr>
                            <tr>
                                <td>{{params.addressLine2}}</td>
                            </tr>
                            <tr><td> {{params.addressCity}}, {{params.addressState}} {{params.addressPostalCode}} </td></tr>
                        </table>    <br /> <br /> 
                    </div>
                    <br />
                    
                    <div class="contact">
                        <div class="contact-email">
                        <br />
                        <p><strong>Problems with your order?</strong></p>
                        <p>Contact us</p>
                        <p>{{params.contactEmail}}</p>
                        <br />
                        </div>
                    </div>
                    <br />
                    
                  
                    <div class="footer">
                        <div class="footer-container">
                        Copyright © 2025 {{params.siteName}} All Rights Reserved
                        </div>
                    </div>
                    
                </div>  
            </center>
            </body>
        </html> ',
     'params' => ['orderNumber' => $receipt_number , 'contactEmail' => $contact_email->contact_email, 'siteName' => $settings->site_name , 'domainName' => $_SERVER['SERVER_NAME'], 'brevoImage' =>$brevo_check->brevo_image, 'addressLine1' => $session->customer_details->address->line1, 'addressLine2' => $address_line2, 'addressCity' => $session->customer_details->address->city, 'addressPostalCode' => $session->customer_details->address->postal_code, 'addressState' => $session->customer_details->address->state, 'products' => $email_loop ],
     
     
     
]); // \Brevo\Client\Model\SendSmtpEmail | Values to send a transactional email
   
    try {
        $result = $apiInstance->sendTransacEmail($sendSmtpEmail);
        //print_r($result);
    } catch (Exception $e) {
        echo 'Exception when calling TransactionalEmailsApi->sendTransacEmail: ', $e->getMessage(), PHP_EOL;
    }
    
    
// ##### SEND EMAIL TO YOURSELF #####
$sendSmtpEmail2 = new \Brevo\Client\Model\SendSmtpEmail([
     'subject' => $settings->site_name.' - New order placed!',
     'sender' => ['name' => $settings->site_name, 'email' => $brevo_check->sender_email],
     'replyTo' => ['name' => $settings->site_name, 'email' => $brevo_check->sender_email],
     'to' => [[ 'name' => $session->customer_details->name, 'email' => $brevo_check->email_self]],
     'htmlContent' => '<html lang="en">
            <head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, inital-scale=1.0"><title>{{params.siteName}} - Thank you for your order</title><link href="https://{{params.domainName}}/usersc/plugins/simple_store/assets/template/css/stylesheet.css" rel="stylesheet" type="text/css">
                <style>
                    @font-face {
                    font-family: "OneLittleFontRegular";
                    font-style: normal;
                    font-weight: 400;
                    src: url(https://{{params.domainName}}/usersc/plugins/simple_store/assets/template/css/one_little_font_regular-webfont.woff2) format("woff2");
                    }
                     div, p, body, button { font-family: "OneLittleFontRegular", Verdana, Helvetica, sans-serif !important; }
        
                    body{
                        font-family:  "OneLittleFontRegular" , sans-serif;
                    }
                    .email-container {
                        max-width: 600px;
                        font-family:  "OneLittleFontRegular", sans-serif;
                    }
                    .hero-content {
                        background: #ECF1FB;
                        border-radius: 5px;
                    }
                    .hero-container {
                        width: 90%;
                    }
                    .order-list {
                        background: #ECF1FB;
                        border-radius: 5px;
                    }
                    .order-list table {
                        background: #ffffff;
                        border-radius: 5px;
                    }
                    .order-information {
                        background: #ECF1FB;
                        border-radius: 5px;
                    }
                    .contact-email {
                        background: #ECF1FB;
                        border-radius: 5px;
                    }
                    .socials a {
                        text-decoration: none;
                        color:#000000;
                    }
                    .footer {
                        background: #ECF1FB;
                        border-radius: 5px;
                    }
                    .footer-container {
                        padding-top: 10px;
                        padding-bottom: 10px;
                    }
                    .product-image {
                        border-radius: 10px;
                    }
                    button {
                        border-radius: 5px;
                        border: none;
                        background-color: #0067FF;
                        color:#ffffff;
                        padding: 15px 32px;
                        text-align: center;
                        text-decoration: none;
                        display: inline-block;
                    }
                </style>
            </head>
            <body>
        
            <center>
                <div class="email-container">
                    <div class="header">
                        <br />
                        <a href="https://{{params.domainName}}" class="logo"><img width="110" height="110" src="https://{{params.domainName}}/{{params.brevoImage}}"></a>
                        <br /><br />
                    </div>
                    <div width="100%" class="hero-content">
                        <div class="hero-container" >
                            <img src="">
                            <p class="title"> Order was Placed!</p>
                            <p>Great news! Someone placed an order! Time to start working!</p>
                            <br />
                            <a href="https://{{params.domainName}}/store_admin.php?id=orders"><button>Go to orders </button></a>
                            <br /><br />
                        </div>
                    </div>
                    <br />
                    <div width="100%" class="order-list">
                        <br />
                        <p class="title">Items in this order</p>
                        <p>Order number: #{{params.orderNumber}}</p>
                        <br />
                        
                        
                        {% for item in params.products %}
                        <table  width="90%">
                        <tr>
                            <td width="25%"><img class="product-image" style="border-radius: 10px" width="80" src="https://{{params.domainName}}/{{item.productImage}}"></td>
                            <td width="50%" >{{ item.productName }}</td>
                            <td>x {{ item.productQuantity }}</td>
                        </tr>
                        </table>
                        <br />
                        {% endfor %}
                        
                        
                              
                        
                        <br />
                    </div>
                    <br />
                    <div width="100%" class="order-information">
                     <br />
                        <table  width="90%">
                            <tr>
                                <td ><strong>Order Information</strong></td>
                            </tr>
                            <tr>
                                <td>Order Number : # {{params.orderNumber}}</td>
                            </tr>
                            <tr></tr>
                            <tr>
                                <td ><strong>Shipping Address</strong></td>
                            </tr>
                            <tr>
                                <td>{{params.addressLine1}}</td>
                            </tr>
                            <tr>
                                <td>{{params.addressLine2}}</td>
                            </tr>
                            <tr><td> {{params.addressCity}}, {{params.addressState}} {{params.addressPostalCode}} </td></tr>
                        </table>    <br /> <br /> 
                    </div>
                    <br />
                    
                    <br />
                    
                  
                    <div class="footer">
                        <div class="footer-container">
                        Copyright © 2025 {{params.siteName}} All Rights Reserved
                        </div>
                    </div>
                    
                </div>  
            </center>
            </body>
        </html> ',
     'params' => ['orderNumber' => $receipt_number , 'contactEmail' => $contact_email->contact_email, 'siteName' => $settings->site_name , 'domainName' => $_SERVER['SERVER_NAME'], 'brevoImage' =>$brevo_check->brevo_image, 'addressLine1' => $session->customer_details->address->line1, 'addressLine2' => $address_line2, 'addressCity' => $session->customer_details->address->city, 'addressPostalCode' => $session->customer_details->address->postal_code, 'addressState' => $session->customer_details->address->state, 'products' => $email_loop ],
     
     
     
]); // \Brevo\Client\Model\SendSmtpEmail | Values to send a transactional email
   
    try {
        $result = $apiInstance->sendTransacEmail($sendSmtpEmail2);
        //print_r($result);
    } catch (Exception $e) {
        echo 'Exception when calling TransactionalEmailsApi->sendTransacEmail: ', $e->getMessage(), PHP_EOL;
    }
    
## End send email    
    
    
    
} 
// if ($db->error()) {  die($db->errorString());  } 
?>

<html lang="en">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>Success Page - <?php echo $settings->site_name;?></title>
    <!-- Vendor CSS Files -->
    <link href="<?php echo $us_url_root. 'usersc/plugins/simple_store/assets/template/'; ?>vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?php echo $us_url_root. 'usersc/plugins/simple_store/assets/template/'; ?>vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <link href="<?php echo $us_url_root. 'usersc/plugins/simple_store/assets/template/'; ?>vendor/aos/aos.css" rel="stylesheet">
    <link href="<?php echo $us_url_root. 'usersc/plugins/simple_store/assets/template/'; ?>vendor/swiper/swiper-bundle.min.css" rel="stylesheet">
  
    <!-- Main CSS File -->
    <link href="<?php echo $us_url_root. 'usersc/plugins/simple_store/assets/template/'; ?>css/main.css" rel="stylesheet">
</head>
    
<body>
    <header id="header" class="header d-flex align-items-center">
         <br /> <br /> 
    <div class="container position-relative d-flex align-items-center justify-content-between">
      
      <div class="logo d-flex align-items-center me-auto me-xl-0"></div>

    
      <div class="d-block  logo d-flex align-items-center me-auto me-xl-0">
          <a href="index.php" aria-label="Go to Homepage" ><?php echo $settings->site_name;?></a>
      </div>
      <div class="header-social-links">
          <div class="icon-cart">
                <a href="cart.php" aria-label="Go to Cart Page">
                    <i class="bi bi-cart text-black"></i>
                    <span>
                        0
                    </span>
                </a>
            </div>
      </div>
    </div>
  </header>
  
    <section id="order-confirmation" class="order-confirmation section" role="main">
      <div class="container" >
        <div class="order-confirmation-1">
          <div class="confirmation-header text-center" >
            <div class="success-icon mb-4">
              <i class="bi bi-check-circle-fill"></i>
            </div>
            
            <h1>Order Placed Successfully!</h1>
            
            <p class="lead">Thank you for your purchase. We've received your order and are processing it now.</p>
            
            <div class="order-number mt-3 mb-4">
              <span style="color:#575757">Order # : </span><strong><?=$receipt_number?></strong>  
              <br />
              <span  style="color:#575757">Email :</span>
              <strong><?=$session->customer_details->email?></strong>
              <span class="mx-2"  style="color:#575757">•</span>
              <span  style="color:#575757"><?=(date("m-d-Y",$session->created)); ?></span>
            </div>
          </div>

          <div class="order-details p-4 mb-4" >
            <div class="row">
              <div class="col-md-6 mb-4 mb-md-0">
                <div class="custom-h4">Shipping Information</div>
                <address class="mt-3">
                  <strong><?=$session->customer_details->name?></strong><br>
                  <?=$session->customer_details->address->line1?><br>
                  <?php if(isset($session->customer_details->address->line2)) {echo $session->customer_details->address->line2."<br>";} ?>
                  <?=$session->customer_details->address->city?>, <?=$session->customer_details->address->state?> <?=$session->customer_details->address->postal_code?><br>
                  United States<br>
                  <?php // check if isset phone, if not, its okay
                    if(isset($session->customer_details->phone)){
                        echo '<i class="bi bi-telephone-fill me-1 text-muted small"></i> '. $session->customer_details->phone ;
                    }
                  ?>
                </address>
                <div class="mt-3">
                  <span class="shipping-method">
                    <i class="bi bi-truck me-2"></i>Ships within (3-5 business days)
                  </span>
                </div>
              </div>
              <div class="col-md-6">
                <div class="custom-h4">Payment Information</div>
                <div class="payment-info mt-3">
                  <div class="payment-method mb-2">
                    <i class="bi bi-credit-card me-2"></i>
                    <span><?=$payment_info->payment_method_details->card->brand?> ending in <?=$payment_info->payment_method_details->card->last4?></span>
                  </div>
                  <div class="billing-address">
                    <strong>Billing Address:</strong> Same as shipping
                  </div>
                </div>
              </div>
            </div>
          </div>
    
          <div class="account ">
                <div class="content-area ">
                    <div class="orders-grid ">
                        <div class="order-card " >
                            <div class="custom-h4">Tracking Summary</div>

                          <!-- Order Tracking -->
                          <div class=" tracking-info" id="tracking1">
                            <div class="tracking-timeline">
                              <div class="timeline-item completed">
                                  
                                <div class="timeline-icon">
                                  <i class="bi bi-check-circle-fill"></i>
                                </div>
                                
                                <div class="timeline-content ">
                                  <div class="custom-h5">Order Confirmed</div>
                                  <p>Your order has been received and confirmed</p>
                                  <span class="timeline-date"><?=(date("Y-m-d h:sa",$session->created)); ?> </span>
                                </div>
                              </div>
    
                              <div class="timeline-item">
                                <div class="timeline-icon  ">
                                  <i class="bi bi-scissors"></i>
                                </div>
                                <div class="timeline-content">
                                  <div class="custom-h5">Processing</div>
                                  <p>Your order is being made</p>
                                  <span class="timeline-date"></span>
                                </div>
                              </div>
            
                              <div class="timeline-item ">
                               <div class="timeline-icon">
                                  <i class="bi bi-box-seam"></i>
                                </div>
                                <div class="timeline-content">
                                  <div class="custom-h5">Packaging</div>
                                  <p>Your items are being packaged for shipping</p>
                                  <span class="timeline-date"></span>
                                </div>
                              </div>
            
                              <div class="timeline-item">
                                <div class="timeline-icon">
                                  <i class="bi bi-truck"></i>
                                </div>
                                <div class="timeline-content">
                                  <div class="custom-h5">In Transit</div>
                                  <p>Your package is on the way</p>
                                </div>
                              </div>
            
                              <div class="timeline-item">
                                <div class="timeline-icon">
                                  <i class="bi bi-house-door"></i>
                                </div>
                                <div class="timeline-content">
                                  <div class="custom-h5">Delivery</div>
                                  <p>Your package has been delivered</p>
                                </div>
                              </div>
                            </div>
                          </div>
                        </div>
                    </div>
                </div>
            </div>

          <div class="order-summary mb-4" >
            <div class="custom-h4">Order Summary</div>
            <div class="order-items mt-3">
                
                <?php 
                // loops/shows all ordered items
                foreach($items['data'] as $productInfo){
                    $product_local_variant = $db->query("SELECT * FROM simple_store_products_variants WHERE price_id = ?",[$productInfo['price']['id']])->first(); // get variant info
                    $product_local = $db->query("SELECT * FROM simple_store_products WHERE id = ?",[$product_local_variant->product_id])->first(); // get product info
                    $product_img = $db->query("SELECT * FROM simple_store_products_images WHERE product_id = ? AND is_primary = ?",[$product_local->id, "1"])->first(); // get primary image info
                 ?>        
                
                  <div class="item-row d-flex">
                    <div class="item-image">
                      <img src="<?=$product_img->image?>" alt="Product <?=$productInfo['description']?>" aria-label="Image of Product named <?=$productInfo['description']?>" class="img-fluid" loading="lazy">
                    </div>
                    <div class="item-details">
                      <div class="custom-h5"><?=$productInfo['description']?></div>
                      <p class="text-muted">Color: <?=$product_local->color?> / Size: <?=$product_local_variant->size?></p>
                      <div class="quantity-price d-flex justify-content-between">
                        <span>Qty: <?=$productInfo['quantity']?></span>
                        <span class="price">$<?=$productInfo['amount_total']/100?></span>
                      </div>
                    </div>
                  </div>
              <?php } ?>
            </div>

            <div class="order-totals mt-4">
              <div class="d-flex justify-content-between py-2">
                <span>Subtotal</span>
                <span>$<?=$session->amount_subtotal/100?></span>
              </div>
              <div class="d-flex justify-content-between py-2">
                <span>Shipping</span>
                <span>$<?=$session->total_details->amount_shipping/100?></span>
              </div>
              <div class="d-flex justify-content-between py-2">
                <span>Tax</span>
                <span>$<?=$session->total_details->amount_tax/100?></span>
              </div>
              <div class="d-flex justify-content-between py-2 total-row">
                <strong>Total</strong>
                <strong>$<?=$session->amount_total/100?></strong>
              </div>
            </div>
          </div>

          <div class="next-steps text-center p-4" >
            <div class="custom-h4">What's Next?</div>
            <p>You'll receive an email confirmation shortly at <strong><?=$session->customer_details->email?></strong></p>
            <div class="tracking-info mb-4">
              <i class="bi bi-envelope me-2"></i>We'll send tracking information once your order ships
            </div>
            <div class="action-buttons">
              <a href="index.php" class="btn btn-primary me-3 mb-2 mb-md-0" aria-label="Go to Homepage">
                <i class="bi bi-bag me-2"></i>Continue Shopping
              </a>
            </div>
          </div>

          <div class="help-contact text-center mt-5" >
            <p>Need help with your order? <a href="support.php?id=contact" aria-label="Go to Support Page">Contact our support team</a></p>
          </div>
        </div>

      </div>

    </section>
    
 <footer id="footer" class="footer">

    <div class="footer-main ">
      <div class="container">
        <div class="row gy-4">
          
          <div class="col-lg-4 col-md-6 col-sm-6">
            <div class="footer-widget">
              <p><strong>Support</strong></p>
              <ul class="footer-links">
                <li><a href="trackorder.php" aria-label="Order Tracking Page">Order tracking</a></li>
                <li><a href="support.php?id=shipping" aria-label="Go to Shipping Page">Shipping Info</a></li>
                <li><a href="support.php?id=returnpolicy" aria-label="Go to Returns Page">Returns &amp; Exchanges</a></li>
                <li><a href="support.php?id=contact" aria-label="Go to Contact Page">Contact</a></li>
              </ul>
            </div>
          </div>
          
         <div class="col-lg-4 col-md-6 col-sm-6">
            <div class="footer-widget">
              <p><strong>About</strong></p>
              <ul class="footer-links">
                <li><a href="index.php" aria-label="Go to Homepage">Home</a></li>
                <li><a href="support.php?id=ourstory" aria-label="Go to Our Story">Our Story</a></li>
              </ul>
            </div>
          </div> 

          <div class="col-lg-4 col-md-6 col-sm-6">
            <div class="footer-widget">
              
            <?php 
            $socials = $db->query("SELECT * FROM simple_store_settings")->first(); // get socials info  
            // quick check to decide to show or not
            $socials_count_total = 0;
            if(!empty($socials->facebook)){ $socials_count_total = $socials_count_total + 1; }
            if(!empty($socials->instagram)){ $socials_count_total = $socials_count_total + 1; }
            if(!empty($socials->tiktok)){ $socials_count_total = $socials_count_total + 1; }
            if(!empty($socials->youtube)){ $socials_count_total = $socials_count_total + 1; }
            if($socials_count_total == 0) {
                // show nothing 
            } else { ?>

                <p><strong>Follow Us</strong></p>
                <div class="social-icons"> 
                    <?php if($socials->facebook == ""){echo "";}else{echo "<a href='".$socials->facebook."' aria-label='Facebook'><i class='bi bi-facebook'></i></a><br />";}?>
                    <?php if($socials->instagram == ""){echo "";}else{echo "<a href='".$socials->instagram."' aria-label='Instagram'><i class='bi bi-instagram'></i></a><br />";}?>
                    <?php if($socials->tiktok == ""){echo "";}else{echo "<a href='".$socials->tiktok."' aria-label='Tiktok'><i class='bi bi-tiktok'></i></a><br />";}?>
                    <?php if($socials->youtube == ""){echo "";}else{echo "<a href='".$socials->youtube."' aria-label='Youtube'><i class='bi bi-youtube'></i></a><br />";}?>
                </div>
              
            <?php } ?> 
            </div>
          </div>
          
          
        </div>
      </div>
    </div>

    <div class="footer-bottom">
      <div class="container">
        <div class="copyright text-center">
            <?php if(isUserLoggedIn()) { $login_out = "users/logout.php";} else { $login_out = "users/login.php";  }?>
          <p><strong class="sitename"><a href="<?=$login_out?>" aria-label="Go to Login"><?php echo $settings->site_name;?></a></strong> // All Rights Reserved.</p>
          <br />
          <?php if(isAdmin()){echo '<a href="users/admin.php" class="btn rounded border" aria-label="Go to Admin Page"> <span>Admin</span> </a> <a href="store_admin.php" class="btn rounded border" aria-label="Go to Store Admin"> <span>Store Admin</span> </a> <a href="users/logout.php" class="btn rounded border" aria-label="Logout"> <span>Logout</span> </a>';  } ?>
        </div>
      </div>
    </div>
  </footer>  
   

    <!-- Vendor JS Files -->
    <script src="<?php echo $us_url_root. 'usersc/plugins/simple_store/assets/template/'; ?>vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo $us_url_root. 'usersc/plugins/simple_store/assets/template/'; ?>vendor/php-email-form/validate.js"></script>
    <script src="<?php echo $us_url_root. 'usersc/plugins/simple_store/assets/template/'; ?>vendor/aos/aos.js"></script>
    <script src="<?php echo $us_url_root. 'usersc/plugins/simple_store/assets/template/'; ?>vendor/swiper/swiper-bundle.min.js"></script>
    
    <script src="<?php echo $us_url_root. 'usersc/plugins/simple_store/assets/template/'; ?>vendor/glightbox/js/glightbox.min.js"></script>
    <script src="<?php echo $us_url_root. 'usersc/plugins/simple_store/assets/template/'; ?>vendor/imagesloaded/imagesloaded.pkgd.min.js"></script>
    <script src="<?php echo $us_url_root. 'usersc/plugins/simple_store/assets/template/'; ?>vendor/isotope-layout/isotope.pkgd.min.js"></script>
    
    
    <!-- Main JS File -->
    <script src="<?php echo $us_url_root. 'usersc/plugins/simple_store/assets/template/'; ?>js/main.js"></script>
    
    
    
</body>
</html>
