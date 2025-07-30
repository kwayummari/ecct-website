</main>

<!-- Footer -->
<style>
    /* Modern Footer Styling */
    .footer-main {
        background:
            url("data:image/svg+xml,%3Csvg width='80' height='80' viewBox='0 0 80 80' xmlns='http://www.w3.org/2000/svg'%3E%3Cdefs%3E%3Cpattern id='hexagons' x='0' y='0' width='40' height='40' patternUnits='userSpaceOnUse'%3E%3Cpolygon points='20,5 35,15 35,30 20,40 5,30 5,15' fill='none' stroke='%23208836' stroke-width='0.8' opacity='0.08'/%3E%3C/pattern%3E%3C/defs%3E%3Crect width='80' height='80' fill='url(%23hexagons)'/%3E%3C/svg%3E"),
            linear-gradient(135deg, #1a2e1a 0%, #1e3e21 50%, #0f2f16 100%);
        position: relative;
        overflow: hidden;
    }

    .footer-main::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background:
            url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cdefs%3E%3Cpattern id='dots' x='0' y='0' width='20' height='20' patternUnits='userSpaceOnUse'%3E%3Ccircle cx='10' cy='10' r='2' fill='%23208836' opacity='0.1'/%3E%3C/pattern%3E%3C/defs%3E%3Crect width='60' height='60' fill='url(%23dots)'/%3E%3C/svg%3E"),
            radial-gradient(circle at 25% 25%, rgba(32, 136, 54, 0.15) 0%, transparent 40%),
            radial-gradient(circle at 75% 75%, rgba(32, 136, 54, 0.12) 0%, transparent 40%),
            radial-gradient(circle at 50% 50%, rgba(32, 136, 54, 0.08) 0%, transparent 60%);
        background-size: 120px 120px, 600px 600px, 800px 800px, 400px 400px;
        animation: floatBg 20s ease-in-out infinite;
        pointer-events: none;
    }

    .footer-main::after {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-image:
            url("data:image/svg+xml,%3Csvg width='100' height='100' viewBox='0 0 100 100' xmlns='http://www.w3.org/2000/svg'%3E%3Cdefs%3E%3Cpattern id='triangles' x='0' y='0' width='20' height='20' patternUnits='userSpaceOnUse'%3E%3Cpolygon points='10,2 18,16 2,16' fill='none' stroke='%23208836' stroke-width='0.6' opacity='0.06'/%3E%3C/pattern%3E%3C/defs%3E%3Crect width='100' height='100' fill='url(%23triangles)'/%3E%3C/svg%3E");
        opacity: 0.4;
        pointer-events: none;
    }

    @keyframes floatBg {

        0%,
        100% {
            transform: translateY(0px) rotate(0deg);
        }

        33% {
            transform: translateY(-20px) rotate(1deg);
        }

        66% {
            transform: translateY(10px) rotate(-0.5deg);
        }
    }

    /* Modern Newsletter Section */
    .footer-newsletter {
        background:
            url("data:image/svg+xml,%3Csvg width='100' height='100' viewBox='0 0 100 100' xmlns='http://www.w3.org/2000/svg'%3E%3Cdefs%3E%3Cpattern id='waves' x='0' y='0' width='50' height='50' patternUnits='userSpaceOnUse'%3E%3Cpath d='M0,25 Q12.5,10 25,25 Q37.5,40 50,25' fill='none' stroke='%23ffffff' stroke-width='1' opacity='0.1'/%3E%3C/pattern%3E%3C/defs%3E%3Crect width='100' height='100' fill='url(%23waves)'/%3E%3C/svg%3E"),
            linear-gradient(135deg, #208836 0%, #1a6b2d 100%);
        position: relative;
        overflow: hidden;
    }

    .footer-newsletter::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background:
            url("data:image/svg+xml,%3Csvg width='80' height='80' viewBox='0 0 80 80' xmlns='http://www.w3.org/2000/svg'%3E%3Cdefs%3E%3Cpattern id='diamonds' x='0' y='0' width='20' height='20' patternUnits='userSpaceOnUse'%3E%3Cpolygon points='10,2 18,10 10,18 2,10' fill='none' stroke='%23ffffff' stroke-width='0.5' opacity='0.08'/%3E%3C/pattern%3E%3C/defs%3E%3Crect width='80' height='80' fill='url(%23diamonds)'/%3E%3C/svg%3E"),
            linear-gradient(45deg, transparent 30%, rgba(255, 255, 255, 0.05) 30%, rgba(255, 255, 255, 0.05) 70%, transparent 70%),
            linear-gradient(-45deg, transparent 30%, rgba(255, 255, 255, 0.03) 30%, rgba(255, 255, 255, 0.03) 70%, transparent 70%);
        background-size: 80px 80px, 60px 60px, 40px 40px;
        animation: slidePattern 15s linear infinite;
        pointer-events: none;
    }

    @keyframes slidePattern {
        0% {
            transform: translateX(0);
        }

        100% {
            transform: translateX(60px);
        }
    }

    /* Modern Copyright Section */
    .footer-copyright {
        background:
            url("data:image/svg+xml,%3Csvg width='40' height='40' viewBox='0 0 40 40' xmlns='http://www.w3.org/2000/svg'%3E%3Cdefs%3E%3Cpattern id='crosses' x='0' y='0' width='20' height='20' patternUnits='userSpaceOnUse'%3E%3Cpath d='M10,0 L10,20 M0,10 L20,10' stroke='%23208836' stroke-width='0.5' opacity='0.06'/%3E%3C/pattern%3E%3C/defs%3E%3Crect width='40' height='40' fill='url(%23crosses)'/%3E%3C/svg%3E"),
            linear-gradient(135deg, #0c1a0c 0%, #1a1a1a 100%);
        position: relative;
    }

    .footer-copyright::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 1px;
        background: linear-gradient(90deg, transparent, #208836, #2ea344, #208836, transparent);
        animation: shimmer 3s ease-in-out infinite;
    }

    @keyframes shimmer {

        0%,
        100% {
            opacity: 0.3;
        }

        50% {
            opacity: 1;
        }
    }

    /* Modern Content Styling */
    .footer-main .container {
        position: relative;
        z-index: 2;
    }

    .footer-newsletter .container {
        position: relative;
        z-index: 2;
    }

    .footer-copyright .container {
        position: relative;
        z-index: 2;
    }

    /* Modern Section Headers */
    .footer-main h5,
    .footer-main h6 {
        position: relative;
        font-weight: 600;
        margin-bottom: 1.5rem;
        color: #ffffff;
    }

    .footer-main h5::after {
        content: '';
        position: absolute;
        bottom: -8px;
        left: 0;
        width: 50px;
        height: 3px;
        background: linear-gradient(135deg, #208836, #2ea344);
        border-radius: 2px;
        box-shadow: 0 2px 10px rgba(32, 136, 54, 0.3);
    }

    /* Modern Social Links */
    .social-links {
        display: flex;
        gap: 15px;
        margin-top: 20px;
    }

    .social-links a {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 50px;
        height: 50px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(10px);
        border: 2px solid rgba(255, 255, 255, 0.2);
        color: #ffffff;
        text-decoration: none;
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        position: relative;
        overflow: hidden;
        line-height: 1;
    }

    .social-links a::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(135deg, #208836, #2ea344);
        opacity: 0;
        transition: all 0.4s ease;
        border-radius: 50%;
    }

    .social-links a:hover::before {
        opacity: 1;
    }

    .social-links a:hover {
        transform: translateY(-8px) scale(1.05);
        box-shadow: 0 15px 35px rgba(32, 136, 54, 0.4);
        border-color: #208836;
    }

    .social-links a i {
        font-size: 1.2rem;
        position: relative;
        z-index: 1;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 100%;
        height: 100%;
    }

    .social-links a:hover i {
        color: white;
        transform: scale(1.1);
    }

    /* Modern Links */
    .footer-main a {
        color: rgba(255, 255, 255, 0.8);
        text-decoration: none;
        transition: all 0.3s ease;
        position: relative;
        display: inline-block;
    }

    .footer-main a:hover {
        color: #2ea344;
        transform: translateX(5px);
    }

    .footer-main a::before {
        content: '';
        position: absolute;
        width: 0;
        height: 2px;
        bottom: -2px;
        left: 0;
        background: linear-gradient(90deg, #208836, #2ea344);
        transition: width 0.3s ease;
    }

    .footer-main a:hover::before {
        width: 100%;
    }

    /* Modern Newsletter Form */
    .newsletter-form {
        display: flex;
        gap: 10px;
        margin-top: 20px;
    }

    .footer-newsletter .form-control {
        flex: 1;
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        border: 2px solid rgba(255, 255, 255, 0.2);
        border-radius: 50px;
        padding: 15px 25px;
        color: #333;
        font-size: 1rem;
        transition: all 0.3s ease;
    }

    .footer-newsletter .form-control:focus {
        outline: none;
        border-color: rgba(255, 255, 255, 0.8);
        box-shadow: 0 0 0 0.2rem rgba(255, 255, 255, 0.25);
        background: rgba(255, 255, 255, 1);
    }

    .footer-newsletter .btn {
        background: rgba(255, 255, 255, 0.9);
        backdrop-filter: blur(10px);
        border: 2px solid rgba(255, 255, 255, 0.3);
        border-radius: 50px;
        padding: 15px 30px;
        color: #208836;
        font-weight: 600;
        font-size: 1rem;
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        position: relative;
        overflow: hidden;
    }

    .footer-newsletter .btn::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.4), transparent);
        transition: left 0.6s ease;
    }

    .footer-newsletter .btn:hover::before {
        left: 100%;
    }

    .footer-newsletter .btn:hover {
        background: rgba(255, 255, 255, 1);
        transform: translateY(-3px);
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
        border-color: rgba(255, 255, 255, 0.8);
    }

    /* Modern Cards */
    .footer-card {
        background: rgba(255, 255, 255, 0.05);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 20px;
        padding: 25px;
        margin-bottom: 20px;
        transition: all 0.3s ease;
    }

    .footer-card:hover {
        background: rgba(255, 255, 255, 0.08);
        transform: translateY(-5px);
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
    }

    /* Contact Icons */
    .contact-icon {
        width: 45px;
        height: 45px;
        border-radius: 12px;
        background: linear-gradient(135deg, #208836, #2ea344);
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 15px;
        box-shadow: 0 8px 25px rgba(32, 136, 54, 0.3);
    }

    .contact-icon i {
        color: white;
        font-size: 1.2rem;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .social-links {
            justify-content: center;
            gap: 10px;
        }

        .social-links a {
            width: 45px;
            height: 45px;
        }

        .social-links a i {
            font-size: 1.1rem;
        }

        .impact-stat {
            margin-bottom: 20px;
        }

        .impact-number {
            font-size: 1.8rem;
        }

        .newsletter-form {
            flex-direction: column;
        }

        .footer-newsletter .btn {
            width: 100%;
        }
    }

    /* Impact Statistics */
    .impact-stat {
        padding: 20px;
        border-radius: 15px;
        background: rgba(255, 255, 255, 0.05);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.1);
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .impact-stat:hover {
        transform: translateY(-5px);
        background: rgba(255, 255, 255, 0.08);
        border-color: rgba(32, 136, 54, 0.3);
        box-shadow: 0 10px 30px rgba(32, 136, 54, 0.2);
    }

    .impact-stat::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: linear-gradient(90deg, #208836, #2ea344);
        opacity: 0;
        transition: all 0.3s ease;
    }

    .impact-stat:hover::before {
        opacity: 1;
    }

    .impact-icon {
        width: 60px;
        height: 60px;
        margin: 0 auto;
        background: linear-gradient(135deg, #208836, #2ea344);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        color: white;
        box-shadow: 0 8px 25px rgba(32, 136, 54, 0.3);
    }

    .impact-number {
        font-size: 2.5rem;
        font-weight: 700;
        color: #2ea344;
        margin-bottom: 5px;
        background: linear-gradient(135deg, #208836, #2ea344);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .impact-label {
        color: #ffffff;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 1px;
        font-size: 0.9rem;
    }

    /* Counter Animation */
    @keyframes countUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .impact-stat {
        animation: countUp 0.8s ease-out;
    }

    .impact-stat:nth-child(2) {
        animation-delay: 0.1s;
    }

    .impact-stat:nth-child(3) {
        animation-delay: 0.2s;
    }

    .impact-stat:nth-child(4) {
        animation-delay: 0.3s;
    }

    .impact-stat:nth-child(5) {
        animation-delay: 0.4s;
    }

    /* Impact stats hover glow effect */
    .impact-stat:hover .impact-icon {
        box-shadow:
            0 8px 25px rgba(32, 136, 54, 0.4),
            0 0 0 10px rgba(32, 136, 54, 0.1),
            0 0 0 20px rgba(32, 136, 54, 0.05);
        transform: scale(1.1);
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
                <p class="small text-white">
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
                    <div class="footer-card">
                        <div class="contact-icon">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <strong class="text-white">Address</strong><br>
                        <span class="text-light">
                            <?php echo htmlspecialchars($db->getSetting('contact_address', 'Dar es Salaam, Tanzania')); ?>
                        </span>
                    </div>

                    <div class="footer-card">
                        <div class="contact-icon">
                            <i class="fas fa-phone"></i>
                        </div>
                        <strong class="text-white">Phone</strong><br>
                        <a href="tel:<?php echo $db->getSetting('contact_phone', ''); ?>" class="text-light text-decoration-none">
                            <?php echo htmlspecialchars($db->getSetting('contact_phone', '+255 123 456 789')); ?>
                        </a>
                    </div>

                    <div class="footer-card">
                        <div class="contact-icon">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <strong class="text-white">Email</strong><br>
                        <a href="mailto:<?php echo $db->getSetting('contact_email', SITE_EMAIL); ?>">
                            <?php echo htmlspecialchars($db->getSetting('contact_email', SITE_EMAIL)); ?>
                        </a>
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

        <!-- Impact Statistics -->
        <div class="row mt-5 pt-4">
            <div class="col-12 text-center mb-4">
                <h4 class="mb-2">Our Environmental Impact</h4>
                <p class="text-light mb-4">Making a difference in Tanzania's environmental conservation</p>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="impact-stat text-center">
                    <div class="impact-icon mb-3">
                        <i class="fas fa-tree"></i>
                    </div>
                    <div class="impact-number" data-count="50000">50,000+</div>
                    <div class="impact-label">Trees Planted</div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="impact-stat text-center">
                    <div class="impact-icon mb-3">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="impact-number" data-count="2500">2,500+</div>
                    <div class="impact-label">Communities Reached</div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="impact-stat text-center">
                    <div class="impact-icon mb-3">
                        <i class="fas fa-leaf"></i>
                    </div>
                    <div class="impact-number" data-count="1200">1,200+</div>
                    <div class="impact-label">Hectares Restored</div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="impact-stat text-center">
                    <div class="impact-icon mb-3">
                        <i class="fas fa-graduation-cap"></i>
                    </div>
                    <div class="impact-number" data-count="5000">5,000+</div>
                    <div class="impact-label">People Trained</div>
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
                                    <p class="card-text small text-white">
                                        <?php echo htmlspecialchars(truncate_text(strip_tags($news['excerpt'] ?: $news['content']), 100)); ?>
                                    </p>
                                    <small class="text-white">
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
                    <form action="<?php echo SITE_URL; ?>/includes/newsletter-subscribe.php" method="POST" class="newsletter-form">
                        <?php echo csrf_field(); ?>
                        <input type="email" name="email" class="form-control" placeholder="Enter your email address" required>
                        <button type="submit" class="btn">
                            <i class="fas fa-paper-plane me-2"></i>Subscribe
                        </button>
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