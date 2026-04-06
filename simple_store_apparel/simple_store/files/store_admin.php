<?php
require_once 'users/init.php';
ob_start();


// checks if is admin, if not, redirect to home
if(isAdmin()){ }else{
    header("Location: index.php");
    die();
    }
  
  
//Brevo likes to be loaded in first i guess    
require_once('usersc/plugins/simple_store/assets/brevo_email/vendor/autoload.php');
use Brevo\Brevo;
use Brevo\TransactionalEmails\Requests\SendTransacEmailRequest;
use Brevo\TransactionalEmails\Types\SendTransacEmailRequestSender;
use Brevo\TransactionalEmails\Types\SendTransacEmailRequestToItem;    



//gets page id
$page_id = "";   
if(isset($_GET["id"])){
    $page_id = $_GET["id"];   
}    
 
//start session and create cart session if one not available  
session_start();
if(!isset($_SESSION['cart'])){
   //create an array if one isnt available
   $_SESSION['cart'] = [];
   } 
?>

<? require_once 'usersc/plugins/simple_store/assets/template/'.'views/header.php'; //Header ?> 
  
<body>
    <div class="container my-5">
            <div class="page-title ">
              <div class="container d-lg-flex justify-content-between align-items-center">
                <h1 class="breadcrumbs">
                    <ol>
                    <li><a href="index.php" aria-label="Go to Home Page">shop</a></li>
                    <li><a href="store_admin.php" aria-label="Go to Store Page">Store admin</a></li>
                  </ol>
                </h1>
              </div>
            </div>
    </div>

        <div class="container my-5">
        <?php


//*****HOME PAGE, NO ID SET
        if($page_id == ""){
            $stripe_key_check = $db->query("SELECT * FROM simple_store_stripe_keys")->first(); // get keys
            $brevo_check = $db->query("SELECT * FROM simple_store_brevo")->first(); // get keys
            // adds warnings to please add keys
            if($stripe_key_check->live_public == ""){echo '<div class="alert alert-danger" role="alert">  Please add Stripe Key - Live Public </div>';}
            if($stripe_key_check->live_secret == ""){echo '<div class="alert alert-danger" role="alert">  Please add Stripe Key - Live Secret </div>';}
            if($stripe_key_check->sandbox_public == ""){echo '<div class="alert alert-danger" role="alert">  Please add Stripe Key - Sandbox Public </div>';}
            if($stripe_key_check->sandbox_secret == ""){echo '<div class="alert alert-danger" role="alert">  Please add Stripe Key - Sandbox Secret </div>';}
            if($stripe_key_check->webhook == ""){echo '<div class="alert alert-danger" role="alert">  Please add Stripe Key - Webhook </div>';}
            if($brevo_check->brevo_key == ""){echo '<div class="alert alert-danger" role="alert">  Please add Brevo Key  </div>';}
            if($brevo_check->sender_email == ""){echo '<div class="alert alert-danger" role="alert">  Please add Brevo Sender Email </div>';}
            echo "
              <div class='next-steps text-center p-4'  >
                <h3>Store Admin Page</h3>
                
                 <!-- Payment Options Grid -->
                 <section id='paymnt-methods' class='paymnt-methods section'>
                    <div class='payment-options'>
                      <div class='row g-4'>
                        
                        <div class='col-md-6 col-lg-4 '>
                        <a href='?id=settings' aria-label='Go to Settings Page'>
                          <div class='payment-card '>
                            <div class='card-content'>
                              <div class='icon-box'>
                                <i class='bi bi-gear'></i>
                              </div>
                              <h4>Store Settings</h4>
                              <p>You can edit most store settings here.</p>
                            </div>
                          </div>
                        </a>
                        </div>
                        
                        <div class='col-md-6 col-lg-4 '>
                        <a href='?id=keys' aria-label='Go to Keys Page'>
                          <div class='payment-card'>
                            <div class='card-content'>
                              <div class='icon-box'>
                                <i class='bi bi-key'></i>
                              </div>
                              <h4>Stripe Settings</h4>
                              <p>Edit your stripe keys and edit whether you are in sandbox or live.</p>
                            </div>
                          </div>
                        </a>
                        </div>
                        
                        <div class='col-md-6 col-lg-4 '>
                        <a href='?id=brevo' aria-label='Go to Brevo Page'>
                          <div class='payment-card '>
                            <div class='card-content'>
                              <div class='icon-box'>
                                <i class='bi bi-envelope'></i>
                              </div>
                              <h4>Brevo Settings</h4>
                              <p>You can edit your brevo key here.</p>
                            </div>
                          </div>
                        </a>
                        </div>
                        
                        <div class='col-md-6 col-lg-4 '>
                        <a href='?id=colors' aria-label='Go to Colors Page'>
                          <div class='payment-card '>
                            <div class='card-content'>
                              <div class='icon-box'>
                                <i class='bi bi-palette'></i>
                              </div>
                              <h4>Colors</h4>
                              <p>You can view/edit/add Colors here.</p>
                            </div>
                          </div>
                        </a>
                        </div>
                        
                        <div class='col-md-6 col-lg-4 '>
                        <a href='?id=catergories' aria-label='Go to Catergories Page'>
                          <div class='payment-card '>
                            <div class='card-content'>
                              <div class='icon-box'>
                                <i class='bi bi-diagram-3'></i>
                              </div>
                              <h4>Catergories</h4>
                              <p>You can view/edit/add Catergories here.</p>
                            </div>
                          </div>
                        </a>
                        </div>
                        
                        <div class='col-md-6 col-lg-4'>
                        <a href='?id=products' aria-label='Go to Products Page'>
                          <div class='payment-card '>
                            <div class='card-content'>
                              <div class='icon-box'>
                                <i class='bi bi-bag-plus'></i>
                              </div>
                              <h4>Products</h4>
                              <p>You can view/edit/add products and upload the pictures here.</p>
                            </div>
                          </div>
                        </a>
                        </div>
                        
                        <div class='col-md-6 col-lg-4 '>
                        <a href='?id=orders' aria-label='Go to Orders Page'>
                          <div class='payment-card '>
                            <div class='card-content'>
                              <div class='icon-box'>
                                <i class='bi bi-cash-stack'></i>
                              </div>
                              <h4>orders</h4>
                              <p>You can view and edit the recent orders here.</p>
                            </div>
                          </div>
                        </a>
                        </div>

                        <div class='col-md-6 col-lg-4 '>
                        <a href='?id=expenses' aria-label='Go to Expenses Page'>
                          <div class='payment-card '>
                            <div class='card-content'>
                              <div class='icon-box'>
                                <i class='bi bi-wallet2'></i>
                              </div>
                              <h4>Expenses</h4>
                              <p>You can add your operating costs here.</p>
                            </div>
                          </div>
                        </a>
                        </div>
                        
                        <div class='col-md-6 col-lg-4 '>
                        <a href='?id=stats' aria-label='Go to Stats Page'>
                          <div class='payment-card '>
                            <div class='card-content'>
                              <div class='icon-box'>
                                <i class='bi bi-clipboard-data'></i>
                              </div>
                              <h4>Fun stats</h4>
                              <p>You can view fun stats on any sales.</p>
                            </div>
                          </div>
                        </a>
                        </div>
           
                        
                      </div>
                    </div>
                    </div>
                </section>
            ";
        }
        
//*****EXPENSES PAGE
        if($page_id == "expenses"){  ?>
        <div class='order-confirmation-1'>
              <div class='next-steps  p-4'  >
                <h3 class='text-center'>Operation Cost</h3>
                
                 <section id='cart' class='cart section'>
                  <div class='container' >
                  <div class='row g-4'>
                  <div class='col-lg-12'>
                      <a class='btn btn-secondary float-start' href='store_admin.php' aria-label='Go to Store Admin Page'>Back to Store Admin</a> 
                      <a class='btn rounded border float-end' href='?id=add_expense' aria-label='Go to Add Expense Page'>Add Expense</a></div>
                  <div class='col-lg-12' >
                  
                  <br />
                  
                    <div class='cart-items'>
                      <div class='cart-header d-none d-lg-block'>
                        <div class='row align-items-center gy-4'>
                          <div class='col-lg-6'>
                            <h5>Name</h5>
                          </div>
                          
                          <div class='col-lg-2 '>
                            <h5>Cost</h5>
                          </div>
                          <div class='col-lg-2 '>
                            <h5>File</h5>
                          </div>
                          <div class='col-lg-2 '>
                            <h5>Edit</h5>
                          </div>
                        </div>
                      </div>
                      
                    <?php  
                    //gets all products  
                    $expenses = $db->query("SELECT * FROM simple_store_expenses")->results(); 
                     $expense_total_count = 0;
                    // if there are no products, show this
                    if (count($expenses) === 0) {
                         echo "You need atleast one expense for this to work...
                               <br /><br />
                            <a href='?id=add_expense' class='btn border rounded' aria-label='Go to Add Expense'>Add an Expense</a>
                         ";
                    } else {
                    
                    // if there are products, do a simple loop to display all products
                   
                    foreach($expenses as $p) {?> 
                              <div class="cart-item" >
                                <div class="row align-items-center gy-4">
                                  <div class="col-lg-6 col-12 mb-3 mb-lg-0">
                                      <span class='text-secondary'>Name  : </span><span><?=$p->name?></span>
                                  </div>
                                  <div class="col-12 col-lg-2 ">
                                      <span class='text-secondary'>Cost : </span><span>$ <?=$p->cost?></span>
                                  </div>
                                  <div class="col-12 col-lg-2 ">
                                      <?php 
                                        if($p->file == ""){
                                            echo "<span class='alert alert-danger'> No File </span>";
                                        } else {
                                            echo "<a class='btn btn-secondary' href='downloads/expenses.php?id=".$p->id."'>Download</a>";
                                        }
                                      ?>
                                  </div>
                                  <div class="col-12 col-lg-2">
                                      <a href="?id=edit_expense&expense_id=<?=$p->id?>" aria-label='Go to Edit Expense Page' ><button type='button' class='btn text-bg-secondary'>Edit Expense</button></a>
                                  </div>
                                </div>
                              </div><!-- End Cart Item -->
                
                    <?php $expense_total_count = $expense_total_count + $p->cost; }
                        
                    } 
                    echo '
                            <div class="cart-item" >
                                <div class="row align-items-center gy-4">
                                  <div class="col-lg-10 col-12 mb-3 mb-lg-0">
                                  </div>
                                  
                                  <div class="col-12 col-lg-2 ">
                                      <span> Total : $ '.number_format((float)$expense_total_count, 2, '.', '') .'
                                  </div>
                                </div>
                              </div><!-- End Cart Item -->
                    ';
                     echo "  </div></div></div></div></div></div> "; 
            
            
        }   
        
//*****ADD EXPENSE PAGE
        if($page_id == "add_expense"){  
            $add_confirm = "";   
             
             //checks if there is a form post
             if(isset($_GET["post"])){
                $add_confirm = $_GET["post"];   
             }
             
            //if post form is add
             if($add_confirm == "add"){
                // prepare fields for database
                $fields = [
                    'name' => $_POST['expense_name'],
                    'cost' => $_POST['expense_cost'],
                    'include_total' => $_POST['include_total'],
                ];
                $result = $db->insert('simple_store_expenses', $fields); // add to database
                $last_id = $db->lastId(); // gets lastId for new product redirect
                header("Location: store_admin.php?id=edit_expense&expense_id=".$last_id); // redirect to edit expense page
                die();
             }
            
            echo "
            <div class='order-confirmation-1'>
              <div class='next-steps  p-4'  >
                <h3 class='text-center'>Add Expense</h3>
                
                 <section id='cart' class='cart section'>
                  <div class='container' >
                  <a class='btn btn-secondary' href='store_admin.php' aria-label='Go to Store Admin Page'>Back to Store Admin</a>
                  <a class='btn btn-secondary' href='store_admin.php?id=expenses' aria-label='Go to Expenses Page'>Back to Expenses </a>
                  <br/><br/>
                  <div class='row g-4'>
                  <div class='col-lg-12' >
                    <div class='cart-items'>
                      <div class='cart-header d-none d-lg-block'>
                        <div class='row align-items-center gy-4'>
                          <div class='col-lg-12'>
                            <h5>Expense</h5>
                          </div>
                        </div>
                      </div>
                      
                      <div class='cart-item' >
                        <div class='row'>
                          <div class='col-lg-12 col-12'>
                            <div class='product-info d-flex '>
                              <div class='product-details'>
                              <form method='POST' action='?id=add_expense&post=add'>
                                <span class='text-secondary'>Expense Name  : </span><input type='text' class='form-control' id='expense_name' name='expense_name' required></span>
                                <br />
                                <span class='text-secondary'>Expense Cost : </span><input type='text' class='form-control' id='expense_cost' name='expense_cost'  required></span>
                                <br />
                                <span class='text-secondary'>Include in total count : </span>
                                    <select class='form-select' id='include_total' name='include_total' required>
                                        <option value='1'> Yes </option>
                                        <option value='0'> No </option>
                                    </select>
                                <br /><br /><br />
                                <button type='submit' name='prod_details_form' value='prod_details_form' class='btn btn-success border rounded'>Add Expense</button>
                                <br /><br />
                              </form>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div></div></div></div></div></div></div>";
        }
        
//***** EDIT EXPENSE PAGE 
        if($page_id == "edit_expense"){
            
            //checks for product id, if one not available, redirect to home
            if(!isset($_GET["expense_id"])){
                header("Location: store_admin.php?id=expenses");
                die();
            }
            
            //checks if form post
            if($_POST){ 
                $post_type = $_GET["post"]; // sets post type
                
                //post general info
                if($post_type == "general"){
                    //sets all fields
                    $fields = [
                    'name' => $_POST['expense_name'],
                    'cost' => $_POST['expense_cost'],
                    'include_total' => $_POST['include_total']
                    ];
                $result = $db->update('simple_store_expenses', $_GET["expense_id"] , $fields); // updates database
                header("Location: store_admin.php?id=edit_expense&expense_id=".$_GET["expense_id"]); //redirects to product page
                die();
                }
                

                // uploads Zip File
                if($post_type == "upload_zip"){

                    //checks if there is a file to upload
                    if($_FILES['zip_upload']['size'] === 0) {
                        header("Location: store_admin.php?id=edit_expense&expense_id=".$_GET["expense_id"]); //redirects to product page
                        die();
                    }
                    
                    //Set directory path
                    $uploaddir = 'usersc/plugins/simple_store/downloads/';
                    $imageFileType = strtolower(pathinfo($_FILES['zip_upload']['name'],PATHINFO_EXTENSION));
                    
                    
                    $id = $_GET["expense_id"]; //get lastID
                    
                    //Upload downloadable file 
                    $uploadfile = $uploaddir . "expense_".$id.".".$imageFileType;
                    $uploadfile_name = "expense_".$id.".".$imageFileType;;
                    echo '<pre>';
                    if (move_uploaded_file($_FILES['zip_upload']['tmp_name'], $uploadfile)) {
                        echo "File is valid, and was successfully uploaded.\n";
                    } else {
                        echo "Possible file upload attack!\n or... check what your upload limit is";
                    }
                    print "</pre>";
                    
                    
                    //prepping fields for database
                    $fields = [
                    'file' => $uploadfile_name,
                    ];
                    $result = $db->update('simple_store_expenses', $id, $fields); //updates database 
                     header("Location: store_admin.php?id=edit_expense&expense_id=".$_GET["expense_id"]); // redirects to product page once completed
                     die();
                }

            }
            
            //this is for links. 
             if(isset($_GET["post"])){
                 $post_type = $_GET["post"]; // sets post type
                 
                // deletes image
                if($post_type == "delete_download"){
                    $expense_info = $db->query("SELECT * FROM simple_store_expenses WHERE id = ?", [$_GET["expense_id"]])->first(); // used to get image source
                    unlink($abs_us_root . $us_url_root .$expense_info->file); //deletes file
                    $fields = [
                    'file' => "",
                    ];
                    $result = $db->update('simple_store_expenses', $_GET["expense_id"], $fields); //updates database 
                    header("Location: store_admin.php?id=edit_expense&expense_id=".$_GET["expense_id"]); //Redirects to edit product page
                    die();
                }
                
            }
            
            
            $expense_info = $db->query("SELECT * FROM simple_store_expenses WHERE id = ?",[$_GET["expense_id"]])->first(); // gets product info
            
            
            echo "
            <div class='order-confirmation-1'>
              <div class='next-steps  p-4'  >
                <h3 class='text-center'>Edit Expense</h3>
                
                 <section id='cart' class='cart section'>
                  <div class='container' >
                  <a class='btn btn-secondary' href='store_admin.php' aria-label='Go to Store admin Page'>Back to Store Admin</a> 
                  <a class='btn btn-secondary' href='store_admin.php?id=expenses' aria-label='Go to Expenses Page'>Back to Expenses </a> 
                  <br/><br/>
                  <div class='row g-4'>
                  <div class='col-lg-12' >
                    <div class='cart-items'>
                      <div class='cart-header d-none d-lg-block'>
                        <div class='row align-items-center gy-4'>
                          <div class='col-lg-12'>
                            <h5>Expense</h5>
                          </div>
                        </div>
                      </div>";
                      
                    $expense_downloads = $db->query("SELECT * FROM simple_store_expenses WHERE id = ? ", [$_GET["expense_id"]])->first(); // get images  
                   
                    //adds alert to add an receipt, just a recommendation
                    if($expense_downloads->file == ""){
                                echo '<div class="alert alert-light" role="alert">
                                        You can upload a receipt for organization purposes
                                      </div>';
                            } 

                      echo "
                      <div class='cart-item' >
                        <div class='row'>
                          <div class='col-lg-12 col-12'>
                            <div class='product-info d-flex '>
                              <div class='product-details'>
                              <form method='POST' action='?id=edit_expense&expense_id=".$_GET["expense_id"]."&post=general'>
                                <span class='text-secondary'>Expense Name  : </span>
                                <input type='text' class='form-control' id='expense_name' name='expense_name' value='".$expense_info->name."' placeholder='Expense Name' required>
                                <br />
                                <span class='text-secondary'>Expense Cost  : </span>
                                <input type='text' class='form-control' id='expense_cost' name='expense_cost' value='".$expense_info->cost."' placeholder='Expense Cost' required>
                                <br />
                                <span class='text-secondary'>Include in total : </span>
                                <select class='form-control' name='include_total' id='include_total'>
                                    <option value='1' "; if($expense_info->include_total == "1"){echo "selected";} echo">Yes</option>
                                    <option value='0' "; if($expense_info->include_total == "0"){echo "selected";} echo">No</option>
                                </select>
                                <br />
                                
                                <button type='submit' name='prod_details_form' value='prod_details_form' class='btn btn-success border rounded'>Update Info</button>
                                <br /><br />
                                <hr /><br />
                              </form>
                              </div>
                            </div>";
                            
                            
                            
                            echo  "
                            <hr /><br />
                            
                            </div>
                            </div>
                            <br />

                            <div class='container border rounded'>
                            <div class='container'>
                            <br />";
                            
                            
                            //if there are no download, ask to upload atleast one download
                            if($expense_downloads->file == ""){
                                echo '<div class="alert alert-light" role="alert">
                                        Please Upload atleast a file.
                                      </div>';
                            } else {
                                echo "
                                    <div class='product-info row'>
                                    <span class='text-secondary'>Download : </span>
                                    
                                    <div>
                                        <a href='/downloads/expenses.php?id=".$expense_downloads->id."' alt='Product' class='btn btn-secondary' loading='lazy'>Download</a>
                                        <br/><br />
                                        <a href='?id=edit_expense&expense_id=".$_GET["expense_id"]."&post=delete_download' class='btn btn-danger' aria-label='Delete Prodduct Download link'>Delete File</a>
                                        <br /><br />
                                    </div>
                                     <br/><br />
                                    </div>   
                                        
                                    ";
                            }
                            
                            
                            echo "
                                <div class='alert alert-secondary' role='alert'>
                                    Upload Your Downloadable File. Only one file allowed.<br />
                                    Be aware of your max upload size. You can change that in you ini.php - Current max upload is ".ini_get("upload_max_filesize")."
                                </div>
                                <form method='POST' action='?id=edit_expense&expense_id=".$_GET["expense_id"]."&post=upload_zip' enctype='multipart/form-data'>
                                    <input type='file' id='zip_upload' name='zip_upload'>
                                    <br /><br />
                                    <button type='submit' name='zip_form' value='zip_form' class='btn btn-success border rounded'>Upload File</button>
                                    <br /><br />
                                </form>
                                <hr /><br />
                            
                            </div>
                            </div>
                            <br />
                            
                            <div class='product-info row'>
                                <div class='product-details'>
                                <a class='btn btn-danger' href='?id=delete_expense&expense_id=".$_GET["expense_id"]."' aria-label='Delete Expense'>Delete Expense</a>
                                </div>   
                            </div>
                            <br /><hr /><br />
                            
                          </div>
                        </div>
                      </div></div></div></div></div></div>   
            ";
        }
        
