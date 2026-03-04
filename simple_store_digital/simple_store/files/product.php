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

<?require_once 'usersc/plugins/simple_store/assets/template/'.'views/header.php'; //Header ?> 

<body>

  <?php 
  if($hidden_to_customer == 1){
  echo '
  <br /><br />
  <div class="container"> 
  <div class="alert alert-secondary text-center" role="alert"> This product is hidden to customer. 
  <br /><br />
  Go to Store Admin -> Products -> Edit Product 
  <br />
  then mark as "Available"
  <br /><br /> 
  <a class="btn btn-secondary" href="store_admin.php?id=edit_product&product_id='.$product_id .'">Click here to edit product</a>
   </div>
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
    <section id="product-details" class="product-details section">

      <div class="container" data-aos="fade-up" data-aos-delay="100">

        <div class="row g-4">
          <!-- Product Gallery -->
          <div class="col-lg-7" data-aos="zoom-in" data-aos-delay="150">
            <div class="product-gallery">
              <div class="main-showcase">
                <div class="image-zoom-container">
                    
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

                  <div class="image-navigation">
                    <button class="nav-arrow prev-image image-nav-btn prev-image" type="button">
                      <i class="bi bi-chevron-left"></i>
                    </button>
                    <button class="nav-arrow next-image image-nav-btn next-image" type="button">
                      <i class="bi bi-chevron-right"></i>
                    </button>
                  </div>
                </div>
              </div>

              <div class="thumbnail-grid">
                  <?php
                        //for each loop to show all product images. it shows the primary image first
                        $products_images_thumb = $db->query("SELECT * FROM simple_store_products_images WHERE product_id = ? ORDER BY is_primary DESC", [$_GET["id"]])->results(); // get images
                        if(count($products_images_thumb) === 0){
                                echo "";
                            } else {
                                foreach($products_images_thumb as $thumb){
                                    if($thumb->is_primary == 1){$primary_check = "active";} else {$primary_check = "";}
                                    echo ' 
                                        <div class="swiper-slide thumbnail-item '.$primary_check.'" data-image="'.$thumb->image.'">
                                          <img src="'.$thumb->image.'" alt="Product Thumbnail" class="img-fluid">
                                        </div>
                                    
                                    ';
                                }
                            }
                        
                        ?>  
              </div>
            </div>
          </div>

          <!-- Product Details -->
          <div class="col-lg-5" data-aos="fade-left" data-aos-delay="200">
            <div class="product-details">
              <div class="product-badge-container">
                <span class="badge-category"><?=$catergory_name?></span>
                
              </div>

              <h1 class="product-name"><?=$products->name?></h1>

              <div class="pricing-section">
                <div class="price-display">
                  <span class="sale-price">$ <?=$products->price?></span>
                  <span class="regular-price">$ <?=round($products_price_10,2)?></span>
                </div>
                <div class="savings-info">
                  <span class="discount-percent">(10% off)</span>
                </div>
              </div>

        
              <?php 
                //Checks if product is sold out, displays if available or not.
                if($products->sold_out == "1"){$sold_out_status = "Sold out"; $sold_out_icon = "bi-x-circle-fill text-danger"; $sold_out_color = "sold_out";} if($products->sold_out == "0"){$sold_out_status = "Available"; $sold_out_icon = "bi-check-circle-fill text-success"; $sold_out_color = "available";}
                ?>    
              <div class="availability-status ">
                <div class="stock-indicator <?=$sold_out_color?>">
                  <i class="bi <?=$sold_out_icon?>"></i>
                  <span class="">&nbsp; <?=$sold_out_status?></span>
                </div>
                <div class="quantity-left"></div>
              </div>

            <form id="product_form" action="#" method="post">    
              <!-- Product Variants -->
              <div class="variant-section d-none">
                <div class="color-selection">
                  <label class="variant-label">Available Colors:</label>
                  <div class="color-grid">
                    <?php $color_hex = $db->query("SELECT * FROM simple_store_products_colors WHERE name = ?",[$products->color])->first();?>
                    <input type="radio" class="btn-check" id="color" name="color" autocomplete="off" id="color" value="<?=$products->color?>" checked  aria-label="Product Color Hex <?=$products->color?>">
                    <label for="color" hidden><?=$products->color?></label>
                    <div class="color-chip active" data-color="Midnight Black" style="background-color: #<?=$color_hex->hex?>;">
                      <span class="selection-check"><i class="bi bi-check"></i></span>
                    </div>
                  </div>
                  <div class="selected-variant">Selected: <span><?=$color_hex->name?></span></div>
                </div>
              </div>
              
              <!-- Size Options if applicable -->
                  <div class="variant-section">
                    <div class="variant-label">Type:</div>

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

              <!-- Purchase Options -->
              <div class="purchase-section">
                <div class="action-buttons">
                  <?php 
                    // if product not sold out, disable as add to cart
                    if($products->sold_out == "0"){
                        echo ' <button type="submit" class="btn secondary-action">
                                  <i class="bi bi-cart-plus"></i> Add to Cart
                                </button> ';}
                    //if product sold out, display button as disabled    
                    if($products->sold_out == "1"){
                            echo '<button class="btn secondary-action disabled">
                                  <i class="bi bi-cart-plus"></i> Sold Out
                                </button> ';}
                    ?>    
                    
                
                </div>
              </div>
            </form>

              <!-- Benefits List -->
              <div class="benefits-list">
                <div class="benefit-item">
                  <i class="bi bi-cloud-download"></i>
                  <span>
                    All files available for instant download once payment is confirmed.</span>
                </div>
                <div class="benefit-item">
                  <i class="bi bi-patch-exclamation"></i>
                  <span>This is a digital product, no exchanges or refunds</span>
                </div>
              </div>


              <?php
                        // if is admin, show download links
                        
                        if(isUserLoggedIn()) {
                            if(hasPerm([2], $user->data()->id)){
                                $products_downloads = $db->query("SELECT * FROM simple_store_products_downloads WHERE product_id = ? ", [$_GET["id"]])->results(); // get downloads  
                                $products_downloads_count = "1"; //used for download count
                                echo "<br /> <div class='benefits-list'>";
                                echo '<div class="benefit-item">
                                        <i class="bi bi-file-lock"></i>
                                        <span>THIS VIEW IS FOR ADMIN ONLY</span>
                                      </div>';
                                
                                //if there are no download, ask to upload atleast one download
                                if(count($products_downloads) === 0){

                                    echo '<div class="benefit-item">
                                            <i class="bi bi-file-earmark-x"></i>
                                            <span>There are no downloads for this product</span>
                                          </div>';
                                    
                                } else {
                                    //loops/shows all downloads
                                    foreach($products_downloads as $product_d){
                                        echo "
                                            <div class='benefit-item '>
                                              <div class='col-6'><span class='text-secondary'>Download ".$products_downloads_count."  : </span></div>
                                              <div class='col-6'><a href='/downloads/index.php?id=".$product_d->id."' alt='Product' class='btn btn-secondary w-100' loading='lazy'>Download</a></div>
                                            </div>
                                        ";
                                        $products_downloads_count++; // adds one for downlaod count
                                    }
                                    
                                }
                                echo "<div class='benefit-item '>
                                        <br />
                                        <div class='col-12'><span><a class='btn btn-secondary w-100' href='store_admin.php?id=edit_product&product_id=".$_GET["id"]."'>Edit Product</a></span></div>
                                      </div>";
                                echo "</div>";
                                
                                
                            }
                        }
                    ?>


            </div>
          </div>
        </div>

        <!-- Information Tabs -->
        <div class="row mt-5" data-aos="fade-up" data-aos-delay="300">
          <div class="col-12">
            <div class="info-tabs-container">
              <nav class="tabs-navigation nav">
                <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#ecommerce-product-details-5-overview" type="button">Overview</button>
              </nav>

              <div class="tab-content">
                <!-- Overview Tab -->
                <div class="tab-pane fade show active" id="ecommerce-product-details-5-overview">
                  <div class="overview-content">
                    <div class="row g-4">
                      <h4>***This is a DIGITAL PRODUCT, No physical item will be sent***</h4>

                      <div class="col-lg-12">
                        <div class="package-contents">
                          <h4>Product Overview</h4>
                          <ul class="contents-list">
                            <?=$products->description?>
                          </ul>
                        <br /> 
                          <h4>Download Instructions</h4>
                          <ul class="contents-list">
                            <li>When you purchase a design, the website will automatically send you an email with a link to download the design. (Please check your “All Mail” folder) .</li>
                                *** I WOULD LOVE TO SEE YOUR FINISHED PRODUCT….. PLEASE SEND US A PHOTO!***
                          </ul>
                        <br /> 
                          <h4>Terms of Use</h4>
                          <ul class="contents-list">
                             <li>You must have an embroidery machine and the ability to transfer designs from your computer to your embroidery machine.</li>
                             <li>You may use our designs to make finished items for personal use, gifts, and finished products you sell. Our designs are not for use to make heat transfers or vinyl designs..</li>
                             <li>Please DO NOT resell, distribute, share, copy, and reproduce any of the designs.</li>
                          </ul>
                        <br /> 
                          <h4>Refunds/Returns</h4>
                          <ul class="contents-list">
                             <li>All of our items are Digital Downloads. Because of this, we don't accept returns or exchanges.</li>
                             <li>However, please contact us with any issues you have with your order and we'll try to make it right.</li>
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
    
        

<?require_once 'usersc/plugins/simple_store/assets/template/'.'views/footer.php'; //Header ?> 