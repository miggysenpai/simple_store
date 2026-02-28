<?php if (!in_array($user->data()->id, $master_account)) {
  Redirect::to($us_url_root . 'users/admin.php');
} //only allow master accounts to manage plugins! 
?>

<?php
include "plugin_info.php";
pluginActive($plugin_name);
if (!empty($_POST)) {
  if (!Token::check(Input::get('csrf'))) {
    include($abs_us_root . $us_url_root . 'usersc/scripts/token_error.php');
  }
}
?>
<div class="content mt-3">
<div class="row">
    <div class="container">
        <div class="container">
            
        <header class="pb-3 mb-4 border-bottom">
          <a href="<?= $us_url_root ?>users/admin.php?view=plugins"  class="d-flex align-items-center text-body-emphasis text-decoration-none" >
            <span class="fs-4 btn rounded border ">Return to the Plugin Manager</span>
          </a>
        </header>
      
        <div class="p-5 mb-4 bg-body-tertiary rounded-3">
          <div class="container-fluid py-5">
            <h1 class="display-5 fw-bold">Simple Store</h1>
            <p class="col-md-8 fs-5">
              This is a simple store designed for my made-to-order apparel business. I've made the store plugin as functional as possible "out of the box", 
              but there are definitely will be some changes that you may want to make to fit your own brand. Some features may be added in the future,
              but I'm not too sure when or what that will be. Please make sure that your website follows all your countries rules and regulations (Copyright, ADA, etc). 
              Down below you will find the instructions to install the plugin properly and good luck.
            </p>
            <br />
            <p class="col-md-8 fs-5">
              Store Requirements:
              <ul>
                  <li>Stripe Account</li>
                  <li>Brevo Account</li>
              </ul>
            </p>
            
          </div>
        </div>
        
        <div class="row align-items-md-stretch">
          <div class="col-md-12">
            <div class="h-100 p-5 bg-body-tertiary border rounded-3">
              <h2>Store Admin</h2>
              <p>
               <a href="<?= $us_url_root ?>store_admin.php" class="btn rounded border">Store Admin Page</a>
              </p>
            </div>
          </div>
          
          <div class="col-md-12"><br />
              <div class="list-group">
                  
                <div class="list-group-item list-group-item-action d-flex gap-3 py-3" aria-current="true" >
                  <div class="d-flex gap-2 w-100 justify-content-between">
                    <div>
                      <h6 class="mb-0">Install Instructions</h6>
                      <p class="mb-0 opacity-75">
                      </p>
                    </div>
                  </div>
                </div>
                
                <div class="list-group-item list-group-item-action d-flex gap-3 py-3" aria-current="true" >
                  <div class="d-flex gap-2 w-100 justify-content-between">
                    <div>
                      <h6 class="mb-0">Add Stripe API Keys</h6>
                      <p class="mb-0 opacity-75">
                        Go to your stripe account and go to Developers-> API Keys. You may need to create a new key. 
                        Adding the stripe api keys allows the website to create and edit products under your stripe account.
                        <br />
                        <a href="https://dashboard.stripe.com/test/apikeys">Click here to get your Stripe API Keys</a>
                        <br />
                        <a href="<?= $us_url_root ?>store_admin.php?id=keys">Click here add your Stripe API keys</a>
                      </p>
                    </div>
                  </div>
                </div>
                
                <div class="list-group-item list-group-item-action d-flex gap-3 py-3" aria-current="true" >
                  <div class="d-flex gap-2 w-100 justify-content-between">
                    <div>
                      <h6 class="mb-0">Add Stripe Webhook Key</h6>
                      <p class="mb-0 opacity-75">
                        Create a webhook with a "checkout.session.completed" event. This is a webhook endpoint. The endpoint URL would 
                        be "&lt;your-website>/webhook.php" .
                        While a webhook is not 100% required, it is still recommended. The webhook is a fall back code just incase the checkout session 
                        completed successfully, but there was an error getting to the success page. (ex. slow wifi/ customer closed page)
                        <br />
                        <a href="https://dashboard.stripe.com/webhooks">Click here to go to Stripe Webhooks</a>
                        <br />
                        <a href="<?= $us_url_root ?>store_admin.php?id=keys">Click here add your webhook key</a>
                      </p>
                    </div>
                  </div>
                </div>
                
                <div class="list-group-item list-group-item-action d-flex gap-3 py-3" aria-current="true" >
                  <div class="d-flex gap-2 w-100 justify-content-between">
                    <div>
                      <h6 class="mb-0">Add Brevo API Key</h6>
                      <p class="mb-0 opacity-75">
                        I decided to use Brevo for my email service as they include 300 free daily emails and are very affordable in the case of growth.
                        It is also important add an email logo. Be aware of your website max upload size. Max upload size can be changed in the ini.php. 
                        <br />
                        <a href="https://app.brevo.com/settings/keys/api">Click here to go to the Brevo API Page</a>
                        <br />
                        <a href="<?= $us_url_root ?>store_admin.php?id=brevo">Click here add your Brevo keys</a>
                      </p>
                    </div>
                  </div>
                </div>
                
                <div class="list-group-item list-group-item-action d-flex gap-3 py-3" aria-current="true" >
                  <div class="d-flex gap-2 w-100 justify-content-between">
                    <div>
                      <h6 class="mb-0">Create a Brevo Sender</h6>
                      <p class="mb-0 opacity-75">
                        Create a Brevo sender. See video at the end for full tutorial.
                        <br />
                        <a href="https://app.brevo.com/senders/add">Click here to go to the Brevo add sender page</a>
                      </p>
                    </div>
                  </div>
                </div>
                
                <div class="list-group-item list-group-item-action d-flex gap-3 py-3" aria-current="true" >
                  <div class="d-flex gap-2 w-100 justify-content-between">
                    <div>
                      <h6 class="mb-0">Add and authenticate your domain with Brevo</h6>
                      <p class="mb-0 opacity-75">
                          Authenticate your domain please. See video at the end for full tutorial.
                        <br />
                        <a href="https://app.brevo.com/senders/domain/list">Click here to go to the Brevo add domain page</a>
                      </p>
                    </div>
                  </div>
                </div>
                
                <hr />
                
                <div class="list-group-item list-group-item-action d-flex gap-3 py-3" aria-current="true" >
                  <div class="d-flex gap-2 w-100 justify-content-between">
                    <div>
                      <h6 class="mb-0">Add Catergories and colors</h6>
                      <p class="mb-0 opacity-75">
                        All the tough stuff is setup! You can now go ahead and add your Catergories and Colors. This is 
                        <strong>required</strong> before adding products
                        <br />
                        <a href="<?= $us_url_root ?>store_admin.php?id=colors">Click here to add Colors</a>
                        <br />
                        <a href="<?= $us_url_root ?>store_admin.php?id=catergories">Click here to Add Catergories</a>
                      </p>
                    </div>
                  </div>
                </div>
                
                <div class="list-group-item list-group-item-action d-flex gap-3 py-3" aria-current="true" >
                  <div class="d-flex gap-2 w-100 justify-content-between">
                    <div>
                      <h6 class="mb-0">Add your Products</h6>
                      <p class="mb-0 opacity-75">
                        You can now add your Products! woot woot. 
                        <br />
                        <a href="<?= $us_url_root ?>store_admin.php?id=products">Click here to add Products</a>
                        <br />
                      </p>
                    </div>
                  </div>
                </div>
                
                <hr />
             
                <div class="list-group-item list-group-item-action d-flex gap-3 py-3" aria-current="true" >
                  <div class="d-flex gap-2 w-100 justify-content-between">
                    <div>
                      <div class="embed-responsive embed-responsive-16by9">
                        <iframe class="embed-responsive-item" src="https://www.youtube.com/embed/hXRgg-4acP8?si=2xGhTIEWr7_09F5l" allowfullscreen></iframe>
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

  <!-- Do not close the content mt-3 div in this file -->