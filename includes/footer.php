</main>

<!-- Footer -->
<footer class="bg-dark text-white mt-5">
    <div class="container py-5">
        <div class="row">
            <!-- About Section -->
            <div class="col-lg-4 col-md-6 mb-4">
                <h5 class="mb-3">About ECCT</h5>
                <img src="<?php echo SITE_URL . '/' . $site_logo; ?>"
                    alt="<?php echo htmlspecialchars($site_name); ?>"
                    height="40" class="mb-3">
                <p class="text-light">
                    <?php echo htmlspecialchars($site_tagline); ?>
                </p>
                <p class="small text-muted">
                    ECCT empowers local communities to create cleaner, greener, resilient and sustainable environments through tackling global environmental pollution.
                </p>
            </div>

            <!-- Quick Links -->
            <div class="col-lg-2 col-md-6 mb-4">
                <h5 class="mb-3">Quick Links</h5>
                <ul class="list-unstyled">
                    <li class="mb-2">
                        <a href="<?php echo SITE_URL; ?>" class="text-light text-decoration-none">
                            <i class="fas fa-chevron-right me-2 small"></i>Home
                        </a>
                    </li>
                    <li class="mb-2">
                        <a href="<?php echo SITE_URL; ?>/about.php" class="text-light text-decoration-none">
                            <i class="fas fa-chevron-right me-2 small"></i>About Us
                        </a>
                    </li>
                    <li class="mb-2">
                        <a href="<?php echo SITE_URL; ?>/programs.php" class="text-light text-decoration-none">
                            <i class="fas fa-chevron-right me-2 small"></i>Programs
                        </a>
                    </li>
                    <li class="mb-2">
                        <a href="<?php echo SITE_URL; ?>/campaigns.php" class="text-light text-decoration-none">
                            <i class="fas fa-chevron-right me-2 small"></i>Campaigns
                        </a>
                    </li>
                    <li class="mb-2">
                        <a href="<?php echo SITE_URL; ?>/volunteer.php" class="text-light text-decoration-none">
                            <i class="fas fa-chevron-right me-2 small"></i>Volunteer
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Resources -->
            <div class="col-lg-2 col-md-6 mb-4">
                <h5 class="mb-3">Resources</h5>
                <ul class="list-unstyled">
                    <li class="mb-2">
                        <a href="<?php echo SITE_URL; ?>/news.php" class="text-light text-decoration-none">
                            <i class="fas fa-chevron-right me-2 small"></i>News & Events
                        </a>
                    </li>
                    <li class="mb-2">
                        <a href="<?php echo SITE_URL; ?>/gallery.php" class="text-light text-decoration-none">
                            <i class="fas fa-chevron-right me-2 small"></i>Gallery
                        </a>
                    </li>
                    <li class="mb-2">
                        <a href="<?php echo SITE_URL; ?>/contact.php" class="text-light text-decoration-none">
                            <i class="fas fa-chevron-right me-2 small"></i>Contact Us
                        </a>
                    </li>
                    <li class="mb-2">
                        <a href="<?php echo SITE_URL; ?>/admin/" class="text-light text-decoration-none">
                            <i class="fas fa-chevron-right me-2 small"></i>Admin Login
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Contact Info -->
            <div class="col-lg-4 col-md-6 mb-4">
                <h5 class="mb-3">Contact Information</h5>
                <div class="contact-info">
                    <div class="d-flex align-items-start mb-3">
                        <i class="fas fa-map-marker-alt me-3 mt-1 text-primary"></i>
                        <div>
                            <strong>Address:</strong><br>
                            <span class="text-light">
                                <?php echo htmlspecialchars($db->getSetting('contact_address', 'Dar es Salaam, Tanzania')); ?>
                            </span>
                        </div>
                    </div>

                    <div class="d-flex align-items-center mb-3">
                        <i class="fas fa-phone me-3 text-primary"></i>
                        <div>
                            <strong>Phone:</strong><br>
                            <a href="tel:<?php echo $db->getSetting('contact_phone', ''); ?>" class="text-light text-decoration-none">
                                <?php echo htmlspecialchars($db->getSetting('contact_phone', '+255 123 456 789')); ?>
                            </a>
                        </div>
                    </div>

                    <div class="d-flex align-items-center mb-3">
                        <i class="fas fa-envelope me-3 text-primary"></i>
                        <div>
                            <strong>Email:</strong><br>
                            <a href="mailto:<?php echo $db->getSetting('contact_email', SITE_EMAIL); ?>" class="text-light text-decoration-none">
                                <?php echo htmlspecialchars($db->getSetting('contact_email', SITE_EMAIL)); ?>
                            </a>
                        </div>
                    </div>

                    <!-- Social Media Links -->
                    <div class="social-links mt-4">
                        <h6 class="mb-3">Follow Us</h6>
                        <div class="d-flex">
                            <?php if (!empty($facebook_url) && $facebook_url !== '#'): ?>
                                <a href="<?php echo $facebook_url; ?>" target="_blank" class="btn btn-outline-primary btn-sm me-2">
                                    <i class="fab fa-facebook-f"></i>
                                </a>
                            <?php endif; ?>
                            <?php if (!empty($twitter_url) && $twitter_url !== '#'): ?>
                                <a href="<?php echo $twitter_url; ?>" target="_blank" class="btn btn-outline-primary btn-sm me-2">
                                    <i class="fab fa-twitter"></i>
                                </a>
                            <?php endif; ?>
                            <?php if (!empty($instagram_url) && $instagram_url !== '#'): ?>
                                <a href="<?php echo $instagram_url; ?>" target="_blank" class="btn btn-outline-primary btn-sm me-2">
                                    <i class="fab fa-instagram"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent News -->
        <div class="row mt-4 pt-4 border-top border-secondary">
            <div class="col-12">
                <h5 class="mb-3">Latest News</h5>
                <div class="row">
                    <?php
                    $recent_news = get_recent_content('news', 3);
                    if ($recent_news):
                    ?>
                        <?php foreach ($recent_news as $news): ?>
                            <div class="col-md-4 mb-3">
                                <div class="card bg-transparent border-secondary">
                                    <div class="card-body p-3">
                                        <h6 class="card-title text-white">
                                            <a href="<?php echo SITE_URL; ?>/news.php?id=<?php echo $news['id']; ?>"
                                                class="text-white text-decoration-none">
                                                <?php echo htmlspecialchars(truncate_text($news['title'], 60)); ?>
                                            </a>
                                        </h6>
                                        <p class="card-text small text-muted">
                                            <?php echo htmlspecialchars(truncate_text(strip_tags($news['excerpt'] ?: $news['content']), 100)); ?>
                                        </p>
                                        <small class="text-muted">
                                            <i class="fas fa-calendar me-1"></i>
                                            <?php echo format_date($news['publish_date']); ?>
                                        </small>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="col-12">
                            <p class="text-muted">No recent news available.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Newsletter Subscription -->
    <div class="bg-primary py-4">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h5 class="mb-1">Stay Updated</h5>
                    <p class="mb-0 small">Subscribe to our newsletter for environmental updates and news.</p>
                </div>
                <div class="col-md-6">
                    <form action="<?php echo SITE_URL; ?>/includes/newsletter-subscribe.php" method="POST" class="d-flex">
                        <?php echo csrf_field(); ?>
                        <input type="email" name="email" class="form-control me-2" placeholder="Enter your email" required>
                        <button type="submit" class="btn btn-light">Subscribe</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Copyright -->
    <div class="bg-darker py-3">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <p class="mb-0 text-muted small">
                        &copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars($site_name); ?>. All rights reserved.
                    </p>
                </div>
                <div class="col-md-6 text-md-end">
                    <div class="d-flex justify-content-md-end">
                        <a href="<?php echo SITE_URL; ?>/privacy-policy.php" class="text-muted text-decoration-none small me-3">
                            Privacy Policy
                        </a>
                        <a href="<?php echo SITE_URL; ?>/terms-of-service.php" class="text-muted text-decoration-none small">
                            Terms of Service
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</footer>

<!-- Back to Top Button -->
<button id="backToTop" class="btn btn-primary position-fixed d-none"
    style="bottom: 20px; right: 20px; z-index: 1000;">
    <i class="fas fa-chevron-up"></i>
</button>

<!-- JavaScript -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
<script src="<?php echo ASSETS_PATH; ?>/js/main.js"></script>

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

</body>

</html>