<?php 
// Include header (site navigation, styles)
require_once('header.php'); 
?>

<!-- Main page content wrapper -->
<div class="page">
    <div class="container">
        <div class="row">            
            <div class="col-md-12">
                <p>
                    <!-- Section title -->
                    <h3 style="margin-top:20px;">
                        <?php echo LANG_VALUE_121; /* e.g., "Welcome" or section heading */ ?>
                    </h3>
                    <!-- Button to return to dashboard -->
                    <a href="dashboard.php" class="btn btn-success">
                        <?php echo LANG_VALUE_91; /* e.g., "Go to Dashboard" text */ ?>
                    </a>
                </p>
            </div>
        </div>
    </div>
</div>

<?php 
// Include footer (closing tags, scripts)
require_once('footer.php'); 
?>
