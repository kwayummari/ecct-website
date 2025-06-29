</div>
<!-- End Content Area -->
</div>
<!-- End Main Content -->
</div>
<!-- End Admin Wrapper -->

<!-- JavaScript -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
<script src="<?php echo ASSETS_PATH; ?>/js/admin.js"></script>

<!-- Additional JavaScript for specific pages -->
<?php if (isset($additional_js) && is_array($additional_js)): ?>
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

</body>

</html>