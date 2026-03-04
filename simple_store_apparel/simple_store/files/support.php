<?php
require_once 'users/init.php';
ob_start();

// makes sure id is set, if not, redirect to index.php
if(isset($_GET["id"])){
    $support_id = $_GET["id"];
} else {
    header("Location: index.php");
    die();
}

// start a session and create a cart session if one not already made
session_start();
if(!isset($_SESSION['cart'])){
   $_SESSION['cart'] = [];
   } 
   
$contact_email = $db->query("SELECT * FROM simple_store_settings ")->first(); // get store contact info 
?>
<? require_once 'usersc/plugins/simple_store/assets/template/'.'views/header.php'; //Header ?> 
    
    <section id="order-confirmation" class="order-confirmation section">
      <div class="container">
          
        <?php
//***** SHIPPING PAGE
        if($support_id == "shipping"){
            echo '
                <!--  Section -->
                    <section id="faq" class="faq section">
                    <div class="custom-h3 text-center">Shipping information <br /><br /></div>
                      <div class="container text-center" data-aos="fade-up" data-aos-delay="100">
                
                        <div class="row gy-4 justify-content-between">
                          <div class="col-lg-12">
                
                            <div class="faq-list">
                              <div class="faq-item faq-active" data-aos="fade-up" data-aos-delay="100">
                                <h3>Where is my item coming from??</h3>
                                <div class="faq-content">
                                  <p>
                                    '.$settings->site_name .' is a small company located in Lakeland Florida, USA. Its where we embroider and screen print all of out products.
                                  </p>
                                </div>
                              </div><!-- End FAQ Item-->
                
                              <div class="faq-item faq-active" data-aos="fade-up" data-aos-delay="200">
                                <h3>How long until my order is shipped?</h3>
                                <div class="faq-content">
                                  <p>
                                    Usually takes us between 1-3 bussiness days to ship, keep an eye out on that email(Check your Spam just in case)! 
                                  </p>
                                </div>
                              </div><!-- End FAQ Item-->
                
                              <div class="faq-item faq-active" data-aos="fade-up" data-aos-delay="300">
                                <h3>Where can I track my order?</h3>
                                <div class="faq-content">
                                  <p>
                                    You can track your order by going to tracking page and inputing your order number and email! <br /> <br />
                                  </p>
                                  <p>
                                    <a href="trackorder.php" class="btn btn-primary"><i class="bi bi-truck me-2"></i> Track My Order</a>
                                  </p>
                                </div>
                              </div><!-- End FAQ Item-->
                
                              <div class="faq-item faq-active" data-aos="fade-up" data-aos-delay="400">
                                <h3>Where do you ship to?</h3>
                                <div class="faq-content">
                                  <p>
                                    We currently only ship to the continuos 48 states of the USA.
                                  </p>
                                </div>
                              </div><!-- End FAQ Item-->
                
                              <div class="faq-item faq-active" data-aos="fade-up" data-aos-delay="500">
                                <h3>Do you offer next day shipping?</h3>
                                <div class="faq-content">
                                  <p>
                                    Unfortunately, at this time we dont, but we are working on making it an option soon!
                                  </p>
                                </div>
                              </div><!-- End FAQ Item-->
                
                            </div>
                
                          </div>
                
                          <div class="col-lg-12" data-aos="fade-up" data-aos-delay="200">
                            <div class="faq-card">
                              <i class="bi bi-chat-dots-fill"></i>
                              <h3>Cant find answer to your question?</h3>
                              <p></p>
                              <a href="support.php?id=contact" class="btn btn-primary">Contact Us</a>
                            </div>
                          </div>
                          
                          <div class="col-lg-12" data-aos="fade-up" data-aos-delay="300">
                            <a href="index.php?id=shop_all" class="btn btn-primary"><i class="bi bi-bag me-2 me-2"></i> Continue Shopping</a>
                          </div>
                          
                        </div>
                
                      </div>
                        
                    </section>
                <!--  /Section -->
            ';
            
            
            
           
        }
        
