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


//require_once $abs_us_root.$us_url_root.'users/includes/template/prep.php';
require_once '../usersc/plugins/simple_store/assets/template/'.'views/header.php'; // Custom Header


$action = Input::get('action');
$actions = [
    'thank_you_verify'=>'_joinThankYou_verify.php',
    'thank_you_join'=>'_joinThankYou.php',
    'thank_you'=>'_joinThankYou.php',
];
if(!in_array($action, array_keys($actions))){
    Redirect::to($us_url_root);
}else{
    echo "<div class='container' style='min-height: 70vh;'>
            <div class='row justify-content-center'>
                <div class='col-md-8 col-sm-12'>
                <br /><br /><br />
        ";
    //usersc then users
    if(file_exists($abs_us_root.$us_url_root.'usersc/views/'.$actions[$action])){
        require $abs_us_root.$us_url_root.'usersc/views/'.$actions[$action];
    }else{
        require $abs_us_root.$us_url_root.'users/views/'.$actions[$action];
    }
    echo "</div></div></div>";
}

// require_once $abs_us_root.$us_url_root.'users/includes/html_footer.php'; 
require_once '../usersc/plugins/simple_store/assets/template/'.'views/footer.php'; // Custom Footer
?>
