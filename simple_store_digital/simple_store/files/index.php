<?php require_once 'users/init.php'; 

//start session and create cart session if one not available
session_start();
if(!isset($_SESSION['cart'])){
   $_SESSION['cart'] = [];
   } 
?>
<?require_once 'usersc/plugins/simple_store/assets/template/'.'views/header.php'; //Header ?> 


<main class="main">
    
    <?php  
        $store_live_check = $db->query("SELECT * FROM simple_store_settings ")->first(); // get store settings
        // if not live, show this
        if($store_live_check->live == "0"){?>
            <!-- STORE NOT LIVE-->
            <section id="call-to-action" class="call-to-action section">
        
              <div class="container "   data-aos="fade-up"data-aos-delay="100">
        
                <div class="row">
                  <div class="col-lg-8 mx-auto">
                    <div class="main-content text-center" data-aos="zoom-in" data-aos-delay="200">
                      <br /><br /><br /><br /><br />
                      <h2 data-aos="fade-up" data-aos-delay="300">Store is Closed!</h2>
        
                      <p class="subtitle" data-aos="fade-up" data-aos-delay="350">
                          The store is currently closed! Sorry for any inconveniences!
                          :c We are working to get it back up as soon as possible! You should 
                          still be able to track any orders ! Contact us for any questions!!
                      </p>
        
                      <div class="action-buttons" data-aos="fade-up" data-aos-delay="450">
                        <?php 
                            //checks if is admin
                              if(isUserLoggedIn()) {
                                  if(hasPerm([2], $user->data()->id)){
                                    echo "<br /><br />
                                      <a class='btn-view-deals' href='store_admin.php?id=settings'>Click here To go to Store Admin</a>
                                     ";
                                  }
                              } 
                        ?>
                        <br /><br /><br /><br /><br /><br /><br />
                      </div>
                    </div>
                  </div>
                </div>
        
              </div>
        
            </section><!-- /Call To Action Section -->
        
        <?php } 
       
        if($store_live_check->live == "1"){  
            
            if(isset($_GET["id"])){
               $store_id = $_GET["id"]; 
            } else {
              $store_id = ""; 
            }
        ?>
        
            
        
        <!-- Call To Action Section -->
        <section id="call-to-action" class="call-to-action section">
    
          <div class="container " data-aos="fade-up" data-aos-delay="100">
    
            <div class="row">
              <div class="col-lg-8 mx-auto ">
                <div class="main-content text-center" data-aos="zoom-in" data-aos-delay="200">
                  
                  <h2 data-aos="fade-up" data-aos-delay="300">Shop.</h2>
    
                  <p class="subtitle" data-aos="fade-up" data-aos-delay="350">Thank you for stopping by and checking out my shop.Hopefully you find something you like!</p>
    
                  <div class="action-buttons" data-aos="fade-up" data-aos-delay="450">
                    <a href="?id=shop_all" class="btn-shop-now">Shop All</a>
                    <a href="?id=shop_catergories" class="btn-view-deals">Shop Catergories</a>
                  </div>
                </div>
              </div>
            </div>
    
          </div>
    
        </section><!-- /Call To Action Section -->
        
        <?php if($store_id == "") {?>
            <!-- Best Sellers Section -->
            <section id="best-sellers" class="best-sellers section">
        
              <!-- Section Title -->
              <div class="container section-title" data-aos="fade-up">
                <h2>New Releases</h2>
                <p>Checkout our newest products</p>
              </div><!-- End Section Title -->
        
              <div class="container" data-aos="fade-up" data-aos-delay="100">
        
                <div class="row g-5">
                    <?php
                        // Gets all products that are marked as available/sold out. 
                          $products = $db->query("SELECT * FROM simple_store_products WHERE status = ? ORDER BY id DESC LIMIT 10", [1])->results(); 
                          foreach($products as $p) {
                                $product_catergory = $db->query("SELECT * FROM simple_store_catergories WHERE id = ? ", [$p->catergory])->first();
                                $product_image = $db->query("SELECT * FROM simple_store_products_images WHERE product_id = ? AND is_primary = ?", [$p->id, "1"])->results(); // checks for primary image
                                if(count($product_image) == 0){$image_src = ""; } else { $image_src = '<img src="'.$product_image[0]->image.'" class="img-fluid" loading="lazy" alt="Product with the name '.$p->name.' in the '.$p->catergory.' catergory">';} //use primary image if available ?>  
                                <!-- Product -->
                                  <div class="col-lg-3 col-md-6">
                                    <a href="product.php?id=<?=$p->id?>" aria-label="Go to Product Page">
                                    <div class="product-item">
                                      <div class="product-image">
                                          <?=$image_src?>
                                        <img src="assets/img/product/product-7.webp" alt="Product Image" class="img-fluid" loading="lazy">
                                        
                                      </div>
                                      <div class="product-info">
                                        <div class="product-category"><?=$product_catergory->name?></div>
                                        <h4 class="product-name"><?=$p->name?></h4>
                                        <div class="product-price">$ <?=$p->price?></div>
                                      </div>
                                    </div>
                                    </a>
                                  </div>
                                <!-- End Product -->    
                                
                           <?php }
                    ?>
        
                </div>
        
              </div>
        
            </section><!-- /Best Sellers Section -->
        <?php 
        }
        if($store_id == "shop_all"){
        ?>
            <!-- Best Sellers Section -->
            <section id="best-sellers" class="best-sellers section">
        
              <!-- Section Title -->
              <div class="container section-title" data-aos="fade-up">
                <h2>Shop All</h2>
                <p>Checkout all of our products</p>
              </div><!-- End Section Title -->
        
              <div class="container" data-aos="fade-up" data-aos-delay="100">
        
                <div class="row g-5">
                    <?php
                        // Gets all products that are marked as available/sold out. 
                          $products = $db->query("SELECT * FROM simple_store_products WHERE status = ? ", [1])->results(); 
                          foreach($products as $p) {
                                $product_catergory = $db->query("SELECT * FROM simple_store_catergories WHERE id = ? ", [$p->catergory])->first();
                                $product_image = $db->query("SELECT * FROM simple_store_products_images WHERE product_id = ? AND is_primary = ?", [$p->id, "1"])->results(); // checks for primary image
                                if(count($product_image) == 0){$image_src = ""; } else { $image_src = '<img src="'.$product_image[0]->image.'" class="img-fluid" loading="lazy" alt="Product with the name '.$p->name.' in the '.$p->catergory.' catergory">';} //use primary image if available ?>  
                                <!-- Product -->
                                  <div class="col-lg-3 col-md-6">
                                    <a href="product.php?id=<?=$p->id?>" aria-label="Go to Product Page">
                                    <div class="product-item">
                                      <div class="product-image">
                                          <?=$image_src?>
                                        <img src="assets/img/product/product-7.webp" alt="Product Image" class="img-fluid" loading="lazy">
                                        
                                      </div>
                                      <div class="product-info">
                                        <div class="product-category"><?=$product_catergory->name?></div>
                                        <h4 class="product-name"><?=$p->name?></h4>
                                        <div class="product-price">$ <?=$p->price?></div>
                                      </div>
                                    </div>
                                    </a>
                                  </div>
                                <!-- End Product -->  
                                
                           <?php }
                    ?>
        
                </div>
        
              </div>
        
            </section><!-- /Best Sellers Section -->
        <?php } 
        
        if($store_id == "shop_catergories"){
        ?>
            <!-- Best Sellers Section -->
            <section id="best-sellers" class="best-sellers section">
        
              <!-- Section Title -->
              <div class="container section-title" data-aos="fade-up">
                <h2>Shop Catergories</h2>
                <p>Checkout all of our products</p>
              </div><!-- End Section Title -->
        
              <div class="container" data-aos="fade-up" data-aos-delay="100">
        
                <div class="row g-5 promo-cards">
                      
                    <?php
                        $product_catergories = $db->query("SELECT * FROM simple_store_catergories WHERE is_subcatergory = ?", ["0"])->results(); // gets all catergories
                        //if no catergories, show this (Filler code, will get redirected before this is shown)
                        if (count($product_catergories) === 0) {
                            echo '
                                <div class="error-404"> 
                                <div class="text-center">
                                  <div class="error-icon mb-4" data-aos="zoom-in" data-aos-delay="200">
                                    <i class="bi bi-exclamation-circle"></i>
                                  </div>
                        
                        
                                  <h2 class="error-title mb-3" data-aos="fade-up" data-aos-delay="400">Oops! No catergories found!</h2>
                        
                                  <p class="error-text mb-4" data-aos="fade-up" data-aos-delay="500">
                                    Admin needs to add atleast one Catergory...
                                  </p>
                        
                                  
                        
                                  <div class="error-action" data-aos="fade-up" data-aos-delay="700">
                                    <a href="/" class="btn btn-primary">Back to Home</a>
                                  </div>
                                </div>
                                Admin needs to add atleast one Catergory...
                                   <br /><br />
                                   
                             ';
                        } else {
                            
                            foreach($product_catergories as $p) {?>
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
                                <div class="col-xl-6">
                                    <div class="category-card cat-men" data-aos="fade-up" data-aos-delay="300">
                                      <div class="category-image">
                                           <?php if($p->image != ""){ echo '<img src="'.$p->image.'" class="img-fluid" alt="image for '.$p->name.'">';} else {echo "";} ?> 
                                      </div>
                                      <div class="category-content">
                                        <h4><?=$p->name?></h4>
                                        <p><?=count($main_cat)?> products</p>
                                        <a href="collections.php?id=<?=$p->id?>" class="card-link">Shop Now <i class="bi bi-arrow-right"></i></a>
                                      </div>
                                    </div>
                                </div>
                            
                            <?php } 
                        }
                        
                    ?>
        
                </div>
        
              </div>
        
            </section><!-- /Best Sellers Section -->
        <?php } ?>
        
        <?php } ?>

  </main>

  
  <?require_once 'usersc/plugins/simple_store/assets/template/'.'views/footer.php'; //Header ?> 
