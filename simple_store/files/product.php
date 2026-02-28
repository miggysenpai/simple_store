<?php
require_once 'users/init.php'; 

$store_live_check = $db->query("SELECT * FROM simple_store_settings ")->first(); // get store settings

// checks if the store is live, if not, redirect to index.php
if($store_live_check->live == "0"){
header("Location: index.php");
die();
} 

//check if there is a product id. 
if(isset($_GET["id"])){
    
    //if product id availble, set id.
    $product_id = $_GET["id"];
    
    //check if product is set to availble
    $product_availabilty = $db->query("SELECT * FROM simple_store_products WHERE id = ? AND status = ? ", [$product_id, 1])->results(); 


    $hidden_to_customer = 0; // variable for admin


    if(isUserLoggedIn()) {
      if(hasPerm([2], $user->data()->id)){
        if(count($product_availabilty) == 0){
            $hidden_to_customer = 1;
        }
      }
      
    } else { 

      //if is not admin and 
      //if product is not set to available redirect to home
      if(count($product_availabilty) == 0){
          header("Location: index.php");
          die();
      }

    }
    
} else {
    //if there is no product id, redirect to home
    header("Location: index.php");
    die();
}

//start session and create cart session if one not available
session_start();
if(!isset($_SESSION['cart'])){
   $_SESSION['cart'] = [];
   } 

//checks if there was a form post, (add to cart)
if (!empty($_POST)) {
        //it checks for a size because its a varient. the size contains the "price_id" needed for stripe checkout    
       if(isset($_POST['size'])) {
         
        $post_id = Input::get('id');
        $post_size = Input::get('size');
        $products = $db->query("SELECT * FROM simple_store_products WHERE id = ?",[$post_id])->first(); // get product info  
        
        //makes sure that the product status is available
        if($products->status == "0"){
            header("Location: index.php");
            die();
        }
        //checks if product is marked as as sold out
        if($products->sold_out == "1"){
            header("Location: index.php");
            die();
        }
        
        
        //these are the properties required to add to cart
        $fields = [
          'product_id' => $post_id,
          'quantity' => 1,
          'size' => $post_size 
          ];
           // echo "<pre>" . var_dump($fields) . "</pre>"; die();
        
        //just to double check that a cart session is avaiable   
         if(!isset($_SESSION['cart'])){
            $_SESSION['cart'] = [];
            } 
        
        //adds product to cart, then redirects to cart for checkout    
        $_SESSION['cart'][] = $fields ; 
        header("Location: cart.php");
        die();
    }
    
}

