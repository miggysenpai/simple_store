<?php 
ob_start();
require_once 'users/init.php'; 

//start session and create cart session if one not already available
session_start();
if(!isset($_SESSION['cart'])){
   $_SESSION['cart'] = [];
   } 
?>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>Track Order Page - <?php echo $settings->site_name;?></title>
    <!-- Vendor CSS Files -->
    <link href="<?php echo $us_url_root. 'usersc/plugins/simple_store/assets/template/'; ?>vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?php echo $us_url_root. 'usersc/plugins/simple_store/assets/template/'; ?>vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <link href="<?php echo $us_url_root. 'usersc/plugins/simple_store/assets/template/'; ?>vendor/aos/aos.css" rel="stylesheet">
    <link href="<?php echo $us_url_root. 'usersc/plugins/simple_store/assets/template/'; ?>vendor/swiper/swiper-bundle.min.css" rel="stylesheet">
    <!-- Main CSS File -->
    <link href="<?php echo $us_url_root. 'usersc/plugins/simple_store/assets/template/'; ?>css/main.css" rel="stylesheet">
</head>

<body role="main">
    <header id="header" class="header d-flex align-items-center">
         <br /> <br /> 
    <div class="container position-relative d-flex align-items-center justify-content-between">
      
      <div class="logo d-flex align-items-center me-auto me-xl-0"></div>
      
      <div class="d-block  logo d-flex align-items-center me-auto me-xl-0">
          <a href="index.php" aria-label="Go to Homepage"><?php echo $settings->site_name;?></a>
      </div>
      <div class="header-social-links">
          <div class="icon-cart">
                <a href="cart.php" aria-label="Go to Cart Page">
                    <i class="bi bi-cart text-black"></i>
                    <span>
                       <?php 
                            //sums total items in cart
                            $sum = 0;
                            foreach($_SESSION['cart'] as $k) {
                               $sum += $k['quantity']; 
                            }
                            echo $sum;
                        ?>
                    </span>
                </a>
            </div>
      </div>
    </div>
  </header>

    <div class="container my-5">
        <div class="page-title ">
            <div class="container d-lg-flex justify-content-between align-items-center">
                <h1 class="breadcrumbs">
                    <ol>
                    <li><a href="index.php" aria-label="Go to Homepage">shop</a></li>
                    <li>Track Order</li>
                </ol>
                </h1>
            </div>
        </div>
    </div>
  
    <section id="order-confirmation" class="order-confirmation section">
      <div class="container" >
           <?php
           // order tracking form
           if(!$_POST){?>  
            <div class="order-summary mb-4 text-center" >
            <div class="custom-h3">Order Tracking</div>
            <div class="order-items mt-3">
            <form action="" method="POST">
                    <div class="row ">
                        <div class="col ">
                          <div class="mb-4">
                            <label for="order_number" class="form-label">Order Number</label>
                            <input type="text" class="form-control" name="order_number" id="order_number" required>
                          </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                          <div class="mb-4">
                            <label for="order_email" class="form-label">Email</label>
                            <input type="text" class="form-control" name="order_email" id="order_email" required>
                          </div>
                        </div>
                    </div>
                    <div class="action-buttons">
                     <button type="submit" class="btn-outline-primary rounded">
                        <i class="bi bi-search me-2"></i>Look Up order
                      </button>
                    </div>
              </form>
            </div>
            
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
    
 <footer id="footer" class="footer">

    <div class="footer-main ">
      <div class="container">
        <div class="row gy-4">
          
          <div class="col-lg-4 col-md-6 col-sm-6">
            <div class="footer-widget">
              <p><strong>Support</strong></p>
              <ul class="footer-links">
                <li><a href="trackorder.php" aria-label="Go to Track Order">Order tracking</a></li>
                <li><a href="support.php?id=shipping" aria-label="Go to Shipping Info">Shipping Info</a></li>
                <li><a href="support.php?id=returnpolicy" aria-label="Go to Return Policy">Returns &amp; Exchanges</a></li>
                <li><a href="support.php?id=contact" aria-label="Go to Contact Page">Contact</a></li>
              </ul>
            </div>
          </div>
          
         <div class="col-lg-4 col-md-6 col-sm-6">
            <div class="footer-widget">
              <p><strong>About</strong></p>
              <ul class="footer-links">
                <li><a href="index.php" aria-label="Go to Homepage">Home</a></li>
                <li><a href="support.php?id=ourstory" aria-label="Go to Our Story Page">Our Story</a></li>
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
          <?php if(isAdmin()){echo '<a href="users/admin.php" class="btn rounded border" aria-label="Go to Admin Page"> <span>Admin</span> </a> <a href="store_admin.php" class="btn rounded border" aria-label="Go to Store Admin"> <span>Store Admin</span> </a> <a href="users/logout.php" aria-label="Logout" class="btn rounded border"> <span>Logout</span> </a>';  } ?>
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