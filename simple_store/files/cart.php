<?php
require_once 'users/init.php';

$store_live_check = $db->query("SELECT * FROM simple_store_settings ")->first(); // get store settings

// checks if the store is live, if not, redirect to index.php
if($store_live_check->live == "0"){
header("Location: index.php");
die();
}

// create cart session if one isnt available
session_start(); 
if(!isset($_SESSION['cart'])){
   $_SESSION['cart'] = [];
} 


if(isset($_GET['edit']) ){
    
    //subtracts item from cart(-1)
    if($_GET['edit'] == "sub"){
        foreach($_SESSION['cart'] as $v => &$prod ){
            if($prod['size'] == $_GET['size'] && $prod['product_id'] == $_GET['id']){
                $newquantity = --$prod['quantity'];
                $prod['quantity'] = $newquantity;
            }
            if($prod['quantity'] == 0){
                unset($_SESSION['cart'][$v]);
            }
        }
        header('Location: cart.php');
        exit();
    }
    
    //adds item from cart (+1)
    if($_GET['edit'] == "add"){
        foreach($_SESSION['cart'] as &$prod){
            if($prod['size'] == $_GET['size'] && $prod['product_id'] == $_GET['id']){
                $newquantity = ++$prod['quantity'];
                $prod['quantity'] = $newquantity;
                header('Location: cart.php');
                exit();
            }
            
        }   
    }
    
    //deletes item from cart
    if($_GET['edit'] == "delete"){
        foreach($_SESSION['cart'] as $v => &$prod ){
            if($prod['size'] == $_GET['size'] && $prod['product_id'] == $_GET['id']){
                unset($_SESSION['cart'][$v]);
            }
        }
        header('Location: cart.php');
        exit();
    }
    
}
?>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>Cart Page - <?php echo $settings->site_name;?></title>
    <!-- Vendor CSS Files -->
    <link href="<?php echo $us_url_root. 'usersc/plugins/simple_store/assets/template/'; ?>vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?php echo $us_url_root. 'usersc/plugins/simple_store/assets/template/'; ?>vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <link href="<?php echo $us_url_root. 'usersc/plugins/simple_store/assets/template/'; ?>vendor/aos/aos.css" rel="stylesheet">
    <link href="<?php echo $us_url_root. 'usersc/plugins/simple_store/assets/template/'; ?>vendor/swiper/swiper-bundle.min.css" rel="stylesheet">
    <link href="<?php echo $us_url_root. 'usersc/plugins/simple_store/assets/template/'; ?>vendor/glightbox/css/glightbox.min.css" rel="stylesheet">
    <link href="<?php echo $us_url_root. 'usersc/plugins/simple_store/assets/template/'; ?>vendor/drift-zoom/drift-basic.css" rel="stylesheet">
  
    <!-- Main CSS File -->
    <link href="<?php echo $us_url_root. 'usersc/plugins/simple_store/assets/template/'; ?>css/main.css" rel="stylesheet">
    
    
     <!-- Stripe File -->
    <script src="https://js.stripe.com/v3/"></script>
    
    
</head>
    
    
<body role="main">
    <header id="header" class="header d-flex align-items-center">
         <br /> <br /> 
    <div class="container position-relative d-flex align-items-center justify-content-between">
   
      <div class="logo d-flex align-items-center me-auto me-xl-0"></div>
     
      <div class="d-block  logo d-flex align-items-center me-auto me-xl-0">
          <a href="index.php" aria-label="Go to Home Page"><?php echo $settings->site_name;?></a>
      </div>
      
      

      <div class="header-social-links">
          <div class="icon-cart">
                <a href="cart.php" aria-label="Go to Cart Page">
                    <i class="bi bi-cart text-black"></i>
                    <span>
                        <?php 
                            //checks total items in cart
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
  
