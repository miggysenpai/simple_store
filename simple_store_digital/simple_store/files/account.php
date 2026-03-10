<?php
// This is a user-facing page
/*
UserSpice
An Open Source PHP User Management System
by the UserSpice Team at http://UserSpice.com

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/
require_once '../users/init.php';
if (!securePage(Server::get('PHP_SELF'))) {
  die();
}

//require_once $abs_us_root . $us_url_root . 'users/includes/template/prep.php';
 require_once '../usersc/plugins/simple_store/assets/template/'.'views/header.php'; // CustomHeader
 $userdetails = $user->data();

$hooks = getMyHooks();
if ($hooks['bottom'] == []) {
  $resize = [];
} else {
  $resize = [];
}
includeHook($hooks, 'pre');

if (!empty($_POST['uncloak'])) {
  logger($user->data()->id, 'Cloaking', 'Attempting Uncloak');
  if (isset($_SESSION['cloak_to'])) {
    $to = $_SESSION['cloak_to'];
    $from = $_SESSION['cloak_from'];
    unset($_SESSION['cloak_to']);
    $_SESSION[Config::get('session/session_name')] = $_SESSION['cloak_from'];
    unset($_SESSION['cloak_from']);
    logger($from, 'Cloaking', 'uncloaked from ' . $to);
    $cloakHook =  getMyHooks(['page' => 'cloakEnd']);
    includeHook($cloakHook, 'body');
    usSuccess("You are now you");
    Redirect::to($us_url_root . 'users/admin.php?view=users');
  } else {
    usError("Something went wrong. Please login again");
    Redirect::to($us_url_root . 'users/logout.php');
  }
}

$grav = fetchProfilePicture($user->data()->id);
$raw = date_parse($user->data()->join_date);
$signupdate = $raw['month'] . '/' . $raw['year'];
if ($hooks['bottom'] == []) { //no plugin hooks present
  $resize = [
    'cardClass' => 'col-md-4 offset-md-4',
    'nameSize' => 'style="font-size:3em;"',
    'sinceSize' => 'style="font-size:2.25em;"',
  ];
} else {
  $resize = [
    'cardClass' => 'col-md-3',
    'nameSize' => '',
    'sinceSize' => '',
  ];
}
?>

<?php 
    $orders_search = $db->query("SELECT * FROM simple_store_stripe_transactions WHERE customer_email = ? ",[$userdetails->email])->results(); // get order info
    $orders_count = count($orders_search);
?>


<!-- Account Section -->
    <section id="account" class="account section"  style="min-height: 68vh;">

      <div class="container" data-aos="fade-up" data-aos-delay="100">

        <!-- Mobile Menu Toggle -->
        <div class="mobile-menu d-lg-none mb-4">
          <button class="mobile-menu-toggle" type="button" data-bs-toggle="collapse" data-bs-target="#profileMenu">
            <i class="bi bi-grid"></i>
            <span>Menu</span>
          </button>
        </div>

        <div class="row g-4">
          <!-- Profile Menu -->
          <div class="col-lg-3">
            <div class="profile-menu collapse d-lg-block" id="profileMenu">
              <!-- User Info -->
              <div class="user-info" data-aos="fade-right">
                <h4><?= $user->data()->fname . ' ' . $user->data()->lname; ?></h4>
                <div class="user-status">
                  <i class="bi bi-award"></i>
                  <span>Coolest Member</span>
                </div>
              </div>

              <!-- Navigation Menu -->
              <nav class="menu-nav">
                <ul class="nav flex-column" role="tablist">
                  <li class="nav-item">
                    <a class="nav-link active" data-bs-toggle="tab" href="#orders">
                      <i class="bi bi-box-seam"></i>
                      <span>My Orders</span>
                      <span class="badge"><?=$orders_count?></span>
                    </a>
                  </li>
                  
                  <li class="nav-item">
                    <a class="nav-link"  href="../users/user_settings.php">
                      <i class="bi bi-gear"></i>
                      <span>Account Settings</span>
                    </a>
                  </li>
                  
                  <?php if($settings->passkeys > 0){ ?>
                    <li class="nav-item">
                        <a class="nav-link"  href="<?= $us_url_root ?>users/passkeys.php">
                          <i class="bi bi-key"></i>
                          <span><?= lang('PASSKEYS_MANAGE_TITLE'); ?></span>
                        </a>
                    </li>
                  <?php } ?>
                  <?php if(isset($settings->totp) && $settings->totp > 0){ ?>
                    <li class="nav-item">
                        <a class="nav-link"  href="<?= $us_url_root ?>users/totp_management.php">
                          <i class="bi bi-lock"></i>
                          <span><?= lang('ACCT_2FA'); ?></span>
                        </a>
                    </li>
                  <?php } ?>
        
                  <?php if (isset($_SESSION['cloak_to'])) { ?>
                    <p>
                    <form class="" action="" method="post">
                      <input type="hidden" name="uncloak" value="Uncloak!">
                      <button class="btn btn-danger btn-block w-100" role="submit">Uncloak</button>
                    </form>
                    </p>
                  <?php  } //end cloak button 
                  ?>
                 
                </ul>

                <div class="menu-footer">
                  <a href="../support.php?id=contact" class="help-link">
                    <i class="bi bi-question-circle"></i>
                    <span>Help Center</span>
                  </a>
                  <a href="../users/logout.php" class="logout-link">
                    <i class="bi bi-box-arrow-right"></i>
                    <span>Log Out</span>
                  </a>
                </div>
              </nav>
            </div>
          </div>

          <!-- Content Area -->
          <div class="col-lg-9">
            <div class="content-area">
              <div class="tab-content">
                <!-- Orders Tab -->
                <div class="tab-pane fade show active" id="orders">
                  <div class="section-header" data-aos="fade-up">
                    <h2>My Orders</h2>
                    <div class="header-actions">
                      <div class="search-box d-none">
                        <i class="bi bi-search"></i>
                        <input type="text" placeholder="Search orders...">
                      </div>
                      
                    </div>
                  </div>

                  <div class="orders-grid">
                   
                   <?php 
                   
                   
                   if(isset($search)){
                       // search is set, show results
                       // i got lazy so no search.
                   } else {
                   // search is not set       
                   
                   if ($orders_count === 0) { 
                       echo '
                        <div class="order-card" data-aos="fade-up" data-aos-delay="400">
                          <div class="order-header">
                            <div class="order-id">
                              <span class="label"></span>
                              <span class="value">No orders found... :c</span>
                            </div>
                          </div>
                        </div>';
                       
                   } else {
                       
                    // there are orders, show this   
                    $total_qty = 0;
                    
                    foreach($orders_search as $t) {
                    $items = $db->query("SELECT * FROM simple_store_transactions_item WHERE customer_email = ? AND receipt_number = ? ", [$userdetails->email, $t->receipt_number])->results(); 
                    
                    
                   
                    
                    ?> 
                  

                    <!-- Order Card 4 -->
                    <div class="order-card" data-aos="fade-up" data-aos-delay="400">
                      <div class="order-header">
                        <div class="order-id">
                          <span class="label">Order ID:</span>
                          <span class="value">#<?=$t->receipt_number?></span>
                        </div>
                        <div class="order-date"><?=(date("F j, Y",$t->created)); ?></div>
                      </div>
                      <div class="order-content">
                        <div class="product-grid">
                            
                            <?php
                            $prod_c = 0;
                            $total_qty = 0;
                            foreach($items as $productInfo){
                            $product_local_variant = $db->query("SELECT * FROM simple_store_products_variants WHERE price_id = ?",[$productInfo->price_id])->first(); // get variant info (size)
                            $product_local = $db->query("SELECT * FROM simple_store_products WHERE id = ?",[$product_local_variant->product_id])->first(); // get product info
                            $product_img = $db->query("SELECT * FROM simple_store_products_images WHERE product_id = ? AND is_primary = ?",[$product_local->id, "1"])->first(); // get primary image
                            
                                if($prod_c < 3){
                                    echo '<img src="../'.$product_img->image.'" alt="Product" loading="lazy">';
                                    $prod_c++;
                                }
                                 $total_qty = $total_qty + $productInfo->qty;
                            ?>
                            
                            <?php } ?>
                         
                            <?php 
                                if(count($items) > 3) {
                                $plus_item = count($items) - 3;
                                  echo '<span class="more-items">+'.$plus_item.'</span>';  
                                }
                            ?>
                          
                        </div>
                        <div class="order-info">
                          <div class="info-row">
                            <span>Items</span>
                            <span><?=$total_qty?> items</span>
                          </div>
                          <div class="info-row">
                            <span>Total</span>
                            <span class="price">$ <?=$t->amount_total/100?></span>
                          </div>
                        </div>
                      </div>
                      <div class="order-footer">
                        <a href="../trackorder.php?order_number=<?=$t->receipt_number?>&order_email=<?=$userdetails->email?>" class='w-100'><button type="button" class="btn-details w-100">View Details</button></a>
                      </div>
                    </div>
                    <?php } // end foreach ?>
                    
                    <?php } }?>
                  </div>

                 
                </div>

            
              </div>
            </div>
          </div>
        </div>

      </div>

    </section><!-- /Account Section -->


<?php
// require_once $abs_us_root . $us_url_root . 'users/includes/html_footer.php'; 
 require_once '../usersc/plugins/simple_store/assets/template/'.'views/footer.php'; // Custom Footer
 ?>