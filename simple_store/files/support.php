<?php
require_once 'users/init.php';
ob_start();

// makes sure id is set, if not, redirect to index.php
if(isset($_GET["id"])){
    $support_id = $_GET["id"];
} else {
    header("Location: index.php");
    die();
}

// start a session and create a cart session if one not already made
session_start();
if(!isset($_SESSION['cart'])){
   $_SESSION['cart'] = [];
   } 
   
$contact_email = $db->query("SELECT * FROM simple_store_settings ")->first(); // get store contact info 
?>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>Support Page - <?php echo $settings->site_name;?></title>
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
                            // sums total items in cart
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
<body>
    <div class="container my-5">
            <div class="page-title ">
              <div class="container d-lg-flex justify-content-between align-items-center">
                <h1 class="breadcrumbs">
                    <ol>
                    <li><a href="index.php" aria-label="Go to Homepage">shop</a></li>
                    <li>support</li>
                  </ol>
                </h1>
              </div>
            </div>
    </div>
    
    <section id="order-confirmation" class="order-confirmation section">
      <div class="container">
          
        <?php
//***** SHIPPING PAGE
        if($support_id == "shipping"){
            echo "
            <div class='order-confirmation-1'>
              <div class='next-steps text-center p-4'  >
                <div class='custom-h3'>Shipping</div>
                
                <section class='faq  accordion text-center'>
                
                <div class='faq-list'>
                  <div class='faq-item faq-active'>
                    <div class='custom-h5'>Where is my item coming from? </div>
                    <div class='faq-content'>
                      <p>
                        ".$settings->site_name ." is a small company located in Lakeland Florida, USA. It's where we embroider and screen print all of out products.
                      </p>
                    </div>
                </div><!-- End FAQ Item-->
    
                <div class='faq-item  faq-active'>
                    <div class='custom-h5'>How long until my order is shipped?</div>
                    <div class='faq-content'>
                      <p>
                        Usually takes us between 1-3 bussiness days to ship, keep an eye out on that email! 
                      </p>
                    </div>
                </div><!-- End FAQ Item-->
                
                <div class='faq-item  faq-active'>
                    <div class='custom-h5'>Where can I track my order?</div>
                    <div class='faq-content'>
                      <p>
                        You can track your order by going to <b><a href='trackorder.php' aria-label='Track Order'>tracking page</a></b> and inputing your order number and email!
                        <br /> <br />
                        <div class='action-buttons'>
                          <a href='trackorder.php' class='btn btn-primary me-3 mb-2 mb-md-0' aria-label='Track Order'>
                            <i class='bi bi-truck me-2'></i>Track my order
                          </a>
                        </div>
                      </p>
                    </div>
                </div><!-- End FAQ Item-->
    
                <div class='faq-item faq-active'>
                    <div class='custom-h5'>Where do you ship to?</div>
                    <div class='faq-content'>
                      <p>
                       We currently only ship to the continuos 48 states of the USA. 
                      </p>
                    </div>
                </div><!-- End FAQ Item-->
    
                <div class='faq-item faq-active'>
                    <div class='custom-h5'>Do you offer next day shipping?</div>
                    <div class='faq-content'>
                      <p>
                        Unfortunately, at this time we don't, but we are working on making it an option soon!  
                      </p>
                    </div>
                </div><!-- End FAQ Item-->
                
                </section>
                  
                <div class='tracking-info mb-4'>
                  <i class='bi bi-envelope me-2'></i>We'll send tracking information once your order ships
                </div>
                <div class='action-buttons'>
                  <a href='index.php' class='btn btn-primary me-3 mb-2 mb-md-0' aria-label='Go to Homepage'>
                    <i class='bi bi-bag me-2'></i>Continue Shopping
                  </a>
                </div>
              </div>
    
              <div class='help-contact text-center mt-5'  >
                <p>Need help with your order? <a href='support.php?id=contact' aria-label='Go to Contact Page'>Contact our support team</a></p>
              </div>
            </div>";
        }
        
