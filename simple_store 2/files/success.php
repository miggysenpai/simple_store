<?php 
ob_start();
require_once 'users/init.php'; 

//Brevo likes to be loaded in first i guess    
require_once('usersc/plugins/simple_store/assets/brevo_email/vendor/autoload.php');
use Brevo\Brevo;
use Brevo\TransactionalEmails\Requests\SendTransacEmailRequest;
use Brevo\TransactionalEmails\Types\SendTransacEmailRequestSender;
use Brevo\TransactionalEmails\Types\SendTransacEmailRequestToItem;    


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
    
    
    
   ## Send email to customer 

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
   
   
    $client = new Brevo($brevo_check->brevo_key);
    
    $result = $client->transactionalEmails->sendTransacEmail(
    new SendTransacEmailRequest([
        'subject' => $settings->site_name.' - Thanks for your order!',
        'htmlContent' => '
            <html lang="en">
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
        </html> 
        ',
        'sender' => new SendTransacEmailRequestSender([
            'name' => $settings->site_name,
            'email' => $brevo_check->sender_email,
        ]),
        'to' => [
            new SendTransacEmailRequestToItem([
                'email' => $session->customer_details->email,
                'name' => $session->customer_details->name,
            ]),
        ],
        'params' => [
            'orderNumber' => $receipt_number , 
            'contactEmail' => $contact_email->contact_email, 
            'siteName' => $settings->site_name , 
            'domainName' => $_SERVER['SERVER_NAME'], 
            'brevoImage' =>$brevo_check->brevo_image, 
            'addressLine1' => $session->customer_details->address->line1, 
            'addressLine2' => $address_line2, 
            'addressCity' => $session->customer_details->address->city, 
            'addressPostalCode' => $session->customer_details->address->postal_code, 
            'addressState' => $session->customer_details->address->state, 
            'products' => $email_loop
        ],
    ])
);
//echo 'Order confirmation sent. Message ID: ' . $result->messageId . PHP_EOL;    
    

    
// ##### SEND EMAIL TO YOURSELF #####


$result2 = $client->transactionalEmails->sendTransacEmail(
    new SendTransacEmailRequest([
        'subject' => $settings->site_name.' - New order placed!',
        'htmlContent' => '
            <html lang="en">
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
            </html> 
        ',
        'sender' => new SendTransacEmailRequestSender([
            'name' => $settings->site_name,
            'email' => $brevo_check->sender_email,
        ]),
        'to' => [
            new SendTransacEmailRequestToItem([
                'email' => $brevo_check->sender_email,
                'name' => $settings->site_name,
            ]),
        ],
        'params' => [
            'orderNumber' => $receipt_number , 
            'contactEmail' => $contact_email->contact_email, 
            'siteName' => $settings->site_name , 
            'domainName' => $_SERVER['SERVER_NAME'], 
            'brevoImage' =>$brevo_check->brevo_image, 
            'addressLine1' => $session->customer_details->address->line1, 
            'addressLine2' => $address_line2, 
            'addressCity' => $session->customer_details->address->city, 
            'addressPostalCode' => $session->customer_details->address->postal_code, 
            'addressState' => $session->customer_details->address->state, 
            'products' => $email_loop 
        ],
    ])
);

//echo 'Order confirmation sent. Message ID: ' . $result2->messageId . PHP_EOL;
    
## End send email    
    
    
    
}  
// if ($db->error()) {  die($db->errorString());  } 
?>

<? require_once 'usersc/plugins/simple_store/assets/template/'.'views/header.php'; //Header ?> 


