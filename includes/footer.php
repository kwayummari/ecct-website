</main>

<!-- Footer -->
<style>
    /* Footer Pattern Backgrounds */
    .footer-main {
        background: #2c3e50;
        background-image:
            radial-gradient(circle at 20% 50%, rgba(0, 123, 255, 0.1) 0%, transparent 50%),
            radial-gradient(circle at 80% 20%, rgba(40, 167, 69, 0.1) 0%, transparent 50%),
            radial-gradient(circle at 40% 80%, rgba(32, 201, 151, 0.1) 0%, transparent 50%),
            linear-gradient(135deg, transparent 25%, rgba(0, 123, 255, 0.05) 25%, rgba(0, 123, 255, 0.05) 50%, transparent 50%, transparent 75%, rgba(40, 167, 69, 0.05) 75%);
        background-size: 400px 400px, 350px 350px, 300px 300px, 60px 60px;
        background-position: 0 0, 100px 100px, 200px 200px, 0 0;
        position: relative;
    }

    .footer-main::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-image:
            url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23007bff' fill-opacity='0.03'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        opacity: 0.3;
        pointer-events: none;
    }

    .footer-newsletter {
        background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
        background-image:
            url("data:image/svg+xml,%3Csvg width='40' height='40' viewBox='0 0 40 40' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='%23ffffff' fill-opacity='0.1' fill-rule='evenodd'%3E%3Cpath d='M20 20c0 11.046-8.954 20-20 20v20h40V20H20z'/%3E%3C/g%3E%3C/svg%3E"),
            repeating-linear-gradient(45deg, transparent, transparent 10px, rgba(255, 255, 255, 0.05) 10px, rgba(255, 255, 255, 0.05) 20px);
        position: relative;
        overflow: hidden;
    }

    .footer-newsletter::before {
        content: '';
        position: absolute;
        top: -50%;
        left: -50%;
        width: 200%;
        height: 200%;
        background:
            radial-gradient(circle, rgba(255, 255, 255, 0.1) 1px, transparent 1px);
        background-size: 20px 20px;
        animation: patternMove 20s linear infinite;
        pointer-events: none;
    }

    @keyframes patternMove {
        0% {
            transform: translateX(0) translateY(0);
        }

        100% {
            transform: translateX(20px) translateY(20px);
        }
    }

    .footer-copyright {
        background: #1a252f;
        background-image:
            linear-gradient(90deg, rgba(0, 123, 255, 0.1) 50%, transparent 50%),
            linear-gradient(rgba(40, 167, 69, 0.05) 50%, transparent 50%);
        background-size: 20px 20px, 20px 10px;
        position: relative;
    }

    .footer-copyright::after {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-image:
            url("data:image/svg+xml,%3Csvg width='20' height='20' viewBox='0 0 20 20' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='%2320c997' fill-opacity='0.05'%3E%3Ccircle cx='3' cy='3' r='1'/%3E%3Ccircle cx='13' cy='13' r='1'/%3E%3C/g%3E%3C/svg%3E");
        pointer-events: none;
    }

    /* Enhanced footer content */
    .footer-main .container {
        position: relative;
        z-index: 1;
    }

    .footer-newsletter .container {
        position: relative;
        z-index: 1;
    }

    .footer-copyright .container {
        position: relative;
        z-index: 1;
    }

    /* Decorative elements */
    .footer-main h5 {
        position: relative;
    }

    .footer-main h5::after {
        content: '';
        position: absolute;
        bottom: -5px;
        left: 0;
        width: 40px;
        height: 2px;
        background: linear-gradient(90deg, #007bff, #20c997);
        border-radius: 1px;
    }

    /* Enhanced social links */
    .social-links a {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 40px;
        height: 40px;
        margin-right: 10px;
        border-radius: 50%;
        background: rgba(0, 123, 255, 0.1);
        color: #007bff;
        text-decoration: none;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .social-links a::before {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        width: 0;
        height: 0;
        background: linear-gradient(135deg, #007bff, #20c997);
        border-radius: 50%;
        transition: all 0.3s ease;
        transform: translate(-50%, -50%);
    }

    .social-links a:hover::before {
        width: 100%;
        height: 100%;
    }

    .social-links a:hover {
        color: white;
        transform: translateY(-2px);
    }

    .social-links a i {
        position: relative;
        z-index: 1;
    }

    /* Newsletter form enhancement */
    .footer-newsletter .form-control {
        background: rgba(255, 255, 255, 0.95);
        border: none;
        border-radius: 25px;
        padding: 12px 20px;
    }

    .footer-newsletter .btn {
        border-radius: 25px;
        padding: 12px 25px;
        font-weight: 600;
        background: white;
        color: #007bff;
        border: none;
        transition: all 0.3s ease;
    }

    .footer-newsletter .btn:hover {
        background: rgba(255, 255, 255, 0.9);
        transform: translateY(-1px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
    }
</style>

<footer class="footer-main text-white mt-5">
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
                    <div class="mt-4">
                        <h6 class="mb-3">Follow Us</h6>
                        <div class="social-links">
                            <?php if (!empty($facebook_url) && $facebook_url !== '#'): ?>
                                <a href="<?php echo $facebook_url; ?>" target="_blank">
                                    <i class="fab fa-facebook-f"></i>
                                </a>
                            <?php endif; ?>
                            <?php if (!empty($twitter_url) && $twitter_url !== '#'): ?>
                                <a href="<?php echo $twitter_url; ?>" target="_blank">
                                    <i class="fab fa-twitter"></i>
                                </a>
                            <?php endif; ?>
                            <?php if (!empty($instagram_url) && $instagram_url !== '#'): ?>
                                <a href="<?php echo $instagram_url; ?>" target="_blank">
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
    <div class="footer-newsletter py-4">
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
    <div class="footer-copyright py-3">
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