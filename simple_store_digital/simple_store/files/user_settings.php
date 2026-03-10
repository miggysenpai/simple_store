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
//require_once $abs_us_root . $us_url_root . 'users/includes/template/prep.php';
 require_once '../usersc/plugins/simple_store/assets/template/'.'views/header.php'; // Custom Header
 echo '<script nonce="'.htmlspecialchars($userspice_nonce ?? '').'"
      src="https://code.jquery.com/jquery-3.7.1.min.js"
      integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo="
      crossorigin="anonymous">
      </script>
      <link rel="stylesheet" href="/users/fonts/css/fontawesome.min.css">
      <link rel="stylesheet" href="/users/fonts/css/solid.min.css">
      
';

if (!securePage(Server::get('PHP_SELF'))) {
  die();
}
$hooks = getMyHooks();
includeHook($hooks, 'pre');

$emailQ = $db->query('SELECT * FROM email');
$emailR = $emailQ->first();
$pw_settings = $db->query("SELECT * FROM us_password_strength")->first();
if ($pw_settings->meter_active == 1) {
  $secondCol = "col-md-9";
} else {
  $secondCol = "col-md-12";
}

//PHP Goes Here!
$errors = [];
$successes = [];
$userId = $user->data()->id;
$grav = fetchProfilePicture($userId);
$validation = new Validate();
$userdetails = $user->data();

