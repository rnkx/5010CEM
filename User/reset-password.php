<?php 
// Include header (navigation, styles, session start if needed)
require_once('header.php'); 

// Fetch banner image for registration page from settings table
$statement = $pdo->prepare("SELECT banner_registration FROM tbl_settings WHERE id = 1");
$statement->execute();
$setting = $statement->fetch(PDO::FETCH_ASSOC);
$banner_registration = $setting['banner_registration'];

// Handle form submission
if (isset($_POST['form1'])) {
    $valid = 1; // Flag for validation
    $error_message = '';

    // Validate required name field
    if (empty($_POST['cust_name'])) {
        $valid = 0;
        $error_message .= LANG_VALUE_123 . "<br>"; // "Name is required"
    }

    // Validate email field
    if (empty($_POST['cust_email'])) {
        $valid = 0;
        $error_message .= LANG_VALUE_131 . "<br>"; // "Email is required"
    } else {
        // Check email format
        if (filter_var($_POST['cust_email'], FILTER_VALIDATE_EMAIL) === false) {
            $valid = 0;
            $error_message .= LANG_VALUE_134 . "<br>"; // "Invalid email format"
        } else {
            // Ensure email is unique
            $stmt = $pdo->prepare("SELECT cust_email FROM tbl_customer WHERE cust_email = ?");
            $stmt->execute([$_POST['cust_email']]);
            if ($stmt->rowCount()) {
                $valid = 0;
                $error_message .= LANG_VALUE_147 . "<br>"; // "Email already exists"
            }
        }
    }

    // Validate phone
    if (empty($_POST['cust_phone'])) {
        $valid = 0;
        $error_message .= LANG_VALUE_124 . "<br>"; // "Phone is required"
    }
    // Validate address, country, city, state, zip
    if (empty($_POST['cust_address'])) {
        $valid = 0;
        $error_message .= LANG_VALUE_125 . "<br>";
    }
    if (empty($_POST['cust_country'])) {
        $valid = 0;
        $error_message .= LANG_VALUE_126 . "<br>";
    }
    if (empty($_POST['cust_city'])) {
        $valid = 0;
        $error_message .= LANG_VALUE_127 . "<br>";
    }
    if (empty($_POST['cust_state'])) {
        $valid = 0;
        $error_message .= LANG_VALUE_128 . "<br>";
    }
    if (empty($_POST['cust_zip'])) {
        $valid = 0;
        $error_message .= LANG_VALUE_129 . "<br>";
    }

    // Validate password and confirmation
    if (empty($_POST['cust_password']) || empty($_POST['cust_re_password'])) {
        $valid = 0;
        $error_message .= LANG_VALUE_138 . "<br>"; // "Password fields required"
    } elseif ($_POST['cust_password'] != $_POST['cust_re_password']) {
        $valid = 0;
        $error_message .= LANG_VALUE_139 . "<br>"; // "Passwords do not match"
    }

    // If all inputs are valid, insert new customer
    if ($valid == 1) {
        // Generate verification token and timestamps
        $token = md5(time());
        $cust_datetime = date('Y-m-d H:i:s');
        $cust_timestamp = time();

        // Insert customer record with inactive status (0)
        $stmt = $pdo->prepare(
            "INSERT INTO tbl_customer (
                cust_name, cust_cname, cust_email, cust_phone,
                cust_country, cust_address, cust_city, cust_state, cust_zip,
                cust_b_name, cust_b_cname, cust_b_phone, cust_b_country,
                cust_b_address, cust_b_city, cust_b_state, cust_b_zip,
                cust_s_name, cust_s_cname, cust_s_phone, cust_s_country,
                cust_s_address, cust_s_city, cust_s_state, cust_s_zip,
                cust_password, cust_token, cust_datetime, cust_timestamp, cust_status
            ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)"
        );
        $stmt->execute([
            strip_tags($_POST['cust_name']),
            strip_tags($_POST['cust_cname']),
            strip_tags($_POST['cust_email']),
            strip_tags($_POST['cust_phone']),
            strip_tags($_POST['cust_country']),
            strip_tags($_POST['cust_address']),
            strip_tags($_POST['cust_city']),
            strip_tags($_POST['cust_state']),
            strip_tags($_POST['cust_zip']),
            // Empty billing and shipping fields
            '', '', '', '', '', '', '', '', '', '', '', '', '', '',
            md5($_POST['cust_password']),
            $token,
            $cust_datetime,
            $cust_timestamp,
            0 // Status 0 = not yet activated
        ]);

        // Build and send activation email to the customer
        $to = $_POST['cust_email'];
        $subject = LANG_VALUE_150; // "Account Confirmation"
        $verify_link = BASE_URL . 'verify.php?email=' . urlencode($to) . '&token=' . $token;
        $message = LANG_VALUE_151 . "<br><br>" .
                   "<a href=\"$verify_link\">$verify_link</a>";
        $headers = 
            "From: noreply@" . BASE_URL . "\r\n" .
            "Reply-To: noreply@" . BASE_URL . "\r\n" .
            "MIME-Version: 1.0\r\n" .
            "Content-Type: text/html; charset=ISO-8859-1\r\n";
        mail($to, $subject, $message, $headers);

        // Clear form POST values
        foreach (['cust_name','cust_cname','cust_email','cust_phone','cust_address','cust_city','cust_state','cust_zip'] as $field) {
            unset($_POST[$field]);
        }

        $success_message = LANG_VALUE_152; // "Registration successful, check email to verify."
    }
}
?>