<main class="main">

    <!-- Page Title -->
    <div class="page-title light-background">
      <div class="container d-lg-flex justify-content-between align-items-center">
        <h1 class="mb-2 mb-lg-0">Order Confirmation</h1>
        <nav class="breadcrumbs">
          <ol>
            <li><a href="/">Home</a></li>
            <li class="current">Order Confirmation</li>
          </ol>
        </nav>
      </div>
    </div><!-- End Page Title -->

    <!-- Order Confirmation Section -->
    <section id="order-confirmation" class="order-confirmation section">

      <div class="container" data-aos="fade-up" data-aos-delay="100">

        <div class="order-confirmation-3">
          <div class="row g-0">
            <!-- Left sidebar with order summary -->
            <div class="col-lg-4 sidebar" data-aos="fade-right">
              <div class="sidebar-content">
                <!-- Success animation -->
                <div class="success-animation">
                  <i class="bi bi-check-lg"></i>
                </div>

                <!-- Order number and date -->
                <div class="order-id">
                  <h4>Order #<?=$receipt_number?></h4>
                  <div class="order-date"><?=(date("F j, Y",$session->created)); ?></div>
                </div>

                <!-- Order progress stepper -->
                <div class="order-progress">
                  <div class="stepper-container">
                    <div class="stepper-item current">
                      <div class="stepper-icon">1</div>
                      <div class="stepper-text">Confirmed</div>
                    </div>
                    <div class="stepper-item ">
                      <div class="stepper-icon">2</div>
                      <div class="stepper-text">Processing</div>
                    </div>
                    <div class="stepper-item ">
                      <div class="stepper-icon">3</div>
                      <div class="stepper-text">Packaging</div>
                    </div>
                    <div class="stepper-item">
                      <div class="stepper-icon">4</div>
                      <div class="stepper-text">Shipped</div>
                    </div>
                    <div class="stepper-item">
                      <div class="stepper-icon">5</div>
                      <div class="stepper-text">Delivered</div>
                    </div>
                  </div>
                </div>

                <!-- Price summary -->
                <div class="price-summary">
                  <h5>Order Summary</h5>
                  <ul class="summary-list">
                    <li>
                      <span>Subtotal</span>
                      <span>$ <?=$session->amount_subtotal/100?></span>
                    </li>
                    <li>
                      <span>Shipping</span>
                      <span>$ <?=$session->total_details->amount_shipping/100?></span>
                    </li>
                    <li>
                      <span>Tax</span>
                      <span>$ <?=$session->total_details->amount_tax/100?></span>
                    </li>
                    <li class="total">
                      <span>Total</span>
                      <span>$ <?=$session->amount_total/100?></span>
                    </li>
                  </ul>
                </div>

                <!-- Delivery info -->
                <div class="delivery-info">
                  <h5>Delivery Information</h5>
                  <p class="delivery-estimate">
                    <i class="bi bi-calendar-check"></i>
                    <span>Ships within (3-5 business days) </span>
                  </p>
                  <p class="shipping-method">
                    <i class="bi bi-truck"></i>
                    <span>Free Shipping</span>
                  </p>
                </div>
                

                <!-- Customer service -->
                <div class="customer-service">
                  <h5>Need Help?</h5>
                  <a href="support.php?id=contact" class="help-link">
                    <i class="bi bi-chat-dots"></i>
                    <span>Contact Support</span>
                  </a>
                  <a href="support.php?id=shipping" class="help-link">
                    <i class="bi bi-question-circle"></i>
                    <span>FAQs</span>
                  </a>
                </div>
              </div>
            </div>

            <!-- Main content area -->
            <div class="col-lg-8 main-content" data-aos="fade-in">
              <!-- Thank you message -->
              <div class="thank-you-message">
                <h1>Thanks for your order!</h1>
                <p>We've received your order and will begin processing it right away.
                  We'll send you updates via email as your order progresses.</p>
              </div>

              <!-- Shipping details -->
              <div class="details-card" data-aos="fade-up">
                <div class="card-header" data-toggle="collapse">
                  <h3>
                    <i class="bi bi-geo-alt"></i>
                    Shipping Details
                  </h3>
                </div>
                <div class="card-body">
                  <div class="row g-4">
                    <div class="col-md-6">
                      <div class="detail-group">
                        <label>Ship To</label>
                        <address>
                          <?=$session->customer_details->name?><br>
                          <?=$session->customer_details->address->line1?><br>
                          <?php if(isset($session->customer_details->address->line2)) {echo $session->customer_details->address->line2."<br>";} ?>
                          <?=$session->customer_details->address->city?>, <?=$session->customer_details->address->state?> <?=$session->customer_details->address->postal_code?><br>
                          United States
                        </address>
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="detail-group">
                        <label>Contact</label>
                        <div class="contact-info">
                          <p><i class="bi bi-envelope"></i><?=$session->customer_details->email?></p>
                          <?php // check if isset phone, if not, its okay
                            if(isset($session->customer_details->phone)){
                                echo '<p><i class="bi bi-telephone"></i> '. $session->customer_details->phone .'</p>';
                            }
                          ?>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Payment details -->
              <div class="details-card" data-aos="fade-up">
                <div class="card-header" data-toggle="collapse">
                  <h3>
                    <i class="bi bi-credit-card"></i>
                    Payment Details
                  </h3>
                </div>
                <div class="card-body">
                  <div class="payment-method">
                    <div class="payment-icon">
                      <i class="bi bi-credit-card-2-front"></i>
                    </div>
                    <div class="payment-details">
                      <div class="card-type"><?=$payment_info->payment_method_details->card->brand?></div>
                      <div class="card-number">•••• •••• •••• <?=$payment_info->payment_method_details->card->last4?></div>
                    </div>
                  </div>
                  <div class="billing-address mt-4">
                    <h5>Billing Address</h5>
                    <p>Same as shipping address</p>
                  </div>
                </div>
              </div>

              <!-- Order items -->
              <div class="details-card" data-aos="fade-up">
            <div class=" card-header" data-toggle="collapse">
                <h3>
                  <i class="bi bi-bag-check"></i>
                  Order Items
                </h3>
              </div>
              <div class="card-body">
                  
                <?php 
                // loops/shows all ordered items
                foreach($items['data'] as $productInfo){
                    $product_local_variant = $db->query("SELECT * FROM simple_store_products_variants WHERE price_id = ?",[$productInfo['price']['id']])->first(); // get variant info
                    $product_local = $db->query("SELECT * FROM simple_store_products WHERE id = ?",[$product_local_variant->product_id])->first(); // get product info
                    $product_img = $db->query("SELECT * FROM simple_store_products_images WHERE product_id = ? AND is_primary = ?",[$product_local->id, "1"])->first(); // get primary image info
                 ?>
                    <div class="item">
                      <div class="item-image">
                        <img src="<?=$product_img->image?>" alt="Product image for <?=$productInfo['description']?>" aria-label="Image of Product named <?=$productInfo['description']?>" loading="lazy">
                      </div>
                      <div class="item-details">
                        <h4><?=$productInfo['description']?></h4>
                        <div class="item-meta">
                          <span>Color: <?=$product_local->color?></span>
                          <span>Size: <?=$product_local_variant->size?></span>
                        </div>
                        <div class="item-price">
                          <span class="quantity"><?=$productInfo['quantity']?> ×</span>
                          <span class="price">$<?=$product_local->price?></span>
                        </div>
                      </div>
                    </div>
              <?php } ?>
                
              </div>
            </div>
            
            <!-- Payment details -->
              <div class="details-card" data-aos="fade-up">
                <div class="card-header" data-toggle="collapse">
                  <h3>
                    <i class="bi bi-question-circle"></i>
                    What's Next? 
                  </h3>
                </div>
                <div class="card-body">
                  <div class="payment-method">
                    <div class="payment-icon">
                      <i class="bi bi-envelope-at"></i>
                    </div>
                    <div class="payment-details">
                      <div class="card-type">You'll receive an email confirmation shortly at <?=$session->customer_details->email?></div>
                      <div class="card-number">We'll send tracking information once your order ships</div>
                    </div>
                  </div>
                </div>
              </div>
              
              <!-- Action buttons -->
            <div class="action-area" data-aos="fade-up">
              <div class="row g-3">
                <div class="col-md-12">
                  <a href="/index.php?id=shop_all" class="btn btn-back">
                    <i class="bi bi-arrow-left"></i>
                     Continue Shopping
                  </a>
                </div>
              </div>
            </div>
            
          </div>
        </div>
      </div>

      </div>

    </section><!-- /Order Confirmation Section -->

  </main>

    
<? require_once 'usersc/plugins/simple_store/assets/template/'.'views/footer.php'; //Footer ?> 
