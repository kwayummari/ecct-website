<?php

/**
 * Contact Page for ECCT Website
 */

define('ECCT_ROOT', __DIR__);
require_once 'includes/config.php';
require_once 'includes/database.php';
require_once 'includes/functions.php';

// Get database instance
$db = new Database();

// Page variables
$page_title = 'Contact Us - ECCT';
$meta_description = 'Contact Environmental Conservation Community of Tanzania for partnerships, volunteer opportunities, or environmental conservation inquiries';
$page_class = 'contact-page';

$success_message = '';
$error_message = '';

// Process contact form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf()) {
        $error_message = 'Invalid security token. Please try again.';
    } else {
        // Rate limiting for contact form
        if (!check_rate_limit('contact_form', 3, 1800)) { // 3 submissions per 30 minutes
            $error_message = 'Too many contact form submissions. Please try again later.';
        } else {
            // Validate form data
            $validation_rules = [
                'name' => ['required' => true, 'min_length' => 2, 'max_length' => 100, 'label' => 'Name'],
                'email' => ['required' => true, 'email' => true, 'label' => 'Email'],
                'subject' => ['required' => true, 'min_length' => 5, 'max_length' => 200, 'label' => 'Subject'],
                'message' => ['required' => true, 'min_length' => 10, 'max_length' => 1000, 'label' => 'Message']
            ];

            $form_data = [
                'name' => sanitize_input($_POST['name'] ?? ''),
                'email' => sanitize_input($_POST['email'] ?? ''),
                'phone' => sanitize_input($_POST['phone'] ?? ''),
                'subject' => sanitize_input($_POST['subject'] ?? ''),
                'message' => sanitize_input($_POST['message'] ?? '')
            ];

            $validation_errors = validate_form($form_data, $validation_rules);

            if (empty($validation_errors)) {
                // Save to database
                $contact_data = [
                    'name' => $form_data['name'],
                    'email' => $form_data['email'],
                    'phone' => $form_data['phone'],
                    'subject' => $form_data['subject'],
                    'message' => $form_data['message'],
                    'ip_address' => get_user_ip(),
                    'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? ''
                ];

                $contact_id = $db->insert('contact_messages', $contact_data);

                if ($contact_id) {
                    // Send email notification
                    if (send_contact_email($form_data)) {
                        $success_message = 'Thank you for your message! We will get back to you soon.';
                        // Clear form data
                        $form_data = [];
                    } else {
                        $success_message = 'Your message has been saved. We will get back to you soon.';
                    }
                } else {
                    $error_message = 'There was an error sending your message. Please try again.';
                }
            } else {
                $error_message = 'Please correct the following errors: ' . implode(', ', $validation_errors);
            }
        }
    }
}

// Get contact information from settings
$contact_info = [
    'email' => $db->getSetting('contact_email', SITE_EMAIL),
    'phone' => $db->getSetting('contact_phone', '+255 123 456 789'),
    'address' => $db->getSetting('contact_address', 'Dar es Salaam, Tanzania'),
    'facebook' => $db->getSetting('facebook_url', ''),
    'twitter' => $db->getSetting('twitter_url', ''),
    'instagram' => $db->getSetting('instagram_url', '')
];

include 'includes/header.php';
?>

