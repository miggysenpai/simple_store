<?php 
ob_start();
require_once 'users/init.php'; 

//start session and create cart session if one not already available
session_start();
if(!isset($_SESSION['cart'])){
   $_SESSION['cart'] = [];
   } 
?>
<? require_once 'usersc/plugins/simple_store/assets/template/'.'views/header.php'; //Header ?> 
  
    <section id="order-confirmation" class="order-confirmation section">
      <div class="container " style="min-height: 60vh;">
           <?php
           $post = 0;
           if(isset($_GET['order_number']) && isset($_GET['order_email']) ){$post = 1;}
           if(!$_POST ){$post = 1;}
         

           // order tracking form
           if($post == 0){?>  
                    <br /><br /><br /><br />
                    <div class="settings-section text-center" data-aos="fade-up" >
                      <h3>Order Tracking</h3>
                      <form class="php-email-form settings-form" action="" method="POST">
                        <div class="row g-3  justify-content-center">
                          <div class="col-md-8">
                            <label for="order_number" class="form-label">Order Number</label>
                            <input type="text" class="form-control" id="order_number" name="order_number" placeholder="Order Number" required>
                          </div>
                          <div class="col-md-8">
                            <label for="order_email" class="form-label">Email</label>
                            <input type="text" class="form-control" id="order_email" name="order_email" placeholder="Order Email" required>
                          </div>
                          <div class="col-md-8">
                            <div class="form-buttons">
                              <button type="submit" class="btn-save">Look up order</button>
                            </div>
                          </div>
                        </div>

                        

                    
                      </form>
                    </div>           
           
          
          <br />
          <p class="text-center">Need help with your order? <a href="support.php?id=contact" aria-label="Go to Contact Page">Contact our support team</a></p>
    <?php }
    
    // check if post 
    if($post == 1){
        $post_order = Input::get('order_number');
        $post_email = Input::get('order_email');
        if(isset($post_order) && isset($post_email)) {
            $order_check = $db->query("SELECT * FROM simple_store_stripe_transactions WHERE customer_email = ? AND receipt_number = ?",[$post_email, $post_order])->first(); // get order info
            
            // show if wrong order number or email
            if(!isset($order_check->id)){
                echo '
                    <br /><br /><br /><br />
                    <div class="settings-section text-center" data-aos="fade-up" >
                      <h3>Order Tracking</h3>
                      <form class="php-email-form settings-form" >
                        <div class="row g-3  justify-content-center">
                          <div class="col-md-8">
                            Wrong order number or email . . .  
                                <br /> <br />
                                please try again. 
                          </div>
                          <div class="col-md-8">
                            <div class="form-buttons">
                              <a href="trackorder.php"><button type="submit" class="btn-save">Return</button></a>
                            </div>
                          </div>
                        </div>

                    
                      </form>
                    </div>           
           
          
                  <br />
                  <p class="text-center">Need help with your order? <a href="support.php?id=contact" aria-label="Go to Contact Page">Contact our support team</a></p>
                ';
                
                
            } else {
            // this shows if the correct email and order number was used
            ?>
            
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
                  <h4>Order #<?=$order_check->receipt_number?></h4>
                  <div class="order-date"><?=(date("F j, Y",$order_check->created)); ?></div>
                </div>

                <!-- Order progress stepper -->
                <div class="order-progress">
                  <div class="stepper-container">
                    <div class="stepper-item current">
                      <div class="stepper-icon">1</div>
                      <div class="stepper-text">Confirmed</div>
                    </div>
                    <div class="stepper-item current">
                      <div class="stepper-icon">2</div>
                      <div class="stepper-text">Processed</div>
                    </div>
                    <div class="stepper-item current">
                      <div class="stepper-icon">3</div>
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
                      <span>$ <?=$order_check->amount_subtotal/100?></span>
                    </li>
                    <li>
                      <span>Shipping</span>
                      <span>$ <?=$order_check->amount_shipping/100?></span>
                    </li>
                    <li>
                      <span>Tax</span>
                      <span>$ <?=$order_check->amount_tax/100?></span>
                    </li>
                    <li class="total">
                      <span>Total</span>
                      <span>$ <?=$order_check->amount_total/100?></span>
                    </li>
                  </ul>
                </div>

                <!-- Delivery info -->
                <div class="delivery-info">
                  <h5>Delivery Information</h5>
                  <p class="delivery-estimate">
                    <i class="bi bi-calendar-check"></i>
                    <span>Download Available Now</span>
                  </p>
                  <p class="shipping-method">
                    <i class="bi bi-patch-exclamation"></i>
                    <span>This is a digital product, no exchanges or refunds</span>
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
                <p>We've received your payment and your files are available for instant download!</p>
              </div>

              <!-- Shipping details -->
              <div class="details-card" data-aos="fade-up">
                <div class="card-header" data-toggle="collapse">
                  <h3>
                    <i class="bi bi-geo-alt"></i>
                    Shipping Details
                  </h3>
                  <i class="bi bi-chevron-down toggle-icon"></i>
                </div>
                <div class="card-body">
                  <div class="row g-4">
                    <div class="col-md-6">
                      <div class="detail-group">
                        <label>Ship To</label>
                        <address>
                          This is a DIGITAL PRODUCT, No physical item will be sent  
                        </address>
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="detail-group">
                        <label>Contact</label>
                        <div class="contact-info">
                          <p><i class="bi bi-envelope"></i><?=$order_check->customer_email?></p>
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
                  <i class="bi bi-chevron-down toggle-icon"></i>
                </div>
                <div class="card-body row">
                  <div class="payment-method col-md-6">
                    <div class="payment-icon">
                      <i class="bi bi-credit-card-2-front"></i>
                    </div>
                    <div class="payment-details">
                      <div class="card-type"><?=$order_check->payment_brand?></div>
                      <div class="card-number">•••• •••• •••• <?=$order_check->payment_last4?></div>
                    </div>
                  </div>
                  
                  <div class="billing-address col-md-6">
                    <h5>Billing Address</h5>
                        <address>
                          <?=$order_check->customer_name?><br>
                          <?=$order_check->address_line1?><br>
                          <?php if(isset($order_check->address_line2)) {echo $order_check->address_line2."<br>";} ?>
                          <?=$order_check->address_city?>, <?=$order_check->address_state?> <?=$order_check->address_postal_code?><br>
                          United States
                        </address>
                  </div>
                </div>
              </div>

              <!-- Order items -->
              <div class="details-card" data-aos="fade-up" >
                <div class=" card-header" data-toggle="collapse">
                    <h3>
                      <i class="bi bi-bag-check"></i>
                      Order Items
                    </h3>
                    <i class="bi bi-chevron-down toggle-icon"></i>
                  </div>
                  <div class="card-body">
                    
                    <?php 
                        $items = $db->query("SELECT * FROM simple_store_transactions_item WHERE customer_email = ? AND receipt_number = ? ", [$post_email, $post_order])->results(); 
                        
                        // loop/show each item ordered
                        foreach($items as $productInfo){
                            $product_local_variant = $db->query("SELECT * FROM simple_store_products_variants WHERE price_id = ?",[$productInfo->price_id])->first(); // get variant info (size)
                            $product_local = $db->query("SELECT * FROM simple_store_products WHERE id = ?",[$product_local_variant->product_id])->first(); // get product info
                            $product_img = $db->query("SELECT * FROM simple_store_products_images WHERE product_id = ? AND is_primary = ?",[$product_local->id, "1"])->first(); // get primary image
                         ?>     
                            <div class="item">
                              <div class="item-image">
                                <img src="<?=$product_img->image?>" alt="Product" loading="lazy">
                              </div>
                              <div class="item-details">
                                <h4><?=$product_local->name?></h4>
                                <div class="item-meta">
                                  <span class="d-none">Color: <?=$product_local->color?></span>
                                  <span>Type: <?=$product_local_variant->size?></span>
                                </div>
                                <div class="item-price">
                                  <span class="quantity"><?=$productInfo->qty?> ×</span>
                                  <span class="price">$ <?=$product_local->price?></span>
                                </div>
                              </div>
                            </div>
                            
                            <br />
                            <?php
                                $products_downloads = $db->query("SELECT * FROM simple_store_products_downloads WHERE product_id = ? ", [$product_local->id])->results(); // get downloads  
                                $products_downloads_count = "1"; //used for download count
                                //if there are no download, ask to upload atleast one download
                                        if(count($products_downloads) === 0){
        
                                            echo '<div class="item-details">
                                                    <i class="bi bi-file-earmark-x"></i>
                                                    <span>There are no downloads available for this product. Please reach out to the administr</span>
                                                  </div>';
                                            
                                        } else {
                                            //loops/shows all downloads
                                            foreach($products_downloads as $product_d){
                                                echo "
                                                    <div class=' item-details row'>
                                                      <div class='col-6'><span class='text-secondary'>Download ".$products_downloads_count."  : </span></div>
                                                      <div class='col-6'><a href='/downloads/index.php?id=".$product_d->id."&order_number=".$order_check->receipt_number."&order_email=".$order_check->customer_email."' alt='Product' class='btn btn-secondary w-100' loading='lazy'>Download</a></div>
                                                    </div>
                                                    <br />
                                                ";
                                                $products_downloads_count++; // adds one for downlaod count
                                            }
                                            
                                        }
                                
                                        ?>
                              
                         
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
                          <div class="card-type">You'll receive an email confirmation shortly at <?=$order_check->customer_email?></div>
                          <div class="card-number">Make sure you have access to that email, thats how you will be able to re-download all your files in the future.</div>
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
    
            <?php }   
        }
 } 
 
 ?>
 <br /><br />
</section>
    
 <? require_once 'usersc/plugins/simple_store/assets/template/'.'views/footer.php'; //Footer ?> 