//***** DELETE EXPENSE
        if($page_id == "delete_expense"){
            
            // gets varient id
            if(!isset($_GET["expense_id"])){
                header("Location: store_admin.php?id=expenses");
                die();
            }
            
            // double checks if delete confirm
             $delete_confirm = "";   
             if(isset($_GET["confirm"])){
                 $delete_confirm = $_GET["confirm"];   
             }
             
             // finally deletes in database 
             if($delete_confirm == "true"){
                 $result = $db->delete('simple_store_expenses', ["id", "=", $_GET["expense_id"]]); // delete from database
                  header("Location: store_admin.php?id=expenses"); // redirect to expenses page
                  die();
             }
            echo "
                <div class='order-confirmation-1'>
                  <div class='next-steps  p-4'  >
                    <h3 class='text-center'>Delete Expense</h3>
                    
                    <section id='cart' class='cart section'>
                    <div class='container' >
                      <a class='btn btn-secondary' href='store_admin.php' aria-label='Go to Store Admin Page'>Back to Store Admin</a>
                      <a class='btn btn-secondary' href='store_admin.php?id=edit_expense&expense_id=".$_GET["expense_id"]."' aria-label='Go to Product Page'>Back to Expense Page</a> <br/><br/>
                      <div class='row g-4'>
                      <div class='col-lg-12' >
                        <div class='cart-items'>
                          <div class='cart-header d-none d-lg-block'>
                            <div class='row align-items-center gy-4'>
                              <div class='col-lg-12'>
                                <h5>**This will DELETE from database.
                                    <br /><br />
                                    
                                </h5>
                              </div>
                            </div>
                          </div>
                          
                          <div class='cart-item' >
                            <div class='row'>
                              <div class='col-lg-12 col-12'>
                                <div class='product-info d-flex '>
                                  <div class='product-details'>
                                     <p class='text-center'>
                                        Are you sure you want to delete?
                                        <br /><br />
                                        <a class='btn btn-danger' href='?id=delete_expense&expense_id=".$_GET["expense_id"]."&confirm=true' aria-label='Delete Expense'>Delete Expense </a>
                                     </p>
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
                </div>
            
            ";
        }
        
//*****STATS PAGE
        if($page_id == "stats"){ 
            
            $transactions_results = $db->query("SELECT * FROM simple_store_stripe_transactions")->results(); // get transactions
            $transaction_total = "0";
            foreach($transactions_results as $t_c) {
                $transaction_total = $transaction_total + $t_c->amount_total;
            }
            
            $expenses_results = $db->query("SELECT * FROM simple_store_expenses")->results(); // get transactions
            $expenses_total = "0";
            foreach($expenses_results as $e_c) {
                $expenses_total = $expenses_total + $e_c->cost;
            }
            
            $expenses_results = $db->query("SELECT * FROM simple_store_expenses")->results(); // get transactions
            $expenses_total = "0";
            foreach($expenses_results as $e_c) {
                $expenses_total = $expenses_total + $e_c->cost;
            }
            
            $purchase_results = $db->query("SELECT * FROM simple_store_products")->results(); // get transactions
            $purchase_total = "0";
            foreach($purchase_results as $p_c) {
                $purchase_total = $purchase_total + $p_c->purchase_count;
            }
            
        
        ?>
                    <h3 class='text-center'>Cool Numbers</h3>
                    <div class='container' >
                      <a class='btn btn-secondary' href='store_admin.php' aria-label='Go to Store Admin Page'>Back to Store Admin</a>
                      <br />
                      <hr />
                    </div>
                
                    
            <!-- Stats Section -->
            <section id="stats" class="stats section ">
        
              <div class="container" data-aos="fade-up" data-aos-delay="100">
        
                <div class="row gy-4">
        
                  <div class="col-lg-3 col-md-6">
                    <div class="stats-item">
                      <i class="bi bi-emoji-smile"></i>
                      <span data-purecounter-start="0" data-purecounter-end="<?=$transaction_total/100?>" data-purecounter-duration="1" data-purecounter-decimals="2" data-purecounter-currency="$" class="purecounter"></span>
                      <p><span>Gross Revenue</span></p>
                    </div>
                  </div><!-- End Stats Item -->
        
                  <div class="col-lg-3 col-md-6">
                    <div class="stats-item">
                      <i class="bi bi-wallet2"></i>
                      <span data-purecounter-start="0" data-purecounter-end="<?=$expenses_total?>" data-purecounter-duration="1"  data-purecounter-decimals="2" data-purecounter-currency="$" class="purecounter"></span>
                      <p><span>Total Expenses</span></p>
                    </div>
                  </div><!-- End Stats Item -->
                  
                  <div class="col-lg-3 col-md-6">
                    <div class="stats-item">
                      <i class="bi bi-basket"></i>
                      <span data-purecounter-start="0" data-purecounter-end="<?=count($transactions_results)?>" data-purecounter-duration="1" class="purecounter"></span>
                      <p><span>Total Orders</span></p>
                    </div>
                  </div><!-- End Stats Item -->
        
                  <div class="col-lg-3 col-md-6">
                    <div class="stats-item">
                      <i class="bi bi-journal-richtext"></i>
                      <span data-purecounter-start="0" data-purecounter-end="<?=$purchase_total?>" data-purecounter-duration="1" class="purecounter"></span>
                      <p><span>Total Items Sold</span></p>
                    </div>
                  </div><!-- End Stats Item -->
                  

                </div>
        
              </div>
        
            </section><!-- /Stats Section -->
            
            
            <!-- Order items -->
            <div id="order-confirmation" class="order-confirmation section"><div class="main-content">
              <div class="details-card" data-aos="fade-up" >
                <div class=" card-header" data-toggle="collapse">
                    <h3>
                      <i class="bi bi-bag-check"></i>
                      Most Popular Items (TOP 3)
                    </h3>
                    <i class="bi bi-chevron-down toggle-icon"></i>
                  </div>
                  <div class="card-body">
                    <?php 
                        $products_results = $db->query("SELECT * FROM simple_store_products ORDER BY `simple_store_products`.`purchase_count` DESC LIMIT 3")->results(); // get products
                        if(count($products_results) == 0){
                            echo "No items sold yet...";
                        }
                        foreach($products_results as $p) {
                            $total_c = $p->price * $p->purchase_count;
                            $product_image = $db->query("SELECT * FROM simple_store_products_images WHERE product_id = ? AND is_primary = ?", [$p->id, "1"])->results();
                            //if the count is 0 show nothing, else show primary image 
                            if(count($product_image) == 0){$image_src = ""; } else { $image_src = $product_image[0]->image;}
                        ?> 
                            <div class="item">
                              <div class="item-image">
                                <img src="<?=$image_src?>" alt="Product" loading="lazy">
                              </div>
                              <div class="item-details">
                                <h4><?=$p->name?></h4>
                                <div class="item-meta">
                                  <span>Color: <?=$p->color?></span>
                                </div>
                                <div class="item-price">
                                  <span class="quantity">Total sold <?=$p->purchase_count?> @</span>
                                  <span class="quantity">$<?=$p->price?> = </span>
                                  <span class="price">$<?=$total_c?></span>
                                </div>
                              </div>
                            </div>
                        <?php } ?> 
    
                  </div>
                </div>
                </div></div>
                
            
        
        <?php }            
        
//*****KEYS PAGE
        if($page_id == "keys"){
            //checks if there is a form post
            if($_POST){
                //sets all the post variables
                $post_live_public = $_POST["live_public"];
                $post_live_secret = $_POST["live_secret"];
                $post_sandbox_public = $_POST["sandbox_public"];
                $post_sandbox_secret = $_POST["sandbox_secret"];
                $post_webhook = $_POST["webhook"];
                $post_currency = $_POST["currency"];
                $post_coupons = $_POST["coupons"];
                
                //fields for db
                $fields = [
                    'live_public' => $post_live_public,
                    'live_secret' => $post_live_secret,
                    'sandbox_public' => $post_sandbox_public,
                    'sandbox_secret' => $post_sandbox_secret,
                    'webhook' => $post_webhook,
                    'stripe_currency' => $post_currency,
                    'stripe_coupons' => $post_coupons,
                ];
                //update db
                $result = $db->update('simple_store_stripe_keys', 1, $fields);
                header("Location: store_admin.php?id=keys");
                die();
                
            }
            
            //gets keys from db
            $stripe_key_check = $db->query("SELECT * FROM simple_store_stripe_keys")->first(); // get keys
            
            //changes sandbox/live modes
            if(isset($_GET["mode"])){
                if($_GET["mode"] == "change"){
                    if($stripe_key_check->is_live == "1"){
                        $fields = [
                            'is_live' => "0",
                        ];
                        $result = $db->update('simple_store_stripe_keys', 1, $fields);
                    }
                    
                    if($stripe_key_check->is_live == "0"){
                        $fields = [
                            'is_live' => "1",
                        ];
                        $result = $db->update('simple_store_stripe_keys', 1, $fields);
                    }
                    header("Location: store_admin.php?id=keys");
                    die();
                }
            }
            
            //sets variables for live or not.
            if($stripe_key_check->is_live == "1"){
                $stripe_live_button = "success";
                $stripe_live_button_op = "danger";
                $stripe_live_sandbox = "Live Keys <i class='bi bi-check-circle-fill text-success'></i>";
                $stripe_button_title = "Live";
                $stripe_button_title_op = "Use Sandbox Keys";
            } else {
                $stripe_live_button = "danger";
                $stripe_live_button_op = "success";
                $stripe_live_sandbox = "Sandbox Keys <i class='bi bi-x-circle-fill text-danger'></i>";
                $stripe_button_title = "Sandbox";
                $stripe_button_title_op = "Use Live Keys";
            }
            
            echo "
            <div class='order-confirmation-1'>
              <div class='next-steps  p-4'  >
                <h3 class='text-center'>Keys Settings</h3>
                
                 <section id='cart' class='cart section'>
                  <div class='container' >
                  <a class='btn btn-secondary' href='store_admin.php' aria-label='Go to Store Admin Page'>Back to Store Admin</a> <br/><br/>
                  <div class='row g-4'>
                  <div class='col-lg-12' >
                    <div class='cart-items'>
                      <div class='cart-header d-none d-lg-block'>
                        <div class='row align-items-center gy-4'>
                          <div class='col-lg-12'>
                            <h5>Keys</h5>
                          </div>
                        </div>
                      </div>   
                      
            <div class='next-steps p-4'  >
                <h3 class=' text-center '>Stripe keys</h3>
                
                 <!-- Payment Options Grid -->
                 <section id='paymnt-methods' class=' section'>
                    <div class='payment-options'>
                      <div class='row g-4 '>
                      
                        <form method='POST' action='?id=keys' class='row '>
                         <input type='text' class='form-control' id='form_post' name='form_post' value='form_post'required hidden>
                        <div class='col-md-12 col-lg-12'>
                          <div class='payment-card'>
                            <div class='card-content'>
                              <label for='live_public' class='form-label text-secondary'>Live Publishable Key</label>
                                <input type='text' class='form-control' id='live_public' name='live_public' value='".$stripe_key_check->live_public."'required>
                                <br />
                            </div>
                          </div>
                        </div>
            
                        <div class='col-md-12 col-lg-12'>
                          <div class='payment-card'>
                            <div class='card-content'>
                              <label for='live_secret' class='form-label text-secondary'>Live Secret Key</label>
                                <input type='text' class='form-control' id='live_secret' name='live_secret' value='".$stripe_key_check->live_secret."' required>
                                <br />
                            </div>
                          </div>
                        </div>
                        
                        <div class='col-md-12 col-lg-12'>
                          <div class='payment-card'>
                            <div class='card-content'>
                              <label for='sandbox_public' class='form-label text-secondary'>Sandbox Publishable Key</label>
                                <input type='text' class='form-control' id='sandbox_public' name='sandbox_public' value='".$stripe_key_check->sandbox_public."' required>
                                <br />
                            </div>
                          </div>
                        </div>
                        
                        <div class='col-md-12 col-lg-12'>
                          <div class='payment-card'>
                            <div class='card-content'>
                              <label for='sandbox_secret' class='form-label text-secondary'>Sandbox secret Key</label>
                                <input type='text' class='form-control' id='sandbox_secret' name='sandbox_secret' value='".$stripe_key_check->sandbox_secret."' required>
                                <br />
                            </div>
                          </div>
                        </div>
                        
                        <div class='col-md-12 col-lg-12'>
                          <div class='payment-card'>
                            <div class='card-content'>
                              <label for='webhook' class='form-label text-secondary'>Webhook Key</label>
                                <input type='text' class='form-control' id='webhook' name='webhook' value='".$stripe_key_check->webhook."' required>
                                <br />
                            </div>
                          </div>
                        </div>
                        
                        <div class='col-md-12 col-lg-12'>
                          <div class='payment-card'>
                            <div class='card-content'>
                              <label for='currency' class='form-label text-secondary'>Stripe currency (Default : usd)</label>
                                <input type='text' class='form-control' id='currency' name='currency' value='".$stripe_key_check->stripe_currency."' required>
                                <br />
                            </div>
                          </div>
                        </div>
                        
                        <div class='col-md-12 col-lg-12'>
                          <div class='payment-card'>
                            <div class='card-content'>
                              <label for='coupons' class='form-label text-secondary'>Allow Stripe Coupons</label>
                                <select class='form-select' id='coupons' name='coupons' >
                                    <option value='1' "; if($stripe_key_check->stripe_coupons == 1){echo "selected";} echo">Yes</option>
                                    <option value='0'"; if($stripe_key_check->stripe_coupons == 0){echo "selected";} echo" >No</option>
                                </select>
                                <br />
                            </div>
                          </div>
                        </div>
                        
                        <div class='col-md-12 col-lg-12 text-center'>
                          <div class='payment-card'>
                            <div class='card-content'>
                              <br /><button class='btn border col-lg-6'  type='submit'>Update keys</button><br /><br />
                            </div>
                          </div>
                        </div>
                        
                        </form>
                        
                        <hr />
                        
                        <div class='container'>
                        <div class='cart-item' >
                        <div class='row align-items-center gy-4'>
                          <div class='col-lg-10 col-12 mb-3 mb-lg-0'>
                            <div class='product-info d-flex '>
                              <div class='product-details'>
                                <span class='text-secondary'> &nbsp; &nbsp;Your store is currently using : </span><span> ".$stripe_live_sandbox."</span>
                                <br />
                              </div>
                            </div>
                          </div>
                           <div class='col-12 col-lg-2'>
                              <a href='?id=keys&mode=change' aria-label='Submit Link><button type='button' class='btn btn-".$stripe_live_button_op." form-control' >".$stripe_button_title_op."</button></a>
                          </div>
                        </div>
                      </div>
                      </div>  
                        
                        
                        
                        
                        
                      </div>
                    </div>
                    </div>
                    </div></div></div></div></div></div>
                </section>
            
            ";
        }
        
        
//*****BREVO PAGE
        if($page_id == "brevo"){
            //checks if there is a form post
            if($_POST){
                $post_type = $_GET["post"]; // sets post type
                
                // uploads image
                if($post_type == "brevo_settings"){
                    //fields for db
                    $fields = [
                        'brevo_key' => $_POST["brevo_key"],
                        'sender_email' => $_POST["sender_email"],
                        'email_self' => $_POST["email_self"],
                    ];
                    //update db
                    $result = $db->update('simple_store_brevo', 1, $fields);
                    header("Location: store_admin.php?id=brevo");
                    die();
                }    
                // uploads image
                if($post_type == "upload_image"){
                    
                    //Set directory path
                    $uploaddir = 'usersc/plugins/simple_store/assets/template/img/';
                    $imageFileType = strtolower(pathinfo($_FILES['image_upload']['name'],PATHINFO_EXTENSION));
                    
                    
                    //Upload image 
                    $uploadfile = $uploaddir . "brevo_image.".$imageFileType;
                    echo '<pre>';
                    if (move_uploaded_file($_FILES['image_upload']['tmp_name'], $uploadfile)) {
                        echo "File is valid, and was successfully uploaded.\n";
                    } else {
                        echo "Possible file upload attack!\n or... check what your upload limit is";
                    }
                    print "</pre>";
                    
                    
                    //prepping fields for database
                    $fields = [
                    'brevo_image' => $uploadfile
                    ];
                    $result = $db->update('simple_store_brevo', 1, $fields); //updates database 
                     header("Location: store_admin.php?id=brevo"); // redirects to product page once completed
                     die();
                }
                
            }
            
            //gets keys from db
            $brevo_check = $db->query("SELECT * FROM simple_store_brevo")->first(); // get keys
            
    
            
            echo "
            <div class='order-confirmation-1'>
              <div class='next-steps  p-4'  >
                <h3 class='text-center'>Brevo Settings</h3>
                
                 <section id='cart' class='cart section'>
                  <div class='container' >
                  <a class='btn btn-secondary' href='store_admin.php' aria-label='Go to Store Admin Page'>Back to Store Admin</a> <br/><br/>
                  <div class='row g-4'>
                  <div class='col-lg-12' >
                    <div class='cart-items'>
                      <div class='cart-header d-none d-lg-block'>
                        <div class='row align-items-center gy-4'>
                          <div class='col-lg-12'>
                            <h5>Keys</h5>
                          </div>
                        </div>
                      </div>   
                      
            <div class='next-steps p-4'  >
                <h3 class=' text-center '>Brevo </h3>
                
                 <!-- Payment Options Grid -->
                 <section id='paymnt-methods' class=' section'>
                    <div class='payment-options'>
                      <div class='row g-4 '>
                      
                        <form method='POST' action='?id=brevo&post=brevo_settings' class='row '>
                         <input type='text' class='form-control' id='form_post' name='form_post' value='form_post'required hidden>
                        <div class='col-md-12 col-lg-12'>
                          <div class='payment-card'>
                            <div class='card-content'>
                              <label for='brevo_key' class='form-label text-secondary'>Brevo Key</label>
                                <input type='text' class='form-control' id='brevo_key' name='brevo_key' value='".$brevo_check->brevo_key."'>
                                <br />
                            </div>
                          </div>
                        </div>
            
                        <div class='col-md-12 col-lg-12'>
                          <div class='payment-card'>
                            <div class='card-content'>
                              <label for='sender_email' class='form-label text-secondary'>Sender Email <small>(May have to verify email with Brevo)</small></label>
                                <input type='text' class='form-control' id='sender_email' name='sender_email' value='".$brevo_check->sender_email."' >
                                <br />
                            </div>
                          </div>
                        </div>
                        
                        <div class='col-md-12 col-lg-12'>
                          <div class='payment-card'>
                            <div class='card-content'>
                             <label for='email_self' class='form-label text-secondary'>Your Email<small>(After order is place, you will be emailed too.)</small></label>
                                <input type='text' class='form-control' id='email_self' name='email_self' value='".$brevo_check->email_self."' >
                                <br />
                            </div>
                          </div>
                        </div>
                        
                        <div class='col-md-12 col-lg-12 text-center'>
                          <div class='payment-card'>
                            <div class='card-content'>
                              <br /><button class='btn border col-lg-6'  type='submit'>Update</button><br /><br />
                            </div>
                          </div>
                        </div>
                        
                        </form>
                        
                      </div>
                      <hr /><br />
                      ";
                      if($brevo_check->brevo_image == ""){
                            // do nothing
                        } else {
                           echo " 
                                <div class='product-info row'>
                                    <span class='text-secondary'>Email Logo Image: </span>
                                    <div class='product-image' >
                                        <img src='".$brevo_check->brevo_image."' width='100px' height='100px' alt='Product' class='img-fluid' loading='lazy'>
                                    </div>
                                </div> <br /><br />" ;
                        }
                      echo "
                       
                            <div class='alert alert-secondary' role='alert'>
                                Be aware of your max upload size. You can change that in you ini.php - Current max upload is ".ini_get("upload_max_filesize")."
                            </div>
                            <form method='POST' action='?id=brevo&post=upload_image' enctype='multipart/form-data'>
                                <input type='file' id='image_upload' name='image_upload'>
                                <br /><br />
                                <button type='submit' name='image_form' value='image_form' class='btn btn-success border rounded'>Upload</button>
                                <br /><br />
                            </form>
                            <hr /><br />
                            
                            
                    </div>
                    </div>
                    </div></div></div></div></div></div>
                </section>
            
            ";
        }
        
