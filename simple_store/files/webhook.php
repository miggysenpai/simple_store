<?php 
ob_start();
require_once 'users/init.php'; 

// Basically, the main reason for this webhook endpoint is if the payment went through,
// but client had an error getting to the success page, so it was not able to inserted 
// into the database. So basically fall back code. 

// required stripe code
require_once 'usersc/plugins/simple_store/assets/stripe/vendor/autoload.php';

// checks whether to use live or sandbox keys
$stripe_key_check = $db->query("SELECT * FROM simple_store_stripe_keys")->first(); // get keys
if($stripe_key_check->is_live == 1){
    $stripeSecretKey = $stripe_key_check->live_secret;
} else {
    $stripeSecretKey = $stripe_key_check->sandbox_secret;
}

//connect to stripe api
$stripe = new \Stripe\StripeClient($stripeSecretKey);

// webhook secret
$endpoint_secret = $stripe_key_check->webhook;

$payload = @file_get_contents('php://input');
$sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'];
$event = null;

try {
  $event = \Stripe\Webhook::constructEvent(
    $payload, $sig_header, $endpoint_secret
  );
} catch(\UnexpectedValueException $e) {
  // Invalid payload
  http_response_code(400);
  exit();
} catch(\Stripe\Exception\SignatureVerificationException $e) {
  // Invalid signature
  http_response_code(400);
  exit();
}

// Handle the event
switch ($event->type) {
  case 'checkout.session.completed':
    //** code below NOT needed, i run multiple websites under the same stripe account. 
    $cancel_url = "https://".$_SERVER["SERVER_NAME"]."/cart.php";
    if($cancel_url != $event->data->object->cancel_url){
        echo " From : ".$_SERVER["SERVER_NAME"]." \n ,";
        echo " Cancel URL : ".$event->data->object->cancel_url." \n , ";
        echo " Wrong Webhook.";
        // reply to webhook. this message will be seen in the stripe logs. 
    } else {
    //** end code  
  
      
    $session_id = $event->data->object->id; // get session id
    $session = $stripe->checkout->sessions->retrieve($session_id); // get session information
    $items = $stripe->checkout->sessions->allLineItems($session_id, []); //get all products ordered
    $payment_intent = $stripe->paymentIntents->retrieve($session->payment_intent , []); // gets payment intent information
    $payment_info = $stripe->charges->retrieve($payment_intent->latest_charge, []); // gets payment information
    $receipt_number = substr($session->created, 0, 8); // the receipt number is the first 8 digits of when it was created (unix timestamp)
    // this sets address line 2 as empty for now, if not, it will crash when adding to database. 
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
    // prepares fields for database
    $checkout_fields = [
        'session_id' => $session_id,
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
    
    //gives it a few seconds before checking. waiting too long will cause an error. 
    // sleep(6);
    //checks if already in database (incase it was submitted through success page)
    $db_check = $db->query("SELECT * FROM simple_store_stripe_transactions WHERE session_id = ?",[$session_id])->first(); 
    //if not in database, add it 
    if(!isset($db_check->id)){
        $result = $db->insert('simple_store_stripe_transactions', $checkout_fields); // add to database

        //inserts item purchased
        
        foreach($items['data'] as $itemInfo){
            $item_id = $db->query("SELECT * FROM simple_store_products_variants WHERE price_id = ?",[$itemInfo['price']['id']])->first(); // get variant info
            
            $ordered_item_loop = [
                'customer_email' => $session->customer_details->email,
                'receipt_number' => $receipt_number,
                'checkout_session_id' => $session_id,
                'product_id' => $item_id->product_id,
                'price_id' => $itemInfo['price']['id'],
                'qty' => $itemInfo['quantity'],
                'purchased_date' => $session->created,
                ];
            $result_2 = $db->insert('simple_store_transactions_item', $ordered_item_loop);  
            unset($ordered_item_loop);
            
        }
        


        
        echo " From : ".$_SERVER["SERVER_NAME"]." \n ,";
        echo " Cancel URL : ".$event->data->object->cancel_url." \n , ";
        echo " Data inserted into database via Webhook .";
        // reply to webhook. this message will be seen in the stripe logs. 
        
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

    
        // if ($db->error()) {  die($db->errorString());  } 
    }  else { 
        echo " From : ".$_SERVER["SERVER_NAME"]." \n ,";
        echo " Cancel URL : ".$event->data->object->cancel_url." \n , ";
        echo " Data already in database.";
        // reply to webhook. this message will be seen in the stripe logs. 
        
     
     
     
        

        
    
        
        
        
        
        
        
        
    }
  
}     
}

http_response_code(200);