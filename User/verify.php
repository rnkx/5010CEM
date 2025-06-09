<?php require_once('header.php'); ?>

<?php
if ( (!isset($_REQUEST['email'])) || (isset($_REQUEST['token'])) )
{
    $var = 1;

    // check if the token is correct and match with database.
    $statement = $pdo->prepare("SELECT * FROM tbl_customer WHERE cust_email=?");
    $statement->execute(array($_REQUEST['email']));
    $result = $statement->fetchAll(PDO::FETCH_ASSOC);                           
    foreach ($result as $row) {
        if($_REQUEST['token'] != $row['cust_token']) {
            header('location: '.BASE_URL);
            exit;
        }
    }

    // everything is correct. now activate the user removing token value from database.
    if($var != 0)
    {
        $statement = $pdo->prepare("UPDATE tbl_customer SET cust_token=?, cust_status=? WHERE cust_email=?");
        $statement->execute(array('',1,$_GET['email']));

        $success_message = '<p style="color:green;">Your email is verified successfully. You can now login to our website.</p><p><a href="'.BASE_URL.'login.php" style="color:#167ac6;font-weight:bold;">Click here to login</a></p>';     
    }
}
?>

<div class="page-banner" style="background-color:#444;">
    <div class="inner">
        <h1>Registration Successful</h1>
    </div>
</div>

<div class="page"><?php 
// Include header (navigation, session, etc.)
require_once('header.php'); 

// Validate required GET parameters: email and token
if (!isset($_REQUEST['email']) || !isset($_REQUEST['token'])) {
    // Missing parameters -> redirect to login/home
    header('Location: ' . BASE_URL . 'login.php');
    exit;
}

// Fetch banner image for reset-password page (optional use)
$statement = $pdo->prepare("SELECT banner_reset_password FROM tbl_settings WHERE id = 1");
$statement->execute();
$setting = $statement->fetch(PDO::FETCH_ASSOC);
$banner_reset_password = $setting['banner_reset_password'];

// Check that email and token match a customer record
$stmt = $pdo->prepare("SELECT cust_token, cust_timestamp FROM tbl_customer WHERE cust_email = ?");
$stmt->execute([$_REQUEST['email']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$user || $_REQUEST['token'] !== $user['cust_token']) {
    // Invalid token or email -> redirect
    header('Location: ' . BASE_URL . 'login.php');
    exit;
}

// Check if the token has expired (older than 24 hours)
$error_message2 = '';
if (time() - $user['cust_timestamp'] > 86400) {
    $error_message2 = LANG_VALUE_144; // "Token expired"
}

// Handle new password submission
if (isset($_POST['form1'])) {
    $valid = 1;
    $error_message = '';

    // Ensure both fields are filled
    if (empty($_POST['cust_new_password']) || empty($_POST['cust_re_password'])) {
        $valid = 0;
        $error_message .= LANG_VALUE_140 . "\n"; // "Password required"
    } elseif ($_POST['cust_new_password'] !== $_POST['cust_re_password']) {
        $valid = 0;
        $error_message .= LANG_VALUE_139 . "\n"; // "Passwords do not match"
    }

    if ($valid) {
        // Update customer password, clear token and timestamp
        $newHash = md5(strip_tags($_POST['cust_new_password']));
        $stmt = $pdo->prepare(
            "UPDATE tbl_customer 
             SET cust_password = ?, cust_token = '', cust_timestamp = '' 
             WHERE cust_email = ?"
        );
        $stmt->execute([$newHash, $_REQUEST['email']]);

        // Redirect to success page
        header('Location: ' . BASE_URL . 'reset-password-success.php');
        exit;
    }
}
?>

<!-- Banner for reset password page -->
<div class="page-banner" style="background-color:#444; background-image:url(assets/uploads/<?php echo $banner_reset_password; ?>);">
    <div class="inner">
        <h1><?php echo LANG_VALUE_149; /* "Reset Password" */ ?></h1>
    </div>
</div>

<div class="page">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="user-content">
                    <?php 
                    // Show any validation error from form
                    if (!empty($error_message)) {
                        echo "<script>alert('" . $error_message . "')</script>";
                    }
                    ?>
                    <?php if (!empty($error_message2)): ?>
                        <!-- Token expired message -->
                        <div class="error"><?php echo $error_message2; ?></div>
                    <?php else: ?>
                        <!-- Reset form -->
                        <form method="post">
                            <?php $csrf->echoInputField(); // CSRF protection ?>
                            <div class="row">
                                <div class="col-md-4"></div>
                                <div class="col-md-4">
                                    <!-- New password field -->
                                    <div class="form-group">
                                        <label><?php echo LANG_VALUE_100; /* "New Password" */ ?> *</label>
                                        <input type="password" class="form-control" name="cust_new_password">
                                    </div>
                                    <!-- Confirm password field -->
                                    <div class="form-group">
                                        <label><?php echo LANG_VALUE_101; /* "Confirm Password" */ ?> *</label>
                                        <input type="password" class="form-control" name="cust_re_password">
                                    </div>
                                    <!-- Submit button -->
                                    <div class="form-group">
                                        <input type="submit" class="btn btn-primary" 
                                               name="form1" 
                                               value="<?php echo LANG_VALUE_149; /* "Reset Password" */ ?>">
                                    </div>
                                </div>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php 
// Include footer (closing tags, JS)
require_once('footer.php'); 
?>

    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="user-content">
                    <?php 
                        echo $error_message;
                        echo $success_message;
                    ?>
                </div>                
            </div>
        </div>
    </div>
</div>

<?php require_once('footer.php'); ?>
