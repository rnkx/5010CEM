<?php 
// Include the header (navigation, styles, etc.)
require_once('header.php'); 
?>

<?php
// Fetch site-wide settings (record with id=1) from tbl_settings
$statement = $pdo->prepare("SELECT * FROM tbl_settings WHERE id=1");
$statement->execute();
$result = $statement->fetchAll(PDO::FETCH_ASSOC);

// Store each setting field into a PHP variable for easy use below
foreach ($result as $row) {
    $cta_title                      = $row['cta_title'];
    $cta_content                    = $row['cta_content'];
    $cta_read_more_text             = $row['cta_read_more_text'];
    $cta_read_more_url              = $row['cta_read_more_url'];
    $cta_photo                      = $row['cta_photo'];
    $featured_product_title         = $row['featured_product_title'];
    $featured_product_subtitle      = $row['featured_product_subtitle'];
    $latest_product_title           = $row['latest_product_title'];
    $latest_product_subtitle        = $row['latest_product_subtitle'];
    $popular_product_title          = $row['popular_product_title'];
    $popular_product_subtitle       = $row['popular_product_subtitle'];
    $total_featured_product_home    = $row['total_featured_product_home'];
    $total_latest_product_home      = $row['total_latest_product_home'];
    $total_popular_product_home     = $row['total_popular_product_home'];
    // These flags control whether each section is shown
    $home_service_on_off            = $row['home_service_on_off'];
    $home_welcome_on_off            = $row['home_welcome_on_off'];
    $home_featured_product_on_off   = $row['home_featured_product_on_off'];
    $home_latest_product_on_off     = $row['home_latest_product_on_off'];
    $home_popular_product_on_off    = $row['home_popular_product_on_off'];
}
?>

<!-- Bootstrap carousel for homepage slider -->
<div id="bootstrap-touch-slider" class="carousel bs-slider fade control-round indicators-line" 
     data-ride="carousel" data-pause="hover" data-interval="false" >

    <!-- Carousel indicators (dots) -->
    <ol class="carousel-indicators">
        <?php
        $i = 0;
        // Get all slider entries
        $statement = $pdo->prepare("SELECT * FROM tbl_slider");
        $statement->execute();
        $sliders = $statement->fetchAll(PDO::FETCH_ASSOC);
        foreach ($sliders as $row) {
            // Mark the first indicator as active
            echo '<li data-target="#bootstrap-touch-slider" data-slide-to="' . $i . '"'
                 . ($i == 0 ? ' class="active"' : '') . '></li>';
            $i++;
        }
        ?>
    </ol>

    <!-- Slides wrapper -->
    <div class="carousel-inner" role="listbox">
        <?php
        $i = 0;
        foreach ($sliders as $row) {
            // Determine CSS classes based on slide index and text position
            $activeClass = ($i == 0) ? 'active' : '';
            $posClass    = 'slide_style_' . strtolower($row['position']);
            // Animation classes vary by position
            $hAnim = $row['position']=='Left'   ? 'zoomInLeft'  
                   : ($row['position']=='Center' ? 'flipInX'     : 'zoomInRight');
            $pAnim = $row['position']=='Left'   ? 'fadeInLeft'  
                   : ($row['position']=='Center' ? 'fadeInDown'  : 'fadeInRight');
            $aAnim = $pAnim;
            ?>
            <div class="item <?php echo $activeClass; ?>" 
                 style="background-image:url(assets/uploads/<?php echo $row['photo']; ?>);">
                <div class="bs-slider-overlay"></div>
                <div class="container">
                    <div class="row">
                        <div class="slide-text <?php echo $posClass; ?>">
                            <!-- Slide heading -->
                            <h1 data-animation="animated <?php echo $hAnim; ?>">
                                <?php echo $row['heading']; ?>
                            </h1>
                            <!-- Slide content -->
                            <p data-animation="animated <?php echo $pAnim; ?>">
                                <?php echo nl2br($row['content']); ?>
                            </p>
                            <!-- Slide button -->
                            <a href="<?php echo $row['button_url']; ?>" target="_blank"
                               class="btn btn-primary"
                               data-animation="animated <?php echo $aAnim; ?>">
                                <?php echo $row['button_text']; ?>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <?php
            $i++;
        }
        ?>
    </div>

    <!-- Carousel controls -->
    <a class="left carousel-control" href="#bootstrap-touch-slider" role="button" data-slide="prev">
        <span class="fa fa-angle-left" aria-hidden="true"></span>
        <span class="sr-only">Previous</span>
    </a>
    <a class="right carousel-control" href="#bootstrap-touch-slider" role="button" data-slide="next">
        <span class="fa fa-angle-right" aria-hidden="true"></span>
        <span class="sr-only">Next</span>
    </a>
</div>