<!-- Registration page banner -->
<div class="page-banner" style="background-color:#444; background-image: url(assets/uploads/<?php echo $banner_registration; ?>);">
    <div class="inner">
        <h1><?php echo LANG_VALUE_16; /* "Register" */ ?></h1>
    </div>
</div>

<!-- Registration form -->
<div class="page">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="user-content">
                    <form action="" method="post">
                        <?php $csrf->echoInputField(); // CSRF token for form security ?>
                        <div class="row">
                            <div class="col-md-2"></div>
                            <div class="col-md-8">
                                <!-- Display validation errors or success message -->
                                <?php if (!empty($error_message)) {
                                    echo "<div class='error' style='padding:10px; background:#f1f1f1; margin-bottom:20px;'>" . $error_message . "</div>";
                                }
                                if (!empty($success_message)) {
                                    echo "<div class='success' style='padding:10px; background:#f1f1f1; margin-bottom:20px;'>" . $success_message . "</div>";
                                } ?>

                                <!-- Name field -->
                                <div class="col-md-6 form-group">
                                    <label><?php echo LANG_VALUE_102; /* "Name" */ ?> *</label>
                                    <input type="text" class="form-control" name="cust_name" value="<?php echo $_POST['cust_name'] ?? ''; ?>">
                                </div>
                                <!-- Company name (optional) -->
                                <div class="col-md-6 form-group">
                                    <label><?php echo LANG_VALUE_103; /* "Company Name" */ ?></label>
                                    <input type="text" class="form-control" name="cust_cname" value="<?php echo $_POST['cust_cname'] ?? ''; ?>">
                                </div>
                                <!-- Email field -->
                                <div class="col-md-6 form-group">
                                    <label><?php echo LANG_VALUE_94; /* "Email" */ ?> *</label>
                                    <input type="email" class="form-control" name="cust_email" value="<?php echo $_POST['cust_email'] ?? ''; ?>">
                                </div>
                                <!-- Phone field -->
                                <div class="col-md-6 form-group">
                                    <label><?php echo LANG_VALUE_104; /* "Phone" */ ?> *</label>
                                    <input type="text" class="form-control" name="cust_phone" value="<?php echo $_POST['cust_phone'] ?? ''; ?>">
                                </div>
                                <!-- Address field -->
                                <div class="col-md-12 form-group">
                                    <label><?php echo LANG_VALUE_105; /* "Address" */ ?> *</label>
                                    <textarea name="cust_address" class="form-control" style="height:70px;"><?php echo $_POST['cust_address'] ?? ''; ?></textarea>
                                </div>
                                <!-- Country dropdown -->
                                <div class="col-md-6 form-group">
                                    <label><?php echo LANG_VALUE_106; /* "Country" */ ?> *</label>
                                    <select name="cust_country" class="form-control select2">
                                        <option value=""><?php echo LANG_VALUE_155; /* "Select country" */ ?></option>
                                        <?php
                                            $stmt = $pdo->prepare("SELECT country_id, country_name FROM tbl_country ORDER BY country_name ASC");
                                            $stmt->execute();
                                            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
                                                echo '<option value="'.$row['country_id'].'">'.$row['country_name'].'</option>';
                                            }
                                        ?>
                                    </select>
                                </div>
                                <!-- City field -->
                                <div class="col-md-6 form-group">
                                    <label><?php echo LANG_VALUE_107; /* "City" */ ?> *</label>
                                    <input type="text" class="form-control" name="cust_city" value="<?php echo $_POST['cust_city'] ?? ''; ?>">
                                </div>
                                <!-- State field -->
                                <div class="col-md-6 form-group">
                                    <label><?php echo LANG_VALUE_108; /* "State" */ ?> *</label>
                                    <input type="text" class="form-control" name="cust_state" value="<?php echo $_POST['cust_state'] ?? ''; ?>">
                                </div>
                                <!-- Zip code field -->
                                <div class="col-md-6 form-group">
                                    <label><?php echo LANG_VALUE_109; /* "Zip Code" */ ?> *</label>
                                    <input type="text" class="form-control" name="cust_zip" value="<?php echo $_POST['cust_zip'] ?? ''; ?>">
                                </div>
                                <!-- Password field -->
                                <div class="col-md-6 form-group">
                                    <label><?php echo LANG_VALUE_96; /* "Password" */ ?> *</label>
                                    <input type="password" class="form-control" name="cust_password">
                                </div>
                                <!-- Confirm password field -->
                                <div class="col-md-6 form-group">
                                    <label><?php echo LANG_VALUE_98; /* "Confirm Password" */ ?> *</label>
                                    <input type="password" class="form-control" name="cust_re_password">
                                </div>
                                <!-- Submit button -->
                                <div class="col-md-6 form-group">
                                    <input type="submit" class="btn btn-danger" value="<?php echo LANG_VALUE_15; /* "Register" */ ?>" name="form1">
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php 
// Include footer (closing HTML tags, JS files)
require_once('footer.php'); 
?>
