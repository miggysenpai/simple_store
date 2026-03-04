<?php 
ob_start();
require_once 'users/init.php'; 

//start session and create cart session if one not already available
session_start();
if(!isset($_SESSION['cart'])){
   $_SESSION['cart'] = [];
   } 
?>
<?require_once 'usersc/plugins/simple_store/assets/template/'.'views/header.php'; //Header ?> 

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


    <section id="best-sellers" class="best-sellers section">

      <!-- Section Title -->
      <div class="container section-title" data-aos="fade-up">
        <h2>Search</h2>
        <p>Search through all of our products</p>
      </div><!-- End Section Title -->

      <div class="container" data-aos="fade-up" data-aos-delay="100">
        
       <?php
       // order tracking form
       if(!$_POST){?>  
        <div class="error-404 ">
          <div class="search-box " data-aos="fade-up" data-aos-delay="600">
            <form action="?" method="POST" class="search-form">
              <div class="input-group">
                <input type="text" class="form-control" name="search" placeholder="Search for products..." aria-label="Search">
                <button class="btn search-btn" type="submit">
                  <i class="bi bi-search"></i>
                </button>
              </div>
            </form>
            <br /><br /><br /><br /><br /><br />
          </div>
        </div>  
                       
        
    <?php }
    
    // check if post 
    if(!empty($_POST)){
        $search = Input::get('search');
        if(isset($search)) {
            $search_check = $db->query("SELECT * FROM simple_store_products WHERE status = 1 AND (name LIKE '%$search%' OR id LIKE '%$search%' OR catergory LIKE '%$search%' OR description LIKE '%$search%')")->results();; // search check
            
            
            if(count($search_check) == 0 ){
                echo '
                    <div class="error-404 ">
                      <div class="search-box " data-aos="fade-up" data-aos-delay="600">
                        <form action="?" method="POST" class="search-form">
                          <div class="input-group">
                            <input type="text" class="form-control" name="search" value="'. $search .'" placeholder="Search for products..." aria-label="Search">
                            <button class="btn search-btn" type="submit">
                              <i class="bi bi-search"></i>
                            </button>
                          </div>
                        </form>
                      </div>
                      <br />
                      <div class="text-center">
                          <p class="error-text " data-aos="fade-up" data-aos-delay="500">
                            nothing found . . .  
                            <br /> <br />
                            please try again.
                          </p>
                          
                            <br /> <br />
                          
                          <div class="error-action" data-aos="fade-up" data-aos-delay="700">
                            <a href="/index.php?id=shop_all" class="btn btn-primary">Shop All</a>
                          </div>
                      </div>
                    </div>  
                        
                        '; 
                
            } else {
            // this shows results
            echo '
                <div class="error-404 ">
                      <div class="search-box " data-aos="fade-up" data-aos-delay="600">
                        <form action="?" method="POST" class="search-form">
                          <div class="input-group">
                            <input type="text" class="form-control" name="search" value="'. $search .'" placeholder="Search for products..." aria-label="Search">
                            <button class="btn search-btn" type="submit">
                              <i class="bi bi-search"></i>
                            </button>
                          </div>
                        </form>
                      </div>
                      <br />
                    </div>';
                    
            echo '<div class="row g-5">';
            echo '';
            foreach($search_check as $p) {
                $product_catergory = $db->query("SELECT * FROM simple_store_catergories WHERE id = ? ", [$p->catergory])->first();
                $product_image = $db->query("SELECT * FROM simple_store_products_images WHERE product_id = ? AND is_primary = ?", [$p->id, "1"])->results(); // checks for primary image
                if(count($product_image) == 0){$image_src = ""; } else { $image_src = '<img src="'.$product_image[0]->image.'" class="card-img-top on-hover-zoom " alt="Product with the name '.$p->name.' in the '.$p->catergory.' catergory">';} //use primary image if available ?> 
                <!-- Product -->
                                  <a href="product.php?id=<?=$p->id?>" aria-label="Go to Product Page">
                                  <div class="col-lg-3 col-md-6">
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
                                  </div>
                                  </a>
                                <!-- End Product --> 
           <?php } ?>
                
              </div>

              </div> 
            <?php }   
        }
 } 
 
 ?>
 <br /><br />
</section>
    
<?require_once 'usersc/plugins/simple_store/assets/template/'.'views/footer.php'; //Header ?> 