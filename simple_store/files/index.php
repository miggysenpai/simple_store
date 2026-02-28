<?php require_once 'users/init.php'; 

//start session and create cart session if one not available
session_start();
if(!isset($_SESSION['cart'])){
   $_SESSION['cart'] = [];
   } 
   
 
       
?>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>Home Page - <?php echo $settings->site_name;?> </title>
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

  
  <div class="container my-5" role="main">
      
      <div class="position-relative p-5 text-center text-muted bg-body rounded-4">
        
        <h1 class="text-body-emphasis">Shop .</h1>
        <p class="col-lg-6 mx-auto mb-4">
          Thank you for stopping by and checking out my shop. 
    	  <br />
    	  Hopefully you find something you like!		
    	  <br /> <br /> 
    	  <br /> <br /> 
    	  <br /> <br /> 
    	  <br /> 
        </p>
      </div>
            <?php 
                // checks if the store is live
                $store_live_check = $db->query("SELECT * FROM simple_store_settings ")->first(); // get store settings
                  // if not live, show this
                  if($store_live_check->live == "0"){
                      echo '
                      <div class="container">
                          <div
                            class="position-relative p-5 text-center text-muted bg-body border border-dashed rounded-5"
                          >
                            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="currentColor" class="bi bi-shop" viewBox="0 0 16 16" aria-hidden="true">
                              <path d="M2.97 1.35A1 1 0 0 1 3.73 1h8.54a1 1 0 0 1 .76.35l2.609 3.044A1.5 1.5 0 0 1 16 5.37v.255a2.375 2.375 0 0 1-4.25 1.458A2.37 2.37 0 0 1 9.875 8 2.37 2.37 0 0 1 8 7.083 2.37 2.37 0 0 1 6.125 8a2.37 2.37 0 0 1-1.875-.917A2.375 2.375 0 0 1 0 5.625V5.37a1.5 1.5 0 0 1 .361-.976zm1.78 4.275a1.375 1.375 0 0 0 2.75 0 .5.5 0 0 1 1 0 1.375 1.375 0 0 0 2.75 0 .5.5 0 0 1 1 0 1.375 1.375 0 1 0 2.75 0V5.37a.5.5 0 0 0-.12-.325L12.27 2H3.73L1.12 5.045A.5.5 0 0 0 1 5.37v.255a1.375 1.375 0 0 0 2.75 0 .5.5 0 0 1 1 0M1.5 8.5A.5.5 0 0 1 2 9v6h1v-5a1 1 0 0 1 1-1h3a1 1 0 0 1 1 1v5h6V9a.5.5 0 0 1 1 0v6h.5a.5.5 0 0 1 0 1H.5a.5.5 0 0 1 0-1H1V9a.5.5 0 0 1 .5-.5M4 15h3v-5H4zm5-5a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1h-2a1 1 0 0 1-1-1zm3 0h-2v3h2z"/>
                            </svg>
                            <br />
                            <br />
                            <h1 class="text-body-emphasis">Store is Closed!</h1>
                            <p class="col-lg-6 mx-auto mb-4">
                              The store is currently closed! Sorry for any inconveniences! :c
                              We are working to get it back up as soon as possible! You should 
                              still be able to track any orders ! Contact us for any questions!
                            </p>';

                              //checks if is admin
                              if(isUserLoggedIn()) {
                                  if(hasPerm([2], $user->data()->id)){
                                    echo "<br /><br />
                                      <a class='btn btn-secondary' href='store_admin.php?id=settings'>Click here To go to Store Admin</a>
                                     ";
                                  }
                              } 
                            echo '
                          </div>
                        </div>
                        ';
                  } else {
          // else show this
          echo '<!--/Search Widget -->
                  <div class="row justify-content-md-center">
                      <div class="col-md-4">
                        <div class="search-widget ">
                          <form action="search.php" method="POST">
                            <input placeholder="Search" type="text" name="search">
                            <button type="submit" title="Search"><i class="bi bi-search"></i></button>
                          </form>
            
                        </div>
                      </div>
                  </div>
                  <br /><br />';          
          echo '<div class="row row row-cols-1 row-cols-md-3 g-4 text-center  ">';
         
          
          // Gets all products that are marked as available/sold out. 
          $products = $db->query("SELECT * FROM simple_store_products WHERE status = ? ", [1])->results(); 
          foreach($products as $p) {
                $product_image = $db->query("SELECT * FROM simple_store_products_images WHERE product_id = ? AND is_primary = ?", [$p->id, "1"])->results(); // checks for primary image
                if(count($product_image) == 0){$image_src = ""; } else { $image_src = '<img src="'.$product_image[0]->image.'" class="card-img-top on-hover-zoom " alt="Product with the name '.$p->name.' in the '.$p->catergory.' catergory">';} //use primary image if available ?>  
                <div class="card-group ">
                <a href="product.php?id=<?=$p->id?>" aria-label="Go to Product Page">
                  <div class="card h-100 border border-0">
                    <div class="image-wrapper on-hover-zoom-wrapper rounded">
                        <?=$image_src?>
                    </div>
                    <div class="card-body">
                      <div class="card-title"><?=$p->name?></div>
                      <p class="card-text">from $ <?=$p->price?>.</p>
                    </div>
                  </div>
                </a>                 
                </div>        
           <?php } } ?>
      </div>
    </div>
  
    <footer id="footer" class="footer">

    <div class="footer-main ">
      <div class="container">
        <div class="row gy-4">
          
          <div class="col-lg-4 col-md-6 col-sm-6">
            <div class="footer-widget">
              <p><strong>Support</strong></p>
              <ul class="footer-links">
                <li><a href="trackorder.php" aria-label="Go to Order Tracking Page">Order tracking</a></li>
                <li><a href="support.php?id=shipping" aria-label="Go to Shipping Page">Shipping Info</a></li>
                <li><a href="support.php?id=returnpolicy" aria-label="Go to Retun Policy Page">Returns &amp; Exchanges</a></li>
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
                    <?php if($socials->facebook == ""){echo "";}else{echo "<a href='".$socials->facebook."' aria-label='Facebook Link'><i class='bi bi-facebook'></i></a><br />";}?>
                    <?php if($socials->instagram == ""){echo "";}else{echo "<a href='".$socials->instagram."' aria-label='Instagram Link'><i class='bi bi-instagram'></i></a><br />";}?>
                    <?php if($socials->tiktok == ""){echo "";}else{echo "<a href='".$socials->tiktok."' aria-label='Tiktok Link'><i class='bi bi-tiktok'></i></a><br />";}?>
                    <?php if($socials->youtube == ""){echo "";}else{echo "<a href='".$socials->youtube."' aria-label='Youtube Link'><i class='bi bi-youtube'></i></a><br />";}?>
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
          <p><strong class="sitename"><a href="<?=$login_out?> " aria-label="Go to Admin Login Page"><?php echo $settings->site_name;?></a> </strong>// All Rights Reserved.</p>
          <br />
          <?php if(isAdmin()){echo '<a href="users/admin.php" class="btn rounded border" aria-label="Go to Admin Page"> <span>Admin</span> </a> <a href="store_admin.php" class="btn rounded border" aria-label="Go to Store Admin Page"> <span>Store Admin</span> </a> <a href="users/logout.php" class="btn rounded border" aria-label="Logout"> <span>Logout</span> </a>';  } ?>
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