<style>
    /* Contact Page Styles */
    .contact-hero {
        position: relative;
        min-height: 70vh;
        display: flex;
        align-items: center;
        background: linear-gradient(135deg, rgba(40, 167, 69, 0.8), rgba(0, 0, 0, 0.6)), url('<?php echo ASSETS_PATH; ?>/images/tree-planting/LUC06450.JPG');
        background-size: cover;
        background-position: center;
        background-attachment: fixed;
        color: white;
    }

    .contact-hero h1 {
        font-size: 3.5rem;
        font-weight: 800;
        text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
        margin-bottom: 1.5rem;
    }

    .contact-hero p {
        font-size: 1.2rem;
        text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.5);
        margin-bottom: 2rem;
    }

    .hero-badge {
        background: rgba(40, 167, 69, 0.9);
        border-radius: 25px;
        padding: 8px 20px;
        display: inline-block;
        margin-bottom: 2rem;
        font-weight: 500;
    }

    .contact-stats {
        background: rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.2);
        border-radius: 15px;
        padding: 30px;
        margin-top: 3rem;
    }

    .contact-content {
        padding: 80px 0;
        background: white;
    }

    .contact-info-card {
        background: white;
        border-radius: 20px;
        padding: 40px;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        height: 100%;
        position: relative;
        overflow: hidden;
    }

    .contact-info-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, #28a745, #20c997);
    }

    .contact-item {
        background: #f8f9fa;
        border-radius: 15px;
        padding: 25px;
        margin-bottom: 25px;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        border-left: 4px solid transparent;
    }

    .contact-item:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
    }

    .contact-item.address {
        border-left-color: #28a745;
    }

    .contact-item.phone {
        border-left-color: #17a2b8;
    }

    .contact-item.email {
        border-left-color: #ffc107;
    }

    .contact-icon {
        width: 60px;
        height: 60px;
        border-radius: 15px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        margin-bottom: 15px;
    }

    .contact-icon.address {
        background: linear-gradient(135deg, #28a745, #20c997);
        color: white;
    }

    .contact-icon.phone {
        background: linear-gradient(135deg, #17a2b8, #20c997);
        color: white;
    }

    .contact-icon.email {
        background: linear-gradient(135deg, #ffc107, #fd7e14);
        color: white;
    }

    .contact-title {
        font-size: 1.2rem;
        font-weight: 600;
        color: #2c3e50;
        margin-bottom: 8px;
    }

    .contact-detail {
        color: #6c757d;
        font-size: 1rem;
        line-height: 1.5;
    }

    .contact-detail a {
        color: inherit;
        text-decoration: none;
        transition: color 0.3s ease;
    }

    .contact-detail a:hover {
        color: #28a745;
    }

    .social-links {
        margin-top: 30px;
    }

    .social-btn {
        width: 50px;
        height: 50px;
        border-radius: 15px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin-right: 10px;
        text-decoration: none;
        color: white;
        transition: all 0.3s ease;
        font-size: 1.2rem;
    }

    .social-btn:hover {
        transform: translateY(-3px);
        color: white;
    }

    .social-btn.facebook {
        background: linear-gradient(135deg, #3b5998, #4267B2);
    }

    .social-btn.twitter {
        background: linear-gradient(135deg, #1da1f2, #0d8bd9);
    }

    .social-btn.instagram {
        background: linear-gradient(90deg, #28a745, #20c997);
    }

    .contact-form-card {
        background: white;
        border-radius: 20px;
        padding: 40px;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        position: relative;
        overflow: hidden;
    }

    .contact-form-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, #28a745, #20c997);
    }

    .form-title {
        font-size: 1.8rem;
        font-weight: 700;
        color: #2c3e50;
        margin-bottom: 10px;
    }

    .form-subtitle {
        color: #6c757d;
        margin-bottom: 30px;
        font-size: 1.1rem;
    }

    .form-group {
        margin-bottom: 25px;
    }

    .form-label {
        font-weight: 600;
        color: #2c3e50;
        margin-bottom: 8px;
        font-size: 0.95rem;
    }

    .form-control {
        border: 2px solid #e9ecef;
        border-radius: 10px;
        padding: 12px 15px;
        font-size: 1rem;
        transition: border-color 0.3s ease, box-shadow 0.3s ease;
        background: #fafafa;
    }

    .form-control:focus {
        border-color: #28a745;
        box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25);
        background: white;
    }

    .form-control:valid {
        border-color: #28a745;
        background: white;
    }

    .btn-submit {
        background: linear-gradient(135deg, #28a745, #20c997);
        border: none;
        border-radius: 10px;
        padding: 12px 30px;
        color: white;
        font-weight: 600;
        font-size: 1rem;
        transition: all 0.3s ease;
        width: 100%;
    }

    .btn-submit:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 20px rgba(40, 167, 69, 0.3);
        color: white;
    }

    .alert {
        border-radius: 10px;
        border: none;
        padding: 15px 20px;
        margin-bottom: 25px;
    }

    .alert-success {
        background: linear-gradient(135deg, #d4edda, #c3e6cb);
        color: #155724;
    }

    .alert-danger {
        background: linear-gradient(135deg, #f8d7da, #f5c6cb);
        color: #721c24;
    }

    .faq-section {
        padding: 80px 0;
        background: #f8f9fa;
    }

    .faq-card {
        background: white;
        border-radius: 15px;
        padding: 30px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    }

    .accordion-item {
        border: none;
        margin-bottom: 15px;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    }

    .accordion-header button {
        background: white;
        border: none;
        border-radius: 10px;
        padding: 20px;
        font-weight: 600;
        color: #2c3e50;
        font-size: 1.1rem;
    }

    .accordion-header button:not(.collapsed) {
        background: linear-gradient(135deg, #28a745, #20c997);
        color: white;
    }

    .accordion-body {
        padding: 20px;
        background: #f8f9fa;
        color: #6c757d;
        line-height: 1.6;
    }

    .map-section {
        padding: 80px 0;
        background: white;
    }

    .map-container {
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
    }

    .placeholder-map {
        background: linear-gradient(135deg, #f8f9fa, #e9ecef);
        border-radius: 20px;
        min-height: 400px;
    }

    .map-btn {
        background: linear-gradient(135deg, #28a745, #20c997);
        border: none;
        border-radius: 25px;
        padding: 12px 25px;
        color: white;
        text-decoration: none;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .map-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 20px rgba(40, 167, 69, 0.3);
        color: white;
    }

    .section-title {
        font-size: 2.5rem;
        font-weight: 700;
        color: #2c3e50;
        margin-bottom: 20px;
    }

    .section-subtitle {
        font-size: 1.1rem;
        color: #6c757d;
        margin-bottom: 50px;
    }

    .section-badge {
        background: linear-gradient(135deg, #28a745, #20c997);
        color: white;
        padding: 8px 20px;
        border-radius: 25px;
        display: inline-block;
        margin-bottom: 20px;
        font-weight: 500;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .contact-hero h1 {
            font-size: 2.5rem;
        }

        .contact-hero p {
            font-size: 1.1rem;
        }

        .section-title {
            font-size: 2rem;
        }

        .contact-info-card,
        .contact-form-card {
            padding: 25px;
            margin-bottom: 30px;
        }

        .contact-item {
            padding: 20px;
        }

        .form-title {
            font-size: 1.5rem;
        }

        .social-btn {
            width: 45px;
            height: 45px;
            margin-right: 8px;
        }

        .faq-card {
            padding: 20px;
        }

        .accordion-header button {
            padding: 15px;
            font-size: 1rem;
        }
    }
</style>

<!-- Contact Hero -->
<section class="contact-hero">
    <div class="container">
        <div class="row justify-content-center text-center">
            <div class="col-lg-8">
                <div class="hero-badge">
                    <i class="fas fa-envelope me-2"></i>Contact Us
                </div>
                <h1>Get in Touch</h1>
                <p>Ready to make a difference? Contact us for partnerships, volunteer opportunities, or environmental conservation inquiries.</p>
            </div>
        </div>

        <div class="contact-stats">
            <div class="row text-center text-white">
                <div class="col-md-4 col-6 mb-3">
                    <div>
                        <i class="fas fa-clock fa-2x mb-2"></i>
                        <h4 class="mb-1">24/7</h4>
                        <p class="mb-0 small">Response Time</p>
                    </div>
                </div>
                <div class="col-md-4 col-6 mb-3">
                    <div>
                        <i class="fas fa-users fa-2x mb-2"></i>
                        <h4 class="mb-1">100+</h4>
                        <p class="mb-0 small">Partnerships</p>
                    </div>
                </div>
                <div class="col-md-4 col-12 mb-3">
                    <div>
                        <i class="fas fa-handshake fa-2x mb-2"></i>
                        <h4 class="mb-1">500+</h4>
                        <p class="mb-0 small">Collaborations</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Contact Content -->
<section class="contact-content">
    <div class="container">
        <div class="row">
            <!-- Contact Information -->
            <div class="col-lg-5 mb-5">
                <div class="contact-info-card">
                    <div class="section-badge mb-3">
                        <i class="fas fa-info-circle me-2"></i>Contact Information
                    </div>
                    <h3 class="form-title">Let's Connect</h3>
                    <p class="form-subtitle">Ready to make a difference? Contact us to learn more about our environmental conservation work or to get involved in our community initiatives.</p>

                    <div class="contact-item address">
                        <div class="contact-icon address">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <div class="contact-title">Our Location</div>
                        <div class="contact-detail">
                            <?php echo htmlspecialchars($contact_info['address']); ?>
                        </div>
                    </div>

                    <div class="contact-item phone">
                        <div class="contact-icon phone">
                            <i class="fas fa-phone"></i>
                        </div>
                        <div class="contact-title">Call Us</div>
                        <div class="contact-detail">
                            <a href="tel:<?php echo $contact_info['phone']; ?>">
                                <?php echo htmlspecialchars($contact_info['phone']); ?>
                            </a>
                        </div>
                    </div>

                    <div class="contact-item email">
                        <div class="contact-icon email">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <div class="contact-title">Email Us</div>
                        <div class="contact-detail">
                            <a href="mailto:<?php echo $contact_info['email']; ?>">
                                <?php echo htmlspecialchars($contact_info['email']); ?>
                            </a>
                        </div>
                    </div>

                    <div class="social-links">
                        <h5 class="contact-title mb-3">Follow Our Journey</h5>
                        <div>
                            <?php if (!empty($contact_info['facebook'])): ?>
                                <a href="<?php echo $contact_info['facebook']; ?>" target="_blank" class="social-btn facebook">
                                    <i class="fab fa-facebook-f"></i>
                                </a>
                            <?php endif; ?>
                            <?php if (!empty($contact_info['twitter'])): ?>
                                <a href="<?php echo $contact_info['twitter']; ?>" target="_blank" class="social-btn twitter">
                                    <i class="fab fa-twitter"></i>
                                </a>
                            <?php endif; ?>
                            <?php if (!empty($contact_info['instagram'])): ?>
                                <a href="<?php echo $contact_info['instagram']; ?>" target="_blank" class="social-btn instagram">
                                    <i class="fab fa-instagram"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contact Form -->
            <div class="col-lg-7">
                <div class="contact-form-card">
                    <div class="section-badge mb-3">
                        <i class="fas fa-paper-plane me-2"></i>Send Message
                    </div>
                    <h3 class="form-title">Start the Conversation</h3>
                    <p class="form-subtitle">Have a question, want to volunteer, or interested in partnership? We'd love to hear from you!</p>

                    <?php if ($success_message): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle me-2"></i>
                            <?php echo htmlspecialchars($success_message); ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($error_message): ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <?php echo htmlspecialchars($error_message); ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" class="needs-validation" novalidate>
                        <?php generate_csrf_field(); ?>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name" class="form-label">
                                        <i class="fas fa-user me-1"></i>Full Name *
                                    </label>
                                    <input type="text" class="form-control" id="name" name="name"
                                        value="<?php echo htmlspecialchars($form_data['name'] ?? ''); ?>"
                                        required minlength="2" maxlength="100">
                                    <div class="invalid-feedback">Please provide your full name.</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="email" class="form-label">
                                        <i class="fas fa-envelope me-1"></i>Email Address *
                                    </label>
                                    <input type="email" class="form-control" id="email" name="email"
                                        value="<?php echo htmlspecialchars($form_data['email'] ?? ''); ?>"
                                        required>
                                    <div class="invalid-feedback">Please provide a valid email address.</div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="phone" class="form-label">
                                        <i class="fas fa-phone me-1"></i>Phone Number
                                    </label>
                                    <input type="tel" class="form-control" id="phone" name="phone"
                                        value="<?php echo htmlspecialchars($form_data['phone'] ?? ''); ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="subject" class="form-label">
                                        <i class="fas fa-tag me-1"></i>Subject *
                                    </label>
                                    <input type="text" class="form-control" id="subject" name="subject"
                                        value="<?php echo htmlspecialchars($form_data['subject'] ?? ''); ?>"
                                        required minlength="5" maxlength="200">
                                    <div class="invalid-feedback">Please provide a subject (5-200 characters).</div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="message" class="form-label">
                                <i class="fas fa-comment me-1"></i>Message *
                            </label>
                            <textarea class="form-control" id="message" name="message" rows="6"
                                required minlength="10" maxlength="1000"
                                placeholder="Tell us about your inquiry, how you'd like to help, or what partnership opportunities you're interested in..."><?php echo htmlspecialchars($form_data['message'] ?? ''); ?></textarea>
                            <div class="invalid-feedback">Please provide a message (10-1000 characters).</div>
                            <div class="form-text">Minimum 10 characters required</div>
                        </div>

                        <button type="submit" class="btn btn-submit">
                            <i class="fas fa-paper-plane me-2"></i>Send Message
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- FAQ Section -->
<section class="faq-section">
    <div class="container">
        <div class="row text-center mb-5">
            <div class="col-12">
                <div class="section-badge">
                    <i class="fas fa-question-circle me-2"></i>Frequently Asked Questions
                </div>
                <h2 class="section-title">Common Questions</h2>
                <p class="section-subtitle">Find quick answers to frequently asked questions about our work and how to get involved</p>
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="faq-card">
                    <div class="accordion" id="faqAccordion">
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                    How can I volunteer with ECCT?
                                </button>
                            </h2>
                            <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    We welcome volunteers from all backgrounds! You can start by filling out our volunteer application form,
                                    attending our orientation sessions, or joining one of our community events. We have opportunities
                                    ranging from tree planting and environmental education to administrative support and fundraising.
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                    What types of partnerships do you offer?
                                </button>
                            </h2>
                            <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    We partner with corporations, NGOs, schools, government agencies, and community groups.
                                    Partnership opportunities include sponsoring conservation projects, employee volunteer programs,
                                    educational collaborations, research initiatives, and awareness campaigns.
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                    How can I donate to support your work?
                                </button>
                            </h2>
                            <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    You can support our work through monetary donations, equipment donations, or in-kind contributions.
                                    Visit our donation page for secure online giving options, or contact us directly to discuss
                                    other ways to contribute to our environmental conservation efforts.
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">
                                    Where do you operate in Tanzania?
                                </button>
                            </h2>
                            <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    While our headquarters are in Dar es Salaam, we operate conservation projects across Tanzania.
                                    Our work includes coastal conservation, forest restoration, urban environmental projects,
                                    and community-based conservation initiatives in both rural and urban areas.
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq5">
                                    How can I stay updated on your activities?
                                </button>
                            </h2>
                            <div id="faq5" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    You can stay updated by following us on social media, subscribing to our newsletter,
                                    visiting our website regularly for news and updates, or joining our volunteer network.
                                    We regularly share updates about our projects, upcoming events, and environmental conservation tips.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Map Section -->
<section class="map-section">
    <div class="container">
        <div class="row text-center mb-5">
            <div class="col-12">
                <div class="section-badge">
                    <i class="fas fa-map-marker-alt me-2"></i>Our Location
                </div>
                <h2 class="section-title">Find Us</h2>
                <p class="section-subtitle">Visit our office in Dar es Salaam or connect with us virtually</p>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="map-container">
                    <div class="placeholder-map d-flex align-items-center justify-content-center">
                        <div class="text-center">
                            <i class="fas fa-map-marker-alt fa-4x text-muted mb-4"></i>
                            <h4 class="text-muted mb-3">Interactive Map</h4>
                            <p class="text-muted mb-4">
                                <i class="fas fa-map-pin me-2"></i>
                                Location: Dar es Salaam, Tanzania
                            </p>
                            <a href="https://maps.google.com/?q=Dar+es+Salaam+Tanzania" target="_blank" class="map-btn">
                                <i class="fas fa-external-link-alt me-2"></i>
                                View on Google Maps
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
    // Form validation
    (function() {
        'use strict';
        window.addEventListener('load', function() {
            var forms = document.getElementsByClassName('needs-validation');
            var validation = Array.prototype.filter.call(forms, function(form) {
                form.addEventListener('submit', function(event) {
                    if (form.checkValidity() === false) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    form.classList.add('was-validated');
                }, false);
            });
        }, false);
    })();

    // Character counter for message field
    document.addEventListener('DOMContentLoaded', function() {
        const messageField = document.getElementById('message');
        const minLength = 10;

        if (messageField) {
            messageField.addEventListener('input', function() {
                const currentLength = this.value.length;
                const formText = this.nextElementSibling.nextElementSibling;

                if (currentLength < minLength) {
                    formText.textContent = `${minLength - currentLength} more characters needed (minimum ${minLength} characters)`;
                    formText.className = 'form-text text-warning';
                } else {
                    formText.textContent = `${currentLength} characters`;
                    formText.className = 'form-text text-success';
                }
            });
        }

        // Form animations
        const formInputs = document.querySelectorAll('.form-control');
        formInputs.forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.classList.add('focused');
            });

            input.addEventListener('blur', function() {
                if (!this.value) {
                    this.parentElement.classList.remove('focused');
                }
            });
        });
    });
</script>

<?php include 'includes/footer.php'; ?>