$allowPasswords = passwordsAllowed($settings->no_passwords);
//Temporary Success Message
$holdover = Input::get('success');
if ($holdover == 'true') {
  $successes[] = 'Account Updated';
}
if (!empty($_POST)) {
  $token = $_POST['csrf'];
  if (!Token::check($token)) {
    include $abs_us_root . $us_url_root . 'usersc/scripts/token_error.php';
  } else {
    includeHook($hooks, 'post');
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
    //if you make it this far, we're going to mark your account as modified.  This will allow other plugins to work.  And it's not that serious.
    $db->update('users', $user->data()->id, ['modified' => date("Y-m-d")]);
    $displayname = Input::get('username');
    if ($userdetails->username != $displayname && ($settings->change_un == 1 || (($settings->change_un == 2) && ($user->data()->un_changed == 0)))) {
      $fields = [
        'username' => $displayname,
        'un_changed' => 1,
      ];
      $validation->check($_POST, [
        'username' => [
          'display' => lang('GEN_UNAME'),
          'required' => true,
          'unique_update' => 'users,' . $userId,
          'min' => $settings->min_un,
          'max' => $settings->max_un,
        ],
      ]);
      if ($validation->passed()) {
        if (($settings->change_un == 2) && ($user->data()->un_changed == 1)) {
          $msg = lang("REDIR_UN_ONCE");
          usError($msg);
          Redirect::to($us_url_root . 'users/user_settings.php');
        }
        $db->update('users', $userId, $fields);
        $successes[] = lang('GEN_UNAME') . ' ' . lang('GEN_UPDATED');
        logger($user->data()->id, 'User', "Changed username from $userdetails->username to $displayname.");
      } else {
        //validation did not pass
        foreach ($validation->errors() as $error) {
          $errors[] = $error;
        }
      }
    }
    //Update first name
    $fname = ucfirst(Input::get('fname'));
    if ($userdetails->fname != $fname) {
      $fields = ['fname' => $fname];
      $validation->check($_POST, [
        'fname' => [
          'display' => lang('GEN_FNAME'),
          'required' => true,
          'min' => 1,
          'max' => 60,
        ],
      ]);
      if ($validation->passed()) {
        $db->update('users', $userId, $fields);
        $successes[] = lang('GEN_FNAME') . ' ' . lang('GEN_UPDATED');
        logger($user->data()->id, 'User', "Changed fname from $userdetails->fname to $fname.");
      } else {
        //validation did not pass
        foreach ($validation->errors() as $error) {
          $errors[] = $error;
        }
      }
    }
    //Update last name
    $lname = ucfirst(Input::get('lname'));
    if ($userdetails->lname != $lname) {
      $fields = ['lname' => $lname];
      $validation->check($_POST, [
        'lname' => [
          'display' => lang('GEN_LNAME'),
          'required' => true,
          'min' => 1,
          'max' => 60,
        ],
      ]);
      if ($validation->passed()) {
        $db->update('users', $userId, $fields);
        $successes[] = lang('GEN_LNAME') . ' ' . lang('GEN_UPDATED');
        logger($user->data()->id, 'User', "Changed lname from $userdetails->lname to $lname.");
      } else {
        //validation did not pass
        foreach ($validation->errors() as $error) {
          $errors[] = $error;
        }
      }
    }
    if (!empty($_POST['password']) || $userdetails->email != $_POST['email'] || !empty($_POST['resetPin'])) {
      //Check password for email or pw update

      //Update email
      $email = Input::get('email');
      if ($userdetails->email != $email) {

        $fields = ['email' => $email];
        $validation->check($_POST, [
          'email' => [
            'display' => lang('GEN_EMAIL'),
            'required' => true,
            'valid_email' => true,
            'unique_update' => 'users,' . $userId,
            'min' => 5,
            'max' => 200,
          ],
        ]);
        if ($validation->passed()) {
          if ($emailR->email_act == 0) {
            $db->update('users', $userId, $fields);
            $successes[] = lang('GEN_EMAIL') . ' ' . lang('GEN_UPDATED');
            logger($user->data()->id, 'User', "Changed email from $userdetails->email to $email.");
          }
          if ($emailR->email_act == 1 || !$allowPasswords) {
            $vericode = uniqid() . randomstring(15);
            $vericode_expiry = date('Y-m-d H:i:s', strtotime("+$settings->join_vericode_expiry hours", strtotime(date('Y-m-d H:i:s'))));
            $db->update('users', $userId, ['email_new' => $email, 'vericode' => hashVericode($vericode), 'vericode_expiry' => $vericode_expiry]);
            //Send the email
            $options = [
              'fname' => $user->data()->fname,
              'email' => rawurlencode($user->data()->email),
              'vericode' => $vericode,
              'user_id' => $userId,
              'join_vericode_expiry' => $settings->join_vericode_expiry,
            ];
            $encoded_email = rawurlencode($email);
            $subject = lang('EML_VER');
            $body = email_body('_email_template_verify_new.php', $options);
            $email_sent = email($email, $subject, $body);
            if (!$email_sent) {
              $errors[] = lang('ERR_EMAIL');
            } else {
              $successes[] = lang('EML_CHK') . ' ' . $settings->join_vericode_expiry . ' ' . lang('T_HOURS');
            }
            if ($emailR->email_act == 1) {
              logger($user->data()->id, 'User', "Requested change email from $userdetails->email to $email. Verification email sent.");
            }
          } else {
            //validation did not pass
            foreach ($validation->errors() as $error) {
              $errors[] = $error;
            }
          }
        }
      }

      if ($allowPasswords && !empty($_POST['password'])) {
        $validation->check($_POST, [
          'password' => [
            'display' => lang('PW_NEW'),
            'required' => true,
            'min' => $settings->min_pw,
            'max' => $settings->max_pw,
          ],
          'confirm' => [
            'display' => lang('PW_CONF'),
            'required' => true,
            'matches' => 'password',
          ],
        ]);
        if ($pw_settings->meter_active == 1 && $pw_settings->enforce_rules == 1) {
          $doubleCheckPassword = userSpicePasswordStrength(Input::get('password'));
          if ($doubleCheckPassword['isValid'] == false) {
            //inject error before processing
            $validation->addError([lang("JOIN_INVALID_PW"), 'password']);
          }
        }
        foreach ($validation->errors() as $error) {
          $errors[] = $error;
        }

        if (empty($errors)) {
          //process

          $new_password_hash = password_hash(Input::get('password'), PASSWORD_BCRYPT, ['cost' => 13]);
          $user->update(['password' => $new_password_hash, 'force_pr' => 0, 'vericode' => hashVericode(randomstring(15))], $user->data()->id);
          $successes[] = lang('PW_UPD');
          logger($user->data()->id, 'User', 'Updated password.');
          if ($settings->session_manager == 1) {
            $passwordResetKillSessions = passwordResetKillSessions();
            if (is_numeric($passwordResetKillSessions)) {
              if ($passwordResetKillSessions == 1) {
                $successes[] = lang('SESS_SUC') . ' 1 ' . lang('GEN_SESSION');
              }
              if ($passwordResetKillSessions > 1) {
                $successes[] = lang('SESS_SUC') . $passwordResetKillSessions . lang('GEN_SESSIONS');
              }
            } else {
              $errors[] = lang('ERR_FAIL_ACT') . $passwordResetKillSessions;
            }
          }
        }
      }
      if (!empty($_POST['resetPin']) && Input::get('resetPin') == 1) {
        $user->update(['pin' => null]);
        logger($user->data()->id, 'User', 'Reset PIN');
        $successes[] = lang('SET_PIN');
        $successes[] = lang('SET_PIN_NEXT');
      }
    }
  }

  sessionValMessages($errors, $successes);
  Redirect::to("user_settings.php");
}