//***** PRODUCTS PAGE        
        if($page_id == "products"){
            $search_var = 0;
            echo "
            <div class='order-confirmation-1'>
              <div class='next-steps  p-4'  >
                <h3 class='text-center'>Products</h3>
                
                 <section id='cart' class='cart section'>
                  <div class='container' >
                  <div class='row g-4'>
                  <div class='col-lg-12'><a class='btn btn-secondary float-start' href='store_admin.php' aria-label='Go to Store Admin Page'>Back to Store Admin</a>  <a class='btn rounded border float-end' href='?id=add_product' aria-label='Go to Add Product Page'>Add Product</a></div>
                  <div class='col-lg-12' >";
                  
                  echo '
                  <!--/Search Widget -->
                  <div class="search-results-header">
                        <div class="col-lg-12" >
                          <form action="store_admin.php?id=products&search=search" method="post" class="search-form">
                            <div class="input-group">
                              <input type="text" class="form-control" placeholder="Search..." name="search" >
                              <button class="btn search-btn" type="submit">
                                <i class="bi bi-search"></i>
                              </button>
                              
                            </div>
                          </form>
                        </div>
                      </div>
                  <br />
                  ';
                  
                  
                  
                  echo "
                    <div class='cart-items'>
                      <div class='cart-header d-none d-lg-block'>
                        <div class='row align-items-center gy-4'>
                          <div class='col-lg-8'>
                            <h5>Product</h5>
                          </div>
                          
                          <div class='col-lg-2 '>
                            <h5>status</h5>
                          </div>
                          <div class='col-lg-2 '>
                            <h5>Edit</h5>
                          </div>
                        </div>
                      </div>";
                      
                    //gets all products  
                    $products = $db->query("SELECT * FROM simple_store_products")->results(); 
                    // if there are no products, show this
                    if (count($products) === 0) {
                                     echo "You need atleast one product for this to work...
                                           <br /><br />
                                           <a href='?id=add_product' class='btn border rounded' aria-label='Go to Add Page'>Add a Product</a>
                                     ";
                                } else {
                    
                    if(!empty($_POST)){
                        $search = Input::get('search');
                        if(isset($search)) {
                         $search_check = $db->query("SELECT * FROM simple_store_products WHERE (name LIKE '%$search%' OR id LIKE '%$search%' OR catergory LIKE '%$search%' OR description LIKE '%$search%')")->results();; // search check
                         $search_var = 1;
                         if(count($search_check) == 0 ) {
                             $search_var = 2;
                         }
                    }
                    }
                                    
                                    
                                    
                    if($search_var == 0){  
                    // if there are products, do a simple loop to display all products
                    foreach($products as $p) {?> 
                              <div class="cart-item" >
                                <div class="row align-items-center gy-4">
                                  <div class="col-lg-8 col-12 mb-3 mb-lg-0">
                                    <div class="product-info d-flex ">
                                      <div class="product-image">
                                        <?php
                                        //checks for primary picture for product
                                        $product_image = $db->query("SELECT * FROM simple_store_products_images WHERE product_id = ? AND is_primary = ?", [$p->id, "1"])->results();
                                        $catergory = $db->query("SELECT * FROM simple_store_catergories WHERE id = ? ", [$p->catergory, ])->first();
                                        
                                        //if the count is 0 show nothing, else show primary image 
                                        if(count($product_image) == 0){$image_src = ""; } else { $image_src = $product_image[0]->image;}
                                        ?>
                                        <img src="<?=$image_src?>" alt="Product" class="img-fluid" loading="lazy">
                                      </div>
                                      <div class="product-details">
                                        <span class='text-secondary'>Product Name  : </span><span><?=$p->name?></span>
                                        <br />
                                        <span class='text-secondary'>Catergory : </span><span><?=$catergory->name?></span>
                                        <br />
                                        
                                      </div>
                                     
                                    </div>
                                  </div>
                                  <div class="col-12 col-lg-2 ">
                                      <?php 
                                      //gets status of product and displays correct button
                                      if($p->status == "0"){ echo '<span class="btn text-bg-danger">Unavailable</span>'; }
                                      if($p->status == "1"){ echo '<span class="btn text-bg-success">Available</span>'; }
                                      ?>
                                  </div>
                                  <div class="col-12 col-lg-2">
                                      <a href="?id=edit_product&product_id=<?=$p->id?>" aria-label='Go to Edit Product Page' ><button type='button' class='btn text-bg-secondary'>Edit Product</button></a>
                                  </div>
                                </div>
                              </div><!-- End Cart Item -->
                
                    <?php }
                        
                    } // end $search = 0
                    
                    if($search_var == 1){  
                        foreach($search_check as $p) {?> 
                              <div class="cart-item" >
                                <div class="row align-items-center gy-4">
                                  <div class="col-lg-8 col-12 mb-3 mb-lg-0">
                                    <div class="product-info d-flex ">
                                      <div class="product-image">
                                        <?php
                                        //checks for primary picture for product
                                        $product_image = $db->query("SELECT * FROM simple_store_products_images WHERE product_id = ? AND is_primary = ?", [$p->id, "1"])->results();
                                        $catergory = $db->query("SELECT * FROM simple_store_catergories WHERE id = ? ", [$p->catergory, ])->first();
                                        //if the count is 0 show nothing, else show primary image 
                                        if(count($product_image) == 0){$image_src = ""; } else { $image_src = $product_image[0]->image;}
                                        ?>
                                        <img src="<?=$image_src?>" alt="Product" class="img-fluid" loading="lazy">
                                      </div>
                                      <div class="product-details">
                                        <span class='text-secondary'>Product Name  : </span><span><?=$p->name?></span>
                                        <br />
                                        <span class='text-secondary'>Catergory : </span><span><?=$catergory->name?></span>
                                        <br />
                                        
                                      </div>
                                       
                                    </div>
                                  </div>
                                  <div class="col-12 col-lg-2 ">
                                      <?php 
                                      //gets status of product and displays correct button
                                      if($p->status == "0"){ echo '<span class="btn text-bg-danger">Unavailable</span>'; }
                                      if($p->status == "1"){ echo '<span class="btn text-bg-success">Available</span>'; }
                                      ?>
                                  </div>
                                  <div class="col-12 col-lg-2">
                                      <a href="?id=edit_product&product_id=<?=$p->id?>" aria-label='Go to Edit Product Page' ><button type='button' class='btn text-bg-secondary'>Edit Product</button></a>
                                  </div>
                                </div>
                              </div><!-- End Cart Item -->
                
                    <?php }    
                    } // end $search = 1
                    
                    if($search_var == 2){ 
                        echo '<div class="cart-item" >
                        <div class="row align-items-center gy-4">
                          <div class="col-lg-12 col-12 mb-3 mb-lg-0">
                            <div class="product-info d-flex ">
                              <div class="product-details">
                                <span class="text-secondary"> &nbsp; &nbsp; Nothing found in search ... please try again  </span><span>&nbsp;</span>
                                <br />
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>';
                        
                    }
                      
                    } echo "  </div></div></div></div></div></div> "; }
                    
                 
//***** EDIT PRODUCT PAGE 
        if($page_id == "edit_product"){
            
            //checks for product id, if one not available, redirect to home
            if(!isset($_GET["product_id"])){
                header("Location: store_admin.php?id=products");
                die();
            }
            
            //checks if form post
            if($_POST){ 
                $post_type = $_GET["post"]; // sets post type
                
                //post general info
                if($post_type == "general"){
                    //sets all fields
                    $fields = [
                    'name' => $_POST['product_name'],
                    'catergory' => $_POST['product_catergory'],
                    'price' => $_POST['product_price'],
                    'color' => $_POST['product_color'],
                    'description' => $_POST['product_description'],
                    'status' => $_POST['product_status'],
                    'sold_out' => $_POST['product_sold_out'],
                ];
                $result = $db->update('simple_store_products', $_GET["product_id"] , $fields); // updates database
                header("Location: store_admin.php?id=edit_product&product_id=".$_GET["product_id"]); //redirects to product page
                die();
                }
                
                
                // uploads image
                if($post_type == "upload_image"){

                    //checks if there is a file to upload
                    if($_FILES['image_upload']['size'] === 0) {
                        header("Location: store_admin.php?id=edit_product&product_id=".$_GET["product_id"]); //redirects to product page
                        die();
                    }
                    
                    //Set directory path
                    $uploaddir = 'usersc/plugins/simple_store/assets/template/img/';
                    $imageFileType = strtolower(pathinfo($_FILES['image_upload']['name'],PATHINFO_EXTENSION));
                    
                    //insert blank in simple_store_products_images
                    $fields = [
                        'product_id' => $_GET["product_id"],
                        'is_primary' => "0"
                        ];
                    $result = $db->insert("simple_store_products_images", $fields);
                    $id = $db->lastId(); //get lastID
                    
                    //Upload image 
                    $uploadfile = $uploaddir . "prod_".$_GET["product_id"] ."_image_".$id.".".$imageFileType;
                    echo '<pre>';
                    if (move_uploaded_file($_FILES['image_upload']['tmp_name'], $uploadfile)) {
                        echo "File is valid, and was successfully uploaded.\n";
                    } else {
                        echo "Possible file upload attack!\n or... check what your upload limit is";
                    }
                    print "</pre>";
                    
                    
                    //Check if theres a primary image, if not, set image just uploaded to primary
                    $products_is_primary_query = $db->query("SELECT * FROM simple_store_products_images WHERE product_id = ? AND is_primary = ?",[$_GET["product_id"], "1"])->results(); 
                    if(count($products_is_primary_query) == 0){  $is_primary = "1";  } else {  $is_primary = "0";  }
                    
                    
                    //prepping fields for database
                    $fields = [
                    'image' => $uploadfile,
                    'is_primary' => $is_primary
                    ];
                    $result = $db->update('simple_store_products_images', $id, $fields); //updates database 
                     header("Location: store_admin.php?id=edit_product&product_id=".$_GET["product_id"]); // redirects to product page once completed
                     die();
                }

            }
            
            //this is for links. 
             if(isset($_GET["post"])){
                 $post_type = $_GET["post"]; // sets post type
                 
                // deletes image
                if($post_type == "delete_img"){
                    $img_info = $db->query("SELECT * FROM simple_store_products_images WHERE id = ?", [$_GET["img_id"]])->first(); // used to get image source
                    unlink($abs_us_root . $us_url_root .$img_info->image); //deletes image
                    $result = $db->delete('simple_store_products_images',["id", "=",  $_GET["img_id"]]); //deletes from database
                    header("Location: store_admin.php?id=edit_product&product_id=".$_GET["product_id"]); //Redirects to edit product page
                    die();
                }
                
                // makes image primary image
                if($post_type == "make_primary"){
                    $img_info = $db->query("SELECT * FROM simple_store_products_images WHERE product_id = ? AND is_primary = ?", [$_GET["product_id"], "1"])->first(); // gets currect `is_primary` image info
                    $fields = ["is_primary" => "0"];
                    $result = $db->update('simple_store_products_images', $img_info->id, $fields); // updates current `is_primary` to `0`
                    
                    $fields2 = ["is_primary" => "1"];
                    $result2 = $db->update('simple_store_products_images', $_GET["img_id"], $fields2); // updates new `is_primary` to `1`
                    
                    header("Location: store_admin.php?id=edit_product&product_id=".$_GET["product_id"]); //Redirects to edit product page
                    die();
                }       
            }
            
                
            
            $product_info = $db->query("SELECT * FROM simple_store_products WHERE id = ?",[$_GET["product_id"]])->first(); // gets product info
            $products_variant = $db->query("SELECT * FROM simple_store_products_variants WHERE product_id = ?",[$_GET["product_id"]])->results(); //gets product variants
            
            //sets product status variable
            if($product_info->status == 1){ $product_status_1 = "selected"; $product_status_0 = ""; }
            if($product_info->status == 0){ $product_status_1 = ""; $product_status_0 = "selected"; }
            if($product_info->sold_out == 1){ $product_sold_out_1 = "selected"; $product_sold_out_0 = ""; }
            if($product_info->sold_out == 0){ $product_sold_out_1 = ""; $product_sold_out_0 = "selected"; }
            
            echo "
            <div class='order-confirmation-1'>
              <div class='next-steps  p-4'  >
                <h3 class='text-center'>Edit Product</h3>
                
                 <section id='cart' class='cart section'>
                  <div class='container' >
                  <a class='btn btn-secondary' href='store_admin.php' aria-label='Go to Store admin Page'>Back to Store Admin</a> <br/><br/>
                  <div class='row g-4'>
                  <div class='col-lg-12' >
                    <div class='cart-items'>
                      <div class='cart-header d-none d-lg-block'>
                        <div class='row align-items-center gy-4'>
                          <div class='col-lg-12'>
                            <h5>Product</h5>
                          </div>
                        </div>
                      </div>";
                      
                    $products_images = $db->query("SELECT * FROM simple_store_products_images WHERE product_id = ? ORDER BY is_primary DESC", [$_GET["product_id"]])->results(); // get images  
                    //adds alert to add an image, atlease one is required so it looks correct
                    if(count($products_images) === 0){
                                echo '<div class="alert alert-danger" role="alert">
                                        Please upload atleast one image
                                      </div>';
                            }   
                    //adds alert to add a variant, atlease one is required for stripe checkout        
                    if (count($products_variant) === 0) {
                                     echo '<div class="alert alert-danger" role="alert">
                                            Please add atleast one varient (product size. Ex Small, Medium, Large)
                                          </div>';
                                     
                                } 
                      echo "
                      <div class='cart-item' >
                        <div class='row'>
                          <div class='col-lg-12 col-12'>
                            <div class='product-info d-flex '>
                              <div class='product-details'>
                              <form method='POST' action='?id=edit_product&product_id=".$_GET["product_id"]."&post=general'>
                                <span class='text-secondary'>Product Name  : </span><input type='text' class='form-control' id='product_name' name='product_name' value='".$product_info->name."' placeholder='Product Name' required></span>
                                <br />
                                <span class='text-secondary'>Catergory : </span>
                                <select class='form-control' name='product_catergory' id='product_catergory'>";
                                $products_catergories = $db->query("SELECT * FROM simple_store_catergories")->results(); // get catergories 
                                // loops/shows all catergories
                                foreach($products_catergories as $p_catergory){
                                    echo "<option value='".$p_catergory->id."'";
                                    if($p_catergory->id == $product_info->catergory){echo " selected";}
                                    echo ">";
                                    
                                    if($p_catergory->is_subcatergory == 1){
                                        $subcartergory_of = $db->query("SELECT * FROM simple_store_catergories WHERE id = ?", [$p_catergory->subcartergory_of])->first(); 
                                        echo $subcartergory_of->name." | ";
                                    }
                                    echo $p_catergory->name."</option>";
                                }
                                echo "
                                </select>
                                <br />
                                ";?>
                                <span class='text-secondary'>Price : </span><span class='text-small'>If price change, delete all varients and re-add to update with stripe</span></span><input type='text' class='form-control' id='product_price' name='product_price'  value='<?=$product_info->price?>' oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*?)\..*/g, '$1');"  placeholder='Product Price' required></span>
                                <br />
                                <span  class='text-secondary'>Color : </span>
                                      <select class='form-control' name='product_color' id='product_color'>
                                        <?php
                                        $products_colors = $db->query("SELECT * FROM simple_store_products_colors")->results(); // get colors 
                                        //loops/shows all colors. currently only one color is available, but added for future me.
                                         foreach($products_colors as $p_c){
                                             echo "<option value='".$p_c->name."'";
                                             if($p_c->name == $product_info->color){echo "selected";}
                                             echo ">".$p_c->name."</option>";
                                         }
                                echo "
                                    </select>
                                <br />
                                
                                <span class='text-secondary' >Description : </span><textarea  rows='5' class='form-control' id='product_description' name='product_description' value=''  placeholder='Product Description. Use &lt;li&gt; &lt;/li&gt;'  >".$product_info->description."</textarea></span>
                                <br />
                                <span  class='text-secondary'>Your product is : </span><span class='text-small'>Show/Hide from customer</span>
                                      <select class='form-control' name='product_status' id='product_status'>
                                        <option value='1' ".$product_status_1." >Available</option>
                                        <option value='0' ".$product_status_0.">Not Available</option>
                                      </select>
                                <br />
                                <span  class='text-secondary'>Mark as sold out : </span><span class='text-small'>Customer can still see item, but unable to purchase</span>
                                <select class='form-control' name='product_sold_out' id='product_sold_out'>
                                        <option value='1' ".$product_sold_out_1." >Sold Out</option>
                                        <option value='0' ".$product_sold_out_0.">Not Sold Out</option>
                                </select>
                                <br />
                                
                                <button type='submit' name='prod_details_form' value='prod_details_form' class='btn btn-success border rounded'>Update Info</button>
                                <br /><br />
                                <hr /><br />
                              </form>
                              </div>
                            </div>";
                            
                            $products_images_count = "1"; //used for image count
                            
                            //if there are no image, ask to upload atleast one image
                            if(count($products_images) === 0){
                                echo "Please Upload atleast one image. <br /><br />";
                            } else {
                                //loops/shows all images
                                foreach($products_images as $product_i){
                                    echo "
                                        <div class='product-info row'>
                                        <span class='text-secondary'>Image ".$products_images_count."  : </span>
                                        <div class='product-image'>
                                            <img src='".$product_i->image."' alt='Product' class='img-fluid' loading='lazy'>
                                          </div>
                                          <div class='product-details'>";
                                          // checks if is primary image
                                          if($product_i->is_primary == "1"){
                                              echo "<button class='btn btn-success'>Primary image</button>";
                                          } else {
                                              echo "<a href='?id=edit_product&product_id=".$_GET["product_id"]."&img_id=".$product_i->id."&post=make_primary' class='btn rounded border' aria-label='Make Primary Image for product link'>Make Primary</a>";
                                          }
                                          echo "
                                         <br/><br />
                                         <a href='?id=edit_product&product_id=".$_GET["product_id"]."&img_id=".$product_i->id."&post=delete_img' class='btn btn-danger' aria-label='Delete Prodduct image link'>Delete Image</a>
                                         <br/><br />
                                        </div>   
                                        </div>
                                    ";
                                    $products_images_count++; // adds one for image count
                                }
                            }
                            
                            echo  "
                            <hr /><br />
                            <div class='alert alert-secondary' role='alert'>
                                Be aware of your max upload size. You can change that in you ini.php - Current max upload is ".ini_get("upload_max_filesize")."
                            </div>
                            <form method='POST' action='?id=edit_product&product_id=".$_GET["product_id"]."&post=upload_image' enctype='multipart/form-data'>
                                <input type='file' id='image_upload' name='image_upload'>
                                <br /><br />
                                <button type='submit' name='image_form' value='image_form' class='btn btn-success border rounded'>Upload</button>
                                <br /><br />
                            </form>
                            <hr /><br />
                            ";
                            
                            echo " 
                                <div class='product-info row'>
                                  <div class='product-details'>
                                  <a class='btn btn-danger' href='?id=delete_product&product_id=".$_GET["product_id"]."' aria-label='Delete Prodduct'>Delete Product</a>
                                </div>   
                                </div>
                                <br /><hr /><br />
                            </div>
                            
                            <div class='cart-header d-none d-lg-block'>
                                <div class='row align-items-center gy-4'>
                                  <div class='col-lg-4'>
                                    <h5>Product Variant</h5>
                                  </div>
                                  <div class='col-lg-6'>
                                    <h5>Stripe Price ID</h5>
                                  </div>
                                  <div class='col-lg-2'>
                                    <a href='?id=add_variant&product_id=".$product_info->id."' class='btn border rounded' aria-label='Add varient Page'>Add a variant</a>
                                  </div>
                                </div>
                            </div>
                            <div class='cart-item' >
                                <div class='row'>";
                                
                                // checks if there are any variants
                                if (count($products_variant) === 0) {
                                     echo "You need atleast one varient for this to work...
                                           <br /><br />
                                           <a href='?id=add_variant&product_id=".$product_info->id."' class='btn border rounded' aria-label='Add variant Page'>Add a variant</a>
                                     ";
                                } else {
                                // loop/show all variants
                                foreach($products_variant as $product_v){
                                ?>
                                  <div class='col-lg-4 col-4'>  
                                    <h5>size <?=$product_v->size?></h5>
                                  </div>
                                  <div class='col-lg-6 col-6'>  
                                    <h5><?=$product_v->price_id?></h5>
                                  </div>
                                  <div class='col-lg-2 col-2'>  
                                    <a href='?id=delete_varient&product_id=<?=$product_info->id?>&varient_id=<?=$product_v->id?>' class='btn btn-danger' aria-label='Delete Variant'>delete</a>
                                  </div>
                                  <br /> <br />
                                 <?php } }
                                 
                                 echo " 
                                </div>
                            </div>
                            <a href='product.php?id=".$product_info->id."' class='btn btn-success' aria-label='Go to Product Page'>Go to Product Page</a>
                            
                          </div>
                        </div>
                      </div></div></div></div></div></div>   
            ";
        }
        
