<?php
require_once("init.php");
//For security purposes, it is MANDATORY that this page be wrapped in the following
//if statement. This prevents remote execution of this code.
if (in_array($user->data()->id, $master_account)){
include "plugin_info.php";

//all actions should be performed here.
$pluginCheck = $db->query("SELECT * FROM us_plugins WHERE plugin = ?",array($plugin_name))->count();
if($pluginCheck > 0){
	err($plugin_name.' has already been installed!');
}else{
 $fields = array(
	 'plugin'=>$plugin_name,
	 'status'=>'installed',
 );
 $db->insert('us_plugins',$fields);
 if(!$db->error()) {
	 	err($plugin_name.' installed');
		logger($user->data()->id,"USPlugins",$plugin_name." installed");
 } else {
	 	err($plugin_name.' was not installed');
		logger($user->data()->id,"USPlugins","Failed to to install plugin, Error: ".$db->errorString());
 }
}

//do you want to inject your plugin in the middle of core UserSpice pages?
//visit https://userspice.com/plugin-hooks/ to get a better understanding of hooks
$hooks = [];


//Creates simple_store_brevo
$db->query("CREATE TABLE `simple_store_brevo` (
  `id` int NOT NULL,
  `brevo_key` varchar(500) NOT NULL,
  `sender_email` varchar(500) NOT NULL,
  `email_self` varchar(1000) NOT NULL,
  `brevo_image` varchar(1000) NOT NULL
)");

$db->query("INSERT INTO `simple_store_brevo` (`id`, `brevo_key`, `sender_email`, `email_self`, `brevo_image`) VALUES
(1, '', '', '', '' )");

$db->query("ALTER TABLE `simple_store_brevo`
  ADD PRIMARY KEY (`id`)");
  
$db->query("ALTER TABLE `simple_store_brevo`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2");

//Creates simple_store_expenses
$db->query("CREATE TABLE `simple_store_expenses` (
  `id` int NOT NULL,
  `name` varchar(500) NOT NULL,
  `cost` varchar(500) NOT NULL,
  `file` varchar(1000) NOT NULL,
  `include_total` varchar(2) NOT NULL
)");

$db->query("ALTER TABLE `simple_store_expenses`
  ADD PRIMARY KEY (`id`)");
  
$db->query("ALTER TABLE `simple_store_expenses`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2");


//Creates simple_store_catergories
$db->query("CREATE TABLE `simple_store_catergories` (
  `id` int NOT NULL,
  `name` varchar(1000) NOT NULL,
  `image` varchar(1000) NOT NULL,
  `is_subcatergory` int NOT NULL,
  `subcartergory_of` int NOT NULL
) ");

$db->query("ALTER TABLE `simple_store_catergories`
  ADD PRIMARY KEY (`id`)");
$db->query("ALTER TABLE `simple_store_catergories`
  MODIFY `id` int NOT NULL AUTO_INCREMENT");


//Creates simple_store_products
$db->query("CREATE TABLE `simple_store_products` (
  `id` int NOT NULL,
  `name` varchar(100) NOT NULL,
  `catergory` varchar(100) NOT NULL,
  `color` varchar(1000) NOT NULL,
  `price` varchar(1000) NOT NULL,
  `description` varchar(1000) NOT NULL,
  `image1` varchar(1000) NOT NULL,
  `image2` varchar(1000) NOT NULL,
  `image3` varchar(1000) NOT NULL,
  `image4` varchar(1000) NOT NULL,
  `status` int NOT NULL,
  `sold_out` varchar(2) NOT NULL,
  `purchase_count` varchar(1000) NOT NULL
)");
$db->query("ALTER TABLE `simple_store_products`
  ADD PRIMARY KEY (`id`)");
$db->query("ALTER TABLE `simple_store_products`
  MODIFY `id` int NOT NULL AUTO_INCREMENT");


//Creates simple_store_products_colors
$db->query("CREATE TABLE `simple_store_products_colors` (
  `id` int NOT NULL,
  `name` varchar(1000) NOT NULL,
  `hex` varchar(1000) NOT NULL
)");
$db->query("ALTER TABLE `simple_store_products_colors`
  ADD PRIMARY KEY (`id`)");
$db->query("ALTER TABLE `simple_store_products_colors`
  MODIFY `id` int NOT NULL AUTO_INCREMENT");

//Creates simple_store_products_images
$db->query("CREATE TABLE `simple_store_products_images` (
  `id` int NOT NULL,
  `product_id` varchar(1000) NOT NULL,
  `image` varchar(1000) NOT NULL,
  `is_primary` varchar(10) NOT NULL
)");
$db->query("ALTER TABLE `simple_store_products_images`
  ADD PRIMARY KEY (`id`)");
  $db->query("ALTER TABLE `simple_store_products_images`
  MODIFY `id` int NOT NULL AUTO_INCREMENT");

//Creates simple_store_products_variants
$db->query("CREATE TABLE `simple_store_products_variants` (
  `id` int NOT NULL,
  `product_id` varchar(1000) NOT NULL,
  `size` varchar(1000) NOT NULL,
  `prod_id` varchar(1000) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `price_id` varchar(1000) NOT NULL,
  `qoh` varchar(1000) NOT NULL,
  `qoh_unlimited` int(2) NOT NULL
)");
$db->query("ALTER TABLE `simple_store_products_variants`
  ADD PRIMARY KEY (`id`)");
$db->query("ALTER TABLE `simple_store_products_variants`
  MODIFY `id` int NOT NULL AUTO_INCREMENT");


//Creates simple_store_settings
$db->query("CREATE TABLE `simple_store_settings` (
  `id` int NOT NULL,
  `live` int NOT NULL,
  `contact_email` varchar(1000) NOT NULL,
  `facebook` varchar(100) NOT NULL,
  `instagram` varchar(100) NOT NULL,
  `tiktok` varchar(100) NOT NULL,
  `youtube` varchar(1000) NOT NULL
)");
$db->query("INSERT INTO `simple_store_settings` (`id`, `live`, `contact_email`, `facebook`, `instagram`, `tiktok`, `youtube`) VALUES
(1, 1, '', '', '', '', '')");
$db->query("ALTER TABLE `simple_store_settings`
  ADD PRIMARY KEY (`id`)");
$db->query("ALTER TABLE `simple_store_settings`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2");


//Creates simple_store_stripe_keys
$db->query("CREATE TABLE `simple_store_stripe_keys` (
  `id` int NOT NULL,
  `live_public` varchar(1000) NOT NULL,
  `live_secret` varchar(1000) NOT NULL,
  `sandbox_public` varchar(1000) NOT NULL,
  `sandbox_secret` varchar(1000) NOT NULL,
  `webhook` varchar(1000) NOT NULL,
  `is_live` int NOT NULL,
  `stripe_currency` varchar(3) NOT NULL,
  `stripe_coupons` int(3) NOT NULL
)");
$db->query("INSERT INTO `simple_store_stripe_keys` (`id`, `live_public`, `live_secret`, `sandbox_public`, `sandbox_secret`, `webhook`, `is_live`, `stripe_currency`, `stripe_coupons`) VALUES
(1, '', '', '', '', '', 0, 'usd' , 0);");
$db->query("ALTER TABLE `simple_store_stripe_keys`
  ADD PRIMARY KEY (`id`)");
$db->query("ALTER TABLE `simple_store_stripe_keys`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2");


//Creates simple_store_stripe_transactions
$db->query("CREATE TABLE `simple_store_stripe_transactions` (
  `id` int UNSIGNED NOT NULL,
  `session_id` varchar(1000) NOT NULL,
  `payment_intent_id` varchar(100) NOT NULL,
  `receipt_number` varchar(1000) NOT NULL,
  `amount_subtotal` varchar(1000) NOT NULL,
  `amount_total` varchar(1000) NOT NULL,
  `amount_shipping` varchar(1000) NOT NULL,
  `amount_tax` varchar(1000) NOT NULL,
  `created` varchar(1000) NOT NULL,
  `status` varchar(100) NOT NULL,
  `customer_email` varchar(1000) NOT NULL,
  `customer_name` varchar(1000) NOT NULL,
  `address_city` varchar(1000) NOT NULL,
  `address_country` varchar(1000) NOT NULL,
  `address_line1` varchar(1000) NOT NULL,
  `address_line2` varchar(1000) NOT NULL,
  `address_postal_code` varchar(100) NOT NULL,
  `address_state` varchar(100) NOT NULL,
  `payment_brand` varchar(10) NOT NULL,
  `payment_last4` varchar(10) NOT NULL,
  `payment_exp_month` varchar(10) NOT NULL,
  `payment_exp_year` varchar(10) NOT NULL,
  `shipping_carrier` varchar(1000) NOT NULL,
  `shipping_tracking` varchar(1000) NOT NULL
)");
$db->query("ALTER TABLE `simple_store_stripe_transactions`
  ADD PRIMARY KEY (`id`)");
$db->query("ALTER TABLE `simple_store_stripe_transactions`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT");

//Creates simple_store_stripe_transactions
$db->query("CREATE TABLE `simple_store_transactions_item` (
  `id` int NOT NULL,
  `customer_email` varchar(1000) NOT NULL,
  `receipt_number` varchar(1000) NOT NULL,
  `checkout_session_id` varchar(1000) NOT NULL,
  `product_id` varchar(1000) NOT NULL,
  `price_id` varchar(1000) NOT NULL,
  `qty` varchar(1000) NOT NULL,
  `purchased_date` varchar(1000) NOT NULL
)");
$db->query("ALTER TABLE `simple_store_transactions_item`
  ADD PRIMARY KEY (`id`)");
$db->query("ALTER TABLE `simple_store_transactions_item`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT");

//Creates simple_store_stripe_transactions_status
$db->query("CREATE TABLE `simple_store_transactions_status` (
  `id` int NOT NULL,
  `transaction_id` varchar(100) NOT NULL,
  `transaction_status` varchar(10) NOT NULL,
  `transaction_status_date` varchar(100) NOT NULL
)");
$db->query("ALTER TABLE `simple_store_transactions_status`
  ADD PRIMARY KEY (`id`)");
$db->query("ALTER TABLE `simple_store_transactions_status`
  MODIFY `id` int NOT NULL AUTO_INCREMENT");
  

//Digital Downloads stuff 
$db->query("CREATE TABLE `simple_store_products_downloads` (
  `id` int NOT NULL,
  `product_id` int NOT NULL,
  `download` varchar(1000) NOT NULL
)"); 
$db->query("ALTER TABLE `simple_store_products_downloads`
  ADD PRIMARY KEY (`id`)");
$db->query("ALTER TABLE `simple_store_products_downloads`
  MODIFY `id` int NOT NULL AUTO_INCREMENT");


$db->query("CREATE TABLE `simple_store_download_logs` (
  `id` int NOT NULL,
  `order_id` varchar(1000) NOT NULL,
  `file_id` varchar(1000) NOT NULL,
  `timestamp` varchar(1000) NOT NULL,
  `ip` varchar(1000) NOT NULL
)"); 
$db->query("ALTER TABLE `simple_store_download_logs`
  ADD PRIMARY KEY (`id`)");
$db->query("ALTER TABLE `simple_store_download_logs`
  MODIFY `id` int NOT NULL AUTO_INCREMENT");

// creates a color for errors. didnt feel like removing it everywhere. lol
$db->query("INSERT INTO `simple_store_products_colors` (`id`, `name`, `hex`) VALUES
(1, 'Digital', 'ffffff')");

          
//Move all the necesary files
rename($abs_us_root . $us_url_root . 'usersc/plugins/simple_store/files/cart.php' , $abs_us_root . $us_url_root ."cart.php");
rename($abs_us_root . $us_url_root . 'usersc/plugins/simple_store/files/checkout.php' , $abs_us_root . $us_url_root ."checkout.php");
rename($abs_us_root . $us_url_root . 'usersc/plugins/simple_store/files/collections.php' , $abs_us_root . $us_url_root ."collections.php");
rename($abs_us_root . $us_url_root . 'usersc/plugins/simple_store/files/pdf.php' , $abs_us_root . $us_url_root ."pdf.php");
rename($abs_us_root . $us_url_root . 'usersc/plugins/simple_store/files/product.php' , $abs_us_root . $us_url_root ."product.php");
rename($abs_us_root . $us_url_root . 'usersc/plugins/simple_store/files/store_admin.php' , $abs_us_root . $us_url_root ."store_admin.php");
rename($abs_us_root . $us_url_root . 'usersc/plugins/simple_store/files/search.php' , $abs_us_root . $us_url_root ."search.php");
rename($abs_us_root . $us_url_root . 'usersc/plugins/simple_store/files/success.php' , $abs_us_root . $us_url_root ."success.php");
rename($abs_us_root . $us_url_root . 'usersc/plugins/simple_store/files/support.php' , $abs_us_root . $us_url_root ."support.php");
rename($abs_us_root . $us_url_root . 'usersc/plugins/simple_store/files/trackorder.php' , $abs_us_root . $us_url_root ."trackorder.php");
rename($abs_us_root . $us_url_root . 'usersc/plugins/simple_store/files/webhook.php' , $abs_us_root . $us_url_root ."webhook.php");
    //check if `yourdomain.com/index.php` exist, if so, rename, then move new index.php
    if (file_exists($abs_us_root . $us_url_root . 'index.php')) {
        rename($abs_us_root . $us_url_root . 'index.php' , $abs_us_root . $us_url_root ."index_olddd.php");
        rename($abs_us_root . $us_url_root . 'usersc/plugins/simple_store/files/index.php' , $abs_us_root . $us_url_root ."index.php");
    } else {
        // if no `yourdomain.com/index.php` file exist, go ahead and move the file
        rename($abs_us_root . $us_url_root . 'usersc/plugins/simple_store/files/index.php' , $abs_us_root . $us_url_root ."index.php");
    }
    
    //check if `yourdomain.com/usersc/login.php` exist, if so, rename, then move new login.php
    if (file_exists($abs_us_root . $us_url_root . 'usersc/login.php')) {
        rename($abs_us_root . $us_url_root . 'usersc/login.php' , $abs_us_root . $us_url_root ."usersc/login_olddd.php");
        rename($abs_us_root . $us_url_root . 'usersc/plugins/simple_store/files/login.php' , $abs_us_root . $us_url_root ."usersc/login.php");
    } else {
        // if no `yourdomain.com/usersc/login.pgp` file exist, go ahead and move the file
        rename($abs_us_root . $us_url_root . 'usersc/plugins/simple_store/files/login.php' , $abs_us_root . $us_url_root ."usersc/login.php");
    }

//****User Files
rename($abs_us_root . $us_url_root . 'usersc/plugins/simple_store/files/account.php' , $abs_us_root . $us_url_root ."/usersc/account.php");
rename($abs_us_root . $us_url_root . 'usersc/plugins/simple_store/files/complete.php' , $abs_us_root . $us_url_root ."/usersc/complete.php");
rename($abs_us_root . $us_url_root . 'usersc/plugins/simple_store/files/forgot_password.php' , $abs_us_root . $us_url_root ."/usersc/forgot_password.php");
rename($abs_us_root . $us_url_root . 'usersc/plugins/simple_store/files/forgot_password_reset.php' , $abs_us_root . $us_url_root ."/usersc/forgot_password_reset.php");
rename($abs_us_root . $us_url_root . 'usersc/plugins/simple_store/files/join.php' , $abs_us_root . $us_url_root ."/usersc/join.php");
rename($abs_us_root . $us_url_root . 'usersc/plugins/simple_store/files/user_settings.php' , $abs_us_root . $us_url_root ."/usersc/user_settings.php");
rename($abs_us_root . $us_url_root . 'usersc/plugins/simple_store/files/verify.php' , $abs_us_root . $us_url_root ."/usersc/verify.php");
rename($abs_us_root . $us_url_root . 'usersc/plugins/simple_store/files/verify_resend.php' , $abs_us_root . $us_url_root ."/usersc/verify_resend.php");


//****Downloads

//create downloads folder if does not exist    
if (!file_exists($abs_us_root . $us_url_root . 'downloads')) {
    mkdir($abs_us_root . $us_url_root . 'downloads', 0755, true);
}    
//move downloads file
rename($abs_us_root . $us_url_root . 'usersc/plugins/simple_store/files/downloads.php' , $abs_us_root . $us_url_root ."downloads/index.php");

//move expenses file
rename($abs_us_root . $us_url_root . 'usersc/plugins/simple_store/files/expenses.php' , $abs_us_root . $us_url_root ."downloads/expenses.php");

//Update htacess file
rename($abs_us_root . $us_url_root . 'usersc/plugins/simple_store/downloads/htaccess' , $abs_us_root . $us_url_root . 'usersc/plugins/simple_store/downloads/.htaccess');



//The format is $hooks['userspicepage.php']['position'] = path to filename to include
//Note you can include the same filename on multiple pages if that makes sense;
//postion options are post,body,form,bottom
//See documentation for more information
// $hooks['login.php']['body'] = 'hooks/loginbody.php';

registerHooks($hooks,$plugin_name);

} //do not perform actions outside of this statement
