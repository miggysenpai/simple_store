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
                echo '<div class="row justify-content-center text-center"> 
                            <div class="col-6">
                            <br /> <br /><br /><br /><br /><br />
                                Wrong order number or email . . .  
                                <br /> <br />
                                please try again. 
                                <div class="product-details">
                                    <div class="product-info">
                                        <a href="trackorder.php" aria-label="Go to Track Order">
                                            <div class="product-actions">
                                                <button  class="btn btn-outline-primary buy-now-btn">
                                                   Return
                                                </button>
                                             </div>
                                        </a>
                            <br /><br /><br /><br /><br /><br /> 
                                    </div>
                                </div>
                            </div>
              
                        </div>
                        
                        '; 
                
            } else {
            // this shows if the correct email and order number was used
            ?>
                <div class="checkout">
                  <div class="confirmation-header text-center" >
                    <div class="custom-h2">Order Summary</div>
                    <p class="lead">Thank you for your purchase. </p>
                    <div class="order-number mt-3 mb-4">
                        <span><?=(date("m-d-Y",$order_check->created)); ?></span> <br />
                      <span>Order # : </span><strong><?=$order_check->receipt_number?></strong>  
                      <br />
                        
                      <span>Email :</span>
                      <strong><?=$order_check->customer_email?></strong>
                      <span class="mx-2"></span>
                      
                    </div>
                  </div>
                  
        
                  <div class="order-details p-4 mb-4" >
                    <div class="row">
                      <div class="col-md-6 mb-4 mb-md-0">
                        <div class="custom-h4">Shipping Information</div>
                        <address class="mt-3">
                          <strong><?=$order_check->customer_name?></strong><br>
                          <?=$order_check->address_line1?><br>
                          <?php if(isset($order_check->address_line2)) {echo$order_check->address_line2."<br>";} ?>
                          
                          <?=$order_check->address_city?>, <?=$order_check->address_state?> <?=$order_check->address_postal_code?><br>
                          United States<br>
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
                            <span><?=$order_check->payment_brand?> ending in <?=$order_check->payment_last4?></span>
                          </div>
                          <div class="billing-address">
                            <strong>Billing Address:</strong> Same as shipping
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                  
                  <?php
                  // checks if theres any shipping information if so, display it
                  if($order_check->shipping_tracking == "") { echo "";  } else   {
                      echo '
                            <div class="order-details p-4 mb-4" >
                            <div class="row">
                              <div class="col-md-12 mb-4 mb-md-0">
                                <div class="custom-h4">Your order has shipped!</div>
                                <div class="mt-3">
                                  <span class="shipping-method">
                                    <i class="bi bi-send me-2"></i>Shipped with '.$order_check->shipping_carrier.'
                                  </span>
                                  <br /><br />
                                  <span class="shipping-method">
                                    <i class="bi bi-truck me-2"></i>Tracking #: '.$order_check->shipping_tracking.'
                                  </span>
                                  <br /><br />
                                  <div class="basic-button ">
                                      <a href="https://www.aftership.com/track/'.$order_check->shipping_tracking.'" class="rounded btn-primary me-3 mb-2 mb-md-0" aria-label="External Tracking Link">
                                        <i class="bi bi-link me-2"></i>Click here to go track in carrier website
                                      </a>
                                  </div>
                                </div>
                              </div>
                            </div>
                          </div>';
                  }?>
       
                  <div class="account ">
                        <div class="content-area ">
                            <div class="orders-grid ">
                                <!-- Order Card 1 -->
                                <div class="order-card " >
                                    <div class="custom-h4">Tracking Summary</div>
     
                                  <div class=" tracking-info" id="tracking1">
                                    <div class="tracking-timeline">
                                        
                                      <div class="timeline-item completed">
                                        <div class="timeline-icon">
                                          <i class="bi bi-check-circle-fill"></i>
                                        </div>
                                        <div class="timeline-content ">
                                          <div class="custom-h5">Order Confirmed</div>
                                          <p>Your order has been received and confirmed</p>
                                          <span class="timeline-date"><?=(date("Y-m-d h:sa",$order_check->created)); ?> </span>
                                        </div>
                                      </div>
                                
                                      <?php $order_status_2 = "";?>
                                      <div class="timeline-item <?php if($order_check->status >=2){$order_status2 = $db->query("SELECT * FROM simple_store_transactions_status WHERE transaction_id = ? AND transaction_status = ?",[$order_check->id, 2])->first(); $order_status_2 = $order_status2->transaction_status_date;   echo "completed";}?>">
                                        <div class="timeline-icon  ">
                                          <i class="bi bi-scissors"></i>
                                        </div>
                                        <div class="timeline-content ">
                                          <div class="custom-h5">Processing</div>
                                          <p>Your order is being made</p>
                                          <span class="timeline-date"><? if($order_status_2 == ""){echo "";} else { echo (date("Y-m-d h:sa",$order_status_2));}?></span>
                                        </div>
                                      </div>
            
                                      <?php $order_status_3 = "";?>                                    
                                      <div class="timeline-item <?php if($order_check->status >=3){$order_status3 = $db->query("SELECT * FROM simple_store_transactions_status WHERE transaction_id = ? AND transaction_status = ?",[$order_check->id, 3])->first(); $order_status_3 = $order_status3->transaction_status_date;   echo "completed";}?>">
                                        <div class="timeline-icon">
                                          <i class="bi bi-box-seam"></i>
                                        </div>
                                        <div class="timeline-content">
                                          <div class="custom-h5">Packaging</div>
                                          <p>Your items are being packaged for shipping</p>
                                          <span class="timeline-date"><? if($order_status_3 == ""){echo "";} else { echo (date("Y-m-d h:sa",$order_status_3));}?></span>
                                        </div>
                                      </div>
            
                                      <?php $order_status_4 = "";?>
                                      <div class="timeline-item <?php if($order_check->status >=4){$order_status4 = $db->query("SELECT * FROM simple_store_transactions_status WHERE transaction_id = ? AND transaction_status = ?",[$order_check->id, 4])->first(); $order_status_4 = $order_status4->transaction_status_date;   echo "completed";}?>">
                                        <div class="timeline-icon">
                                          <i class="bi bi-truck"></i>
                                        </div>
                                        <div class="timeline-content">
                                          <div class="custom-h5">In Transit</div>
                                          <p>Your package is on the way</p>
                                          <span class="timeline-date"><? if($order_status_4 == ""){echo "";} else { echo (date("Y-m-d h:sa",$order_status_4));}?></span>
                                        </div>
                                      </div>
            
                                      <?php $order_status_5 = "";?>
                                      <div class="timeline-item <?php if($order_check->status >=5){$order_status5 = $db->query("SELECT * FROM simple_store_transactions_status WHERE transaction_id = ? AND transaction_status = ?",[$order_check->id, 5])->first(); $order_status_5 = $order_status5->transaction_status_date;   echo "completed";}?>">
                                        <div class="timeline-icon">
                                          <i class="bi bi-house-door"></i>
                                        </div>
                                        <div class="timeline-content">
                                          <div class="custom-h5">Delivery</div>
                                          <p>Your package has been delivery</p>
                                          <span class="timeline-date"><? if($order_status_5 == ""){echo "";} else { echo (date("Y-m-d h:sa",$order_status_5));}?></span>
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
                        
                        $items = $db->query("SELECT * FROM simple_store_transactions_item WHERE customer_email = ? AND receipt_number = ? ", [$post_email, $post_order])->results(); 
                        $sub_total = 0;
                        // loop/show each item ordered
                        foreach($items as $productInfo){
                            $product_local_variant = $db->query("SELECT * FROM simple_store_products_variants WHERE price_id = ?",[$productInfo->price_id])->first(); // get variant info (size)
                            $product_local = $db->query("SELECT * FROM simple_store_products WHERE id = ?",[$product_local_variant->product_id])->first(); // get product info
                            $product_img = $db->query("SELECT * FROM simple_store_products_images WHERE product_id = ? AND is_primary = ?",[$product_local->id, "1"])->first(); // get primary image
                         ?>     
                          <div class="item-row d-flex">
                            <div class="item-image">
                              <img src="<?=$product_img->image?>" alt="Product" class="img-fluid" loading="lazy">
                            </div>
                            <div class="item-details">
                              <div class="custom-h5"><?=$productInfo->name?></div>
                              <p class="text-muted">Color: <?=$product_local->color?> / Size: <?=$product_local_variant->size?></p>
                              <div class="quantity-price d-flex justify-content-between">
                                <span>Qty: <?=$productInfo->qty?></span>
                                <span class="price">$<?=$product_local->price?></span>
                              </div>
                            </div>
                          </div>
                          <?php 
                            $pre_sub = $productInfo->qty * $product_local->price;
                            $sub_total = $sub_total + $pre_sub;
                          ?>
                      <?php } ?>
                    </div>
                    <?php // echo '<pre>' , var_dump($items) , '</pre>';?>
        
                    <div class="order-totals mt-4">
                      <div class="d-flex justify-content-between py-2">
                        <span>Subtotal</span>
                        <span>$<?=$sub_total?></span>
                      </div>
                      <div class="d-flex justify-content-between py-2">
                        <span>Shipping</span>
                        <span>$<?=$order_check->amount_shipping/100?></span>
                      </div>
                      <div class="d-flex justify-content-between py-2">
                        <span>Tax</span>
                        <span>$<?=$order_check->amount_tax/100?></span>
                      </div>
                      <div class="d-flex justify-content-between py-2 total-row">
                        <strong>Total</strong>
                        <strong>$<?=$order_check->amount_total/100?></strong>
                      </div>
                    </div>
                  </div>
                </div>
        
                  
                  <div class="next-steps text-center " >
                    <br />
                    <div class="custom-h4">What's Next?</div>
                    <br />
                    <p>You'll receive an email at <strong><?=$order_check->customer_email?></strong> once there is an update on your order!</p>
                    <div class="tracking-info mb-4">
                      <i class="bi bi-envelope me-2"></i>We'll send tracking information once your order ships
                    </div>
                    <div class="action-buttons">
                      <a href="index.php" class="btn btn-primary me-3 mb-2 mb-md-0" aria-label="Go to Homepage">
                        <i class="bi bi-bag me-2"></i>Continue Shopping
                      </a>
                      <br /><br />
                    </div>
                  </div>
        
                  <div class="help-contact text-center mt-5" >
                    <p>Need help with your order? <a href="support.html" aria-label="Go to Support Page">Contact our support team</a></p>
                    <br />
                  </div>
                


              </div>


              </div>
            </section>    
            <?php }   
        }
 } 
 
 ?>
 <br /><br />
</section>
    
 <? require_once 'usersc/plugins/simple_store/assets/template/'.'views/footer.php'; //Footer ?> 