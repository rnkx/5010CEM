<?php 
// Include header (navigation, site-wide CSS/JS)
require_once('header.php'); 

// Fetch banner image for product category from settings
$statement = $pdo->prepare("SELECT * FROM tbl_settings WHERE id=1");
$statement->execute();
$result = $statement->fetchAll(PDO::FETCH_ASSOC);
foreach ($result as $row) {
    $banner_product_category = $row['banner_product_category'];
}

// Validate incoming category parameters
if (!isset($_REQUEST['id']) || !isset($_REQUEST['type'])) {
    // Redirect to homepage if missing parameters
    header('Location: index.php');
    exit;
} else {
    // Ensure type is one of the allowed category levels
    $type = $_REQUEST['type'];
    if (!in_array($type, ['top-category','mid-category','end-category'])) {
        header('Location: index.php');
        exit;
    }

    // Load all top, mid, and end categories into arrays
    $top = $topNames = [];
    $mid = $midNames = $midParent = [];
    $end = $endNames = $endParent = [];

    // Top categories
    $stmt = $pdo->prepare("SELECT * FROM tbl_top_category");
    $stmt->execute();
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $top[]     = $row['tcat_id'];
        $topNames[] = $row['tcat_name'];
    }
    // Mid categories
    $stmt = $pdo->prepare("SELECT * FROM tbl_mid_category");
    $stmt->execute();
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $mid[]      = $row['mcat_id'];
        $midNames[] = $row['mcat_name'];
        $midParent[] = $row['tcat_id'];
    }
    // End categories
    $stmt = $pdo->prepare("SELECT * FROM tbl_end_category");
    $stmt->execute();
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $end[]      = $row['ecat_id'];
        $endNames[] = $row['ecat_name'];
        $endParent[] = $row['mcat_id'];
    }

    // Determine title and list of end-category IDs to display
    $id = $_REQUEST['id'];
    $final_ecat_ids = [];
    switch ($type) {
        case 'top-category':
            if (!in_array($id, $top)) { header('Location: index.php'); exit; }
            // Title = matching top category name
            $title = $topNames[array_search($id, $top)];
            // Gather all mid-category IDs under this top
            $childrenMid = array_keys($midParent, $id);
            // Gather end-category IDs under those mids
            foreach ($childrenMid as $idx) {
                $childrenEnd = array_keys($endParent, $mid[$idx]);
                foreach ($childrenEnd as $eidx) {
                    $final_ecat_ids[] = $end[$eidx];
                }
            }
            break;

        case 'mid-category':
            if (!in_array($id, $mid)) { header('Location: index.php'); exit; }
            // Title = matching mid category name
            $title = $midNames[array_search($id, $mid)];
            // Gather end-category IDs under this mid
            $childrenEnd = array_keys($endParent, $id);
            foreach ($childrenEnd as $eidx) {
                $final_ecat_ids[] = $end[$eidx];
            }
            break;

        case 'end-category':
            if (!in_array($id, $end)) { header('Location: index.php'); exit; }
            // Title = matching end category name
            $title = $endNames[array_search($id, $end)];
            // Only this end category
            $final_ecat_ids[] = $id;
            break;
    }
}
?>

<!-- Page banner with category title -->
<div class="page-banner" style="background-image: url(assets/uploads/<?php echo $banner_product_category; ?>)">
    <div class="inner">
        <h1><?php echo LANG_VALUE_50; /* "Category:" */ ?> <?php echo $title; ?></h1>
    </div>
</div>

<div class="page">
    <div class="container">
        <div class="row">
            <!-- Sidebar categories -->
            <div class="col-md-3">
                <?php require_once('sidebar-category.php'); ?>
            </div>
            <!-- Products listing -->
            <div class="col-md-9">
                <h3><?php echo LANG_VALUE_51; /* "Products in" */ ?> "<?php echo $title; ?>"</h3>
                <div class="product product-cat">
                    <div class="row">
                        <?php
                        // Check if any products exist under final end-category IDs
                        $prod_count = 0;
                        $prodCats = [];
                        $stmt = $pdo->prepare("SELECT ecat_id FROM tbl_product");
                        $stmt->execute();
                        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
                            $prodCats[] = $row['ecat_id'];
                        }
                        foreach ($final_ecat_ids as $ecid) {
                            if (in_array($ecid, $prodCats)) {
                                $prod_count++;
                            }
                        }

                        if ($prod_count == 0) {
                            // No products found
                            echo '<div class="pl_15">' . LANG_VALUE_153 . '</div>';
                        } else {
                            // Loop through each end category and display its products
                            foreach ($final_ecat_ids as $ecid) {
                                $stmt = $pdo->prepare(
                                    "SELECT * FROM tbl_product WHERE ecat_id=? AND p_is_active=1"
                                );
                                $stmt->execute([$ecid]);
                                foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
                                    // Calculate average rating for each product
                                    $t_rating = 0;
                                    $rs = $pdo->prepare("SELECT rating FROM tbl_rating WHERE p_id=?");
                                    $rs->execute([$row['p_id']]);
                                    $ratings = $rs->fetchAll(PDO::FETCH_ASSOC);
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
                                    <div class="col-md-4 item item-product-cat">
                                        <div class="inner">
                                            <!-- Product thumbnail -->
                                            <div class="thumb">
                                                <div class="photo" style="background-image:url(assets/uploads/<?php echo $row['p_featured_photo']; ?>);"></div>
                                                <div class="overlay"></div>
                                            </div>
                                            <!-- Product info -->
                                            <div class="text">
                                                <h3><a href="product.php?id=<?php echo $row['p_id']; ?>"><?php echo $row['p_name']; ?></a></h3>
                                                <h4>
                                                    <?php echo LANG_VALUE_1; /* currency symbol */ ?><?php echo $row['p_current_price']; ?>
                                                    <?php if ($row['p_old_price'] != ''): ?>
                                                        <del><?php echo LANG_VALUE_1; ?><?php echo $row['p_old_price']; ?></del>
                                                    <?php endif; ?>
                                                </h4>
                                                <div class="rating">
                                                    <?php
                                                    // Display star icons based on average rating
                                                    if ($avg_rating > 0) {
                                                        for ($i = 1; $i <= 5; $i++) {
                                                            echo ($i <= $avg_rating)
                                                                ? '<i class="fa fa-star"></i>'
                                                                : '<i class="fa fa-star-o"></i>';
                                                        }
                                                    }
                                                    ?>
                                                </div>
                                                <?php if ($row['p_qty'] == 0): ?>
                                                    <!-- Out of stock badge -->
                                                    <div class="out-of-stock"><div class="inner">Out Of Stock</div></div>
                                                <?php else: ?>
                                                    <p><a href="product.php?id=<?php echo $row['p_id']; ?>">
                                                        <i class="fa fa-shopping-cart"></i> <?php echo LANG_VALUE_154; /* "Add to Cart" */ ?>
                                                    </a></p>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                    <?php
                                }
                            }
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php 
// Include footer
require_once('footer.php'); 
?>
