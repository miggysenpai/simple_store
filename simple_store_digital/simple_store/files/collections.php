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

<?require_once 'usersc/plugins/simple_store/assets/template/'.'views/header.php'; //Header ?> 

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
  

        
<?require_once 'usersc/plugins/simple_store/assets/template/'.'views/footer.php'; //Header ?> 