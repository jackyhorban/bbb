<?php

function boir_manager_form_Fn($atts) {
    ob_start();
    $_SESSION['checkout_processor'] = 'nmi';
    require(plugin_dir_path(__FILE__) . '/forms/BOIRForm.php');
    return ob_get_clean();
}
add_shortcode('boir_manager_form', 'boir_manager_form_Fn');

function boir_manager_signin_form_Fn() {
    global $boir_client_dashboard_url;
    ob_start();
    echo '<div class="boir_manager_signin_form">';
    echo '<h2>Sign In</h2>';
    wp_login_form(array(
        'redirect' => $boir_client_dashboard_url,
        'echo' => true,
        'form_id' => 'boir_manager_signin_form',
        'label_username' => 'Email Address',
        'label_password' => 'Password',
        'label_log_in' => 'Sign in',
        'remember' => false,
    ));
    if (isset($_GET['login']) && $_GET['login'] == 'failed') {
        $errors = array(
            'invalid_username' => 'Invalid username.',
            'incorrect_password' => 'Incorrect password.',
            'empty_username' => 'Username field is empty.',
            'empty_password' => 'Password field is empty.',
            'unknown' => 'Unknown error. Please try again later.',
        );
        $error_codes = isset($_GET['errors']) ? explode(',', $_GET['errors']) : [];
        $error_messages = array_map(function ($code) use ($errors) {
            return $errors[$code] ?? '';
        }, $error_codes);

        if (!empty($error_messages)) {
            foreach ($error_messages as $error_message) {
                echo '<div class="error">';
                echo('<i class="bi bi-exclamation-circle"></i> '.$error_message);
                echo '</div>';
            }
        }
    }
    echo '</div>';
    return ob_get_clean();
}
add_shortcode('boir_manager_signin_form', 'boir_manager_signin_form_Fn');


function boir_manager_dashboard_Fn(){
    ob_start();
    require( plugin_dir_path( __FILE__ ) . '/dashboard/BOIRDashboard.php');
    return ob_get_clean();
}
add_shortcode('boir_manager_dashboard', 'boir_manager_dashboard_Fn');

// function boir_manager_dashboard_test_Fn(){
//     error_log('boir_manager_dashboard_test_Fn');
//     ob_start();
//     require( plugin_dir_path( __FILE__ ) . '/dashboard/BOIRDashboardTest.php');
//     return ob_get_clean();
// }
// add_shortcode('boir_manager_dashboard_test', 'boir_manager_dashboard_test_Fn');

function getUserByEmail($email) {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'users';
    
    try {
        $query = $wpdb->prepare("SELECT * FROM $table_name WHERE user_email = %s", $email);
        $result = $wpdb->get_row($query);
    } catch (Exception $e) {
        // echo ($e->getMessage()); // Log the exception message
        return null;
    }
    
    return $result;
}

function updateUserOTP($id, $otp_code, $otp_expired) {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'users';
    
    try {
        $result = $wpdb->update(
            $table_name,
            ['otp_code' => $otp_code, 'otp_expired' => $otp_expired],
            ['ID' => $id],
            ['%s', '%d'],
            ['%d']
        );
    } catch (Exception $e) {
        // echo ($e->getMessage()); // Log the exception message
        return false;
    }
    
    return $result !== false;
}

function boir_manager_otp_login_form_Fn() {
    ob_start();
    $error_message = '';
    
    // Check if the form is submitted
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['boir_otp_login'])) {
            $email = sanitize_email($_POST['email']);
            
            if (!is_email($email)) {
                $error_message = 'Invalid email address.';
            } else {
                $user = getUserByEmail($email); // Ensure this function exists
                
                if ($user) {
                    $otp = rand(100000, 999999); // Generate a 6-digit OTP
                    $expiry = time() + 300; // OTP expires in 5 minutes
                    
                    // Send OTP to user's email
                    if (updateUserOTP($user->ID, $otp, $expiry)) {
                        // Send OTP to user's email
                        $subject = 'Your Login OTP Code';
                        $message = "Your OTP code is: $otp. It will expire in 5 minutes.";
                        wp_mail($email, $subject, $message);
                        
                        // // Redirect to same page with email parameter
                        boir_manager_redirect(add_query_arg('email', $email, get_permalink()));
                        
                        exit;
                        
                    } else {
                        $error_message = 'Failed to generate OTP. Please try again.';
                    }
                } else {
                    $error_message = 'Email not found.';
                }
            }
        }
        
        if (isset($_POST['boir_otp_confirm'])) {
            $email = sanitize_email($_POST['email']);
            $otp = sanitize_text_field($_POST['otp']);

            $user = getUserByEmail($email);

            if ($user) {
                // Validate OTP
                $stored_otp = $user->otp_code;
                $expiry_time = $user->otp_expired;

                if ($otp === $stored_otp && time() <= $expiry_time) {
                    wp_set_auth_cookie($user->ID, true); // true for persistent login
                    wp_set_current_user($user->ID); // Set the current user
                    // do_action('wp_login', $user->user_login, $user); // Trigger login actions
                    do_action('wp_set_otp_session', $user);
                    
                    boir_manager_redirect(home_url('/dashboard')); // Redirect to dashboard
                    exit;
                } else {
                    $error_message = 'Invalid OTP or OTP expired.';
                }
            } else {
                $error_message = 'User not found.';
            }
        }
    }

    echo '<div class="boir_manager_otp_login_form">';
    echo '<h2>Log In with OTP</h2>';
    
    if (isset($_GET['email'])) {
        // OTP verification form
        $email = sanitize_email($_GET['email']);
        echo '<p for="notification" class="alert">Your OTP code is just sent. It will expire in 5 minutes. Please confirm your email box.</p>';
        echo '<form method="POST">';
        echo '<input type="hidden" name="email" value="' . esc_attr($email) . '">';
        echo '<label for="otp" class="login-otp">Enter OTP</label>';
        echo '<input type="text" name="otp" id="otp" required>';
        echo '<input type="submit" name="boir_otp_confirm" id="boir_otp_confirm" class="button button-primary" value="Confirm OTP">';        
        echo '<input type="button" id="boir_back_to_login" name="boir_back_to_login" class="button button-secondary" value="Back To Login">';
        echo '</form>';
        
        
        // Add JavaScript for the Back button
        echo '<script>
            document.getElementById("boir_back_to_login").addEventListener("click", function() {
                    const url = new URL(window.location.href);
                    url.searchParams.delete("email");
                    window.location.href = url.href;
                });
        </script>';
    } else {
        // Email input form
        echo '<form method="POST">';
        echo '<label for="email" class="login-email">Email Address:</label>';
        echo '<input type="email" name="email" id="email" required>';
        echo '<input type="submit" name="boir_otp_login" id="boir_otp_login" class="button button-primary" value="Send OTP">';
        echo '</form>';
    }

    if ($error_message !== '') {
        echo '<div class="error"><i class="bi bi-exclamation-circle"></i> ' . $error_message . '</div>';
    }

    echo '</div>';
    
    return ob_get_clean();
}
add_shortcode('boir_manager_otp_login_form', 'boir_manager_otp_login_form_Fn');


function boir_manager_redirect($url){
    if (headers_sent()){
      die('<script type="text/javascript">window.location=\''.$url.'\';</script>');
    }else{
      header('Location: ' . $url);
    }    
}
