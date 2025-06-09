<?php
// Start output buffering and session for accessing session variables
ob_start();
session_start();

// Include database config and utility functions
include("../../admin/inc/config.php");
include("../../admin/inc/functions.php");

// Load language constants dynamically
$i = 1;
$statement = $pdo->prepare("SELECT * FROM tbl_language");
$statement->execute();
foreach ($statement->fetchAll(PDO::FETCH_ASSOC) as $row) {
    // Define constants LANG_VALUE_1, LANG_VALUE_2, ...
    define('LANG_VALUE_' . $i, $row['lang_value']);
    $i++;
}

// Check if this is a normal submission (msg param not set)
if (!isset($_REQUEST['msg'])) {
    // Ensure transaction info is provided
    if (empty($_POST['transaction_info'])) {
        // Missing info -> redirect back to checkout
        header('Location: ../../checkout.php');
        exit;
    } else {
        // Prepare payment fields
        $payment_date = date('Y-m-d H:i:s'); // current timestamp for payment
        $payment_id   = time();              // unique ID based on current time

        // Insert payment record as "Pending" bank deposit
        $stmt = $pdo->prepare(
            "INSERT INTO tbl_payment (
                customer_id,
                customer_name,
                customer_email,
                payment_date,
                txnid,
                paid_amount,
                card_number,
                card_cvv,
                card_month,
                card_year,
                bank_transaction_info,
                payment_method,
                payment_status,
                shipping_status,
                payment_id
            ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)"
        );
        $stmt->execute([
            $_SESSION['customer']['cust_id'],           // customer ID
            $_SESSION['customer']['cust_name'],          // name
            $_SESSION['customer']['cust_email'],         // email
            $payment_date,                              // date/time
            '',                                         // external txnid (none)
            $_POST['amount'],                           // amount paid
            '', '', '', '',                             // card details empty
            $_POST['transaction_info'],                 // bank transaction note
            'Bank Deposit',                             // method
            'Pending',                                  // payment status
            'Pending',                                  // shipping
            $payment_id                                 // internal payment ID
        ]);

        // Re-index cart session arrays for processing
        $arr_cart_p_id = array_values($_SESSION['cart_p_id']);
        $arr_cart_p_name = array_values($_SESSION['cart_p_name']);
        $arr_cart_size_name = array_values($_SESSION['cart_size_name']);
        $arr_cart_color_name = array_values($_SESSION['cart_color_name']);
        $arr_cart_p_qty = array_values($_SESSION['cart_p_qty']);
        $arr_cart_p_current_price = array_values($_SESSION['cart_p_current_price']);

        // Fetch all products to get current stock levels
        $stmt = $pdo->prepare("SELECT p_id, p_qty FROM tbl_product");
        $stmt->execute();
        $allProducts = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

        // Create order items and update stock
        foreach ($arr_cart_p_id as $idx => $product_id) {
            // Insert into order table
            $stmt = $pdo->prepare(
                "INSERT INTO tbl_order (
                    product_id,
                    product_name,
                    size,
                    color,
                    quantity,
                    unit_price,
                    payment_id
                ) VALUES (?,?,?,?,?,?,?)"
            );
            $stmt->execute([
                $product_id,
                $arr_cart_p_name[$idx],
                $arr_cart_size_name[$idx],
                $arr_cart_color_name[$idx],
                $arr_cart_p_qty[$idx],
                $arr_cart_p_current_price[$idx],
                $payment_id
            ]);

            // Deduct ordered quantity from stock
            $current_qty = $allProducts[$product_id] ?? 0;
            $new_qty = $current_qty - $arr_cart_p_qty[$idx];
            $stmt = $pdo->prepare("UPDATE tbl_product SET p_qty = ? WHERE p_id = ?");
            $stmt->execute([$new_qty, $product_id]);
        }

        // Clear the shopping cart session arrays
        foreach (['cart_p_id','cart_size_id','cart_size_name','cart_color_id',
                  'cart_color_name','cart_p_qty','cart_p_current_price',
                  'cart_p_name','cart_p_featured_photo'] as $key) {
            unset($_SESSION[$key]);
        }

        // Redirect to success page
        header('Location: ../../payment_success.php');
        exit;
    }
}
?>
