<?php require_once('header.php'); ?>



<section class="content-header">
    <h1>Dashboard</h1>
</section>

<?php
// Existing queries
$statement = $pdo->prepare("SELECT * FROM tbl_top_category");
$statement->execute();
$total_top_category = $statement->rowCount();

$statement = $pdo->prepare("SELECT * FROM tbl_mid_category");
$statement->execute();
$total_mid_category = $statement->rowCount();

$statement = $pdo->prepare("SELECT * FROM tbl_end_category");
$statement->execute();
$total_end_category = $statement->rowCount();

$statement = $pdo->prepare("SELECT * FROM tbl_product");
$statement->execute();
$total_product = $statement->rowCount();

$statement = $pdo->prepare("SELECT * FROM tbl_customer WHERE cust_status='1'");
$statement->execute();
$total_customers = $statement->rowCount();

$statement = $pdo->prepare("SELECT * FROM tbl_payment WHERE payment_status=?");
$statement->execute(array('Completed'));
$total_order_completed = $statement->rowCount();

$statement = $pdo->prepare("SELECT * FROM tbl_payment WHERE payment_status=?");
$statement->execute(array('Pending'));
$total_order_pending = $statement->rowCount();

// New queries for Size and Color
$statement = $pdo->prepare("SELECT * FROM tbl_size");
$statement->execute();
$total_size = $statement->rowCount();

$statement = $pdo->prepare("SELECT * FROM tbl_color");
$statement->execute();
$total_color = $statement->rowCount();

?>

<section class="content">
    <div class="row">
        <<div class="col-lg-3 col-xs-6">
            <div class="small-box bg-green">
                <div class="inner">
                    <h3><?php echo $total_order_completed; ?></h3>
                    <p>Completed Orders</p>
                </div>
                <div class="icon">
                    <i class="ionicons ion-android-checkbox-outline"></i>
                </div>
               
            </div>
        </div>    
    
        <div class="col-lg-3 col-xs-6">
            <div class="small-box bg-maroon">
                <div class="inner">
                    <h3><?php echo $total_order_pending; ?></h3>
                    <p>Pending Orders</p>
                </div>
                <div class="icon">
                    <i class="ionicons ion-clipboard"></i>
                </div>
                <a href="order.php" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
            </div>
        </div>
        <div class="col-lg-3 col-xs-6">
            <div class="small-box bg-primary">
                <div class="inner">
                    <h3><?php echo $total_product; ?></h3>
                    <p>Products</p>
                </div>
                <div class="icon">
                    <i class="ionicons ion-android-cart"></i>
                </div>
                <a href="product.php" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
            </div>
        </div>
        <div class="col-lg-3 col-xs-6">
            <div class="small-box bg-red">
                <div class="inner">
                    <h3><?php echo $total_customers; ?></h3>
                    <p>Active Customers</p>
                </div>
                <div class="icon">
                    <i class="ionicons ion-person-stalker"></i>
                </div>
                <a href="customer.php" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
            </div>
        </div>
        
        <div class="col-lg-3 col-xs-6">
            <div class="small-box bg-olive">
                <div class="inner">
                    <h3><?php echo $total_top_category; ?></h3>
                    <p>Main Categories</p>
                </div>
                <div class="icon">
                    <i class="ionicons ion-arrow-up-b"></i>
                </div>
                <a href="top-category.php" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
            </div>
        </div>

        <div class="col-lg-3 col-xs-6">
            <div class="small-box bg-blue">
                <div class="inner">
                    <h3><?php echo $total_mid_category; ?></h3>
                    <p>Type Categories</p>
                </div>
                <div class="icon">
                    <i class="ionicons ion-android-menu"></i>
                </div>
                <a href="mid-category.php" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
            </div>
        </div>

        <div class="col-lg-3 col-xs-6">
            <div class="small-box bg-maroon">
                <div class="inner">
                    <h3><?php echo $total_end_category; ?></h3>
                    <p>Sub Categories</p>
                </div>
                <div class="icon">
                    <i class="ionicons ion-arrow-down-b"></i>
                </div>
                <a href="end-category.php" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
            </div>
        </div>

        <!-- New Size Category Box -->
        <div class="col-lg-3 col-xs-6">
            <div class="small-box bg-teal">
                <div class="inner">
                    <h3><?php echo $total_size; ?></h3>
                    <p>Sizes</p>
                </div>
                <div class="icon">
                    <i class="ionicons ion-arrow-resize"></i>
                </div>
                <a href="size.php" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
            </div>
        </div>

        <!-- New Color Category Box -->
        <div class="col-lg-3 col-xs-6">
            <div class="small-box bg-purple">
                <div class="inner">
                    <h3><?php echo $total_color; ?></h3>
                    <p>Colors</p>
                </div>
                <div class="icon">
                    <i class="ionicons ion-paintbrush"></i>
                </div>
                <a href="color.php" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
            </div>
        </div>
    </div>
</section>

<?php require_once('footer.php'); ?>