//***** ADD VARIANT
        if($page_id == "add_variant"){
            
            // if no product id, redirect to product page
            if(!isset($_GET["product_id"])){
                header("Location: store_admin.php?id=products");
                die();
            }
            
            // gets product info
            $product_info = $db->query("SELECT * FROM simple_store_products WHERE id = ?",[$_GET["product_id"]])->first(); 
            
            // checks for post form
            $add_confirm = "";   
            if(isset($_GET["post"])){
                $add_confirm = $_GET["post"];   
            }
            
            //post add
             if($add_confirm == "add"){
                 
                require_once 'usersc/plugins/simple_store/assets/stripe/vendor/autoload.php'; // required stripe stuff
                $stripe_key_check = $db->query("SELECT * FROM simple_store_stripe_keys")->first(); // get keys
                
                //decides which keys to use
                if($stripe_key_check->is_live == 1){
                    $stripeSecretKey = $stripe_key_check->live_secret;
                } else {
                    $stripeSecretKey = $stripe_key_check->sandbox_secret;
                }
                
                // creates a new product and price id via stripe api
                $stripe = new \Stripe\StripeClient($stripeSecretKey);
                $product = $stripe->products->create([
                  'name' => $product_info->name,
                  'description' => "Size ".$_POST['product_variant'],
                  'tax_code' => "txcd_99999999", // you may have to update this for your specific needs
                  'default_price_data' => [
                    'unit_amount' => $product_info->price*100,
                    'currency' => $stripe_key_check->stripe_currency, // you may have to update this based on your needs
                  ],
                  'expand' => ['default_price'],
                ]);
                
                //echo "<pre>".var_dump($product)."</pre>";
                // echo $product->default_price->id;
                
                //preps fields for database
                $fields = [
                    'product_id' => $_GET["product_id"],
                    'size' => $_POST['product_variant'],
                    'prod_id' => $product->id,
                    'price_id' => $product->default_price->id,
                ];
                $result = $db->insert('simple_store_products_variants', $fields); //inserts into database
                header("Location: store_admin.php?id=edit_product&product_id=".$_GET["product_id"]); // redirects to product page
                die();
             }
            
            echo "
            <div class='order-confirmation-1'>
              <div class='next-steps  p-4'  >
                <h3 class='text-center'>Add Product Variant</h3>
                
                 <section id='cart' class='cart section'>
                  <div class='container' >
                  <a class='btn btn-secondary' href='store_admin.php' aria-label='Go to Store Admin Page'>Back to Store Admin</a> <a class='btn btn-secondary' href='store_admin.php?id=edit_product&product_id=".$_GET["product_id"]."' aria-label='Go to Product Page'>Back to Product Page</a> <br/><br/>
                  <div class='row g-4'>
                  <div class='col-lg-12' >
                    <div class='cart-items'>
                      <div class='cart-header d-none d-lg-block'>
                        <div class='row align-items-center gy-4'>
                          <div class='col-lg-12'>
                            <h5>**Stripe keys must be setup before adding
                                <br /><br />
                                This will use stripe API to generate a new product and add a price id
                            </h5>
                          </div>
                        </div>
                      </div>
                      
                      <div class='cart-item' >
                        <div class='row'>
                          <div class='col-lg-12 col-12'>
                            <div class='product-info d-flex '>
                              <div class='product-details'>
                              <form method='POST' action='?id=add_variant&product_id=".$_GET["product_id"]."&post=add'>
                                <span class='text-secondary'>Product Size  : </span><input type='text' class='form-control' id='product_variant' name='product_variant' required></span>
                                <br />
                                
                                <button type='submit' name='prod_details_form' value='prod_details_form' class='btn btn-success border rounded'>Add Product Variant</button>
                                <br /><br />
                              </form>
                              </div>
                            </div>
                              
                            </div>
                          </div>
                        </div>
                      </div>
                       </div> </div> </div> </div> </div>
                      
            ";
        }
        
//***** DELETE VARIANT
        if($page_id == "delete_varient"){
            
            // gets varient id
            if(!isset($_GET["varient_id"])){
                header("Location: store_admin.php?id=products");
                die();
            }
            
            // double checks if delete confirm
             $delete_confirm = "";   
             if(isset($_GET["confirm"])){
                 $delete_confirm = $_GET["confirm"];   
             }
             
             // finally deletes in database ****ONLY DELETES IN DB, WILL HAVE TO MANUALLY DELETE IN STRIPE> I GOT LAZY
             if($delete_confirm == "true"){
                 $result = $db->delete('simple_store_products_variants', ["id", "=", $_GET["varient_id"]]); // delete from database
                  header("Location: store_admin.php?id=edit_product&product_id=".$_GET["product_id"]); // redirect to product page
                  die();
             }
            echo "
                <div class='order-confirmation-1'>
                  <div class='next-steps  p-4'  >
                    <h3 class='text-center'>Delete Product Variant</h3>
                    
                    <section id='cart' class='cart section'>
                    <div class='container' >
                      <a class='btn btn-secondary' href='store_admin.php' aria-label='Go to Store Admin Page'>Back to Store Admin</a> <a class='btn btn-secondary' href='store_admin.php?id=edit_product&product_id=".$_GET["product_id"]."' aria-label='Go to Product Page'>Back to Product Page</a> <br/><br/>
                      <div class='row g-4'>
                      <div class='col-lg-12' >
                        <div class='cart-items'>
                          <div class='cart-header d-none d-lg-block'>
                            <div class='row align-items-center gy-4'>
                              <div class='col-lg-12'>
                                <h5>**This will only delete from local database. It will NOT DELETE from stripe account
                                    <br /><br />
                                    Deleting varient may cause issues when customer tries to track order. 
                                </h5>
                              </div>
                            </div>
                          </div>
                          
                          <div class='cart-item' >
                            <div class='row'>
                              <div class='col-lg-12 col-12'>
                                <div class='product-info d-flex '>
                                  <div class='product-details'>
                                     <p class='text-center'>
                                        Are you sure you want to delete?
                                        <br /><br />
                                        <a class='btn btn-danger' href='?id=delete_varient&varient_id=".$_GET["varient_id"]."&confirm=true' aria-label='Delete Product Variant'>Delete Product Variant</a>
                                     </p>
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
                </div>
            
            ";
        }
        
//***** DELETE PRODUCT
        if($page_id == "delete_product"){
            //makes sure there is a product id, if not redirect to product page
            if(!isset($_GET["product_id"])){
                header("Location: store_admin.php?id=products");
                die();
            }
            
            //double checks if you want to delete
             $delete_confirm = "";   
             if(isset($_GET["confirm"])){
                 $delete_confirm = $_GET["confirm"];   
             }
             
             //actually deletes product 
             if($delete_confirm == "true"){
                 $result = $db->delete('simple_store_products', ["id", "=", $_GET["product_id"]]); // deletes product from database
                  header("Location: store_admin.php?id=products"); 
                  die();
             }
            echo "
            <div class='order-confirmation-1'>
                <div class='next-steps  p-4'  >
                    <h3 class='text-center'>Delete Product </h3>
                    
                    <section id='cart' class='cart section'>
                    <div class='container' >
                      <a class='btn btn-secondary' href='store_admin.php' aria-label='Go to Store Admin Page'>Back to Store Admin</a> <a class='btn btn-secondary' href='store_admin.php?id=edit_product&product_id=".$_GET["product_id"]."' aria-label='Go to Product Page'>Back to Product Page</a> <br/><br/>
                      <div class='row g-4'>
                      <div class='col-lg-12' >
                        <div class='cart-items'>
                          <div class='cart-header d-none d-lg-block'>
                            <div class='row align-items-center gy-4'>
                              <div class='col-lg-12'>
                                <h5>**This will only delete from local database. It will NOT DELETE from stripe account
                                    <br /><br />
                                    Deleting a Product may cause issues when customer tries to track order. 
                                </h5>
                              </div>
                            </div>
                          </div>
                          
                          <div class='cart-item' >
                            <div class='row'>
                              <div class='col-lg-12 col-12'>
                                <div class='product-info d-flex '>
                                  <div class='product-details'>
                                     <p class='text-center'>
                                        Are you sure you want to delete?
                                        <br /><br />
                                        <a class='btn btn-danger' href='?id=delete_product&product_id=".$_GET["product_id"]."&confirm=true' aria-label='Delete Product'>Delete Product</a>
                                     </p>
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
            </div>";
        }
        
//***** ADD PRODUCT PAGE
        if($page_id == "add_product"){
             $add_confirm = "";   
             
             //checks if form post
             if(isset($_GET["post"])){
                $add_confirm = $_GET["post"]; // set add confirm
             }
             
             //adds to database
             if($add_confirm == "add"){
                 // prepares fields to add to database
                $fields = [
                    'name' => $_POST['product_name'],
                    'catergory' => $_POST['product_catergory'],
                    'price' => $_POST['product_price'],
                    'color' => $_POST['product_color'],
                    'description' => $_POST['product_description'],
                    'status' => "0",
                    'sold_out' => "1",
                    'purchase_count' => "0",
                ];
                $result = $db->insert('simple_store_products', $fields); // inserts into database
                $last_id = $db->lastId(); // gets lastId for new product redirect
                header("Location: store_admin.php?id=edit_product&product_id=".$last_id); // redirects to new product
                die();
             }
            
            echo "
            <div class='order-confirmation-1'>
              <div class='next-steps  p-4'  >
                <h3 class='text-center'>Add Product</h3>
                
                 <section id='cart' class='cart section'>
                  <div class='container' >
                  <a class='btn btn-secondary' href='store_admin.php' aria-label='Go to Store Admin Page'>Back to Store Admin</a> <br/><br/>
                  <div class='row g-4'>
                  <div class='col-lg-12' >
                    <div class='cart-items'>
                      <div class='cart-header d-none d-lg-block'>
                        <div class='row align-items-center gy-4'>
                          <div class='col-lg-12'>
                            <h5>Product</h5>
                          </div>
                        </div>
                      </div>
                      
                      <div class='cart-item' >
                        <div class='row'>
                          <div class='col-lg-12 col-12'>
                            <div class='product-info d-flex '>
                              <div class='product-details'>
                              <form method='POST' action='?id=add_product&post=add'>
                                <span class='text-secondary'>Product Name  : </span><input type='text' class='form-control' id='product_name' name='product_name' placeholder='Product Name' required></span>
                                <br />
                                <span class='text-secondary'>Catergory : </span>
                                <select class='form-control' name='product_catergory' id='product_catergory'>";
                                $products_catergories = $db->query("SELECT * FROM simple_store_catergories")->results(); // gets catergories 
                                // loops/shows catergory options
                                foreach($products_catergories as $p_catergory){
                                    echo "<option value='".$p_catergory->id."'>";
                                        if($p_catergory->is_subcatergory == 1){
                                        $subcartergory_of = $db->query("SELECT * FROM simple_store_catergories WHERE id = ?", [$p_catergory->subcartergory_of])->first(); 
                                        echo $subcartergory_of->name." | ";
                                        }
                                    echo $p_catergory->name."</option>";
                                }
                                echo "
                                </select>
                                
                                <br />";?>
                                <span class='text-secondary'>Price : </span><input type='text' class='form-control' id='product_price' name='product_price' oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*?)\..*/g, '$1');" placeholder='Product Price' required></span>
                                <br />
                                <span  class='text-secondary'>Color : </span>
                                      <select class='form-control' name='product_color' id='product_color'>
                                <?php
                                $products_colors = $db->query("SELECT * FROM simple_store_products_colors")->results(); // get colors
                                // loops/shows color options 
                                foreach($products_colors as $p_c){
                                             echo "<option value='".$p_c->name."' >".$p_c->name."</option>";
                                         }
                                echo "
                                    </select>
                                <br />
                                <span class='text-secondary' >Description : </span><textarea  rows='5' class='form-control' id='product_description' name='product_description' value='' placeholder='Product Description. Use &lt;li&gt; &lt;/li&gt;'></textarea></span>
                                <br />
                                <button type='submit' name='prod_details_form' value='prod_details_form' class='btn btn-success border rounded'>Add Product</button>
                                <br /><br />
                              </form>
                              </div>
                            </div>
                            </div>
                          </div>
                        </div>
                      </div></div></div></div></div></div> ";
        }
        
//***** COLORS PARE
        if($page_id == "colors"){
            echo "
            <div class='order-confirmation-1'>
              <div class='next-steps  p-4'  >
                <h3 class='text-center'>Colors</h3>
    
                 <section id='cart' class='cart section'>
                  <div class='container' >
                  
                  <div class='row g-4'>
                  <div class='col-lg-10'><a class='btn btn-secondary' href='store_admin.php' aria-label='Go to Store Admin Page'>Back to Store Admin</a> </div>
                  <div class='col-lg-2'><a class='btn rounded border float-end' href='?id=add_color' aria-label='Go to Add Color Page'>Add Colors</a></div>
                  <div class='col-lg-12' >
                  
                    <div class='cart-items'>
                      <div class='cart-header d-none d-lg-block'>
                        <div class='row align-items-center gy-4'>
                          <div class='col-lg-8'>
                            <h5>Color</h5>
                          </div>
                          
                          <div class='col-lg-2 '>
                            <h5>Hex</h5>
                          </div>
                          <div class='col-lg-2 '>
                            <h5>Delete</h5>
                          </div>
                        </div>
                      </div>";          
            $product_colors = $db->query("SELECT * FROM simple_store_products_colors")->results(); // gets all colors
            // if there are no colors, show this
            if (count($product_colors) === 0) {
                 echo "You need atleast one color...
                       <br /><br />
                       <a href='?id=add_color' class='btn border rounded' aria-label='Go to Add Color Page'>Add Colors</a>
                 ";
            } else {
            // loop/show all colors     
            foreach($product_colors as $p) {?> 
                      <div class="cart-item" >
                        <div class="row align-items-center gy-4">
                          <div class="col-lg-8 col-12 mb-3 mb-lg-0">
                            <div class="product-info d-flex ">
                              
                                <span style="height: 25px; width: 25px;-moz-border-radius:50%; -webkit-border-radius: 50%;  background-color: #<?=$p->hex?>;" class="img-fluid" loading="lazy"> </span> &nbsp;
                              
                              <div class="product-details">
                                <span class='text-secondary'> &nbsp; &nbsp; Color  : </span><span><?=$p->name?></span>
                                <br />
                              </div>
                             
                            </div>
                          </div>
                          <div class="col-12 col-lg-2">
                              <span class='text-secondary'>Hex : </span><span><?=$p->hex?> </span>
                          </div>
                           <div class="col-12 col-lg-2">
                              <a href="?id=delete_color&color_id=<?=$p->id?>"><button type='button' class='btn text-bg-secondary' aria-label='Delete Color'>Delete Color</button></a>
                          </div>
                        </div>
                      </div><!-- End Cart Item -->
                
            <?php } } echo "</div></div></div></div></div></div>";
        }
        
//***** ADD COLOR
        if($page_id == "add_color"){
             $add_confirm = "";   
             
             //checks if there is a form post
             if(isset($_GET["post"])){
                $add_confirm = $_GET["post"];   
             }
             
            //if post form is add
             if($add_confirm == "add"){
                // prepare fields for database
                $fields = [
                    'name' => $_POST['color_name'],
                    'hex' => $_POST['color_hex'],
                ];
                $result = $db->insert('simple_store_products_colors', $fields); // add to database
                header("Location: store_admin.php?id=colors"); // redirect to colors page
                die();
             }
            
            echo "
            <div class='order-confirmation-1'>
              <div class='next-steps  p-4'  >
                <h3 class='text-center'>Add Color</h3>
                
                 <section id='cart' class='cart section'>
                  <div class='container' >
                  <a class='btn btn-secondary' href='store_admin.php' aria-label='Go to Store Admin Page'>Back to Store Admin</a> <br/><br/>
                  <div class='row g-4'>
                  <div class='col-lg-12' >
                    <div class='cart-items'>
                      <div class='cart-header d-none d-lg-block'>
                        <div class='row align-items-center gy-4'>
                          <div class='col-lg-12'>
                            <h5>Color</h5>
                          </div>
                        </div>
                      </div>
                      
                      <div class='cart-item' >
                        <div class='row'>
                          <div class='col-lg-12 col-12'>
                            <div class='product-info d-flex '>
                              <div class='product-details'>
                              <form method='POST' action='?id=add_color&post=add'>
                                <span class='text-secondary'>Color Name  : </span><input type='text' class='form-control' id='color_name' name='color_name' required></span>
                                <br />
                                <span class='text-secondary'>Color Hex : </span><input type='text' class='form-control' id='color_hex' name='color_hex'  required></span>
                                <br />
                                <button type='submit' name='prod_details_form' value='prod_details_form' class='btn btn-success border rounded'>Add Color</button>
                                <br /><br />
                              </form>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div></div></div></div></div></div></div>";
        }
        
//***** DELETE COLOR
        if($page_id == "delete_color"){
            
            // checks if thre is a color id, if not redirect to colors page
            if(!isset($_GET["color_id"])){
                header("Location: store_admin.php?id=colors");
                die();
            }
            
            // double checks if you want to delete color
             $delete_confirm = "";   
             if(isset($_GET["confirm"])){
                 $delete_confirm = $_GET["confirm"];   
             }
            // if true, then delete from database
             if($delete_confirm == "true"){
                 $result = $db->delete('simple_store_products_colors', ["id", "=", $_GET["color_id"]]); // delete from database
                  header("Location: store_admin.php?id=colors"); // redirect to colors page
                  die();
             }
            echo "
            <div class='order-confirmation-1'>
                <div class='next-steps  p-4'  >
                    <h3 class='text-center'>Delete Color </h3>
                    
                    <section id='cart' class='cart section'>
                    <div class='container' >
                      <a class='btn btn-secondary' href='store_admin.php' aria-label='Go to Store Admin Page'>Back to Store Admin</a> <a class='btn btn-secondary' href='store_admin.php?id=colors' aria-label='Go to Product Page'>Back to Colors Page</a> <br/><br/>
                      <div class='row g-4'>
                      <div class='col-lg-12' >
                        <div class='cart-items'>
                          <div class='cart-header d-none d-lg-block'>
                            <div class='row align-items-center gy-4'>
                              <div class='col-lg-12'>
                                <h5>**This will delete from local database. 
                                    <br /><br />
                                    Deleting a Color may cause issues when customer tries to track order. 
                                </h5>
                              </div>
                            </div>
                          </div>
                          
                          <div class='cart-item' >
                            <div class='row'>
                              <div class='col-lg-12 col-12'>
                                <div class='product-info d-flex '>
                                  <div class='product-details'>
                                     <p class='text-center'>
                                        Are you sure you want to delete?
                                        <br /><br />
                                        <a class='btn btn-danger' href='?id=delete_color&color_id=".$_GET["color_id"]."&confirm=true' aria-label='Delete Color'>Delete Color</a>
                                     </p>
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
            </div>  ";
        }
        