//***** RETURNS/ EXCHANGE POLICY
        if($support_id == "returnpolicy"){
            echo '
                <!--  Section -->
                    <section id="faq" class="faq section">
                    <div class="custom-h3 text-center">Returns and Exchanges <br /><br /></div>
                      <div class="container text-center" data-aos="fade-up" data-aos-delay="100">
                
                        <div class="row gy-4 justify-content-between">
                          <div class="col-lg-12">
                
                            <div class="faq-list">
                              <div class="faq-item faq-active" data-aos="fade-up" data-aos-delay="100">
                                <h3>Do you offer refunds?</h3>
                                <div class="faq-content">
                                  <p>
                                    All of our items are custom made-to-order. Because of this, we DO NOT offer refunds once an item has been made. ALL SALES ARE FINAL.
                                  </p>
                                </div>
                              </div><!-- End FAQ Item-->
                
                              <div class="faq-item faq-active" data-aos="fade-up" data-aos-delay="200">
                                <h3>My item came defective :C</h3>
                                <div class="faq-content">
                                  <p>
                                    Please contact us with any issues you have with your order and we will try to make it right. The best way is to email us at 
                                    '.$contact_email->contact_email.'  Please include your order number, email associated with order, and pictures of the damages. Customers have 2 
                                    days after their order has beed delivered to inspect their package and contact us regarding any defects. After 2 days, we will no 
                                    longer be able to help resolve any issues.
                                  </p>
                                </div>
                              </div><!-- End FAQ Item-->
                
                              <div class="faq-item faq-active" data-aos="fade-up" data-aos-delay="300">
                                <h3>I ordered the wrong size!</h3>
                                <div class="faq-content">
                                  <p>
                                    We are not responsible for any incorrect information provided at checkout. All of our items are custom made-to-order and because of this, we DO NOT
                                    accept refunds or exchanges.ALL SALES ARE FINAL.
                                  </p>
                                </div>
                              </div><!-- End FAQ Item-->
                
                              <div class="faq-item faq-active" data-aos="fade-up" data-aos-delay="400">
                                <h3>My package got stolen :C</h3>
                                <div class="faq-content">
                                  <p>
                                    We are not responsible for lost or stolen packages. We are also not responsible to packages lost due to incorrect shipping addresses entered at checkout.
                                    If your item states it is been delivered, please make an inquiry to the carrier.
                                  </p>
                                </div>
                              </div><!-- End FAQ Item-->
                
                              <div class="faq-item faq-active" data-aos="fade-up" data-aos-delay="500">
                                <h3>How can I cancel my order?</h3>
                                <div class="faq-content">
                                  <p>
                                    For cancellations, please contact us within 24 hours of your order. Once the order has been made, we are no longer able to cancel the order. ALL SALES ARE FINAL.
                                  </p>
                                </div>
                              </div><!-- End FAQ Item-->
                              
                              <div class="faq-item faq-active" data-aos="fade-up" data-aos-delay="500">
                                <h3>Changes to policy</h3>
                                <div class="faq-content">
                                  <p>
                                    '.$settings->site_name .' reserves the right to modify or update our policies at any time without prior notice. We encourage you to review our policies periodically to stay informed about any revisions.
                                  </p>
                                </div>
                              </div><!-- End FAQ Item-->
                
                            </div>
                
                          </div>
                
                          <div class="col-lg-12" data-aos="fade-up" data-aos-delay="200">
                            <div class="faq-card">
                              <i class="bi bi-chat-dots-fill"></i>
                              <h3>Cant find answer to your question?</h3>
                              <p></p>
                              <a href="support.php?id=contact" class="btn btn-primary">Contact Us</a>
                            </div>
                          </div>
                          
                          <div class="col-lg-12" data-aos="fade-up" data-aos-delay="300">
                            <a href="index.php?id=shop_all" class="btn btn-primary"><i class="bi bi-bag me-2 me-2"></i> Continue Shopping</a>
                          </div>
                          
                        </div>
                        
                      </div>
                
                    </section>
                <!--  /Section -->
            ';
            
        }
        
//***** CONTACT PAGE
        if($support_id == "contact"){
            echo '
                <!--  Section -->
                    <section id="faq" class="faq section " style="min-height: 60vh;">
                    <div class="custom-h3 text-center" >Contact <br /><br /></div>
                      <div class="container text-center" data-aos="fade-up" data-aos-delay="100">
                
                        <div class="row gy-4 justify-content-between">
                         
                          <div class="col-lg-12" data-aos="fade-up" data-aos-delay="200">
                            <div class="faq-card">
                              <i class="bi bi-envelope-at"></i>
                              <h3>Whats the best form of contact?</h3>
                              <p></p>
                              '.$contact_email->contact_email.'
                            </div>
                          </div>
                          
                          <div class="col-lg-12" data-aos="fade-up" data-aos-delay="300">
                            <a href="index.php?id=shop_all" class="btn btn-primary"><i class="bi bi-bag me-2 me-2"></i> Continue Shopping</a>
                          </div>
                           
                        </div>
                         
                      </div>
                 
                    </section>
                <!--  /Section -->
            ';
            
            
        }
        
//***** Our Store
        if($support_id == "ourstory"){
            echo '
                <!--  Section -->
                    <section id="faq" class="faq section " style="min-height: 60vh;" >
                    <div class="custom-h3 text-center" >Our Story <br /><br /></div>
                      <div class="container text-center" data-aos="fade-up" data-aos-delay="100">
                
                        <div class="row gy-4 justify-content-between">
                         
                          <div class="col-lg-12" data-aos="fade-up" data-aos-delay="200">
                            <div class="faq-card">
                              <i class="bi bi-cup-hot"></i>
                              <h3></h3>
                              <p></p>
                              Just a one man army doing my best. :) 
                            </div>
                          </div>
                          
                          <div class="col-lg-12" data-aos="fade-up" data-aos-delay="300">
                            <a href="index.php?id=shop_all" class="btn btn-primary"><i class="bi bi-bag me-2 me-2"></i> Continue Shopping</a>
                          </div>
                           
                        </div>
                         
                      </div>
                 
                    </section>
                <!--  /Section -->
            ';
        }?>        
        
        
        
        
      </div>

    </section>
        
 <? require_once 'usersc/plugins/simple_store/assets/template/'.'views/footer.php'; //Footer ?> 