// double checks that there is a product id
if(isset($product_id)){
    if($product_id == ""){  header("Location: index.php");  } // makes sure id isnt blank
    $products = $db->query("SELECT * FROM simple_store_products WHERE id = ?",[$product_id])->first(); // get product info
    $products_price_10 = $products->price * 1.1; // adds 10% to display "10% discount"
} else {
    //if there is no product id, redirect to home page. 
    header("Location: index.php");
    die();
}
?>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>Product Page - <?php echo $settings->site_name;?></title>
    <!-- Vendor CSS Files -->
    <link href="<?php echo $us_url_root. 'usersc/plugins/simple_store/assets/template/'; ?>vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?php echo $us_url_root. 'usersc/plugins/simple_store/assets/template/'; ?>vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <link href="<?php echo $us_url_root. 'usersc/plugins/simple_store/assets/template/'; ?>vendor/aos/aos.css" rel="stylesheet">
    <link href="<?php echo $us_url_root. 'usersc/plugins/simple_store/assets/template/'; ?>vendor/swiper/swiper-bundle.min.css" rel="stylesheet">
    <link href="<?php echo $us_url_root. 'usersc/plugins/simple_store/assets/template/'; ?>vendor/drift-zoom/drift-basic.css" rel="stylesheet">
    
    <!-- Main CSS File -->
    <link href="<?php echo $us_url_root. 'usersc/plugins/simple_store/assets/template/'; ?>css/main.css" rel="stylesheet">
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
<body>

  <?php 
  if($hidden_to_customer == 1){
  echo '<div class="alert alert-secondary text-center" role="alert"> This product is hidden to customer. 
  <br /><br />
  Go to Store Admin -> Products -> Edit Product 
  <br />
  then mark as "Available"
  <br /><br /> 
  <a class="btn btn-secondary" href="store_admin.php?id=edit_product&product_id='.$product_id .'">Click here to edit product</a>
   </div>
        <br /><br /><br /><br /><br />';
  }
  ?>

    
    <?php 
    // Check if catergory exist 
    $catergory_check = $db->query("SELECT * FROM simple_store_catergories WHERE id =? ",[$products->catergory])->results(); 
    $catergory_id = $catergory_check[0]->id;
    $catergory_name = $catergory_check[0]->name;
    $sub_c = "";
    if (count($catergory_check) === 0){
        $catergory_link = $products->catergory;
    } else {
        $catergory_link = "<a href='collections.php?id=".$catergory_id."' aria-label='Go to Collections Page'>".$catergory_name."</a>";
        
        if($catergory_check[0]->is_subcatergory == 1){
            $subcartergory_of = $db->query("SELECT * FROM simple_store_catergories WHERE id = ?", [$catergory_check[0]->subcartergory_of])->first(); 
            $sub_c = "<li><a href='collections.php?id=".$subcartergory_of->id."'>".$subcartergory_of->name."</a></li>";
        }
    }
    
    ?>
    <!-- Page Title -->
    <div class="container my-5">
            <div class="page-title ">
              <div class="container d-lg-flex justify-content-between align-items-center">
                <h1 class="breadcrumbs">
                    <ol>
                    <li><a href="index.php" aria-label="Go to Home Page">shop</a></li>
                    <?=$sub_c?>
                    <li><?=$catergory_link?></li>
                    <li><?=$products->name?></li>
                  </ol>
                </h1>
              </div>
            </div>
    </div>

        <!-- Product Details Section -->
        <section  class="product-details ">
          <div class="container" >
            <div class="row">
              <!-- Product Images -->
              <div class="col-lg-6 mb-5 mb-lg-0">
                <div class="product-images">
                  <div class="main-image-container mb-3">
                    <?php 
                        //checks if there is a primary image and displays it
                        $product_image = $db->query("SELECT * FROM simple_store_products_images WHERE product_id = ? AND is_primary = ?", [$_GET["id"], "1"])->results();
                        if(count($product_image) == 0){
                            echo ""; 
                        } else { 
                            echo   '<div class="image-zoom-container">
                                      <img src="'.$product_image[0]->image.'" alt="Product Image for '.$products->name.'" class="img-fluid main-image drift-zoom" id="main-product-image" data-zoom="'.$product_image[0]->image.'">
                                    </div>'; 
                        }
                    ?>
                  </div>
    
                  <div class="product-thumbnails">
                    <div class="swiper product-thumbnails-slider init-swiper">
                      <script type="application/json" class="swiper-config">
                        {
                          "loop": false,
                          "speed": 400,
                          "slidesPerView": 4,
                          "spaceBetween": 10,
                          "navigation": {
                            "nextEl": ".swiper-button-next",
                            "prevEl": ".swiper-button-prev"
                          },
                          "breakpoints": {
                            "320": {
                              "slidesPerView": 3
                            },
                            "576": {
                              "slidesPerView": 4
                            }
                          }
                        }
                      </script>
                      <div class="swiper-wrapper">
                        <?php
                        //for each loop to show all product images. it shows the primary image first
                        $products_images_thumb = $db->query("SELECT * FROM simple_store_products_images WHERE product_id = ? ORDER BY is_primary DESC", [$_GET["id"]])->results(); // get images
                        if(count($products_images_thumb) === 0){
                                echo "";
                            } else {
                                foreach($products_images_thumb as $thumb){
                                    if($thumb->is_primary == 1){$primary_check = "active";} else {$primary_check = "";}
                                    echo '  <div class="swiper-slide thumbnail-item '.$primary_check.'" data-image="'.$thumb->image.'">
                                              <img src="'.$thumb->image.'" alt="Product Thumbnail" class="img-fluid">
                                            </div>
                                    
                                    ';
                                }
                            }
                        
                        ?>  
                      </div>
                      <div class="swiper-button-next"></div>
                      <div class="swiper-button-prev"></div>
                    </div>
                  </div>
                </div>
              </div>
    
              <!-- Product Info -->
              <div class="col-lg-6" >
                <div class="product-info">
                  <div class="product-meta mb-2">
                    <span class="product-category"><?=$catergory_name?></span>
                    <!-- 
                    <div class="product-rating">
                      <i class="bi bi-star-fill"></i>
                      <i class="bi bi-star-fill"></i>
                      <i class="bi bi-star-fill"></i>
                      <i class="bi bi-star-fill"></i>
                      <i class="bi bi-star-half"></i>
                      <span class="rating-count">(42)</span>
                    </div>
                    -->
                  </div>
    
                  <h1 class="product-title"><?=$products->name?></h1>
    
                  <div class="product-price-container mb-4">
                    <span class="current-price">$<?=$products->price?></span>
                    
                    <span class="original-price">$<?=round($products_price_10,2)?></span>
                    <span class="discount-badge">-10%</span>
                  </div>
    
                <?php 
                //Checks if product is sold out, displays if available or not.
                if($products->sold_out == "1"){$sold_out_status = "Sold out"; $sold_out_icon = "bi-x-circle-fill text-danger";} if($products->sold_out == "0"){$sold_out_status = "In Stock"; $sold_out_icon = "bi-check-circle-fill text-success";}
                ?>
                  <div class="product-availability mb-4">
                    <i class="bi <?=$sold_out_icon?>"></i>
                    <span><?=$sold_out_status?></span>
                    <span class="stock-count"></span>
                  </div>
                    
                <form id="product_form" action="#" method="post">
                  <!-- Color Options -->
                  <div class="product-colors mb-4">
                    <div class="option-title">Color:</div>
                    <div class="color-options">
                        <?php $color_hex = $db->query("SELECT * FROM simple_store_products_colors WHERE name = ?",[$products->color])->first();?>
                      <div class="color-option active" data-color="Natural" style="background-color: #<?=$color_hex->hex?>;">
                        <i class="bi bi-check"></i>
                         
                         <input type="radio" class="btn-check" id="color" name="color" autocomplete="off" id="color" value="<?=$products->color?>" checked  aria-label="Product Color Hex <?=$products->color?>">
                         <label for="color" hidden><?=$products->color?></label>
                      </div>
                    </div>
                  </div>
    
                  <!-- Size Options if applicable -->
                  <div class="product-sizes mb-4">
                    <div class="option-title">Size:</div>
                    <div class="size-options">
                        <?php
                        // basically added this incase someone wants to expand on the sizes avaiable. i didnt feel like it. 
                        $products_variant = $db->query("SELECT * FROM simple_store_products_variants WHERE product_id = ?",[$products->id])->results();
                        $product_checked = "checked";
                        foreach($products_variant as $product_v){
                            echo "<input type='radio' class='btn-check' name='size' autocomplete='off' id='".$product_v->size."' value='".$product_v->size."'  ".$product_checked.">
                                  <label class='btn' for='".$product_v->size."'>".$product_v->size."</label>";
                            $product_checked = "";
                        }?>   
                    </div>
                  </div>
                  
                  <!-- Action Buttons -->
                  <div class="product-actions">
                    <?php 
                    // if product not sold out, disable as add to cart
                    if($products->sold_out == "0"){
                        echo ' <button type="submit" class="btn btn-outline-primary buy-now-btn">
                                  <i class="bi bi-cart-plus"></i> Add to Cart
                                </button> ';}
                    //if product sold out, display button as disabled    
                    if($products->sold_out == "1"){
                            echo '<button class="btn btn-outline-primary buy-now-btn disabled">
                                  <i class="bi bi-cart-plus"></i> Sold Out
                                </button> ';}
                    ?>
                  </div>
                  
                  <div class="additional-info mt-4">
                    <div class="info-item">
                      <i class="bi bi-truck"></i>
                      <span>Free shipping on all orders </span>
                    </div>
                    <div class="info-item">
                      <i class="bi bi-box2-heart"></i>
                      <span>Made to order just for you </span>
                    </div>
                    <div class="info-item">
                      <i class="bi bi-arrow-repeat"></i>
                      <span>Processing time is 1-3 bussiness days before shipping</span>
                    </div>
                    <div class="info-item">
                      <i class="bi bi-patch-exclamation"></i>
                      <span>This is a custom order, no exchanges or refunds</span>
                    </div>
                    
                  </div>
                </form>
    
                </div>
              </div>
            </div>
    
            <!-- Product Details Tabs -->
            <div class="row mt-5" >
              <div class="col-12">
                <div class="product-details-tabs">
                  <ul class="nav nav-tabs" id="productTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                      <button class="nav-link active text-black" id="description-tab" data-bs-toggle="tab" data-bs-target="#description" type="button" role="tab" aria-controls="description" aria-selected="true">Description</button>
                    </li>
                  </ul>
                  <div class="tab-content" id="productTabsContent">
                    <!-- Description Tab -->
                    <div class="tab-pane fade show active" id="description" role="tabpanel" aria-labelledby="description-tab">
                      <div class="product-description">
                        <h4 class="text-black">Product Overview</h4>
                        <ul><?=$products->description?></ul>
    
                        <h4 class="text-black">Care Instructions</h4>
                        <ul>
                          <li>Wash inside-out</li>
                          <li>Wash in cold water with similar colors using gentle cycle.</li>
                          <li>Tumble dry low or hang-dry.</li>
                          <li>Do not use bleach.</li>
                          <li>Do not iron directly on the design.</li>
                          <li>Do not dry clean.</li>
                        </ul>
    
                        <h4 class="text-black">Shipping Times</h4>
                        <ul>
                          <li>You can expect your item to be shipped within 3 business days.</li>
                          <li>All items are custom made to order.</li>
                          <li>We are a small business, but we do our best to ship as fast as possible.</li>
                        </ul>
                        
                        <h4 class="text-black">Refunds/Returns</h4>
                        <ul>
                          <li>All of our items are custom made-to-order. Because of this, we don't accept returns or exchanges unless your item is defective.</li>
                          <li>However, please contact us with any issues you have with your order and we'll try to make it right.</li>
                          <li>For cancellations, please contact us within 24 hours of your order.</li>
                        </ul>
                      </div>
                    </div>
    
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </section><!-- /Product Details Section -->
        
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
          <p><strong class="sitename"><a href="<?=$login_out?>" aria-label="Go to Login Page"><?php echo $settings->site_name;?></a></strong> // All Rights Reserved.</p>
          <br />
          <?php if(isAdmin()){echo '<a href="users/admin.php" class="btn rounded border" aria-label="Go to Admin Page"> <span>Admin</span> </a> <a href="store_admin.php" class="btn rounded border" aria-label="Go to Store Admin Page"> <span>Store Admin</span> </a> <a href="users/logout.php" class="btn rounded border" aria-label="Logout Page"> <span>Logout</span> </a>';  } ?>
        </div>
      </div>
    </div>
  </footer>  
   

    <!-- Vendor JS Files -->
    <script src="<?php echo $us_url_root. 'usersc/plugins/simple_store/assets/template/'; ?>vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo $us_url_root. 'usersc/plugins/simple_store/assets/template/'; ?>vendor/php-email-form/validate.js"></script>
    <script src="<?php echo $us_url_root. 'usersc/plugins/simple_store/assets/template/'; ?>vendor/aos/aos.js"></script>
    <script src="<?php echo $us_url_root. 'usersc/plugins/simple_store/assets/template/'; ?>vendor/swiper/swiper-bundle.min.js"></script>
    <script src="<?php echo $us_url_root. 'usersc/plugins/simple_store/assets/template/'; ?>vendor/drift-zoom/Drift.min.js"></script>
    <script src="<?php echo $us_url_root. 'usersc/plugins/simple_store/assets/template/'; ?>vendor/purecounter/purecounter_vanilla.js"></script>
    <script src="<?php echo $us_url_root. 'usersc/plugins/simple_store/assets/template/'; ?>vendor/glightbox/js/glightbox.min.js"></script>
    <script src="<?php echo $us_url_root. 'usersc/plugins/simple_store/assets/template/'; ?>vendor/imagesloaded/imagesloaded.pkgd.min.js"></script>
    <script src="<?php echo $us_url_root. 'usersc/plugins/simple_store/assets/template/'; ?>vendor/isotope-layout/isotope.pkgd.min.js"></script>
    
    
    <!-- Main JS File -->
    <script src="<?php echo $us_url_root. 'usersc/plugins/simple_store/assets/template/'; ?>js/main.js"></script>

</body>
</html>