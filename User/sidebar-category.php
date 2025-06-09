<?php 
// Display the category sidebar header
<h3><?php echo LANG_VALUE_49; /* "Categories" or similar */ ?></h3>
<div id="left" class="span3">

    <!-- Top-level categories menu -->
    <ul id="menu-group-1" class="nav menu">
        <?php
            $i = 0;
            // Fetch all top categories marked to show on the menu
            $stmt = $pdo->prepare("SELECT * FROM tbl_top_category WHERE show_on_menu=1");
            $stmt->execute();
            $topCats = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($topCats as $row) {
                $i++;
                ?>
                <li class="cat-level-1 deeper parent">
                    <!-- Link to top-category page -->
                    <a href="product-category.php?id=<?php echo $row['tcat_id']; ?>&type=top-category">
                        <!-- Toggle icon for collapse -->
                        <span data-toggle="collapse" data-parent="#menu-group-1" 
                              href="#cat-lvl1-id-<?php echo $i; ?>" class="sign">
                            <i class="fa fa-plus"></i>
                        </span>
                        <!-- Category name -->
                        <span class="lbl"><?php echo $row['tcat_name']; ?></span>
                    </a>

                    <!-- Second-level (mid) categories collapse list -->
                    <ul class="children nav-child unstyled small collapse" id="cat-lvl1-id-<?php echo $i; ?>">
                        <?php
                        $j = 0;
                        // Fetch mid-categories under this top category
                        $stmt1 = $pdo->prepare("SELECT * FROM tbl_mid_category WHERE tcat_id = ?");
                        $stmt1->execute([$row['tcat_id']]);
                        $midCats = $stmt1->fetchAll(PDO::FETCH_ASSOC);

                        foreach ($midCats as $row1) {
                            $j++;
                            ?>
                            <li class="deeper parent">
                                <!-- Link to mid-category page -->
                                <a href="product-category.php?id=<?php echo $row1['mcat_id']; ?>&type=mid-category">
                                    <!-- Collapse toggle for end categories -->
                                    <span data-toggle="collapse" data-parent="#menu-group-1" 
                                          href="#cat-lvl2-id-<?php echo $i . $j; ?>" class="sign">
                                        <i class="fa fa-plus"></i>
                                    </span>
                                    <!-- Mid category name -->
                                    <span class="lbl lbl1"><?php echo $row1['mcat_name']; ?></span>
                                </a>

                                <!-- Third-level (end) categories collapse list -->
                                <ul class="children nav-child unstyled small collapse" id="cat-lvl2-id-<?php echo $i . $j; ?>">
                                    <?php
                                    $k = 0;
                                    // Fetch end-categories under this mid category
                                    $stmt2 = $pdo->prepare("SELECT * FROM tbl_end_category WHERE mcat_id = ?");
                                    $stmt2->execute([$row1['mcat_id']]);
                                    $endCats = $stmt2->fetchAll(PDO::FETCH_ASSOC);

                                    foreach ($endCats as $row2) {
                                        $k++;
                                        ?>
                                        <li class="item-<?php echo $i . $j . $k; ?>">
                                            <!-- Link to end-category page -->
                                            <a href="product-category.php?id=<?php echo $row2['ecat_id']; ?>&type=end-category">
                                                <span class="sign"></span>
                                                <span class="lbl lbl1"><?php echo $row2['ecat_name']; ?></span>
                                            </a>
                                        </li>
                                        <?php
                                    }
                                    ?>
                                </ul>
                            </li>
                            <?php
                        }
                        ?>
                    </ul>
                </li>
                <?php
            }
        ?>
    </ul>

</div>