//***** CATERGORIES PAGE
        if($page_id == "catergories"){
            echo "
            <div class='order-confirmation-1'>
              <div class='next-steps  p-4'  >
                <h3 class='text-center'>Catergories</h3>
                 <section id='cart' class='cart section'>
                  <div class='container' >
                  <div class='row g-4'>
                  <div class='col-lg-6'><a class='btn btn-secondary' href='store_admin.php' aria-label='Go to Store Admin Page'>Back to Store Admin</a> </div>
                  <div class='col-lg-4'><a class='btn rounded border float-end' href='?id=add_subcatergory' aria-label='Go to Add Sub Catergory Page'>Add Sub Catergory</a></div>
                  <div class='col-lg-2'><a class='btn rounded border float-end' href='?id=add_catergory' aria-label='Go to Add Catergory Page'>Add Catergory</a></div>
                  <div class='col-lg-12' >
                    <div class='cart-items'>
                      <div class='cart-header d-none d-lg-block'>
                        <div class='row align-items-center gy-4'>
                          <div class='col-lg-10'>
                            <h5>Catergories</h5>
                          </div>
                          <div class='col-lg-2 '>
                            <h5>Delete</h5>
                          </div>
                        </div>
                      </div>";
            $product_catergories = $db->query("SELECT * FROM simple_store_catergories WHERE is_subcatergory = ?", ["0"])->results(); // gets all catergories
            //if no catergories, show this
            if (count($product_catergories) === 0) {
                 echo "You need atleast one Catergory...
                       <br /><br />
                       <a href='?id=add_catergory' class='btn border rounded' aria-label='Go to Add Catergory Page'>Add Catergory</a>
                 ";
            } else {
            // loop/show all catergories    
            foreach($product_catergories as $p) {
                $product_catergories_sub = $db->query("SELECT * FROM simple_store_catergories WHERE subcartergory_of = ?", [$p->id])->results(); // gets all catergories
            ?> 
                      <div class="cart-item" >
                        <div class="row align-items-center gy-4">
                          <div class="col-lg-10 col-12 mb-3 mb-lg-0">
                            <div class="product-info d-flex ">
                              <div class="product-details">
                                <span class='text-secondary'> &nbsp; &nbsp; Catergory : </span><span><?=$p->name?></span>
                                <br />
                              </div>
                            </div>
                          </div>
                           <div class="col-12 col-lg-2">
                              <a href="?id=edit_catergory&catergory_id=<?=$p->id?>"><button type='button' class='btn text-bg-secondary w-100' aria-label='Go to Edit Catergory Page'>Edit Catergory</button></a>
                          </div>
                        </div>
                          
            <?php
                foreach($product_catergories_sub as $p_s) {?>
                    <div class="" >
                        <br />
                        <div class="row align-items-center ">
                          <div class="col-lg-10 ">
                            <div class="product-info">
                              <div class="product-details">
                                <span class='text-secondary'> &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; &nbsp; Sub Catergory : </span><span><?=$p_s->name?></span>
                                <br />
                              </div>
                            </div>
                          </div>
                           <div class="col-lg-2">
                              <a href="?id=edit_catergory&catergory_id=<?=$p_s->id?>"><button type='button' class='btn text-bg-secondary w-100' aria-label='Go to Edit Catergory Page'>Edit Sub Catergory</button></a>
                          </div>
                        </div>
                      </div> 
                <?php }
                echo "</div>";
                
            } } echo "</div></div></div></div></div></div>";
        }
        
//***** ADD CATERGORY
        if($page_id == "add_catergory"){
             $add_confirm = "";   
             
             // checks if form post
             if(isset($_GET["post"])){
                $add_confirm = $_GET["post"];   
             }
             
             // checks if post to add
             if($add_confirm == "add"){
                // prepares fields for database 
                $fields = [
                    'name' => $_POST['catergory_name'],
                ];
                $result = $db->insert('simple_store_catergories', $fields); // adds to databse
                $last_id = $db->lastId(); 
                header("Location: store_admin.php?id=edit_catergory&catergory_id=".$last_id); // redirects to database
                die();
             }
            
            echo "
            <div class='order-confirmation-1'>
              <div class='next-steps  p-4'  >
                <h3 class='text-center'>Add Catergory</h3>
                 <section id='cart' class='cart section'>
                  <div class='container' >
                  <div class='row g-4'>
                  <div class='col-md-10'><a class='btn btn-secondary' href='store_admin.php' aria-label='Go to Store Admin Page'>Back to Store Admin</a> </div>
                  <div class='col-md-2'><a class='btn rounded border float-end' href='store_admin.php?id=catergories' aria-label='Go to Store Admin Page'>Back to Catergories</a></div>
                  <div class='col-lg-12' >
                    <div class='cart-items'>
                      <div class='cart-header d-none d-lg-block'>
                        <div class='row align-items-center gy-4'>
                          <div class='col-lg-12'>
                            <h5>Catergory</h5>
                          </div>
                        </div>
                      </div>
                      <div class='cart-item' >
                        <div class='row'>
                          <div class='col-lg-12 col-12'>
                            <div class='product-info d-flex '>
                              <div class='product-details'>
                              <form method='POST' action='?id=add_catergory&post=add'>
                                <span class='text-secondary'>Catergory Name  : </span><input type='text' class='form-control' id='catergory_name' name='catergory_name' required></span>
                                <br />
                                <button type='submit' name='prod_details_form' value='prod_details_form' class='btn btn-success border rounded'>Add Catergory</button>
                                <br /><br />
                              </form>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div></div></div></div></div></div></div></div>";
        }
        
//***** ADD SUB CATERGORY
        if($page_id == "add_subcatergory"){
             $add_confirm = "";   
             
             // checks if form post
             if(isset($_GET["post"])){
                $add_confirm = $_GET["post"];   
             }
             
             // checks if post to add
             if($add_confirm == "add"){
                // prepares fields for database 
                $fields = [
                    'name' => $_POST['catergory_name'],
                    'is_subcatergory' => 1,
                    'subcartergory_of' => $_POST['main_catergory_id'], 
                ];
                $result = $db->insert('simple_store_catergories', $fields); // adds to databse
                $last_id = $db->lastId(); 
                header("Location: store_admin.php?id=edit_catergory&catergory_id=".$last_id); // redirects to database
                die();
             }
            $cat_loop = $db->query("SELECT * FROM simple_store_catergories WHERE is_subcatergory = ?", ["0"])->results(); // get catergory info
            
            echo "
            <div class='order-confirmation-1'>
              <div class='next-steps  p-4'  >
                <h3 class='text-center'>Add Sub Catergory</h3>
                 <section id='cart' class='cart section'>
                  <div class='container' >
                  <div class='row g-4'>
                  <div class='col-md-8'><a class='btn btn-secondary' href='store_admin.php' aria-label='Go to Store Admin Page'>Back to Store Admin</a> </div>
                  <div class='col-md-4'><a class='btn rounded border float-end' href='store_admin.php?id=catergories' aria-label='Go to Store Admin Page'>Back to Catergories</a></div>
                  <div class='col-lg-12' >
                    <div class='cart-items'>
                      <div class='cart-header d-none d-lg-block'>
                        <div class='row align-items-center gy-4'>
                          <div class='col-lg-12'>
                            <h5>Catergory</h5>
                          </div>
                        </div>
                      </div>
                      <div class='cart-item' >
                        <div class='row'>
                          <div class='col-lg-12 col-12'>
                            <div class='product-info d-flex '>
                              <div class='product-details'>
                              <form method='POST' action='?id=add_subcatergory&post=add'>
                                <select class='form-control' name='main_catergory_id' id='main_catergory_id' required>
                                      "; 
                                            foreach($cat_loop as $c) {
                                                echo "<option value='".$c->id."'>".$c->name."</option>
                                                ";
                                            }
                                      echo "              
                                </select>
                                <span class='text-secondary'>Catergory Name  : </span><input type='text' class='form-control' id='catergory_name' name='catergory_name' required></span>
                                <br />
                                <button type='submit' name='prod_details_form' value='prod_details_form' class='btn btn-success border rounded'>Add Catergory</button>
                                <br /><br />
                              </form>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div></div></div></div></div></div></div></div>";
        }        
        
//***** EDIT CATERGORY CATERGORY PAGE
        if($page_id == "edit_catergory"){
            // checks if there is a catergory id, if not redirect to catergories page
            if(!isset($_GET["catergory_id"])){
                header("Location: store_admin.php?id=catergories");
                die();
            }
            
            
            $cat_info = $db->query("SELECT * FROM simple_store_catergories WHERE id = ?", [ $_GET["catergory_id"]])->first(); // get catergory info
             
            //checks if form post
            if($_POST){ 
                $post_type = $_GET["post"]; // sets post type
                
                //post general info
                if($post_type == "general"){
                    //sets all fields
                    $fields = [
                    'name' => $_POST['catergory_name'],
                ];
                $result = $db->update('simple_store_catergories', $_GET["catergory_id"] , $fields); // updates database
                header("Location: store_admin.php?id=edit_catergory&catergory_id=".$_GET["catergory_id"]); //redirects to product page
                die();
                }
                
                //post general info
                if($post_type == "subcat"){
                    //sets all fields
                    $fields = [
                    'name' => $_POST['catergory_name'],
                    'subcartergory_of' => $_POST['main_catergory_id'], 
                ];
                $result = $db->update('simple_store_catergories', $_GET["catergory_id"] , $fields); // updates database
                header("Location: store_admin.php?id=edit_catergory&catergory_id=".$_GET["catergory_id"]); //redirects to product page
                die();
                }
                
                
                // uploads image
                if($post_type == "upload_image"){

                    //checks if there is a file to upload
                    if($_FILES['image_upload']['size'] === 0) {
                        header("Location: store_admin.php?id=edit_catergory&catergory_id=".$_GET["catergory_id"]); //redirects to product page
                        die();
                    }
                    
                    //Set directory path
                    $uploaddir = 'usersc/plugins/simple_store/assets/template/img/';
                    $imageFileType = strtolower(pathinfo($_FILES['image_upload']['name'],PATHINFO_EXTENSION));
                    
                    
                    $id = $_GET["catergory_id"]; //get id
                    
                    //Upload image 
                    $uploadfile = $uploaddir . "catergory_".$id.".".$imageFileType;
                    echo '<pre>';
                    if (move_uploaded_file($_FILES['image_upload']['tmp_name'], $uploadfile)) {
                        echo "File is valid, and was successfully uploaded.\n";
                    } else {
                        echo "Possible file upload attack!\n or... check what your upload limit is";
                    }
                    print "</pre>";
                    
                    
                    //prepping fields for database
                    $fields = [
                    'image' => $uploadfile,
                    ];
                    $result = $db->update('simple_store_catergories', $id, $fields); //updates database 
                     header("Location: store_admin.php?id=edit_catergory&catergory_id=".$_GET["catergory_id"]); // redirects to product page once completed
                     die();
                }

            }
            
             
            echo "
                <div class='order-confirmation-1'>
                  <div class='next-steps  p-4'  >
                    <h3 class='text-center'>Edit Catergory</h3>
                     <section id='cart' class='cart section'>
                      <div class='container' >
                      <div class='row g-4'>
                      <div class='col-md-8'><a class='btn btn-secondary' href='store_admin.php' aria-label='Go to Store Admin Page'>Back to Store Admin</a> </div>
                      <div class='col-md-4'><a class='btn rounded border float-end' href='store_admin.php?id=catergories' aria-label='Go to Store Admin Page'>Back to Catergories</a></div>
                      <div class='col-lg-12' >
                        <div class='cart-items'>
                          <div class='cart-header d-none d-lg-block'>
                            <div class='row align-items-center gy-4'>
                              <div class='col-lg-12'>
                                <h5>Catergory</h5>
                              </div>
                            </div>
                          </div>
                          <div class='cart-item' >
                            <div class='row'>
                              <div class='col-lg-12 col-12'>
                                <div class='product-info d-flex '>
                                  <div class='product-details'>";
                                  
                                  
                                  if($cat_info->is_subcatergory == 0){
                                      echo "
                                        <form method='POST' action='?id=edit_catergory&catergory_id=".$_GET["catergory_id"]."&post=general'>
                                  
                                            <span class='text-secondary'>Catergory Name  : </span><input type='text' class='form-control' id='catergory_name' name='catergory_name' value='".$cat_info->name."' required></span>
                                            <br />
                                            <button type='submit' name='prod_details_form' value='prod_details_form' class='btn btn-success border rounded'>Edit Catergory</button>
                                            <br /><br />
                                        </form>
                                        </div></div>
                                        <hr> <br />
                                      
                                      ";
                                  }
                                  
                                  if($cat_info->is_subcatergory == 1){
                                      $cat_loop = $db->query("SELECT * FROM simple_store_catergories WHERE is_subcatergory = ?", ["0"])->results(); // get catergory info
                                      echo "
                                        <form method='POST' action='?id=edit_catergory&catergory_id=".$_GET["catergory_id"]."&post=subcat'>
                                            <span class='text-secondary'>Catergory Name  : </span>
                                                <select class='form-control' name='main_catergory_id' id='main_catergory_id' required>
                                      ";
                                            
                                            foreach($cat_loop as $c) {
                                               
                                                echo "<option value='".$c->id."' "; 
                                                    if($c->id == $cat_info->subcartergory_of){echo "selected";}
                                                echo "  >".$c->name."</option>
                                                ";
                                                
                                            }
                                      echo "              
                                                </select>
                                            <span class='text-secondary'>Sub Catergory Name  : </span><input type='text' class='form-control' id='catergory_name' name='catergory_name' value='".$cat_info->name."' required></span>
                                            <br />
                                            <button type='submit' name='prod_details_form' value='prod_details_form' class='btn btn-success border rounded'>Edit Sub Catergory</button>
                                            <br /><br />
                                        </form>
                                        </div></div>
                                        <hr> <br />
                                      
                                      ";
                                  }
                                  
                
                                  
                                    if($cat_info->image == ""){
                                        // do nothing
                                    } else {
                                       echo " 
                                            <div class='product-info row'>
                                                <span class='text-secondary'>Catergory Image: </span>
                                                <div class='product-image' >
                                                    <img src='".$cat_info->image."' width='100px' height='100px' alt='Product' class='img-fluid' loading='lazy'>
                                                </div>
                                            </div> <br /><br />" ;
                                    }
                                  echo "
                                   
                                        <div class='alert alert-secondary' role='alert'>
                                            Be aware of your max upload size. You can change that in you ini.php - Current max upload is ".ini_get("upload_max_filesize")."
                                        </div>
                                        <form method='POST' action='?id=edit_catergory&catergory_id=".$_GET["catergory_id"]."&post=upload_image' enctype='multipart/form-data'>
                                            <input type='file' id='image_upload' name='image_upload'>
                                            <br /><br />
                                            <button type='submit' name='image_form' value='image_form' class='btn btn-success border rounded'>Upload</button>
                                            <br /><br />
                                        </form>
                                        <hr /><br />";
                                  
                                  echo "
                                  <a class='btn btn-danger' href='?id=delete_catergory&catergory_id=".$_GET["catergory_id"]."'>Delete Catergory</a>
                                </div>
                              </div>
                            </div>
                          </div></div></div></div></div></div></div></div>
            ";
        }
        
        
//***** DELETE CATERGORY PAGE
        if($page_id == "delete_catergory"){
            // checks if there is a catergory id, if not redirect to catergories page
            if(!isset($_GET["catergory_id"])){
                header("Location: store_admin.php?id=catergories");
                die();
            }
             $delete_confirm = ""; 
             
             // checks if form post 
             if(isset($_GET["confirm"])){
                 $delete_confirm = $_GET["confirm"];   
             }
             //double checks if you want to delete
             if($delete_confirm == "true"){
                 $result = $db->delete('simple_store_catergories', ["id", "=", $_GET["catergory_id"]]); // deletes from database
                  header("Location: store_admin.php?id=catergories");  // redirects to catergories page
                  die();
             }
            echo "
            <div class='order-confirmation-1'>
              <div class='next-steps  p-4'  >
                <h3 class='text-center'>Delete Catergory</h3>
                 <section id='cart' class='cart section'>
                 <br />
                 <p class='text-center'>
                    Are you sure you want to delete?
                    <br /><br />
                    <a class='btn btn-danger' href='?id=delete_catergory&catergory_id=".$_GET["catergory_id"]."&confirm=true' aria-label='Go to Delete Catergory Page'>Delete Catergory</a>
                 </p>
                </div>
            </div>";
        }
        
