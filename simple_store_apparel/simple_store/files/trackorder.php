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
           // order tracking form
           if(!$_POST){?>  
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
    if(!empty($_POST)){
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
                    <div class="stepper-item completed">
                      <div class="stepper-icon">1</div>
                      <div class="stepper-text">Confirmed</div>
                    </div>
                    
                    <?php $order_status_2 = "";?>
                    <div class="stepper-item <?php if($order_check->status >=2){$order_status2 = $db->query("SELECT * FROM simple_store_transactions_status WHERE transaction_id = ? AND transaction_status = ?",[$order_check->id, 2])->first(); $order_status_2 = $order_status2->transaction_status_date;   echo "completed";}?>">
                      <div class="stepper-icon">2</div>
                      <div class="stepper-text">Processing <span class="timeline-date" style="opacity: 0.8;"> - <? if($order_status_2 == ""){echo "";} else { echo (date("F j, Y",$order_status_2));}?></span></div>
                    </div>
                    
                    <?php $order_status_3 = "";?>
                    <div class="stepper-item <?php if($order_check->status >=3){$order_status3 = $db->query("SELECT * FROM simple_store_transactions_status WHERE transaction_id = ? AND transaction_status = ?",[$order_check->id, 3])->first(); $order_status_3 = $order_status3->transaction_status_date;   echo "completed";}?>">
                      <div class="stepper-icon">3</div>
                      <div class="stepper-text">Packaging <span class="timeline-date"  style="opacity: 0.8;"><? if($order_status_3 == ""){echo "";} else { echo (date("F j, Y",$order_status_3));}?></span></div>
                    </div>
                    
                    <?php $order_status_4 = "";?>
                    <div class="stepper-item <?php if($order_check->status >=4){$order_status4 = $db->query("SELECT * FROM simple_store_transactions_status WHERE transaction_id = ? AND transaction_status = ?",[$order_check->id, 4])->first(); $order_status_4 = $order_status4->transaction_status_date;   echo "completed";}?>">
                      <div class="stepper-icon">4</div>
                      <div class="stepper-text">Shipped <span class="timeline-date"  style="opacity: 0.8;"><? if($order_status_4 == ""){echo "";} else { echo (date("F j, Y",$order_status_4));}?></span></div>
                    </div>
                    
                    <?php $order_status_5 = "";?>
                    <div class="stepper-item <?php if($order_check->status >=5){$order_status4 = $db->query("SELECT * FROM simple_store_transactions_status WHERE transaction_id = ? AND transaction_status = ?",[$order_check->id, 5])->first(); $order_status_5 = $order_status5->transaction_status_date;   echo "completed";}?>">
                      <div class="stepper-icon">5</div>
                      <div class="stepper-text">Delivered <span class="timeline-date"  style="opacity: 0.8;"><? if($order_status_5 == ""){echo "";} else { echo (date("F j, Y",$order_status_5));}?></span></div>
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
                <?php
                  // checks if theres any shipping information if so, display it
                  if($order_check->shipping_tracking == "") { 
                        echo '<div class="delivery-info">
                              <h5>Delivery Information</h5>
                              <p class="delivery-estimate">
                                <i class="bi bi-calendar-check"></i>
                                <span>Ships within (3-5 business days)</span>
                              </p>
                              <p class="shipping-method">
                                <i class="bi bi-truck"></i>
                                <span>Free Shipping</span>
                              </p>
                            </div>
                            ';  
                         } else   {
                          echo '
                            <div class="delivery-info">
                              <h5>Delivery Information</h5>
                              <p class="delivery-estimate">
                                <i class="bi bi-calendar-check"></i>
                                <span>Shipped with '.$order_check->shipping_carrier.'</span>
                              </p>
                              <p class="shipping-method">
                                <i class="bi bi-truck"></i>
                                <span>Tracking #: '.$order_check->shipping_tracking.'</span>
                              </p>
                              <style>
                                .shipping-method a {color:#fff}
                                .shipping-method a:hover {opacity: 0.8}
                              </style>
                              <p class="shipping-method">
                                <i class="bi bi-box-arrow-up-right"  ></i>
                                <a  href="https://www.aftership.com/track/'.$order_check->shipping_tracking.'"  aria-label="External Tracking Link">
                                    <span>Click here to go track in carrier website</span>
                                </a>
                              </p>
                            </div>
                          ';
                  }?>
                  
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
                <p>We'll send you updates via email as your order progresses.</p>
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
                          <?=$order_check->customer_name?><br>
                          <?=$order_check->address_line1?><br>
                          <?php if(isset($order_check->address_line2)) {echo $order_check->address_line2."<br>";} ?>
                          <?=$order_check->address_city?>, <?=$order_check->address_state?> <?=$order_check->address_postal_code?><br>
                          United States
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
                <div class="card-body">
                  <div class="payment-method">
                    <div class="payment-icon">
                      <i class="bi bi-credit-card-2-front"></i>
                    </div>
                    <div class="payment-details">
                      <div class="card-type"><?=$order_check->payment_brand?></div>
                      <div class="card-number">•••• •••• •••• <?=$order_check->payment_last4?></div>
                    </div>
                  </div>
                  <div class="billing-address mt-4">
                    <h5>Billing Address</h5>
                    <p>Same as shipping address</p>
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
                                  <span>Color: <?=$product_local->color?></span>
                                  <span>Size: <?=$product_local_variant->size?></span>
                                </div>
                                <div class="item-price">
                                  <span class="quantity"><?=$productInfo->qty?> ×</span>
                                  <span class="price">$ <?=$product_local->price?></span>
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
                          <div class="card-type">You'll receive an email confirmation shortly at <?=$order_check->customer_email?></div>
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
    
            <?php }   
        }
 } 
 
 ?>
 <br /><br />
</section>
    
 <? require_once 'usersc/plugins/simple_store/assets/template/'.'views/footer.php'; //Footer ?> 