//***** RETURNS/ EXCHANGE POLICY
        if($support_id == "returnpolicy"){
            echo "
            <div class='order-confirmation-1'>
              <div class='next-steps text-center p-4'  >
                <div class='custom-h3'>Returns and Exchanges</div>
                
                <section class='faq  accordion text-center'>
                
                <div class='faq-list'>
                  <div class='faq-item faq-active'>
                    <div class='custom-h5'>Do you offer refunds? </div>
                    <div class='faq-content'>
                      <p>
                        All of our items are custom made-to-order. Because of this, we don't offer refunds once an item has been made. ALL SALES ARE FINAL.
                      </p>
                    </div>
                </div><!-- End FAQ Item-->
                
                <div class='faq-list'>
                  <div class='faq-item faq-active'>
                    <div class='custom-h5'>I ordered the wrong size!</div>
                    <div class='faq-content'>
                      <p>
                        We are not responsible for any incorrect information provided at checkout. All of our items are custom made-to-order and because of this, we don't accept refunds or exchanges.ALL SALES ARE FINAL.
                      </p>
                    </div>
                </div><!-- End FAQ Item-->
    
                <div class='faq-item  faq-active'>
                    <div class='custom-h5'>My item came defective :C</div>
                    <div class='faq-content'>
                      <p>
                         Please contact us with any issues you have with your order and we'll try to make it right. The best way is to email us at ".$contact_email->contact_email." 
                         Please include your order number, email associated with order, and pictures of the damages. Customers have 2 days 
                         after their order has beed delivered to inspect their package and contact us regarding any defects. After 2 days, we 
                         will no longer be able to help resolve any issues.
                      </p>
                    </div>
                </div><!-- End FAQ Item-->
                
                <div class='faq-item  faq-active'>
                    <div class='custom-h5'>My package got stolen :C</div>
                    <div class='faq-content'>
                      <p>
                        We are not responsible for lost or stolen packages. We are also not responsible to packages lost due to incorrect shipping addresses entered at checkout. If your item
                        states it's been delivered, please make an inquiry to the carrier.
                      </p>
                    </div>
                </div><!-- End FAQ Item-->
                
                <div class='faq-item  faq-active'>
                    <div class='custom-h5'>How can I cancel my order?</div>
                    <div class='faq-content'>
                      <p>
                        For cancellations, please contact us within 24 hours of your order. Once the order has been made, we are no longer able to cancel the order. ALL SALES ARE FINAL.
                      </p>
                    </div>
                </div><!-- End FAQ Item-->
                
                <div class='faq-item  faq-active'>
                    <div class='custom-h5'>Changes to policy</div>
                    <div class='faq-content'>
                      <p>
                        ".$settings->site_name ." reserves the right to modify or update our policies at any time without prior notice. We encourage you to review our policies periodically to stay informed about any revisions.
                      </p>
                    </div>
                </div><!-- End FAQ Item-->
                
                </section>
                
                <div class='action-buttons'>
                  <a href='index.php' class='btn btn-primary me-3 mb-2 mb-md-0' aria-label='Go to Homepage'>
                    <i class='bi bi-bag me-2'></i>Continue Shopping
                  </a>
                </div>
              </div>
    
              <div class='help-contact text-center mt-5'  >
                <p>Need help with your order? <a href='support.php?id=contact' aria-label='Go to Contact Page'>Contact our support team</a></p>
              </div>
            </div>
            
            ";
        }
        
//***** CONTACT PAGE
        if($support_id == "contact"){
            echo "
            <div class='order-confirmation-1'>
              <div class='next-steps text-center p-4'  >
                <div class='custom-h3'>Contact</div>
                
                <section class='faq  accordion text-center'>
                
                <div class='faq-list'>
                  <div class='faq-item faq-active'>
                    <div class='custom-h5'>Whats the best form of contact? </div>
                    <div class='faq-content'>
                      <p>
                       ".$contact_email->contact_email."
                      </p>
                    </div>
                </div><!-- End FAQ Item-->
                
                </section>
                
                <div class='action-buttons'>
                  <a href='index.php' class='btn btn-primary me-3 mb-2 mb-md-0' aria-label='Go to Homepage'>
                    <i class='bi bi-bag me-2'></i>Continue Shopping
                  </a>
                </div>
              </div>
    
              <div class='help-contact text-center mt-5'  >
                <p>Need help with your order? <a href='support.php?id=contact' aria-label='Go to contact Page'>Contact our support team</a></p>
              </div>
            </div>";
        }
        
//***** Our Store
        if($support_id == "ourstory"){
            echo "
            <div class='order-confirmation-1'>
              <div class='next-steps text-center p-4'  >
                <div class='custom-h3'>Our Story</div>
                
                <section class='faq  accordion text-center'>
                
                <div class='faq-list'>
                  <div class='faq-item faq-active'>
                    <div class='faq-content'>
                      <p>
                        Just a one man army doing my best. :) 
                      </p>
                    </div>
                </div><!-- End FAQ Item-->
                
                
                
                </section>
                
                <div class='action-buttons'>
                  <a href='index.php' class='btn btn-primary me-3 mb-2 mb-md-0' aria-label='Go to Homepage'>
                    <i class='bi bi-bag me-2'></i>Continue Shopping
                  </a>
                </div>
              </div>
    
              <div class='help-contact text-center mt-5'  >
                <p>Need help with your order? <a href='support.php?id=contact' aria-label='Go to Contact Page'>Contact our support team</a></p>
              </div>
            </div>
            ";
        }?>        
        
        
        
        
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
                <li><a href="trackorder.php" aria-label="Go to Track Order" >Order tracking</a></li>
                <li><a href="support.php?id=shipping" aria-label="Go to Shipping Page">Shipping Info</a></li>
                <li><a href="support.php?id=returnpolicy" aria-label="Go to Return Policy Change">Returns &amp; Exchanges</a></li>
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