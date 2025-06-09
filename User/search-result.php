<?php 
// Include header (navigation, session start, etc.)
require_once('header.php'); 

// Validate that search_text parameter is provided and non-empty; otherwise redirect home
if (!isset($_REQUEST['search_text']) || trim($_REQUEST['search_text']) === '') {
    header('Location: index.php');
    exit;
} 

// Sanitize the raw search text for display and SQL matching
$raw_search = strip_tags($_REQUEST['search_text']);
$search_pattern = '%' . $raw_search . '%';

// Fetch banner image setting for search results page
$statement = $pdo->prepare("SELECT banner_search FROM tbl_settings WHERE id = 1");
$statement->execute();
$setting = $statement->fetch(PDO::FETCH_ASSOC);
$banner_search = $setting['banner_search'];
?>

<!-- Page banner with dynamic search term -->
<div class="page-banner" style="background-image:url(assets/uploads/<?php echo $banner_search; ?>);">
    <div class="overlay"></div>
    <div class="inner">
        <h1>Search By: <?php echo htmlspecialchars($raw_search); ?></h1>
    </div>
</div>

<div class="page">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="product product-cat">
                    <div class="row">
                        <?php 
                        /* ========== Pagination Setup ========== */
                        $limit = 12;                                 // items per page
                        $adjacents = 5;                           // pages to show around current
                        $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
                        $start = ($page - 1) * $limit;

                        // Count total matching products
                        $stmt = $pdo->prepare(
                            "SELECT COUNT(*) FROM tbl_product 
                             WHERE p_is_active = 1 
                             AND p_name LIKE ?"
                        );
                        $stmt->execute([$search_pattern]);
                        $total_pages = $stmt->fetchColumn();

                        // Fetch current page of products
                        $stmt = $pdo->prepare(
                            "SELECT * FROM tbl_product 
                             WHERE p_is_active = 1 
                             AND p_name LIKE ? 
                             LIMIT ?, ?"
                        );
                        $stmt->bindValue(1, $search_pattern, PDO::PARAM_STR);
                        $stmt->bindValue(2, $start, PDO::PARAM_INT);
                        $stmt->bindValue(3, $limit, PDO::PARAM_INT);
                        $stmt->execute();
                        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

                        // Build pagination HTML
                        $lastpage = ceil($total_pages / $limit);
                        $prev = $page - 1;
                        $next = $page + 1;
                        $target = BASE_URL . 'search-result.php?search_text=' . urlencode($raw_search);
                        $pagination = '';
                        if ($lastpage > 1) {
                            $pagination .= '<div class="pagination">';
                            // Previous link
                            if ($page > 1) {
                                $pagination .= '<a href="' . $target . '&page=' . $prev . '">&#171; previous</a>';
                            } else {
                                $pagination .= '<span class="disabled">&#171; previous</span>';
                            }
                            // Page number links (simplified for brevity)
                            for ($counter = 1; $counter <= $lastpage; $counter++) {
                                if ($counter == $page) {
                                    $pagination .= '<span class="current">' . $counter . '</span>';
                                } else {
                                    $pagination .= '<a href="' . $target . '&page=' . $counter . '">' . $counter . '</a>';
                                }
                            }
                            // Next link
                            if ($page < $lastpage) {
                                $pagination .= '<a href="' . $target . '&page=' . $next . '">next &#187;</a>';
                            } else {
                                $pagination .= '<span class="disabled">next &#187;</span>';
                            }
                            $pagination .= '</div>';
                        }

                        // Display message if no results
                        if ($total_pages == 0) {
                            echo '<div class="col-md-12" style="color:red;font-size:18px;">No result found</div>';
                        } else {
                            // Loop through each product and render its card
                            foreach ($results as $row) {
                                ?>
                                <div class="col-md-3 item item-search-result">
                                    <div class="inner">
                                        <!-- Product thumbnail -->
                                        <div class="thumb">
                                            <div class="photo" style="background-image:url(assets/uploads/<?php echo $row['p_featured_photo']; ?>);"></div>
                                            <div class="overlay"></div>
                                        </div>
                                        <div class="text">
                                            <h3><a href="product.php?id=<?php echo $row['p_id']; ?>"><?php echo htmlspecialchars($row['p_name']); ?></a></h3>
                                            <h4>
                                                $<?php echo number_format($row['p_current_price'], 2); ?>
                                                <?php if (!empty($row['p_old_price'])): ?>
                                                    <del>$<?php echo number_format($row['p_old_price'],2); ?></del>
                                                <?php endif; ?>
                                            </h4>
                                            <!-- [Rating display code here, similar to other pages] -->
                                            <?php if ($row['p_qty'] == 0): ?>
                                                <div class="out-of-stock"><div class="inner">Out Of Stock</div></div>
                                            <?php else: ?>
                                                <p><a href="product.php?id=<?php echo $row['p_id']; ?>">Add to Cart</a></p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                <?php
                            }
                            // Clear floats and show pagination
                            echo '<div class="clear"></div>';
                            echo $pagination;
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php 
// Include footer (closing tags, scripts)
require_once('footer.php'); 
?>
