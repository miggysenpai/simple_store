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
                    <li><a href="search.php">Search</a></li>
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
            <div class="custom-h3">Search</div>
            <div class="order-items mt-3">
            <!--/Search Widget -->
              <div class="row justify-content-md-center">
                  <div class="col-md-4">
                    <div class="search-widget ">
                      <form action="?" method="POST">
                        <input placeholder="Search" type="text" name="search">
                        <button type="submit" title="Search"><i class="bi bi-search"></i></button>
                      </form>
                      <br />
                        Go ahead and type something ...
                    </div>
                  </div>
              </div>
              <br /><br />
            </div>
            
          </div>  
          
          <br />
         
    <?php }
    
    // check if post 
    if(!empty($_POST)){
        $search = Input::get('search');
        if(isset($search)) {
            $search_check = $db->query("SELECT * FROM simple_store_products WHERE status = 1 AND (name LIKE '%$search%' OR id LIKE '%$search%' OR catergory LIKE '%$search%' OR description LIKE '%$search%')")->results();; // search check
            
            // show if wrong order number or email
            if(count($search_check) == 0 ){
                echo '</section><div class="container"><div class="row justify-content-center "> 
                            <div class="col-md-4">
                             <div class="col-12">
                            <div class="search-widget ">
                              <form action="?" method="POST">
                                <input placeholder="Search" type="text" name="search" value="'. $search .'">
                                <button type="submit" title="Search"><i class="bi bi-search"></i></button>
                              </form>
                              <br />
                            </div>
                      </div>
                      <p class="text-center">
                                nothing found . . .  
                                <br /> <br />
                                please try again. 
                      </p>
                                <div class="product-details">
                                    <div class="product-info">
                                        <a href="index.php" aria-label="Go to index Page">
                                            <div class="product-actions">
                                                <button  class="btn btn-outline-primary buy-now-btn">
                                                  Shop All
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
            // this shows results
            echo '</section><div class="container"><div class="row justify-content-md-center">
                      <div class="col-md-4">
                            <div class="search-widget ">
                              <form action="?" method="POST">
                                <input placeholder="Search" type="text" name="search" value="'. $search .'">
                                <button type="submit" title="Search"><i class="bi bi-search"></i></button>
                              </form>
                              <br /><br />
                            </div>
                      </div>
                 </div>';
            echo '<div class="row row-cols-1 row-cols-md-3 g-4 text-center  ">';
            echo '';
            foreach($search_check as $p) {
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
           <?php }
            
            
            
            
            ?>
                
        
            
                  
                  
        
                  
                


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