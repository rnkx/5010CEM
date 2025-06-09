<?php 
// Include header (site navigation, styles)
require_once('header.php'); 

// Fetch banner image for login page from settings table
$statement = $pdo->prepare("SELECT * FROM tbl_settings WHERE id=1");
$statement->execute();
$result = $statement->fetchAll(PDO::FETCH_ASSOC);
foreach ($result as $row) {
    // Store banner filename
    $banner_login = $row['banner_login'];
}
?>

<?php
// Handle login form submission
if (isset($_POST['form1'])) {
    // Check for empty email or password
    if (empty($_POST['cust_email']) || empty($_POST['cust_password'])) {
        $error_message = LANG_VALUE_132 . '<br>'; // Required fields missing
    } else {
        // Sanitize user input
        $cust_email    = strip_tags($_POST['cust_email']);
        $cust_password = strip_tags($_POST['cust_password']);

        // Look up customer by email
        $statement = $pdo->prepare("SELECT * FROM tbl_customer WHERE cust_email=?");
        $statement->execute([$cust_email]);
        $total = $statement->rowCount();
        $result = $statement->fetchAll(PDO::FETCH_ASSOC);

        // Retrieve status and hashed password
        foreach ($result as $row) {
            $cust_status = $row['cust_status'];
            $row_password = $row['cust_password'];
        }

        if ($total == 0) {
            // No user found with this email
            $error_message .= LANG_VALUE_133 . '<br>';
        } else {
            // Verify password using MD5 hash
            if ($row_password != md5($cust_password)) {
                $error_message .= LANG_VALUE_139 . '<br>'; // Incorrect password
            } else {
                // Check if account is active
                if ($cust_status == 0) {
                    $error_message .= LANG_VALUE_148 . '<br>'; // Account not activated
                } else {
                    // Successful login: set session and redirect to dashboard
                    $_SESSION['customer'] = $row;
                    header("Location: " . BASE_URL . "dashboard.php");
                }
            }
        }
    }
}
?>

<!-- Page banner with background image -->
<div class="page-banner" style="background-color:#444; background-image: url(assets/uploads/<?php echo $banner_login; ?>);">
    <div class="inner">
        <h1><?php echo LANG_VALUE_10; /* "Login" text */ ?></h1>
    </div>
</div>

<div class="page">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="user-content">

                    <!-- Login form -->
                    <form action="" method="post">
                        <?php $csrf->echoInputField(); /* CSRF protection token */ ?>  
                        <div class="row">
                            <div class="col-md-4"></div>
                            <div class="col-md-4">
                                <?php
                                // Display any error or success messages
                                if (!empty($error_message)) {
                                    echo "<div class='error' style='padding:10px; background:#f1f1f1; margin-bottom:20px;'>" . $error_message . "</div>";
                                }
                                if (!empty($success_message)) {
                                    echo "<div class='success' style='padding:10px; background:#f1f1f1; margin-bottom:20px;'>" . $success_message . "</div>";
                                }
                                ?>

                                <!-- Email input -->
                                <div class="form-group">
                                    <label><?php echo LANG_VALUE_94; /* "Email" label */ ?> *</label>
                                    <input type="email" class="form-control" name="cust_email">
                                </div>

                                <!-- Password input -->
                                <div class="form-group">
                                    <label><?php echo LANG_VALUE_96; /* "Password" label */ ?> *</label>
                                    <input type="password" class="form-control" name="cust_password">
                                </div>

                                <!-- Submit button -->
                                <div class="form-group">
                                    <input type="submit" class="btn btn-success" value="<?php echo LANG_VALUE_4; /* "Submit" */ ?>" name="form1">
                                </div>

                                <!-- Forgot password link -->
                                <a href="forget-password.php" style="color:#e4144d;">
                                    <?php echo LANG_VALUE_97; /* "Forgot Password?" */ ?>
                                </a>
                            </div>
                        </div>                        
                    </form>

                </div>                
            </div>
        </div>
    </div>
</div>

<?php 
// Include footer (closing tags, scripts)
require_once('footer.php'); 
?>