//***** ORDERS PAGE
        if($page_id == "orders"){
            $stripe_transactions = $db->query("SELECT * FROM simple_store_stripe_transactions ORDER BY id DESC LIMIT 50")->results(); // Gets last 50 transactions
            echo "
            <div class='order-confirmation-1'>
              <div class='next-steps  p-4'  >
                <h3 class='text-center'>Orders</h3>
                 <section id='cart' class='cart section'>
                  <div class='container' >
                  <a class='btn btn-secondary' href='store_admin.php' aria-label='Go to Store Admin Page'>Back to Store Admin</a> <br/><br/>";
                  
                  if (count($stripe_transactions) === 0) { 
                    echo '
                        <div class="cart-item" >
                        <div class="row align-items-center gy-4">
                          <div class="col-lg-12 col-12 mb-3 mb-lg-0">
                            <div class="product-info d-flex ">
                              <div class="product-details">
                                <span class="text-secondary"> &nbsp; &nbsp; No orders found ...  </span><span>&nbsp;</span>
                                <br />
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>  
                    '; } else {
                  echo "
                     <div class='container'>
                        <div class='row gy-4'>
                          <div class='col-md-4'>
                              <a href='?id=search_order' aria-label='Search for an order'><button type='button' class='btn btn-secondary w-100'>Search Orders</button></a>
                          </div>
                          <div class='col-md-4'>
                              <a href='?id=orders_view_all' aria-label='Go to View Order Page'><button type='button' class='btn text-bg-secondary w-100'>View All Orders</button></a>
                          </div>
                          
                          <div class='col-md-4 dropdown'>
                            <button class='btn btn-secondary dropdown-toggle w-100' type='button' data-bs-toggle='dropdown' aria-expanded='false'>
                              Sort By
                            </button>
                            <ul class='dropdown-menu'>
                              <li><a class='dropdown-item' href='?id=orders_sort_by&sort=order_confirmed'>Order Confirmed</a></li>
                              <li><a class='dropdown-item' href='?id=orders_sort_by&sort=processing'>Processing</a></li>
                              <li><a class='dropdown-item' href='?id=orders_sort_by&sort=packaging'>Packaging</a></li>
                              <li><a class='dropdown-item' href='?id=orders_sort_by&sort=in_transit'>In Transit</a></li>
                              <li><a class='dropdown-item' href='?id=orders_sort_by&sort=Delivered'>Delivered</a></li>
                            </ul>
                          </div>
                          
                          
                          <br /><br /><br />
                      </div>
                   </div>
                " ;}
                
                
                  echo "
                  <div class='row g-4'>
                  <div class='col-lg-12' >
                    <div class='cart-items'>
                      <div class='cart-header d-none d-lg-block'>
                        <div class='row align-items-center gy-4'>
                        <div class='col-lg-12'>Last 50 Orders</div>
                          <div class='col-lg-2'>
                            <h5>Order #: </h5>
                          </div>
                          <div class='col-lg-4'>
                            <h5>Email</h5>
                          </div>
                          <div class='col-lg-2 '>
                            <h5>Total</h5>
                          </div>
                          <div class='col-lg-2 '>
                            <h5>Status</h5>
                          </div>
                          <div class='col-lg-2 '>
                            <h5>View</h5>
                          </div>
                        </div>
                      </div>";
            
            // if no transactions, show this
            if (count($stripe_transactions) === 0) {
                 echo "No sales yet...
                       <br /><br />
                 ";
            } else {
            // loop/show through all transactions   
            
            foreach($stripe_transactions as $t) {?> 
                      <div class="cart-item" >
                        <div class="row align-items-center gy-4">
                          <div class="col-lg-2 col-12 mb-3 mb-lg-0">
                            <span class='text-secondary'>Order #  : </span><span><?=$t->receipt_number?></span>
                          </div>
                          <div class="col-lg-4 col-12 mb-3 mb-lg-0">
                            <span class='text-secondary'>Email : </span><span><?=$t->customer_email?></span>
                          </div>
                          <div class="col-lg-2 col-12 mb-3 mb-lg-0">
                            <span class='text-secondary'>Total : </span><span>$<?=$t->amount_total/100?></span>
                          </div>
                          <div class="col-lg-2 col-12 mb-3 mb-lg-0">
                            <span class='text-secondary'>Status : </span><span>
                                <?php if($t->status == "1"){echo "Order Confirmed";}?>
                                <?php if($t->status == "2"){echo "Processing";}?>
                                <?php if($t->status == "3"){echo "Packaging";}?>
                                <?php if($t->status == "4"){echo "In Transit";}?>
                                <?php if($t->status == "5"){echo "Delivered";}?></span>
                          </div>
                           <div class="col-12 col-lg-2">
                              <a href="?id=edit_order&order_id=<?=$t->id?>" aria-label='Go to View Order Page'><button type='button' class='btn text-bg-secondary'>View Order</button></a>
                          </div>
                        </div>
                      </div><!-- End Cart Item -->
                
            <?php } } echo "</div></div></div></div></div></div>";
        }
        
//***** SEARCH ORDERS PAGE
        if($page_id == "search_order"){
            
            if(!isset($_GET["search"])){
                echo "
                 <section id='cart' class='cart section ' style='min-height: 60vh;'>
                  <div class='container' >
                  <a class='btn btn-secondary' href='store_admin.php' aria-label='Go to Store Admin Page'>Back to Store Admin</a>  <a class='btn btn-secondary' href='store_admin.php?id=orders' aria-label='Go to Store Orders Page'>Back to Orders Page</a> <br/><br/>
                  <div class='row g-4'>
                  
                  <div class='col-lg-12' >
                  
                    <div class='cart-items'>
                      <div class='cart-header d-none d-lg-block'>
                        <div class='row align-items-center gy-4'>";
                echo '
                <div class="order-confirmation-1">
                      <div class="next-steps  p-4"  >
                        <h3 class="text-center">Search Orders</h3>
                          <div class="container" > 
                              <section id="cart" class="cart section  border rounded">
                                <div class="row">
                              <div class="col-12 col-md-6 offset-md-3 text-center ">
                                <strong>Enter an Order Number, Customer Name, or Email Address</strong>
                                <form action="?" method="get">
                                    <input type="text" name="id" value="search_order" class="btn btn-primary " hidden>
                                  <div class="input-group mb-3">
                                    <input type="text" name="search" value="" class="form-control">
                                    <div class="input-group-append">
                                    <button type="submit" class="btn text-bg-secondary">Search</button>
                                    </div>
                                  </div>
                                </form>
                              </div>
                            </div>
                        </section>
                        </div></div></div></div></div></div></div></div></div>
                        ';
             } else {
                 $query = Input::get('search');
                 if (!empty($query)) {
                    $find = $db->query("SELECT * FROM simple_store_stripe_transactions WHERE (receipt_number LIKE '%$query%' OR customer_email LIKE '%$query%' OR customer_name LIKE '%$query%' )")->results();
          
          
          
                 echo "
                 <div class='order-confirmation-1'>
                          <div class='next-steps  p-4'  >
                            <h3 class='text-center'>Search Orders</h3>
                             <section id='cart' class='cart section'>
                              <div class='container' >
                                  <a class='btn btn-secondary' href='store_admin.php?id=search_order' aria-label='Go to Store Search Page'>Back to Search Page</a> <br/><br/>
                                  <div class='row g-4'>
                                  <div class='col-lg-12' >
                                    <div class='cart-items'>
                                      <div class='cart-header d-none d-lg-block'>
                                        <div class='row align-items-center gy-4'>
                                          <div class='col-lg-2'>
                                            <h5>Order #: </h5>
                                          </div>
                                          <div class='col-lg-4'>
                                            <h5>Email</h5>
                                          </div>
                                          <div class='col-lg-2 '>
                                            <h5>Total</h5>
                                          </div>
                                          <div class='col-lg-2 '>
                                            <h5>Status</h5>
                                          </div>
                                          <div class='col-lg-2 '>
                                            <h5>View</h5>
                                          </div>
                                        </div>
                                      </div>";
                                      
                                      if(count($find) == 0) {
                                          echo "No orders found...";
                                      }
                
                                    foreach($find as $t) {?> 
                                              <div class="cart-item" >
                                                <div class="row align-items-center gy-4">
                                                  <div class="col-lg-2 col-12 mb-3 mb-lg-0">
                                                    <span class='text-secondary'>Order #  : </span><span><?=$t->receipt_number?></span>
                                                  </div>
                                                  <div class="col-lg-4 col-12 mb-3 mb-lg-0">
                                                    <span class='text-secondary'>Email : </span><span><?=$t->customer_email?></span>
                                                  </div>
                                                  <div class="col-lg-2 col-12 mb-3 mb-lg-0">
                                                    <span class='text-secondary'>Total : </span><span>$<?=$t->amount_total/100?></span>
                                                  </div>
                                                  <div class="col-lg-2 col-12 mb-3 mb-lg-0">
                                                    <span class='text-secondary'>Status : </span><span>
                                                        <?php if($t->status == "1"){echo "Order Confirmed";}?>
                                                        <?php if($t->status == "2"){echo "Processing";}?>
                                                        <?php if($t->status == "3"){echo "Packaging";}?>
                                                        <?php if($t->status == "4"){echo "In Transit";}?>
                                                        <?php if($t->status == "5"){echo "Delivered";}?></span>
                                                  </div>
                                                   <div class="col-12 col-lg-2">
                                                      <a href="?id=edit_order&order_id=<?=$t->id?>" aria-label='Go to View Order Page'><button type='button' class='btn text-bg-secondary'>View Order</button></a>
                                                  </div>
                                                </div>
                                              </div><!-- End Cart Item -->
                                        
                                            <?php }
                                            echo "</div></div></div></div></div></div>";
                    } 
                                 }
                 
             
        }
        
//***** ORDER VIEW ALL PAGE
        if($page_id == "orders_view_all"){
            $stripe_transactions = $db->query("SELECT * FROM simple_store_stripe_transactions")->results(); // gets all transactions
            echo "
            <div class='order-confirmation-1'>
              <div class='next-steps  p-4'  >
                <h3 class='text-center'>Orders</h3>
                 <section id='cart' class='cart section'>
                  <div class='container' >
                  <a class='btn btn-secondary' href='store_admin.php' aria-label='Go to Store Admin Page'>Back to Store Admin</a> <br/><br/>";
                  
                  if (count($stripe_transactions) === 0) {
                   echo '
                        <div class="cart-item" >
                        <div class="row align-items-center gy-4">
                          <div class="col-lg-12 col-12 mb-3 mb-lg-0">
                            <div class="product-info d-flex ">
                              <div class="product-details">
                                <span class="text-secondary"> &nbsp; &nbsp; No orders found...  </span><span>&nbsp;</span>
                                <br />
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>  
                   '; } else {
                  echo "
                    <div class='container'>
                      <div class='row gy-4'>
                        <div class='col-md-4'>
                            <a href='?id=search_order' aria-label='Search for an order'><button type='button' class='btn btn-secondary w-100'>Search Orders</button></a>
                        </div>
                        <div class='col-md-4'>
                            <a href='?id=orders_view_all' aria-label='Go to View Order Page'><button type='button' class='btn text-bg-secondary w-100'>View All Orders</button></a>
                        </div>
                        
                        <div class='col-md-4 dropdown'>
                          <button class='btn btn-secondary dropdown-toggle w-100' type='button' data-bs-toggle='dropdown' aria-expanded='false'>
                            Sort By
                          </button>
                          <ul class='dropdown-menu'>
                            <li><a class='dropdown-item' href='?id=orders_sort_by&sort=order_confirmed'>Order Confirmed</a></li>
                            <li><a class='dropdown-item' href='?id=orders_sort_by&sort=processing'>Processing</a></li>
                            <li><a class='dropdown-item' href='?id=orders_sort_by&sort=packaging'>Packaging</a></li>
                            <li><a class='dropdown-item' href='?id=orders_sort_by&sort=in_transit'>In Transit</a></li>
                            <li><a class='dropdown-item' href='?id=orders_sort_by&sort=Delivered'>Delivered</a></li>
                          </ul>
                        </div>
                        
                        
                        <br /><br /><br />
                     </div>
                    </div>

                  " ;}
                
                
                  echo "
                  <div class='row g-4'>
                  <div class='col-lg-12' >
                    <div class='cart-items'>
                      <div class='cart-header d-none d-lg-block'>
                        <div class='row align-items-center gy-4'>
                          <div class='col-lg-2'>
                            <h5>Order #: </h5>
                          </div>
                          <div class='col-lg-4'>
                            <h5>Email</h5>
                          </div>
                          <div class='col-lg-2 '>
                            <h5>Total</h5>
                          </div>
                          <div class='col-lg-2 '>
                            <h5>Status</h5>
                          </div>
                          <div class='col-lg-2 '>
                            <h5>View</h5>
                          </div>
                        </div>
                      </div>";
            
            // if no transactions, show this
            if (count($stripe_transactions) === 0) {
                 echo "No sales yet...
                       <br /><br />
                 ";
            } else {
            // loop/show through all transactions   
            
            foreach($stripe_transactions as $t) {?> 
                      <div class="cart-item" >
                        <div class="row align-items-center gy-4">
                          <div class="col-lg-2 col-12 mb-3 mb-lg-0">
                            <span class='text-secondary'>Order #  : </span><span><?=$t->receipt_number?></span>
                          </div>
                          <div class="col-lg-4 col-12 mb-3 mb-lg-0">
                            <span class='text-secondary'>Email : </span><span><?=$t->customer_email?></span>
                          </div>
                          <div class="col-lg-2 col-12 mb-3 mb-lg-0">
                            <span class='text-secondary'>Total : </span><span>$<?=$t->amount_total/100?></span>
                          </div>
                          <div class="col-lg-2 col-12 mb-3 mb-lg-0">
                            <span class='text-secondary'>Status : </span><span>
                                <?php if($t->status == "1"){echo "Order Confirmed";}?>
                                <?php if($t->status == "2"){echo "Processing";}?>
                                <?php if($t->status == "3"){echo "Packaging";}?>
                                <?php if($t->status == "4"){echo "In Transit";}?>
                                <?php if($t->status == "5"){echo "Delivered";}?></span>
                          </div>
                           <div class="col-12 col-lg-2">
                              <a href="?id=edit_order&order_id=<?=$t->id?>" aria-label='Go to View Order Page'><button type='button' class='btn text-bg-secondary'>View Order</button></a>
                          </div>
                        </div>
                      </div><!-- End Cart Item -->
                
            <?php } } echo "</div></div></div></div></div></div>";
        }

//***** ORDERS SORT BY PAGE
        if($page_id == "orders_sort_by"){
            $stripe_transactions = $db->query("SELECT * FROM simple_store_stripe_transactions")->results(); // gets all transactions
            echo "
            <div class='order-confirmation-1'>
              <div class='next-steps  p-4'  >
                <h3 class='text-center'>Orders</h3>
                 <section id='cart' class='cart section'>
                  <div class='container' >
                  <a class='btn btn-secondary' href='store_admin.php' aria-label='Go to Store Admin Page'>Back to Store Admin</a> <br/><br/>";
                  
                  if (count($stripe_transactions) === 0) { 
                      echo '
                            <div class="cart-item" >
                                <div class="row align-items-center gy-4">
                                  <div class="col-lg-12 col-12 mb-3 mb-lg-0">
                                    <div class="product-info d-flex ">
                                      <div class="product-details">
                                        <span class="text-secondary"> &nbsp; &nbsp; No orders yet ...  </span><span>&nbsp;</span>
                                        <br />
                                      </div>
                                    </div>
                                  </div>
                                </div>
                              </div>  
                      '; } else {
                  echo "
                    <div class='container'>
                      <div class='row gy-4'>
                        <div class='col-md-4'>
                            <a href='?id=search_order' aria-label='Search for an order'><button type='button' class='btn btn-secondary w-100'>Search Orders</button></a>
                        </div>
                        <div class='col-md-4'>
                            <a href='?id=orders_view_all' aria-label='Go to View Order Page'><button type='button' class='btn text-bg-secondary w-100'>View All Orders</button></a>
                        </div>
                        
                        <div class='col-md-4 dropdown'>
                          <button class='btn btn-secondary dropdown-toggle w-100' type='button' data-bs-toggle='dropdown' aria-expanded='false'>
                            Sort By
                          </button>
                          <ul class='dropdown-menu'>
                            <li><a class='dropdown-item' href='?id=orders_sort_by&sort=order_confirmed'>Order Confirmed</a></li>
                            <li><a class='dropdown-item' href='?id=orders_sort_by&sort=processing'>Processing</a></li>
                            <li><a class='dropdown-item' href='?id=orders_sort_by&sort=packaging'>Packaging</a></li>
                            <li><a class='dropdown-item' href='?id=orders_sort_by&sort=in_transit'>In Transit</a></li>
                            <li><a class='dropdown-item' href='?id=orders_sort_by&sort=Delivered'>Delivered</a></li>
                          </ul>
                        </div>
                        
                        
                        <br /><br /><br />
                      </div>
                    </div>
                  
                  " ;}
                
                
                  echo "
                  <div class='row g-4'>
                  <div class='col-lg-12' >
                    <div class='cart-items'>
                      <div class='cart-header d-none d-lg-block'>
                        <div class='row align-items-center gy-4'>
                          <div class='col-lg-2'>
                            <h5>Order #: </h5>
                          </div>
                          <div class='col-lg-4'>
                            <h5>Email</h5>
                          </div>
                          <div class='col-lg-2 '>
                            <h5>Total</h5>
                          </div>
                          <div class='col-lg-2 '>
                            <h5>Status</h5>
                          </div>
                          <div class='col-lg-2 '>
                            <h5>View</h5>
                          </div>
                        </div>
                      </div>";
            
            // if no transactions, show this
            if (count($stripe_transactions) === 0) {
                 echo "No sales yet...
                       <br /><br />
                 ";
            } else {
            // loop/show through all transactions   
            $sort = "";   
            if(isset($_GET["sort"])){
                $sort = $_GET["sort"];   
            }  
            
            // Sort Oder Confirmed
            if($sort == "order_confirmed") {
                 $sort_transaction = $db->query("SELECT * FROM simple_store_stripe_transactions WHERE status = ? ", ['1'])->results(); // gets all transactions
                 
                 if(count($sort_transaction) == 0){
                     echo '
                        <div class="cart-item" >
                        <div class="row align-items-center gy-4">
                          <div class="col-lg-12 col-12 mb-3 mb-lg-0">
                            <div class="product-info d-flex ">
                              <div class="product-details">
                                <span class="text-secondary"> &nbsp; &nbsp; No orders under this catergory ...  </span><span>&nbsp;</span>
                                <br />
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>  
                     ';
                     
                 } else {
                     foreach($sort_transaction as $t) {?> 
                          <div class="cart-item" >
                            <div class="row align-items-center gy-4">
                              <div class="col-lg-2 col-12 mb-3 mb-lg-0">
                                <span class='text-secondary'>Order #  : </span><span><?=$t->receipt_number?></span>
                              </div>
                              <div class="col-lg-4 col-12 mb-3 mb-lg-0">
                                <span class='text-secondary'>Email : </span><span><?=$t->customer_email?></span>
                              </div>
                              <div class="col-lg-2 col-12 mb-3 mb-lg-0">
                                <span class='text-secondary'>Total : </span><span>$<?=$t->amount_total/100?></span>
                              </div>
                              <div class="col-lg-2 col-12 mb-3 mb-lg-0">
                                <span class='text-secondary'>Status : </span><span>
                                    <?php if($t->status == "1"){echo "Order Confirmed";}?>
                                    <?php if($t->status == "2"){echo "Processing";}?>
                                    <?php if($t->status == "3"){echo "Packaging";}?>
                                    <?php if($t->status == "4"){echo "In Transit";}?>
                                    <?php if($t->status == "5"){echo "Delivered";}?></span>
                              </div>
                               <div class="col-12 col-lg-2">
                                  <a href="?id=edit_order&order_id=<?=$t->id?>" aria-label='Go to View Order Page'><button type='button' class='btn text-bg-secondary'>View Order</button></a>
                              </div>
                            </div>
                          </div><!-- End Cart Item -->
                    
                     <?php }
                }
            } 
            
            // Sort Oder Confirmed
            if($sort == "processing") {
                 $sort_transaction = $db->query("SELECT * FROM simple_store_stripe_transactions WHERE status = ? ", ['2'])->results(); // gets all transactions
                 
                 if(count($sort_transaction) == 0){
                     echo '
                        <div class="cart-item" >
                        <div class="row align-items-center gy-4">
                          <div class="col-lg-12 col-12 mb-3 mb-lg-0">
                            <div class="product-info d-flex ">
                              <div class="product-details">
                                <span class="text-secondary"> &nbsp; &nbsp; No orders under this catergory ...  </span><span>&nbsp;</span>
                                <br />
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>  
                     ';
                     
                 } else {
                     foreach($sort_transaction as $t) {?> 
                          <div class="cart-item" >
                            <div class="row align-items-center gy-4">
                              <div class="col-lg-2 col-12 mb-3 mb-lg-0">
                                <span class='text-secondary'>Order #  : </span><span><?=$t->receipt_number?></span>
                              </div>
                              <div class="col-lg-4 col-12 mb-3 mb-lg-0">
                                <span class='text-secondary'>Email : </span><span><?=$t->customer_email?></span>
                              </div>
                              <div class="col-lg-2 col-12 mb-3 mb-lg-0">
                                <span class='text-secondary'>Total : </span><span>$<?=$t->amount_total/100?></span>
                              </div>
                              <div class="col-lg-2 col-12 mb-3 mb-lg-0">
                                <span class='text-secondary'>Status : </span><span>
                                    <?php if($t->status == "1"){echo "Order Confirmed";}?>
                                    <?php if($t->status == "2"){echo "Processing";}?>
                                    <?php if($t->status == "3"){echo "Packaging";}?>
                                    <?php if($t->status == "4"){echo "In Transit";}?>
                                    <?php if($t->status == "5"){echo "Delivered";}?></span>
                              </div>
                               <div class="col-12 col-lg-2">
                                  <a href="?id=edit_order&order_id=<?=$t->id?>" aria-label='Go to View Order Page'><button type='button' class='btn text-bg-secondary'>View Order</button></a>
                              </div>
                            </div>
                          </div><!-- End Cart Item -->
                    
                     <?php }
                }
            }
            
            // Sort Packaging
            if($sort == "packaging") {
                 $sort_transaction = $db->query("SELECT * FROM simple_store_stripe_transactions WHERE status = ? ", ['3'])->results(); // gets all transactions
                 
                 if(count($sort_transaction) == 0){
                     echo '
                        <div class="cart-item" >
                        <div class="row align-items-center gy-4">
                          <div class="col-lg-12 col-12 mb-3 mb-lg-0">
                            <div class="product-info d-flex ">
                              <div class="product-details">
                                <span class="text-secondary"> &nbsp; &nbsp; No orders under this catergory ...  </span><span>&nbsp;</span>
                                <br />
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>  
                     ';
                     
                 } else {
                     foreach($sort_transaction as $t) {?> 
                          <div class="cart-item" >
                            <div class="row align-items-center gy-4">
                              <div class="col-lg-2 col-12 mb-3 mb-lg-0">
                                <span class='text-secondary'>Order #  : </span><span><?=$t->receipt_number?></span>
                              </div>
                              <div class="col-lg-4 col-12 mb-3 mb-lg-0">
                                <span class='text-secondary'>Email : </span><span><?=$t->customer_email?></span>
                              </div>
                              <div class="col-lg-2 col-12 mb-3 mb-lg-0">
                                <span class='text-secondary'>Total : </span><span>$<?=$t->amount_total/100?></span>
                              </div>
                              <div class="col-lg-2 col-12 mb-3 mb-lg-0">
                                <span class='text-secondary'>Status : </span><span>
                                    <?php if($t->status == "1"){echo "Order Confirmed";}?>
                                    <?php if($t->status == "2"){echo "Processing";}?>
                                    <?php if($t->status == "3"){echo "Packaging";}?>
                                    <?php if($t->status == "4"){echo "In Transit";}?>
                                    <?php if($t->status == "5"){echo "Delivered";}?></span>
                              </div>
                               <div class="col-12 col-lg-2">
                                  <a href="?id=edit_order&order_id=<?=$t->id?>" aria-label='Go to View Order Page'><button type='button' class='btn text-bg-secondary'>View Order</button></a>
                              </div>
                            </div>
                          </div><!-- End Cart Item -->
                    
                     <?php }
                }
            }
            
            // Sort In Transit
            if($sort == "in_transit") {
                 $sort_transaction = $db->query("SELECT * FROM simple_store_stripe_transactions WHERE status = ? ", ['4'])->results(); // gets all transactions
                 
                 if(count($sort_transaction) == 0){
                     echo '
                        <div class="cart-item" >
                        <div class="row align-items-center gy-4">
                          <div class="col-lg-12 col-12 mb-3 mb-lg-0">
                            <div class="product-info d-flex ">
                              <div class="product-details">
                                <span class="text-secondary"> &nbsp; &nbsp; No orders under this catergory ...  </span><span>&nbsp;</span>
                                <br />
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>  
                     ';
                     
                 } else {
                     foreach($sort_transaction as $t) {?> 
                          <div class="cart-item" >
                            <div class="row align-items-center gy-4">
                              <div class="col-lg-2 col-12 mb-3 mb-lg-0">
                                <span class='text-secondary'>Order #  : </span><span><?=$t->receipt_number?></span>
                              </div>
                              <div class="col-lg-4 col-12 mb-3 mb-lg-0">
                                <span class='text-secondary'>Email : </span><span><?=$t->customer_email?></span>
                              </div>
                              <div class="col-lg-2 col-12 mb-3 mb-lg-0">
                                <span class='text-secondary'>Total : </span><span>$<?=$t->amount_total/100?></span>
                              </div>
                              <div class="col-lg-2 col-12 mb-3 mb-lg-0">
                                <span class='text-secondary'>Status : </span><span>
                                    <?php if($t->status == "1"){echo "Order Confirmed";}?>
                                    <?php if($t->status == "2"){echo "Processing";}?>
                                    <?php if($t->status == "3"){echo "Packaging";}?>
                                    <?php if($t->status == "4"){echo "In Transit";}?>
                                    <?php if($t->status == "5"){echo "Delivered";}?></span>
                              </div>
                               <div class="col-12 col-lg-2">
                                  <a href="?id=edit_order&order_id=<?=$t->id?>" aria-label='Go to View Order Page'><button type='button' class='btn text-bg-secondary'>View Order</button></a>
                              </div>
                            </div>
                          </div><!-- End Cart Item -->
                    
                     <?php }
                }
            }
            
            
            // Sort Delivered
            if($sort == "Delivered") {
                 $sort_transaction = $db->query("SELECT * FROM simple_store_stripe_transactions WHERE status = ? ", ['5'])->results(); // gets all transactions
                 
                 if(count($sort_transaction) == 0){
                     echo '
                        <div class="cart-item" >
                        <div class="row align-items-center gy-4">
                          <div class="col-lg-12 col-12 mb-3 mb-lg-0">
                            <div class="product-info d-flex ">
                              <div class="product-details">
                                <span class="text-secondary"> &nbsp; &nbsp; No orders under this catergory ...  </span><span>&nbsp;</span>
                                <br />
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>  
                     ';
                     
                 } else {
                     foreach($sort_transaction as $t) {?> 
                          <div class="cart-item" >
                            <div class="row align-items-center gy-4">
                              <div class="col-lg-2 col-12 mb-3 mb-lg-0">
                                <span class='text-secondary'>Order #  : </span><span><?=$t->receipt_number?></span>
                              </div>
                              <div class="col-lg-4 col-12 mb-3 mb-lg-0">
                                <span class='text-secondary'>Email : </span><span><?=$t->customer_email?></span>
                              </div>
                              <div class="col-lg-2 col-12 mb-3 mb-lg-0">
                                <span class='text-secondary'>Total : </span><span>$<?=$t->amount_total/100?></span>
                              </div>
                              <div class="col-lg-2 col-12 mb-3 mb-lg-0">
                                <span class='text-secondary'>Status : </span><span>
                                    <?php if($t->status == "1"){echo "Order Confirmed";}?>
                                    <?php if($t->status == "2"){echo "Processing";}?>
                                    <?php if($t->status == "3"){echo "Packaging";}?>
                                    <?php if($t->status == "4"){echo "In Transit";}?>
                                    <?php if($t->status == "5"){echo "Delivered";}?></span>
                              </div>
                               <div class="col-12 col-lg-2">
                                  <a href="?id=edit_order&order_id=<?=$t->id?>" aria-label='Go to View Order Page'><button type='button' class='btn text-bg-secondary'>View Order</button></a>
                              </div>
                            </div>
                          </div><!-- End Cart Item -->
                    
                     <?php }
                }
            }
            
            
            
             } echo "</div></div></div></div></div></div>";
        }
        
//***** EDIT ORDER PAGE
        if($page_id == "edit_order"){
            // checks if there is an order id, if not, redirect to orders page
            if(!isset($_GET["order_id"])){
                header("Location: store_admin.php?id=orders");
                die();
            }
            
            $order_info = $db->query("SELECT * FROM simple_store_stripe_transactions WHERE id = ?",[$_GET["order_id"]])->first();  // get order info
            $amount_total = $order_info->amount_total/100; // total amount
            $created = (date("Y-m-d h:sa",$order_info->created)); // date created unix date to regular date
            
            // set order status
            if($order_info->status == "1"){$order_status = "Order Confirmed";}
            if($order_info->status == "2"){$order_status = "Processing";}
            if($order_info->status == "3"){$order_status = "Packaging";}
            if($order_info->status == "4"){$order_status = "In Transit";}
            if($order_info->status == "5"){$order_status = "Delivered";}
            echo "
            <div class='order-confirmation-1'>
              <div class='next-steps  p-4'  >
                <h3 class='text-center'>Edit Order</h3>
                
                 <section id='cart' class='cart section'>
                  <div class='container' >
                  <a class='btn btn-secondary' href='store_admin.php' aria-label='Go to Store Admin Page'>Back to Store Admin</a> <br/><br/>
                  <div class='row g-4'>
                  <div class='col-lg-12' >
                    <div class='cart-items'>
                      <div class='cart-header d-none d-lg-block'>
                        <div class='row align-items-center gy-4'>
                          <div class='col-lg-12'>
                            <h5>Order</h5>
                          </div>
                        </div>
                      </div>
                      
                      <div class='cart-item' >
                        <div class='row'>
                          <div class='col-lg-12 col-12'>
                            <div class='product-info d-flex '>
                              <div class='product-details'>
                                <span class='text-secondary'>Session ID  : </span><input type='text' class='form-control' id='session_id' name='session_id' value='".$order_info->session_id."' disabled>
                                <br />
                                <span class='text-secondary'>Payment Intent ID  : </span><input type='text' class='form-control' id='payment_intent_id' name='payment_intent_id' value='".$order_info->payment_intent_id."' disabled>
                                <br />
                                <span class='text-secondary'>Receipt Number  : </span><input type='text' class='form-control' id='receipt_number' name='receipt_number' value='".$order_info->receipt_number."' disabled>
                                <br />
                                <span class='text-secondary'>Amount Total  : </span><input type='text' class='form-control' id='amount_total' name='amount_total' value='$".$amount_total."' disabled>
                                <br />
                                <span class='text-secondary'>Created  : </span><input type='text' class='form-control' id='created' name='created' value='".$created."' disabled>
                                <br />
                                <span class='text-secondary'>Status  : </span><input type='text' class='form-control' id='status' name='status' value='".$order_status."' disabled>
                                <br />
                                <a href='?id=next_status&order_id=".$_GET["order_id"]."' class='btn btn-success' aria-label='Change Order to next status'>Change to next status </a>
                                <br /><br />
                                <span class='text-secondary'>Customer Email  : </span><input type='text' class='form-control' id='customer_email' name='customer_email' value='".$order_info->customer_email."' disabled>
                                <br />
                                <span class='text-secondary'>Customer Name  : </span><input type='text' class='form-control' id='customer_name' name='customer_name' value='".$order_info->customer_name."' disabled>
                                <br />
                                <span class='text-secondary'>Customer Address  : </span><input type='text' class='form-control' id='customer_address' name='customer_address' value='".$order_info->address_line1." ".$order_info->address_line2.", ".$order_info->address_city." ".$order_info->address_state." ".$order_info->address_postal_code.", ".$order_info->address_country."' disabled>
                                <br />
                                <span class='text-secondary'>Payment Brand : </span><input type='text' class='form-control' id='payment_brand' name='payment_brand' value='".$order_info->payment_brand."' disabled>
                                <br />
                                <span class='text-secondary'>Payment Last 4  : </span><input type='text' class='form-control' id='payment_last4' name='payment_last4' value='".$order_info->payment_last4."' disabled>
                                <br />
                                <span class='text-secondary'>Payment Exp  : </span><input type='text' class='form-control' id='payment_exp' name='payment_exp' value='".$order_info->payment_exp_month." / ".$order_info->payment_exp_year."' disabled>
                                <br />
                                <span class='text-secondary'>Shipping Carrier  : </span><input type='text' class='form-control' id='shipping_carrier' name='shipping_carrier' value='".$order_info->shipping_carrier."' disabled>
                                <br />
                                <span class='text-secondary'>Shipping Tracking  : </span><input type='text' class='form-control' id='shipping_tracking' name='shipping_tracking' value='".$order_info->shipping_tracking."' disabled>
                                <br />
                                <a href='?id=shipping_update&order_id=".$_GET["order_id"]."' class='btn btn-success' aria-label='Go to Update Shipping information Page'>Update Shipping information </a>
                                <br /><br />
                                <a href='?id=view_ordered&order_id=".$_GET["order_id"]."' class='btn btn-success' aria-label='Go to View ordered items Page'>View ordered items</a>
                                <br />
                                <br /><br />
                              </form>
                              </div>
                            </div>
                          </div>
                          
                        
                        </div>
                      </div></div></div></div></div></div></div>";
        }
        
//***** UPDATE TO NEXT STATUS PAGE
        if($page_id == "next_status"){
            // checks if there is an order id, if not, redirect to orders page
            if(!isset($_GET["order_id"])){
                header("Location: store_admin.php?id=orders");
                die();
            }
            $order_info = $db->query("SELECT * FROM simple_store_stripe_transactions WHERE id = ?",[$_GET["order_id"]])->first(); // get order info
            // update order status math
            $numm = $order_info->status;
            $new_num = $numm+1;
            if(isset($_GET["confirm"])){
                // checks if they want to confirm
                if($_GET["confirm"] == "true"){
                    
                //preps fields for database 
                $fields_1 = [
                    'transaction_id' => $_GET['order_id'],
                    'transaction_status' => $new_num,
                    'transaction_status_date' => time()
                ];
                $result_1 = $db->insert('simple_store_transactions_status', $fields_1); // inserts into database   
                
                //preps fields for database    
                $fields_2 = [
                    'status' => $new_num,
                ];
                $result_2 = $db->update('simple_store_stripe_transactions', $_GET["order_id"] , $fields_2); //updates database
                header("Location: store_admin.php?id=next_status&order_id=".$_GET["order_id"]); // redirects to next status page  
                die();  
                }
            }
            
            $created = (date("Y-m-d h:sa",$order_info->created)); // sets created variable
            
            // sets status variables
            if($order_info->status == "1"){$order_status = "Order Confirmed";}
            if($order_info->status == "2"){$order_status = "Processing";}
            if($order_info->status == "3"){$order_status = "Packaging";}
            if($order_info->status == "4"){$order_status = "In Transit";}
            if($order_info->status == "5"){$order_status = "Delivered";}
            
            echo "
            <div class='order-confirmation-1'>
              <div class='next-steps  p-4'  >
                <h3 class='text-center'>Edit Order</h3>
                
                 <section id='cart' class='cart section'>
                  <div class='container' >
                  <a class='btn btn-secondary' href='store_admin.php' aria-label='Go to Store Admin Page'>Back to Store Admin</a> <br/><br/>
                  <a class='btn btn-secondary' href='?id=edit_order&order_id=".$_GET["order_id"]."' aria-label='Go Back to Order Page'>Back to order</a>
                  <br /><br />
                  <div class='row g-4'>
                  <div class='col-lg-12' >
                    <div class='cart-items'>
                      <div class='cart-header d-none d-lg-block'>
                        <div class='row align-items-center gy-4'>
                          <div class='col-lg-12'>
                            <h5>Order</h5>
                          </div>
                        </div>
                      </div>";
                      ?>
                      <div class="account ">
                        <div class="content-area ">
                            <div class="orders-grid ">
                                <!-- Order Card 1 -->
                                <div class="order-card " >
                                    <h4>Tracking Summary</h4>
                               
                                  <!-- Order Tracking -->
                                  <div class=" tracking-info" id="tracking1">
                                    <div class="tracking-timeline">
                                        
                                      <div class="timeline-item completed">
                                        <div class="timeline-icon">
                                          <i class="bi bi-check-circle-fill"></i>
                                        </div>
                                        <div class="timeline-content ">
                                          <h5>Order Confirmed</h5>
                                          <p>Your order has been received and confirmed</p>
                                          <span class="timeline-date"><?=(date("Y-m-d h:sa",$order_info->created)); ?> </span>
                                        </div>
                                      </div>
                                
                                      <?php $order_status_2 = "";?>
                                      <div class="timeline-item <?php if($order_info->status >=2){$order_status2 = $db->query("SELECT * FROM simple_store_transactions_status WHERE transaction_id = ? AND transaction_status = ?",[$order_info->id, 2])->first(); $order_status_2 = $order_status2->transaction_status_date;   echo "completed";}?>">
                                        <div class="timeline-icon  ">
                                          <i class="bi bi-scissors"></i>
                                        </div>
                                        <div class="timeline-content ">
                                          <h5>Processing</h5>
                                          <p>Your order is being made</p>
                                          <span class="timeline-date"><? if($order_status_2 == ""){echo "";} else { echo (date("Y-m-d h:sa",$order_status_2));}?></span>
                                        </div>
                                      </div>
            
                                      <?php $order_status_3 = "";?>                                    
                                      <div class="timeline-item <?php if($order_info->status >=3){$order_status3 = $db->query("SELECT * FROM simple_store_transactions_status WHERE transaction_id = ? AND transaction_status = ?",[$order_info->id, 3])->first(); $order_status_3 = $order_status3->transaction_status_date;   echo "completed";}?>">
                                        <div class="timeline-icon">
                                          <i class="bi bi-box-seam"></i>
                                        </div>
                                        <div class="timeline-content">
                                          <h5>Packaging</h5>
                                          <p>Your items are being packaged for shipping</p>
                                          <span class="timeline-date"><? if($order_status_3 == ""){echo "";} else { echo (date("Y-m-d h:sa",$order_status_3));}?></span>
                                        </div>
                                      </div>
            
                                      <?php $order_status_4 = "";?>
                                      <div class="timeline-item <?php if($order_info->status >=4){$order_status4 = $db->query("SELECT * FROM simple_store_transactions_status WHERE transaction_id = ? AND transaction_status = ?",[$order_info->id, 4])->first(); $order_status_4 = $order_status4->transaction_status_date;   echo "completed";}?>">
                                        <div class="timeline-icon">
                                          <i class="bi bi-truck"></i>
                                        </div>
                                        <div class="timeline-content">
                                          <h5>In Transit</h5>
                                          <p>Your package is on the way</p>
                                          <span class="timeline-date"><? if($order_status_4 == ""){echo "";} else { echo (date("Y-m-d h:sa",$order_status_4));}?></span>
                                        </div>
                                      </div>
            
                                      <?php $order_status_5 = "";?>
                                      <div class="timeline-item <?php if($order_info->status >=5){$order_status5 = $db->query("SELECT * FROM simple_store_transactions_status WHERE transaction_id = ? AND transaction_status = ?",[$order_info->id, 5])->first(); $order_status_5 = $order_status5->transaction_status_date;   echo "completed";}?>">
                                        <div class="timeline-icon">
                                          <i class="bi bi-house-door"></i>
                                        </div>
                                        <div class="timeline-content">
                                          <h5>Delivery</h5>
                                          <p>Your package has been delivered</p>
                                          <span class="timeline-date"><? if($order_status_5 == ""){echo "";} else { echo (date("Y-m-d h:sa",$order_status_5));}?></span>
                                        </div>
                                      </div>
                                    </div>
                                  </div>
                                </div>
 
                            </div>
                        </div>
                    </div>
                    
                    <a class="btn btn-secondary" href="?id=next_status&order_id=<?=$_GET["order_id"]?>&confirm=true" aria-label='Update to next staus'>Update to next status </a>
                    
                    </div></div></div></div></div></div>
        <?php }
        
//***** SHIPPING UPDATE PAGE
        if($page_id == "shipping_update"){
            
            
            
            // checks if there is an order id, if not, redirect to orders page
            if(!isset($_GET["order_id"])){
                header("Location: store_admin.php?id=orders");
                die();
            }
            $order_info = $db->query("SELECT * FROM simple_store_stripe_transactions WHERE id = ?",[$_GET["order_id"]])->first(); // gets order info
            $add_confirm = ""; 
            
            // checks for form post
            if(isset($_GET["post"])){
                $add_confirm = $_GET["post"];   
            }
             if($add_confirm == "add"){
                // prepares fields for database 
                $fields = [
                    'shipping_carrier' => $_POST['shipping_carrier'],
                    'shipping_tracking' => $_POST['shipping_tracking']
                ];
                $result = $db->update('simple_store_stripe_transactions', $_GET["order_id"], $fields); // updates database
                
                
                
##### Send email    

                

                $contact_email = $db->query("SELECT * FROM simple_store_settings ")->first(); // get store contact info 
                $brevo_check = $db->query("SELECT * FROM simple_store_brevo")->first(); // get keys
                
                
                
                $client = new Brevo($brevo_check->brevo_key);
                
                $result = $client->transactionalEmails->sendTransacEmail(
                    new SendTransacEmailRequest([
                        'subject' => $settings->site_name.' - Your Order Has Been Shipped!',
                        'htmlContent' => '
                                <html lang="en">
                                  <head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, inital-scale=1.0"><title>{{params.siteName}} - Your Order Has Been Shipped!</title><link href="https://{{params.domainName}}/usersc/plugins/simple_store/assets/template/css/stylesheet.css" rel="stylesheet" type="text/css">
                                    <style>
                                            @font-face {
                                            font-family: "OneLittleFontRegular";
                                            font-style: normal;
                                            font-weight: 400;
                                            src: url(https://{{params.domainName}}/usersc/plugins/simple_store/assets/template/css/one_little_font_regular-webfont.woff2) format("woff2");
                                            }
                                             div, p, body, button { font-family: "OneLittleFontRegular", Verdana, Helvetica, sans-serif !important; }
                                
                                            body{
                                                font-family:  "OneLittleFontRegular" , sans-serif;
                                            }
                                      .email-container {
                                        max-width: 600px;
                                                font-family:  "OneLittleFontRegular", sans-serif;
                                      }
                                      .hero-content {
                                        background: #ECF1FB;
                                        border-radius: 5px;
                                      }
                                            .hero-container {
                                                width: 90%;
                                            }
                                            .order-list {
                                                background: #ECF1FB;
                                        border-radius: 5px;
                                            }
                                            .order-list table {
                                                background: #ffffff;
                                                border-radius: 5px;
                                            }
                                            .order-information {
                                                background: #ECF1FB;
                                        border-radius: 5px;
                                            }
                                            .contact-email {
                                                background: #ECF1FB;
                                        border-radius: 5px;
                                            }
                                            .socials a {
                                                text-decoration: none;
                                                color:#000000;
                                            }
                                            .footer {
                                                background: #ECF1FB;
                                        border-radius: 5px;
                                            }
                                            .footer-container {
                                                padding-top: 10px;
                                                padding-bottom: 10px;
                                            }
                                            .product-image {
                                                border-radius: 10px;
                                            }
                                            button {
                                                border-radius: 5px;
                                                border: none;
                                                background-color: #0067FF;
                                                color:#ffffff;
                                                padding: 15px 32px;
                                                text-align: center;
                                                text-decoration: none;
                                                display: inline-block;
                                            }
                                    </style>
                                    </head>
                                  <body>
                                
                                  <center>
                                    <div class="email-container">
                                      <div class="header">
                                                <br />
                                        <a href="https://{{params.domainName}}" class="logo"><img width="110" height="110" src="https://{{params.domainName}}/{{params.brevoImage}}"></a>
                                                <br /><br />
                                      </div>
                                      <div width="100%" class="hero-content">
                                                <div class="hero-container" >
                                                    <img src="">
                                                    <p class="title"> Your order has been shipped!</p>
                                                    <p>Great news! Your order has been shipped! Check below for the tracking information!</p>
                                                    <br />
                                                    <p><a href="https://www.aftership.com/track/{{params.orderTracking}}"><button>Track your order</button></a></p>
                                                    
                                                    <br />
                                                </div>
                                      </div>
                                            <br />
                                            <div width="100%" class="order-list">
                                                <br />
                                                <p class="title">Shipping information</p>
                                                <p>Order number : #{{params.orderNumber}}</p>
                                                <br />
                                                <p>Shipping Carrier : {{params.orderCarrier}}</p>
                                                <br />
                                                <p>Tracking Number : <a href="https://www.aftership.com/track/{{params.orderTracking}}"> {{params.orderTracking}}</a></p>
                                               
                                                <br />
                                            </div>
                                            <br />
                                            <div width="100%" class="order-information">
                                             <br />
                                                <table  width="90%">
                                                    <tr>
                                                        <td ><strong>Order Information</strong></td>
                                                    </tr>
                                                    <tr>
                                                        <td>Order Number : # {{params.orderNumber}}</td>
                                                    </tr>
                                                    <tr>
                                                        <td ><strong>Shipping Address</strong></td>
                                                    </tr>
                                                    <tr>
                                                        <td>{{params.addressLine1}}</td>
                                                    </tr>
                                                    <tr>
                                                        <td>{{params.addressLine2}}</td>
                                                    </tr>
                                                    <tr><td> {{params.addressCity}}, {{params.addressState}} {{params.addressPostalCode}} </td></tr>
                                                </table>    <br /> <br /> 
                                            </div>
                                            <br />
                                            
                                            <div class="contact">
                                                <div class="contact-email">
                                                <br />
                                                <p><strong>Problems with your order?</strong></p>
                                                <p>Contact us</p>
                                                <p>{{params.contactEmail}}</p>
                                                <br />
                                                </div>
                                            </div>
                                            <br />
                                            
                                            <div class="socials">
                                                
                                                <a href=""  style="margin-right: 10px">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-instagram" viewBox="0 0 16 16" >
                                                  <path d="M8 0C5.829 0 5.556.01 4.703.048 3.85.088 3.269.222 2.76.42a3.9 3.9 0 0 0-1.417.923A3.9 3.9 0 0 0 .42 2.76C.222 3.268.087 3.85.048 4.7.01 5.555 0 5.827 0 8.001c0 2.172.01 2.444.048 3.297.04.852.174 1.433.372 1.942.205.526.478.972.923 1.417.444.445.89.719 1.416.923.51.198 1.09.333 1.942.372C5.555 15.99 5.827 16 8 16s2.444-.01 3.298-.048c.851-.04 1.434-.174 1.943-.372a3.9 3.9 0 0 0 1.416-.923c.445-.445.718-.891.923-1.417.197-.509.332-1.09.372-1.942C15.99 10.445 16 10.173 16 8s-.01-2.445-.048-3.299c-.04-.851-.175-1.433-.372-1.941a3.9 3.9 0 0 0-.923-1.417A3.9 3.9 0 0 0 13.24.42c-.51-.198-1.092-.333-1.943-.372C10.443.01 10.172 0 7.998 0zm-.717 1.442h.718c2.136 0 2.389.007 3.232.046.78.035 1.204.166 1.486.275.373.145.64.319.92.599s.453.546.598.92c.11.281.24.705.275 1.485.039.843.047 1.096.047 3.231s-.008 2.389-.047 3.232c-.035.78-.166 1.203-.275 1.485a2.5 2.5 0 0 1-.599.919c-.28.28-.546.453-.92.598-.28.11-.704.24-1.485.276-.843.038-1.096.047-3.232.047s-2.39-.009-3.233-.047c-.78-.036-1.203-.166-1.485-.276a2.5 2.5 0 0 1-.92-.598 2.5 2.5 0 0 1-.6-.92c-.109-.281-.24-.705-.275-1.485-.038-.843-.046-1.096-.046-3.233s.008-2.388.046-3.231c.036-.78.166-1.204.276-1.486.145-.373.319-.64.599-.92s.546-.453.92-.598c.282-.11.705-.24 1.485-.276.738-.034 1.024-.044 2.515-.045zm4.988 1.328a.96.96 0 1 0 0 1.92.96.96 0 0 0 0-1.92m-4.27 1.122a4.109 4.109 0 1 0 0 8.217 4.109 4.109 0 0 0 0-8.217m0 1.441a2.667 2.667 0 1 1 0 5.334 2.667 2.667 0 0 1 0-5.334"/>
                                                </svg>
                                                </a>    
                                                
                                                <a href="" style="margin-right: 10px">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-tiktok" viewBox="0 0 16 16">
                                                  <path d="M9 0h1.98c.144.715.54 1.617 1.235 2.512C12.895 3.389 13.797 4 15 4v2c-1.753 0-3.07-.814-4-1.829V11a5 5 0 1 1-5-5v2a3 3 0 1 0 3 3z"/>
                                                </svg>
                                                </a>
                                
                                                <a href="" >
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-youtube" viewBox="0 0 16 16">
                                                  <path d="M8.051 1.999h.089c.822.003 4.987.033 6.11.335a2.01 2.01 0 0 1 1.415 1.42c.101.38.172.883.22 1.402l.01.104.022.26.008.104c.065.914.073 1.77.074 1.957v.075c-.001.194-.01 1.108-.082 2.06l-.008.105-.009.104c-.05.572-.124 1.14-.235 1.558a2.01 2.01 0 0 1-1.415 1.42c-1.16.312-5.569.334-6.18.335h-.142c-.309 0-1.587-.006-2.927-.052l-.17-.006-.087-.004-.171-.007-.171-.007c-1.11-.049-2.167-.128-2.654-.26a2.01 2.01 0 0 1-1.415-1.419c-.111-.417-.185-.986-.235-1.558L.09 9.82l-.008-.104A31 31 0 0 1 0 7.68v-.123c.002-.215.01-.958.064-1.778l.007-.103.003-.052.008-.104.022-.26.01-.104c.048-.519.119-1.023.22-1.402a2.01 2.01 0 0 1 1.415-1.42c.487-.13 1.544-.21 2.654-.26l.17-.007.172-.006.086-.003.171-.007A100 100 0 0 1 7.858 2zM6.4 5.209v4.818l4.157-2.408z"/>
                                                </svg>
                                                </a>
                                                
                                            </div>
                                            <div class="footer">
                                                <div class="footer-container">
                                                Copyright © 2025 {{params.siteName}} All Rights Reserved
                                                </div>
                                            </div>
                                            
                                    </div>  
                                  </center>
                                  </body>
                                </html>
                        ',
                        'sender' => new SendTransacEmailRequestSender([
                            'name' => $settings->site_name,
                            'email' => $brevo_check->sender_email,
                        ]),
                        'to' => [
                            new SendTransacEmailRequestToItem([
                                'email' => $order_info->customer_email,
                                'name' => $order_info->customer_name,
                            ]),
                        ],
                        'params' => [
                            'siteName' => $settings->site_name, 
                            'orderNumber' => $order_info->receipt_number, 
                            'orderCarrier' => $_POST['shipping_carrier'], 
                            'orderTracking' =>$_POST['shipping_tracking'], 
                            'addressLine1' => $order_info->address_line1, 
                            'addressLine2' => $order_info->address_line2, 
                            'addressCity' => $order_info->address_city, 
                            'addressState' => $order_info->address_state, 
                            'addressPostalCode' => $order_info->address_postal_code, 
                            'contactEmail' => $contact_email->contact_email,  
                            'domainName' => $_SERVER['SERVER_NAME'], 
                            'brevoImage' =>$brevo_check->brevo_image,],
                    ])
                );
    
## End send email    
                
                header("Location: store_admin.php?id=edit_order&order_id=".$_GET["order_id"]); // redirects to edit order page
                die();
             }
            
            echo "
            <div class='order-confirmation-1'>
              <div class='next-steps  p-4'  >
                <h3 class='text-center'>Update Shipping Information</h3>
                
                 <section id='cart' class='cart section'>
                  <div class='container' >
                  <a class='btn btn-secondary' href='store_admin.php' aria-label='Go to Store Admin Page'>Back to Store Admin</a> <br/><br/>
                  <div class='row g-4'>
                  <div class='col-lg-12' >
                    <div class='cart-items'>
                      <div class='cart-header d-none d-lg-block'>
                        <div class='row align-items-center gy-4'>
                          <div class='col-lg-12'>
                            <h5>Shipping</h5>
                          </div>
                        </div>
                      </div>
                      
                      <div class='cart-item' >
                        <div class='row'>
                          <div class='col-lg-12 col-12'>
                            <div class='product-info d-flex '>
                              <div class='product-details'>
                              <form method='POST' action='?id=shipping_update&order_id=".$_GET["order_id"]."&post=add'>
                                <span class='text-secondary'>Shipping Carrier  : </span><input type='text' class='form-control' id='shipping_carrier' name='shipping_carrier' value='".$order_info->shipping_carrier."' required></span>
                                <br />
                                <span class='text-secondary'>Shipping Tracking  : </span><input type='text' class='form-control' id='shipping_tracking' name='shipping_tracking' value='".$order_info->shipping_tracking."' required></span>
                                <br /> 
                                <button type='submit' name='prod_details_form' value='prod_details_form' class='btn btn-success border rounded'>Update</button>
                                <br /><br />
                              </form>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div></div></div></div></div></div></div></div></div>";
        }
        
//***** VIEW ORDERED PAGE
        if($page_id == "view_ordered"){
            // checks if order_id isset, if not, redirect to orders page
            if(!isset($_GET["order_id"])){
                header("Location: store_admin.php?id=orders");
                die();
            } 
            $store = $db->query("SELECT * FROM simple_store_settings")->first(); // get store settings
            $order_info = $db->query("SELECT * FROM simple_store_stripe_transactions WHERE id = ?",[$_GET["order_id"]])->first(); // get order info
            ?>
            
                <h3 class='text-center'>Ordered Items</h3>
                
                 <section id='cart' class='cart section'>
                  <div class='container order-confirmation' >
                  <a class='btn btn-secondary' href='?id=edit_order&order_id=<?=$_GET["order_id"]?>' aria-label='Go Back to order'>Back to order</a> <br/><br/>
                  <div class='row g-4 main-content'>
                  <div class='col-lg-12 details-card' >
                    <div class='card-body'>
                      
                                  
                        
                        
                        <form method="post" action="pdf.php">
                        <input class="d-none" type="text"  name="site_name" value="<?=$_SERVER['SERVER_NAME']?>" hidden>
                        <input class="d-none" type="text"  name="contact_email" value="<?=$store->contact_email?>" hidden>
                        
                        <input class="d-none" type="text"  name="order_number" value="<?=$order_info->receipt_number?>" hidden>
                        <input class="d-none" type="text"  name="order_date" value="<?=date("Y-m-d", $order_info->created)?>" hidden>
                        
                        <input class="d-none" type="text"  name="customer_name" value="<?=$order_info->customer_name?>" hidden>
                        <input class="d-none" type="text"  name="customer_email" value="<?=$order_info->customer_email?>" hidden>
                        <input class="d-none" type="text"  name="customer_address_1" value="<?=$order_info->address_line1?> <?=$order_info->address_line2?>," hidden>
                        <input class="d-none" type="text"  name="customer_address_2" value="<?=$order_info->address_city?> <?=$order_info->address_state?> <?=$order_info->address_postal_code?>" hidden>
                        <?php 
                        
                        $items = $db->query("SELECT * FROM simple_store_transactions_item WHERE checkout_session_id = ? ", [$order_info->session_id])->results(); 
                        $prod_array = "0";
                        
                        // loops/shows all products ordered
                        foreach($items as $productInfo){
                            $product_local_variant = $db->query("SELECT * FROM simple_store_products_variants WHERE price_id = ?",[$productInfo->price_id])->first(); // get product info
                            $product_local = $db->query("SELECT * FROM simple_store_products WHERE id = ?",[$product_local_variant->product_id])->first(); // get product info
                            $product_img = $db->query("SELECT * FROM simple_store_products_images WHERE product_id = ? AND is_primary = ?",[$product_local->id, "1"])->first(); // get product info
                            
                         ?>
                         <div class="item">
                              <div class="item-image">
                                <img src="<?=$product_img->image?>" alt="Product" loading="lazy">
                              </div>
                              <div class="item-details">
                                <h4><?=$product_local->name?></h4>
                                <div class="item-meta">
                                  <span class="">Color: <?=$product_local->color?></span>
                                  <span>Size: <?=$product_local_variant->size?></span>
                                </div>
                                <div class="item-price">
                                  <span class="quantity"><?=$productInfo->qty?> ×</span>
                                  <span class="price">$ <?=$product_local->price?></span>
                                </div>
                              </div>
                            </div>
                            
                        <input class="d-none" type="text"  name="product[<?=$prod_array?>][name]"  value="<?=$product_local->name?>" hidden>
                        <input class="d-none" type="text" name="product[<?=$prod_array?>][quanity]"  value="<?=$productInfo->qty?>" hidden>
                        <?php $prod_array = $prod_array + 1; ?>  
                       
                      <?php } ?>
                      <br />
                      <button type="submit" class="btn border rounded">View Ordered in PDF</button>
                      </form>
                    </div> </div> </div></div></div></div></div></div></div>
        <?php }
        

        
//***** SETTINGS PAGE
        if($page_id == "settings"){
            $store = $db->query("SELECT * FROM simple_store_settings")->first(); // get store settings
            
            // checks if store live variables
            if($store->live == "1"){$live_setting = "Live"; $live_btn = "<i class='bi bi-check-circle-fill text-success'></i>"; $live_op = "Go Offline"; $live_btn_op = "danger"; }
            if($store->live == "0"){$live_setting = "Offline"; $live_btn = "<i class='bi bi-x-circle-fill text-danger'></i>"; $live_op = "Go Live"; $live_btn_op = "success";}
            
            // checks if using live/sandbox keys
            $store_keys = $db->query("SELECT * FROM simple_store_stripe_keys")->first(); // get stripe keys
            if($store_keys->is_live == "1"){$live_key_btn = "<i class='bi bi-check-circle-fill text-success'></i>"; $live_key_value = "Live Keys"; }
            if($store_keys->is_live == "0"){$live_key_btn = "<i class='bi bi-x-circle-fill text-danger'></i>"; $live_key_value = "Sandbox Keys";}
            
            // checks and changes live/offline modes 
            if(isset($_GET["live"])){ if($_GET["live"] == "change"){
                if($store->live == "0"){$live_change = "1";}
                if($store->live == "1"){$live_change = "0";}
                $fields = ['live' => $live_change];
                $result = $db->update('simple_store_settings',1 , $fields); 
                header("Location: store_admin.php?id=settings"); 
                die();
            }}
            
            // checks for form post for general information
            if(isset($_POST)){
                if(isset($_GET["post"])){  if($_GET["post"] == "confirm"){
                // prepares database fields    
                $fields = [
                    'contact_email' => $_POST["contact_email"],
                    'facebook' => $_POST["facebook"],
                    'instagram' => $_POST["instagram"],
                    'tiktok' => $_POST["tiktok"],
                    'youtube' => $_POST["youtube"],
                    ];
                $result = $db->update('simple_store_settings',1 , $fields); // updates database
                header("Location: store_admin.php?id=settings"); // redirects to settings page        
                die();
                    
                }}
            }
            ?>
            <div class='order-confirmation-1'>
              <div class='next-steps  p-4'  >
                <h3 class='text-center'>General settings</h3>
                
                 <section id='cart' class='cart section'>
                  <div class='container' >
                  <a class='btn btn-secondary' href='store_admin.php' aria-label='Go to Store Admin Page'>Back to Store Admin</a> <br/><br/>
                  <div class='row g-4'>
                  <div class='col-lg-12' >
                    <div class='cart-items'>
                      <div class='cart-header d-none d-lg-block'>
                        <div class='row align-items-center gy-4'>
                          <div class='col-lg-12'>
                            <h5>settings</h5>
                          </div>
                        </div>
                      </div>    
                      
                      <div class="cart-item" >
                        <div class="row align-items-center gy-4">
                          <div class="col-lg-10 col-12 mb-3 mb-lg-0">
                            <div class="product-info d-flex ">
                              <div class="product-details">
                                <span class='text-secondary'> &nbsp; &nbsp; Your store is currently : <?=$live_setting?></span><span>&nbsp; <?=$live_btn?></span>
                                <br />
                              </div>
                            </div>
                          </div>
                           <div class="col-12 col-lg-2">
                              <a href="?id=settings&live=change" aria-label='Change Live Setting'><button type='button' class='btn btn-<?=$live_btn_op?> form-control' ><?=$live_op?></button></a>
                          </div>
                        </div>
                      </div>
                      
                      <div class="cart-item" >
                        <div class="row align-items-center gy-4">
                          <div class="col-lg-10 col-12 mb-3 mb-lg-0">
                            <div class="product-info d-flex ">
                              <div class="product-details">
                                <span class='text-secondary'> &nbsp; &nbsp; Your store is currently using: <?=$live_key_value?> </span><span><?=$live_key_btn?></span>
                                <br />
                              </div>
                            </div>
                          </div>
                           <div class="col-12 col-lg-2">
                              <a href="?id=keys" aria-label='Go to Keys Page' ><button type='button' class='btn btn-secondary form-control'>Go to Keys page</button></a>
                          </div>
                        </div>
                      </div>
                        
                       
                       <form method='POST' action='?id=settings&post=confirm'>
                           <br />
                       <span class='text-secondary'>Contact Email  : </span><input type='text' class='form-control' id='contact_email' name='contact_email' value='<?=$store->contact_email?>' ></span>
                       <br />
                       <span class='text-secondary'>Facebook  : </span><input type='text' class='form-control' id='facebook' name='facebook' value='<?=$store->facebook?>' ></span>
                       <br />
                       <span class='text-secondary'>Instagram  : </span><input type='text' class='form-control' id='instagram' name='instagram' value='<?=$store->instagram?>' ></span>
                       <br />
                       <span class='text-secondary'>Tiktok  : </span><input type='text' class='form-control' id='tiktok' name='tiktok' value='<?=$store->tiktok?>' ></span>
                       <br />
                       <span class='text-secondary'>Youtube  : </span><input type='text' class='form-control' id='youtube' name='youtube' value='<?=$store->youtube?>' ></span>
                       <br />
                       <button type="submit" style="min-width:100px; max-width:150px;" class="btn btn-secondary">Update</button>
                       </form>            
                       
                       </div></div></div></div></div></div></div>
        <?php }?>
        </div>              
        
 
<? require_once 'usersc/plugins/simple_store/assets/template/'.'views/footer.php'; //Footer ?> 
