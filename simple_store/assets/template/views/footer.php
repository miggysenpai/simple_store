  <footer id="footer" class="footer dark-background">
    <div class="footer-main">
      <div class="container">
        <div class="row gy-4">
          <div class="col-lg-4 col-md-6">
            <div class="footer-widget footer-about">
              <a href="index.html" class="logo">
                <span class="sitename"><?php echo $settings->site_name;?></span>
              </a>
              <p>Thank you for checking out my shop.</p>

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

              <div class="social-links mt-4">
                <h5>Connect With Us</h5>
                <div class="social-icons">
                 <?php if($socials->facebook == ""){echo "";}else{echo "<a href='".$socials->facebook."' aria-label='Facebook Link'><i class='bi bi-facebook'></i></a>";}?>
                 <?php if($socials->instagram == ""){echo "";}else{echo "<a href='".$socials->instagram."' aria-label='Instagram Link'><i class='bi bi-instagram'></i></a>";}?>
                 <?php if($socials->tiktok == ""){echo "";}else{echo "<a href='".$socials->tiktok."' aria-label='Tiktok Link'><i class='bi bi-tiktok'></i></a>";}?>
                 <?php if($socials->youtube == ""){echo "";}else{echo "<a href='".$socials->youtube."' aria-label='Youtube Link'><i class='bi bi-youtube'></i></a>";}?>
                </div>
              </div>

               <?php } ?> 

            </div>
          </div>

          <div class="col-lg-4 col-md-6 col-sm-6">
            <div class="footer-widget">
              <h4>Shop</h4>
              <ul class="footer-links">
                <li><a href="<?=$us_url_root?>index.php?id=shop_all">Shop All</a></li>
                <li><a href="<?=$us_url_root?>index.php?id=shop_catergories">Shop Catergories</a></li>
                <li><a href="<?=$us_url_root?>support.php?id=ourstory">Our Story</a></li>
              </ul>
            </div>
          </div>

          <div class="col-lg-4 col-md-6 col-sm-6">
            <div class="footer-widget">
              <h4>Support</h4>
              <ul class="footer-links">
                <li><a href="<?=$us_url_root?>trackorder.php">Order Status</a></li>
                <li><a href="<?=$us_url_root?>support.php?id=shipping">Shipping Info</a></li>
                <li><a href="<?=$us_url_root?>support.php?id=returnpolicy">Returns &amp; Exchanges</a></li>
                <li><a href="<?=$us_url_root?>support.php?id=contact">Contact Us</a></li>
              </ul>
            </div>
          </div>

        </div>
      </div>
    </div>

    <div class="footer-bottom">
      <div class="container">
        <div class="row gy-3 align-items-center">
          <div class="col-lg-6 col-md-12">
            <div class="copyright">
              <p>© <span>Copyright</span> <strong class="sitename"><?php echo $settings->site_name;?></strong>. All Rights Reserved.</p>
            </div>
          </div>

          <div class="col-lg-6 col-md-12">
            <div class="d-flex flex-wrap justify-content-lg-end justify-content-center align-items-center gap-4">
              <div class="payment-methods">
                <div class="payment-icons">
                  <i class="bi bi-credit-card" aria-label="Credit Card"></i>
                  <i class="bi bi-paypal" aria-label="PayPal"></i>
                  <i class="bi bi-apple" aria-label="Apple Pay"></i>
                  <i class="bi bi-google" aria-label="Google Pay"></i>
                  <i class="bi bi-shop" aria-label="Shop Pay"></i>
                  <i class="bi bi-cash" aria-label="Cash on Delivery"></i>
                </div>
              </div>

              <div class="legal-links">
                <?php if(isUserLoggedIn()) { $login_out = $us_url_root."users/logout.php"; $login_text = "Logout";} else { $login_out = $us_url_root."users/login.php";  $login_text = "Admin Login";} 
                if(isAdmin()){echo '
                    <a href="users/admin.php"  aria-label="Go to Admin Page"> <span>Admin</span> </a> 
                    <a href="store_admin.php" aria-label="Go to Store Admin"> <span>Store Admin</span> </a> ';  } ?>
                <a href="<?=$login_out?>"><?=$login_text?></a>    
                <a href="tos.html">Terms </a>
                <a href="privacy.html">Privacy</a>
              </div>
            </div>
          </div>
        </div>

      </div>
    </div>
  </footer>

  <!-- Scroll Top -->
  <a href="#" id="scroll-top" class="scroll-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

  <!-- Preloader -->
  <div id="preloader"></div>

  <!-- Vendor JS Files -->
  <script src="<?php echo $us_url_root. 'usersc/plugins/simple_store/assets/template/'; ?>vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="<?php echo $us_url_root. 'usersc/plugins/simple_store/assets/template/'; ?>vendor/php-email-form/validate.js"></script>
  <script src="<?php echo $us_url_root. 'usersc/plugins/simple_store/assets/template/'; ?>vendor/swiper/swiper-bundle.min.js"></script>
  <script src="<?php echo $us_url_root. 'usersc/plugins/simple_store/assets/template/'; ?>vendor/aos/aos.js"></script>
  <script src="<?php echo $us_url_root. 'usersc/plugins/simple_store/assets/template/'; ?>vendor/glightbox/js/glightbox.min.js"></script>
  <script src="<?php echo $us_url_root. 'usersc/plugins/simple_store/assets/template/'; ?>vendor/drift-zoom/Drift.min.js"></script>
  <script src="<?php echo $us_url_root. 'usersc/plugins/simple_store/assets/template/'; ?>vendor/purecounter/purecounter_vanilla.js"></script>

  <!-- Main JS File -->
  <script src="<?php echo $us_url_root. 'usersc/plugins/simple_store/assets/template/'; ?>js/main.js"></script>

</body>

</html>