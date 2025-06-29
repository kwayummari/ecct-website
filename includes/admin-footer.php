</div>
</main>

<!-- Footer -->
<footer class="admin-footer bg-light border-top mt-5">
    <div class="container-fluid">
        <div class="row align-items-center py-3">
            <div class="col-md-6">
                <small class="text-muted">
                    &copy; <?php echo date('Y'); ?> Environmental Conservation Community of Tanzania.
                    Admin Panel v1.0
                </small>
            </div>
            <div class="col-md-6 text-md-end">
                <small class="text-muted">
                    Last login: <?php echo date('M j, Y g:i A'); ?> |
                    <a href="<?php echo SITE_URL; ?>/admin/help.php" class="text-decoration-none">Help</a> |
                    <a href="<?php echo SITE_URL; ?>/admin/support.php" class="text-decoration-none">Support</a>
                </small>
            </div>
        </div>
    </div>
</footer>

<!-- JavaScript -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
<script src="<?php echo ASSETS_PATH; ?>/js/admin.js"></script>

<!-- Additional JavaScript for specific pages -->
<?php if (isset($additional_js)): ?>
    <?php foreach ($additional_js as $js): ?>
        <script src="<?php echo $js; ?>"></script>
    <?php endforeach; ?>
<?php endif; ?>

<!-- Inline JavaScript if needed -->
<?php if (isset($inline_js)): ?>
    <script>
        <?php echo $inline_js; ?>
    </script>
<?php endif; ?>

<!-- Auto-logout warning -->
<script>
    // Session timeout warning (30 minutes)
    let sessionTimeout = 1800000; // 30 minutes in milliseconds
    let warningTimeout = 1500000; // 25 minutes in milliseconds

    setTimeout(function() {
        if (confirm('Your session will expire in 5 minutes. Click OK to stay logged in.')) {
            // Refresh page to extend session
            window.location.reload();
        }
    }, warningTimeout);

    setTimeout(function() {
        alert('Your session has expired. You will be redirected to the login page.');
        window.location.href = '<?php echo SITE_URL; ?>/admin/login.php';
    }, sessionTimeout);
</script>

</body>

</html>