<?php if ($home_service_on_off == 1): ?>
<!-- Services section (if enabled) -->
<div class="service bg-gray">
    <div class="container">
        <div class="row">
            <?php
            // Fetch and display services
            $statement = $pdo->prepare("SELECT * FROM tbl_service");
            $statement->execute();
            $services = $statement->fetchAll(PDO::FETCH_ASSOC);
            foreach ($services as $row) {
                ?>
                <div class="col-md-4">
                    <div class="item">
                        <div class="photo">
                            <img src="assets/uploads/<?php echo $row['photo']; ?>" width="150" 
                                 alt="<?php echo $row['title']; ?>">
                        </div>
                        <h3><?php echo $row['title']; ?></h3>
                        <p><?php echo nl2br($row['content']); ?></p>
                    </div>
                </div>
                <?php
            }
            ?>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if ($home_featured_product_on_off == 1): ?>
<!-- Featured products carousel -->
<div class="product pt_70 pb_70">
    <div class="container">
        <div class="headline">
            <h2><?php echo $featured_product_title; ?></h2>
            <h3><?php echo $featured_product_subtitle; ?></h3>
        </div>
        <div class="product-carousel">
            <?php
            // Query featured & active products, limit by settings
            $stmt = $pdo->prepare(
                "SELECT * FROM tbl_product WHERE p_is_featured=1 AND p_is_active=1 
                 LIMIT " . intval($total_featured_product_home)
            );
            $stmt->execute();
            $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($products as $row) {
                // Calculate average rating
                $t_rating = 0;
                $stmt1 = $pdo->prepare("SELECT rating FROM tbl_rating WHERE p_id=?");
                $stmt1->execute([$row['p_id']]);
                $ratings = $stmt1->fetchAll(PDO::FETCH_ASSOC);
                $tot_rating = count($ratings);
                if ($tot_rating) {
                    foreach ($ratings as $r) {
                        $t_rating += $r['rating'];
                    }
                    $avg_rating = $t_rating / $tot_rating;
                } else {
                    $avg_rating = 0;
                }
                ?>
                <div class="item">
                    <!-- Product image -->
                    <div class="thumb">
                        <div class="photo" 
                             style="background-image:url(assets/uploads/<?php echo $row['p_featured_photo']; ?>);">
                        </div>
                        <div class="overlay"></div>
                    </div>
                    <!-- Product details -->
                    <div class="text">
                        <h3>
                            <a href="product.php?id=<?php echo $row['p_id']; ?>">
                                <?php echo $row['p_name']; ?>
                            </a>
                        </h3>
                        <h4>
                            $<?php echo $row['p_current_price']; ?>
                            <?php if ($row['p_old_price']!=''): ?>
                                <del>$<?php echo $row['p_old_price']; ?></del>
                            <?php endif; ?>
                        </h4>
                        <div class="rating">
                            <?php
                            // Output star icons based on average rating
                            if ($avg_rating > 0) {
                                for ($i = 1; $i <= 5; $i++) {
                                    echo $i <= $avg_rating 
                                        ? '<i class="fa fa-star"></i>' 
                                        : '<i class="fa fa-star-o"></i>';
                                }
                            }
                            ?>
                        </div>
                        <?php if ($row['p_qty'] == 0): ?>
                            <!-- Out of stock overlay -->
                            <div class="out-of-stock"><div class="inner">Out Of Stock</div></div>
                        <?php else: ?>
                            <p>
                                <a href="product.php?id=<?php echo $row['p_id']; ?>">
                                    <i class="fa fa-shopping-cart"></i> Add to Cart
                                </a>
                            </p>
                        <?php endif; ?>
                    </div>
                </div>
                <?php
            }
            ?>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if ($home_latest_product_on_off == 1): ?>
<!-- Latest products section -->
<div class="product bg-gray pt_70 pb_30">
    <div class="container">
        <div class="headline">
            <h2><?php echo $latest_product_title; ?></h2>
            <h3><?php echo $latest_product_subtitle; ?></h3>
        </div>
        <div class="product-carousel">
            <?php
            // Query most recently added active products
            $stmt = $pdo->prepare(
                "SELECT * FROM tbl_product WHERE p_is_active=1 
                 ORDER BY p_id DESC LIMIT " . intval($total_latest_product_home)
            );
            $stmt->execute();
            $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($products as $row) {
                // (Repeat rating & stock logic as above)
                ?>
                <div class="item">
                    <!-- ... identical structure to featured products ... -->
                </div>
                <?php
            }
            ?>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if ($home_popular_product_on_off == 1): ?>
<!-- Popular products section -->
<div class="product pt_70 pb_70">
    <div class="container">
        <div class="headline">
            <h2><?php echo $popular_product_title; ?></h2>
            <h3><?php echo $popular_product_subtitle; ?></h3>
        </div>
        <div class="product-carousel">
            <?php
            // Query most-viewed active products
            $stmt = $pdo->prepare(
                "SELECT * FROM tbl_product WHERE p_is_active=1 
                 ORDER BY p_total_view DESC LIMIT " . intval($total_popular_product_home)
            );
            $stmt->execute();
            $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($products as $row) {
                // (Repeat rating & stock logic)
                ?>
                <div class="item">
                    <!-- ... -->
                </div>
                <?php
            }
            ?>
        </div>
    </div>
</div>
<?php endif; ?>

<?php 
// Include the footer (closing tags, scripts, etc.)
require_once('footer.php'); 
?>
