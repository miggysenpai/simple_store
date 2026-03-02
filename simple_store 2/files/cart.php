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

<? require_once 'usersc/plugins/simple_store/assets/template/'.'views/header.php'; //Header ?> 

  
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
                echo '
              
                <div class="login section">    
                  <div class="">
                    <div class="form-header">
                    <br /><br />
                      <h3>Kinda empty here . . . </h3>
                      <p>Go add some stuff to your cart!</p>
                    </div>
                        
                      <a href="index.php?id=shop_all" class="auth-btn primary-btn mb-3">
                        Shop All
                        <i class="bi bi-arrow-right"></i>
                      </a>
                      <br /><br /><br /><br /><br /><br /><br /><br /><br />
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
                <br /><br />
              </form>    
              </div>
              
              <div class="continue-shopping">
                <a href="index.php" class="btn btn-accent w-100" aria-label="Go to Home Page">
                  <i class="bi bi-arrow-left"></i> Continue Shopping
                </a>
              </div>
                
              <br />    
              <div class="payment-methods text-center">
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
   
 <? require_once 'usersc/plugins/simple_store/assets/template/'.'views/footer.php'; //Header ?> 