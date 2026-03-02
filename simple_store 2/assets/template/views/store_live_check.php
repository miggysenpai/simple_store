<?php
$store_live_check = $db->query("SELECT * FROM simple_store_settings ")->first(); // get store settings

// checks if the store is live, if not, redirect to index.php
if($store_live_check->live == "0"){
header("Location: index.php");
die();
}
?>