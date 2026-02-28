<?php
require_once 'users/init.php'; 

$store_live_check = $db->query("SELECT * FROM simple_store_settings ")->first(); // get store settings
// checks if the store is live, if not, redirect to index.php
if($store_live_check->live == "0"){
header("Location: index.php");
die();
} 

$catergory_id = "";
$catergory_name = "";
//check if there is a catergory id. 
if(isset($_GET["id"])){
    
    //if catergory id availble, set id.
    $catergory_id = $_GET["id"];
    
    //check if catergory exist
    $catergory_availabilty = $db->query("SELECT * FROM simple_store_catergories WHERE id = ? ", [$catergory_id])->results(); 
    
    //if catergory does not exist, redirect to home
    if(count($catergory_availabilty) == 0){
        header("Location: index.php");
        die();
    }
    $catergory_name = $catergory_availabilty[0]->name;
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


?>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>Collections Page - <?php echo $settings->site_name;?></title>
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
    
    <!-- Page Title -->
    <div class="container my-5">
            <div class="page-title ">
              <div class="container d-lg-flex justify-content-between align-items-center">
                <h1 class="breadcrumbs">
                    <ol>
                    <li><a href="index.php" aria-label="Go to Home Page">shop</a></li>
                    <li><a href="collections.php" aria-label="Go to Collections Page">Collections</a></li>
                  </ol>
                </h1>
              </div>
            </div>
    </div>
    
    <?
    //*****MAIN PAGE, NO ID SET
        if($catergory_id == ""){
            
            echo "
            <div class='order-confirmation-1'>
              <div class='next-steps  p-4'  >
                
                 <section id='cart' class='cart section'>
                  <div class='container' >
                  
                  <div class='row g-4'>
                  
                  <div class='col-lg-12' >
                    <div class='cart-items'>
              <div class='next-steps text-center p-4'  >
                <h1>Collections</h1>
                
                 <!-- Payment Options Grid -->
                 <section id='paymnt-methods' class='paymnt-methods section'>
                    <div class='payment-options'>
                      <div class='row g-4 '>
                      <div class='container'>
                      ";
                      $product_catergories = $db->query("SELECT * FROM simple_store_catergories WHERE is_subcatergory = ?", ["0"])->results(); // gets all catergories
                    //if no catergories, show this (Filler code, will get redirected before this is shown)
                    if (count($product_catergories) === 0) {
                         echo "Admin needs to add atleast one Catergory...
                               <br /><br />
                               
                         ";
                    } else {
                    // loop/show all catergories    
                    echo '<div class="row ">';
                    foreach($product_catergories as $p) {
                    
                    ?> 
                    <!--ADD CLASSES HERE d-flex align-items-stretch-->
                      <div class="col-lg-4 mb-3 d-flex align-items-stretch">
                        <div class="card w-100">
                           <?php if($p->image != ""){ echo '<div class="container rounded"><img src="'.$p->image.'" class="card-img-top" alt="image for '.$p->name.'"></div>';} else {echo "";} ?> 
                          <div class="card-body d-flex flex-column">
                            <a href="?id=<?=$p->id?>">  
                                <h5 class="card-title"><?=$p->name?></h5>
                                <?php 
                                // Gets all products that are marked as available/sold out. 
                                  $products_sub_loop = $db->query("SELECT * FROM simple_store_catergories WHERE subcartergory_of = ? ", [  $p->id ])->results(); 
                                  $prod_sub_merge = [];
                                  foreach($products_sub_loop as $p_l) {
                                      $prod_sub_loop2 = [$p_l->id];
                                      $prod_sub_merge = array_merge($prod_sub_merge, $prod_sub_loop2);
                                  }
                                  $prod_sub_loop = [$p->id ];
                                  $prod_sub_merge = array_merge($prod_sub_merge, $prod_sub_loop);
                                
                                $main_cat = $db->query("SELECT * FROM simple_store_products WHERE catergory IN (".implode(',',$prod_sub_merge).") ")->results(); // gets all catergories?>
                                <h6><?=count($main_cat)?> Products</h6>
                            </a>
                            <hr />
                                <?php $product_catergories_sub = $db->query("SELECT * FROM simple_store_catergories WHERE subcartergory_of = ?", [$p->id])->results(); // gets all catergories?>
                                <?php foreach($product_catergories_sub as $p_s) {?>
                                <?php $p_s_c = $db->query("SELECT * FROM simple_store_products WHERE catergory = ?", [$p->id])->results(); // gets all catergories?>
                                <p class="card-text mb-4"><a href="?id=<?=$p_s->id?>"><?=$p_s->name?> <span class="text-secondary">(<?=count($p_s_c)?> Products)</span> </a></p>
                                <?php }?>
                          </div>
                        </div>
                      </div>
                             
                    <?php } 
                        echo "</div>";
                    }
                         echo "
                        
                        
                      </div></div>
                    </div>
                    </div>
                </section>
                 </div></div></div></div></div></div>
            ";
        }
        
        
//*****Catergory is set
        if($catergory_id !== ""){
            echo '<div class="container my-5"><div class="position-relative p-5 text-center text-muted bg-body rounded-4">
        <div class="text-body-emphasis custom-h3">'.$catergory_name.' </div>
        
      </div>
      
      <div class="row row row-cols-1 row-cols-md-3 g-4 text-center  ">';
          // Gets all products that are marked as available/sold out. 
          $products_sub_loop = $db->query("SELECT * FROM simple_store_catergories WHERE subcartergory_of = ? ", [  $catergory_id ])->results(); 
          $prod_sub_merge = [];
          foreach($products_sub_loop as $p_l) {
              $prod_sub_loop2 = [$p_l->id];
              $prod_sub_merge = array_merge($prod_sub_merge, $prod_sub_loop2);
          }
          $prod_sub_loop = [$catergory_id];
          $prod_sub_merge = array_merge($prod_sub_merge, $prod_sub_loop);
          
          
          
          $products = $db->query("SELECT * FROM simple_store_products WHERE status = 1 AND catergory IN (".implode(',',$prod_sub_merge).") ")->results(); 
          if(count($products) === 0){ echo "<div class='col-12'><p class='text-center'>nothing available for this collection ... </p></div>";}
          foreach($products as $p) {
                $product_image = $db->query("SELECT * FROM simple_store_products_images WHERE product_id = ? AND is_primary = ? ", [$p->id, "1"])->results(); // checks for primary image
                if(count($product_image) == 0){$image_src = ""; } else { $image_src = '<img src="'.$product_image[0]->image.'" class="card-img-top on-hover-zoom " alt="product image for '.$p->name.'">';} //use primary image if available ?>  
                <div class="card-group ">
                <a href="product.php?id=<?=$p->id?>" aria-label="Go to Procuct Page By the Name <?=$p->name?>">
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
           echo "</div></div>";
            
        };?>
   

        
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
    <script src="<?php echo $us_url_root. 'usersc/plugins/simple_store/assets/template/'; ?>vendor/drift-zoom/Drift.min.js"></script>
    <script src="<?php echo $us_url_root. 'usersc/plugins/simple_store/assets/template/'; ?>vendor/purecounter/purecounter_vanilla.js"></script>
    <script src="<?php echo $us_url_root. 'usersc/plugins/simple_store/assets/template/'; ?>vendor/glightbox/js/glightbox.min.js"></script>
    <script src="<?php echo $us_url_root. 'usersc/plugins/simple_store/assets/template/'; ?>vendor/imagesloaded/imagesloaded.pkgd.min.js"></script>
    <script src="<?php echo $us_url_root. 'usersc/plugins/simple_store/assets/template/'; ?>vendor/isotope-layout/isotope.pkgd.min.js"></script>
    
    
    <!-- Main JS File -->
    <script src="<?php echo $us_url_root. 'usersc/plugins/simple_store/assets/template/'; ?>js/main.js"></script>
</body>
</html>