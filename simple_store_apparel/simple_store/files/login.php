<?php
// This is a user-facing page
/*
UserSpice 5
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
ini_set("allow_url_fopen", 1);
require_once '../users/init.php';


// create cart session if one isnt available
session_start(); 
if(!isset($_SESSION['cart'])){
   $_SESSION['cart'] = [];
} 
$siginin_fail = 0;

$hooks =  getMyHooks();
includeHook($hooks, 'pre');
$emailSet = $db->query("SELECT * FROM email")->first();
if (!isset($settings->no_passwords)) {
  $settings->no_passwords = 0;
}
if ($emailSet->email_login == "yourEmail@gmail.com" || $emailSet->email_login == "" || $emailSet->email_pass == "1234" || $settings->no_passwords == 1) {
  $showForgot = false;
} else {
  $showForgot = true;
}

if ($settings->no_passwords == 1) {
  $topPad = "";
} else {
  // $topPad = " py-2 p-md-5";
  $topPad = "";
}
if ($showForgot == true && $settings->registration == 1) {
  $bottomClass = "col-12 col-lg-6";
  $showBottom = true;
  $forgotClass = "";
  $regClass = "text-end";
} elseif ($showForgot == true || $settings->registration == 1) {
  $showBottom = true;
  $bottomClass = "col-12";
  $forgotClass = "text-center";
  $regClass = "text-end";
} else {
  $showBottom = false;
}

$errors = $successes = [];
if (Input::get('err') != '') {
  $errors[] = Input::get('err');
}

if ($user->isLoggedIn()) {
  Redirect::to($us_url_root . $settings->redirect_uri_after_login);
}

if (!empty($_POST)) {
  $token = Input::get('csrf');
  if (!Token::check($token)) {
    include($abs_us_root . $us_url_root . 'usersc/scripts/token_error.php');
  }

  $validate = new Validate();
  $validation = $validate->check(
    $_POST,
    array(
      'username' => array('display' => lang('GEN_UNAME'), 'required' => true),
      'password' => array('display' => lang('GEN_PASS'), 'required' => true)
    )
  );
  $validated = $validation->passed();
  // Set $validated to False to kill validation, or run additional checks, in your post hook
  $username = Input::get('username');
  $password = trim(Input::get('password'));
  $remember = false;
  includeHook($hooks, 'post');

  if ($validated) {
    //Log user in
    $user = new User();
    $rawpassword = $_POST['password'];
    $login = $user->loginEmail($username, $password, $remember, $rawpassword);
    if ($login) {
      $hooks =  getMyHooks(['page' => 'loginSuccess']);
      includeHook($hooks, 'body');
      $dest = sanitizedDest('dest');
      # if user was attempting to get to a page before login, go there
      $_SESSION['last_confirm'] = date("Y-m-d H:i:s");

      if (!empty($dest)) {
        $redirect = Input::get('redirect');
        if (!empty($redirect) || $redirect !== '') Redirect::to(html_entity_decode($redirect));
        else Redirect::to($dest);
      } elseif (file_exists($abs_us_root . $us_url_root . 'usersc/scripts/custom_login_script.php')) {

        # if site has custom login script, use it
        # Note that the custom_login_script.php normally contains a Redirect::to() call
        require_once $abs_us_root . $us_url_root . 'usersc/scripts/custom_login_script.php';
      } else {
        if (($dest = Config::get('homepage')) ||
          ($dest = 'account.php')
        ) {
          Redirect::to($dest);
        }
      }
    } else {
      $eventhooks =  getMyHooks(['page' => 'loginFail']);
      includeHook($eventhooks, 'body');
      logger("0", "Login Fail", "A failed login on login.php");
      $msg = lang("SIGNIN_FAIL");
      $msg2 = lang("SIGNIN_PLEASE_CHK");
      $errors[] = '<strong>' . $msg . '</strong>' . $msg2;
      $siginin_fail = 1;
    }
  } else {
    $errors = $validation->errors();
    
  }
  sessionValMessages($errors, $successes, NULL);
}
if (empty($dest = sanitizedDest('dest'))) {
  $dest = '';
}
?>

<? require_once '../usersc/plugins/simple_store/assets/template/'.'views/header.php'; //Header ?> 




<!-- Login Section -->
    <section id="login" class="login section" style="min-height: 68vh;">

      <div class="container" data-aos="fade-up" data-aos-delay="100">
      <br /><br /><br />
        <div class="row justify-content-center">
          <div class="col-lg-8 col-md-10">
            <div class="auth-container" data-aos="fade-in" data-aos-delay="200">
    
              <!-- Login Form -->
              <div class="auth-form login-form active">
                <div class="form-header">
                
                  <h3><?= lang("SIGNIN_TITLE", ""); ?></h3>
                  <p>Sign in to your account</p>
                </div>
                
                <div class="usmsgblock">
                    <?php if($siginin_fail == 1){
                        echo '
                           <div class="alert alert-danger text-center" role="alert">
                              ** FAILED LOGIN **
                              <br />
                              Please check your username and password and try again
                            </div>
                        ';
                    }
                    ?>
                    <?php
                    $usmsgs = array(
                      'err',    //url err= messages
                      'msg',    //urk msg= messages
                      'valSuc', //Validation class success messages
                      'valErr', //Validation class error messages
                      'genMsg', //misc messages
                    );
                    foreach ($usmsgs as $u) { ?>
                      <div style="" id="<?= $u ?>UserSpiceMessages" class="show d-none">
                        <span id="<?= $u ?>UserSpiceMessage"></span>
                        <button type="button" class="close btn-close" data-dismiss="alert" data-bs-dismiss="alert" aria-label="Close"></button>
                      </div>
                    <?php } ?>
                </div>
                  
                <?php
                 includeHook($hooks, 'body');
                 if ($settings->no_passwords == 0) {
                ?>

                <form name="login" id="login-form" class="auth-form-content" method="post">
                  <?= tokenHere(); ?>
                
                  <div class="input-group mb-3">
                    <span class="input-icon">
                      <i class="bi bi-envelope"></i>
                    </span>
                    <label class="form-label" for="username" hidden><?= lang("SIGNIN_UORE") ?></label>
                    <input type="text" class="form-control" id="username" name="username" placeholder="<?= lang("SIGNIN_UORE") ?>" required autocomplete="username">
                  </div>

                  <div class="input-group mb-3">
                    <span class="input-icon">
                      <i class="bi bi-lock"></i>
                    </span>
                    <label class="form-label" for="password" hidden><?= lang("SIGNIN_PASS") ?></label>
                    <input type="password" class="form-control" placeholder="Password" required id="password" name="password" autocomplete="password">
                    <span class="password-toggle">
                      <i class="bi bi-eye"></i>
                    </span>
                  </div>
                  
                  <?php includeHook($hooks, 'form'); ?>
                  <input type="hidden" name="redirect" value="<?= Input::get('redirect') ?>" />
                  
                  

                  <button type="submit" class="auth-btn primary-btn mb-3" id="next_button">
                    <?= lang("SIGNIN_BUTTONTEXT", ""); ?>
                    <i class="bi bi-arrow-right"></i>
                  </button>
                    
                  <?php } //end no password logins 
                  ?>
                  
                  <?php
                  if (file_exists($abs_us_root . $us_url_root . "usersc/views/_social_logins.php")) {
                    require_once $abs_us_root . $us_url_root . "usersc/views/_social_logins.php";
                  } else {
                    require_once $abs_us_root . $us_url_root . "users/views/_social_logins.php";
                  }
                  includeHook($hooks, 'bottom');
        
                  if ($showBottom) { ?>
                    <div class="row p-3">
                      <?php if ($showForgot) { ?>
                        <div class="form-options mb-4">
                          <a href="?= $us_url_root ?>users/forgot_password.php" class="forgot-password">?= lang("SIGNIN_FORGOTPASS", ""); ?></a>
                        </div> 
                      <?php }
        
                      if ($settings->registration == 1) { ?>
                         <div class="switch-form">
                            <span>Don't have an account?</span>
                            <a href="<?= $us_url_root ?>users/join.php"><button type="button" class="switch-btn" data-target="register"><?= lang("SIGNUP_TEXT", ""); ?></button></a>
                          </div>
                      <?php }
                      ?>
        
                    </div>
        
                  <?php } //end showBottom 
                  ?>
                  
                </form>
              </div>
            </div>
          </div>
        </div>

      </div>

    </section><!-- /Login Section -->
  

<? require_once '..//usersc/plugins/simple_store/assets/template/'.'views/footer.php'; //Footer ?> 