$orders_search = $db->query("SELECT * FROM simple_store_stripe_transactions WHERE customer_email = ? ",[$userdetails->email])->results(); // get order info
$orders_count = count($orders_search);


?>



<!-- Account Section -->
    <section id="account" class="account section">

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
                    <a class="nav-link " href="../users/account.php">
                      <i class="bi bi-box-seam"></i>
                      <span>My Orders</span>
                      <span class="badge"><?=$orders_count?></span>
                    </a>
                  </li>
                  
                  <li class="nav-item">
                    <a class="nav-link active"  href="../users/user_settings.php">
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
                  
                <!-- Settings Tab -->
                <div class="tab-pane fade show active" id="settings">
                  <div class="section-header " data-aos="fade-up">
                    <h2>Account Settings</h2>
                  </div>
                  
                  <?php
                  if ($errors) {
                    display_errors($errors);
                  }
                  if ($successes) {
                    display_successes($successes);
                  }
                  includeHook($hooks, 'body');
                  ?>

                  <div class="settings-content">
                    <!-- Personal Information -->
                    <div class="settings-section" data-aos="fade-up">
                      <h3>Personal Information</h3>
                      <form class="php-email-form settings-form" name="updateAccount" action="" method="post">
                        <div class="row g-3">
                            <?php
                              $readonly_username = ($settings->change_un == 0 || ($settings->change_un == 2 && $userdetails->un_changed == 1));
                              $input_class = $readonly_username ? "form-control-plaintext" : "form-control";
                            ?>    
                            <div class="col-md-12">
                            <label for="username" class="form-label"><?= lang('GEN_UNAME'); ?></label>
                            <input type="text" class="form-control <?= $input_class; ?>" id="username" name="username" 
                                value="<?= $userdetails->username; ?>" autocomplete="off" <?= $readonly_username ? 'readonly' : ''; ?> >
                                <?php if ($readonly_username) { ?>
                                <sup>
                                  <span class="input-group-addon" data-toggle="tooltip" title="<?= lang($settings->change_un == 0 ? 'SET_NOCHANGE' : 'SET_ONECHANGE'); ?>"><?= lang('SET_WHY'); ?></span>
                                </sup>
                              <?php } ?>
                          </div>
                            
                          <div class="col-md-6">
                            <label for="fname" class="form-label"><?= lang('GEN_FNAME'); ?></label>
                            <input type="text" class="form-control" id="fname" name="fname" value="<?= $userdetails->fname; ?>" autocomplete="off" required="">
                          </div>
                          <div class="col-md-6">
                            <label for="lname" class="form-label"><?= lang('GEN_LNAME'); ?></label>
                            <input type="text" class="form-control" id="lname" name="lname" value="<?= $userdetails->lname; ?>" autocomplete="off" required="">
                          </div>
                          <div class="col-md-6">
                            <label for="email" class="form-label"><?= lang('GEN_EMAIL'); ?></label>
                            <input type="email" class="form-control" id="email" name="email" value="<?= $userdetails->email; ?>" autocomplete="off" required="">
                            <br />
                          </div>
                          
                          
                          
                          
                          <?php if ($allowPasswords) { ?>
                          <div class="col-md-12"><h3>Security</h3> </div>
                          
                          
                          <div class="col-md-6">
                            <label for="password" class="form-label"><?= lang('PW_NEW'); ?></label>
                            <input type="password" class="form-control" id="password" name="password" aria-describedby="passwordhelp" autocomplete="off" >
                          </div>
                          <div class="col-md-6">
                            <label for="confirm" class="form-label"><?= lang('PW_CONF'); ?></label>
                            <input type="password" class="form-control" id="confirm" name="confirm" autocomplete="new-password">
                            
                          </div>    
                          
                          <?php if ($pw_settings->meter_active == 1) { ?>
                            <div class="col-md-12">
                              <?php
                              if (file_exists($abs_us_root . $us_url_root . 'usersc/includes/password_meter.php')) {
                                include($abs_us_root . $us_url_root . 'usersc/includes/password_meter.php');
                              } else {
                                include($abs_us_root . $us_url_root . 'users/includes/password_meter.php');
                              }
                              ?>
                            </div>
                          <?php } ?>
                          
                        </div>
                        <?php }?>
                        
                        <?php includeHook($hooks, 'form'); ?>
                        <input type="hidden" name="csrf" value="<?= Token::generate(); ?>" />
                        <div class="form-buttons">
                            <br />
                          <button type="submit" class="btn-save">Update Changes</button>
                        </div>
                        
                         <?php
                          if (isset($user->data()->oauth_provider) && $user->data()->oauth_provider != null) {
                            echo lang('ERR_GOOG');
                          }
                          includeHook($hooks, 'bottom');
                          ?>

                        <div class="loading">Loading</div>
                        <div class="error-message"></div>
                        <div class="sent-message">Your changes have been saved successfully!</div>
                      </form>
                    </div>

                    

                   

                    <!-- Delete Account 
                    <div class="settings-section danger-zone" data-aos="fade-up" data-aos-delay="300">
                      <h3>Delete Account</h3>
                      <div class="danger-zone-content">
                        <p>Once you delete your account, there is no going back. Please be certain.</p>
                        <button type="button" class="btn-danger">Delete Account</button>
                      </div>
                    </div>
                    -->
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

      </div>

    </section><!-- /Account Section -->
    <script nonce="<?=htmlspecialchars($userspice_nonce ?? '')?>">
      $(document).ready(function() {
        // $('body').removeClass('is-collapsed');
        // $('.meetingPag').DataTable({searching: false, paging: false, info: false});
    
        <?php if ($allowPasswords) { ?>
          $('.password_view_control').hover(function() {
            $('#password').attr('type', 'text');
            $('#confirm').attr('type', 'text');
          }, function() {
            $('#password').attr('type', 'password');
            $('#confirm').attr('type', 'password');
          });
        <?php } ?>
    
        $('[data-toggle="popover"], .pwpopover').popover();
        $('.pwpopover').on('click', function(e) {
          $('.pwpopover').not(this).popover('hide');
        });
        $('.modal').on('hidden.bs.modal', function() {
          $('.pwpopover').popover('hide');
        });
      });
    </script>


<?php
// require_once $abs_us_root . $us_url_root . 'users/includes/html_footer.php'; 
 require_once '../usersc/plugins/simple_store/assets/template/'.'views/footer.php'; // Custom Footer
 ?>