<body  class="cart-page" role="main" >
   
    <div class="container my-5">
        <!-- Page Title -->
            <div class="page-title ">
              <div class="container d-lg-flex justify-content-between align-items-center">
                <h1 class="breadcrumbs">
                    <ol>
                    <li><a href="index.php" aria-label="Go to Home Page">shop</a></li>
                    <li>cart</li>
                  </ol>
                </h1>
              </div>
            </div><!-- End Page Title -->
    </div>
   
   <!-- Cart Section -->
    <section id="cart" class="cart section">
      <div class="container" >
        <?php 
        // if cart is empty, show this
        if($sum == 0){
                echo ' <div class="row justify-content-center text-center"> 
                            <div class="col-6">
                                Kinda empty here . . .  
                                <br /> <br />
                                <div class="product-details">
                                    <div class="product-info">
                                        <a href="index.php" aria-label="Go to Home Page">
                                            <div class="product-actions">
                                                <button aria-label="Shop button" class="btn btn-outline-primary buy-now-btn">
                                                  <i class="bi bi-cart-plus"></i> shop all
                                                </button>
                                             </div>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
              ';
        } else {
            echo '<div class="row g-4">
          <div class="col-lg-12" >
            <div class="cart-items">
              <div class="cart-header d-none d-lg-block">
                <div class="row align-items-center gy-4">
                  <div class="col-lg-6">
                    <h5>Product</h5>
                  </div>
                  <div class="col-lg-2 text-center">
                    <h5>Price</h5>
                  </div>
                  <div class="col-lg-2 text-center">
                    <h5>Quantity</h5>
                  </div>
                  <div class="col-lg-2 text-center">
                    <h5>Total</h5>
                  </div>
                </div>
              </div>';
            
        }
                //Simple Loop to display cart items
                foreach($_SESSION['cart'] as $productInfo){
                    
                 $products = $db->query("SELECT * FROM simple_store_products WHERE id = ?",[$productInfo['product_id']])->first(); // get product info
                 $product_subtotal = 0;
                 $product_subtotal = $product_subtotal + $products->price*$productInfo['quantity']; // adds up total for a certain item in cart. ex (baseball t shirt)(2 in cart)(10$ each) total would be 20$ 
                 $product_img = $db->query("SELECT * FROM simple_store_products_images WHERE product_id = ? AND is_primary = ?",[$productInfo['product_id'], "1"])->first(); // get primary product image
   ?>
                 <!-- Cart Item -->
              <div class="cart-item" >
                <div class="row align-items-center gy-4">
                  <div class="col-lg-6 col-12 mb-3 mb-lg-0">
                    <div class="product-info d-flex align-items-center">
                      <a href="product.php?id=<?=$products->id?>" aria-label="Go to Product Page"><div class="product-image">
                        <img src="<?=$product_img->image?>" alt="Product Image" class="img-fluid" loading="lazy">
                      </div>
                      </a>
                      <div class="product-details">
                        <a href="product.php?id=<?=$products->id?>" aria-label="Go to product Page"><h6 class="product-title"><?=$products->name?></h6></a>
                        <div class="product-meta">
                          <span class="product-color">Color: <?=$products->color?></span>
                          <span class="product-size">Size: <?=$productInfo['size']?></span>
                        </div>
                        <button class="remove-item" type="button">
                           <a href="?edit=delete&id=<?=$productInfo['product_id']?>&size=<?=$productInfo['size']?>" aria-label="Remove Product"><i class="bi bi-trash"></i> Remove</a>
                        </button>
                      </div>
                    </div>
                  </div>
                  <div class="col-12 col-lg-2 text-center">
                    <div class="price-tag">
                      <span class="current-price"><?=$products->price?></span>
                      
                      <span class="original-price"><?=round($products->price*1.1,2)?></span>
                    </div>
                  </div>
                  <div class="col-12 col-lg-2 text-center">
                    <div class="quantity-selector">
                      <button class="quantity-btn decrease">
                         <a href="?edit=sub&id=<?=$productInfo['product_id']?>&size=<?=$productInfo['size']?>" aria-label="Subtract By One"><i class="bi bi-dash"></i></a>
                      </button>
                      <span class="quantity-input"  > <?=$productInfo['quantity']?> </span>
                      <button class="quantity-btn increase">
                        <a href="?edit=add&id=<?=$productInfo['product_id']?>&size=<?=$productInfo['size']?>" aria-label="Increase By One"><i class="bi bi-plus"></i></a>
                      </button>
                    </div>
                  </div>
                  <div class="col-12 col-lg-2 text-center mt-3 mt-lg-0">
                    <div class="item-total">
                      <span>$ <?=$products->price*$productInfo['quantity']?></span>
                    </div>
                  </div>
                </div>
              </div><!-- End Cart Item -->

                
            <?php }
        
        //show checkout if cart isn't empty
        if($sum == 0){
                echo ' ';
        } else {
            echo '
             <div class="cart-summary">
              <div class="checkout-button">
              <form action="checkout.php" method="POST">
                <button type="submit" id="checkout-button" class="btn btn-accent w-100" >Proceed to Checkout <i class="bi bi-arrow-right"></i></button> 
              </form>    
              </div>
              
              <div class="continue-shopping">
                <a href="index.php" class="btn btn-accent w-100" aria-label="Go to Home Page">
                  <i class="bi bi-arrow-left"></i> Continue Shopping
                </a>
              </div>

              <div class="payment-methods">
                <p class="payment-title">We Accept</p>
                <div class="payment-icons">
                  <i class="bi bi-credit-card-2-front"></i>
                  <i class="bi bi-paypal"></i>
                  <i class="bi bi-wallet2"></i>
                  <i class="bi bi-apple"></i>
                  <i class="bi bi-google"></i>
                </div>
                <br />
                <p class="payment-title">Safe and secure payment through stripe checkout</p>
              </div>
            </div>
          </div>
         
            </div>
          </div>';
            
        } ?>
        
      </div>

    </section><!-- /Cart Section -->
   
 <footer id="footer" class="footer">

    <div class="footer-main ">
      <div class="container">
        <div class="row gy-4">
          
          <div class="col-lg-4 col-md-6 col-sm-6">
            <div class="footer-widget">
              <p><strong>Support</strong></p>
              <ul class="footer-links">
                <li><a href="trackorder.php" aria-label="Go to Tracking Page">Order tracking</a></li>
                <li><a href="support.php?id=shipping" aria-label="Go to Shipping Page">Shipping Info</a></li>
                <li><a href="support.php?id=returnpolicy" aria-label="Go to Return Policy Page">Returns &amp; Exchanges</a></li>
                <li><a href="support.php?id=contact" aria-label="Go to Contact Page">Contact</a></li>
              </ul>
            </div>
          </div>
          
         <div class="col-lg-4 col-md-6 col-sm-6">
            <div class="footer-widget">
              <p><strong>About</strong></p>
              <ul class="footer-links">
                <li><a href="index.php" aria-label="Go to Home Page">Home</a></li>
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
          <p><strong class="sitename"><a href="<?=$login_out?>" aria-label="Go to Login Page"><?php echo $settings->site_name;?> </a> </strong>// All Rights Reserved.</p>
          <br />
          <?php if(isAdmin()){echo '<a href="users/admin.php" class="btn rounded border" aria-label="Go to Admin Page"> <span>Admin</span> </a> <a href="store_admin.php" class="btn rounded border" aria-label="Go to Store Admin Page"> <span>Store Admin</span> </a> <a href="users/logout.php" class="btn rounded border" aria-label="Go to Logout Page"> <span>Logout</span> </a>';  } ?>
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