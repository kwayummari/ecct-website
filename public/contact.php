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

<!-- Page Header -->
<section class="page-header bg-primary text-white py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h1 class="display-4 fw-bold mb-3">Contact Us</h1>
                <p class="lead mb-0">
                    Get in touch for partnerships, volunteer opportunities, or environmental conservation inquiries
                </p>
            </div>
            <div class="col-lg-4 text-lg-end">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb justify-content-lg-end mb-0">
                        <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>" class="text-white-50">Home</a></li>
                        <li class="breadcrumb-item active text-white" aria-current="page">Contact</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</section>

<!-- Contact Section -->
<section class="contact-section py-5">
    <div class="container">
        <div class="row">
            <!-- Contact Information -->
            <div class="col-lg-4 mb-5">
                <div class="contact-info">
                    <h3 class="h4 fw-bold mb-4">Get in Touch</h3>
                    <p class="text-muted mb-4">
                        Ready to make a difference? Contact us to learn more about our environmental conservation work
                        or to get involved in our community initiatives.
                    </p>

                    <div class="contact-item d-flex align-items-start mb-4">
                        <div class="contact-icon bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px; min-width: 50px;">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <div>
                            <h6 class="fw-semibold mb-1">Address</h6>
                            <p class="text-muted mb-0"><?php echo htmlspecialchars($contact_info['address']); ?></p>
                        </div>
                    </div>

                    <div class="contact-item d-flex align-items-start mb-4">
                        <div class="contact-icon bg-success bg-opacity-10 text-success rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px; min-width: 50px;">
                            <i class="fas fa-phone"></i>
                        </div>
                        <div>
                            <h6 class="fw-semibold mb-1">Phone</h6>
                            <p class="text-muted mb-0">
                                <a href="tel:<?php echo $contact_info['phone']; ?>" class="text-decoration-none">
                                    <?php echo htmlspecialchars($contact_info['phone']); ?>
                                </a>
                            </p>
                        </div>
                    </div>

                    <div class="contact-item d-flex align-items-start mb-4">
                        <div class="contact-icon bg-info bg-opacity-10 text-info rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px; min-width: 50px;">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <div>
                            <h6 class="fw-semibold mb-1">Email</h6>
                            <p class="text-muted mb-0">
                                <a href="mailto:<?php echo $contact_info['email']; ?>" class="text-decoration-none">
                                    <?php echo htmlspecialchars($contact_info['email']); ?>
                                </a>
                            </p>
                        </div>
                    </div>

                    <!-- Social Media -->
                    <div class="social-links mt-4">
                        <h6 class="fw-semibold mb-3">Follow Us</h6>
                        <div class="d-flex">
                            <?php if (!empty($contact_info['facebook'])): ?>
                                <a href="<?php echo $contact_info['facebook']; ?>" target="_blank"
                                    class="btn btn-outline-primary btn-sm me-2">
                                    <i class="fab fa-facebook-f"></i>
                                </a>
                            <?php endif; ?>
                            <?php if (!empty($contact_info['twitter'])): ?>
                                <a href="<?php echo $contact_info['twitter']; ?>" target="_blank"
                                    class="btn btn-outline-info btn-sm me-2">
                                    <i class="fab fa-twitter"></i>
                                </a>
                            <?php endif; ?>
                            <?php if (!empty($contact_info['instagram'])): ?>
                                <a href="<?php echo $contact_info['instagram']; ?>" target="_blank"
                                    class="btn btn-outline-danger btn-sm">
                                    <i class="fab fa-instagram"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contact Form -->
            <div class="col-lg-8">
                <div class="contact-form">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-5">
                            <h4 class="fw-bold mb-4">Send us a Message</h4>

                            <?php if ($success_message): ?>
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    <i class="fas fa-check-circle me-2"></i>
                                    <?php echo htmlspecialchars($success_message); ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            <?php endif; ?>

                            <?php if ($error_message): ?>
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    <?php echo htmlspecialchars($error_message); ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            <?php endif; ?>

                            <form method="POST" action="" class="needs-validation" novalidate>
                                <?php echo csrf_field(); ?>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="name" class="form-label">Full Name <span class="text-danger">*</span></label>
                                        <input type="text"
                                            class="form-control"
                                            id="name"
                                            name="name"
                                            value="<?php echo htmlspecialchars($form_data['name'] ?? ''); ?>"
                                            required>
                                        <div class="invalid-feedback">
                                            Please provide your full name.
                                        </div>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                                        <input type="email"
                                            class="form-control"
                                            id="email"
                                            name="email"
                                            value="<?php echo htmlspecialchars($form_data['email'] ?? ''); ?>"
                                            required>
                                        <div class="invalid-feedback">
                                            Please provide a valid email address.
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="phone" class="form-label">Phone Number</label>
                                        <input type="tel"
                                            class="form-control"
                                            id="phone"
                                            name="phone"
                                            value="<?php echo htmlspecialchars($form_data['phone'] ?? ''); ?>">
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="subject" class="form-label">Subject <span class="text-danger">*</span></label>
                                        <select class="form-select" id="subject" name="subject" required>
                                            <option value="">Choose a subject...</option>
                                            <option value="General Inquiry" <?php echo (($form_data['subject'] ?? '') === 'General Inquiry') ? 'selected' : ''; ?>>General Inquiry</option>
                                            <option value="Volunteer Opportunity" <?php echo (($form_data['subject'] ?? '') === 'Volunteer Opportunity') ? 'selected' : ''; ?>>Volunteer Opportunity</option>
                                            <option value="Partnership" <?php echo (($form_data['subject'] ?? '') === 'Partnership') ? 'selected' : ''; ?>>Partnership</option>
                                            <option value="Environmental Project" <?php echo (($form_data['subject'] ?? '') === 'Environmental Project') ? 'selected' : ''; ?>>Environmental Project</option>
                                            <option value="Media Inquiry" <?php echo (($form_data['subject'] ?? '') === 'Media Inquiry') ? 'selected' : ''; ?>>Media Inquiry</option>
                                            <option value="Other" <?php echo (($form_data['subject'] ?? '') === 'Other') ? 'selected' : ''; ?>>Other</option>
                                        </select>
                                        <div class="invalid-feedback">
                                            Please select a subject.
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label for="message" class="form-label">Message <span class="text-danger">*</span></label>
                                    <textarea class="form-control"
                                        id="message"
                                        name="message"
                                        rows="6"
                                        placeholder="Tell us about your inquiry, how you'd like to get involved, or any questions you have..."
                                        required><?php echo htmlspecialchars($form_data['message'] ?? ''); ?></textarea>
                                    <div class="invalid-feedback">
                                        Please provide your message.
                                    </div>
                                    <div class="form-text">
                                        Minimum 10 characters required.
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="privacy" required>
                                        <label class="form-check-label" for="privacy">
                                            I agree to the <a href="<?php echo SITE_URL; ?>/privacy-policy.php" target="_blank">Privacy Policy</a>
                                            and consent to the processing of my personal data.
                                        </label>
                                        <div class="invalid-feedback">
                                            You must agree to our privacy policy.
                                        </div>
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-paper-plane me-2"></i>
                                    Send Message
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- FAQ Section -->
<section class="faq-section py-5 bg-light">
    <div class="container">
        <div class="row">
            <div class="col-12 text-center mb-5">
                <h2 class="section-title">Frequently Asked Questions</h2>
                <p class="text-muted">Quick answers to common questions about ECCT</p>
            </div>
        </div>
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="accordion" id="faqAccordion">
                    <div class="accordion-item border-0 shadow-sm mb-3">
                        <h2 class="accordion-header">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                How can I volunteer with ECCT?
                            </button>
                        </h2>
                        <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                You can volunteer by filling out our volunteer application form on our website.
                                We welcome individuals with diverse skills and backgrounds who are passionate about
                                environmental conservation. Once you apply, our team will contact you about
                                opportunities that match your interests and availability.
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item border-0 shadow-sm mb-3">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                What types of environmental projects does ECCT work on?
                            </button>
                        </h2>
                        <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                ECCT focuses on plastic waste reduction, marine and coastal conservation,
                                climate change mitigation, biodiversity preservation, and community education.
                                Our projects include beach cleanups, waste management programs, environmental
                                education in schools, and sustainable livelihood initiatives.
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item border-0 shadow-sm mb-3">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                How can my organization partner with ECCT?
                            </button>
                        </h2>
                        <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                We welcome partnerships with organizations, businesses, schools, and government
                                agencies that share our environmental mission. Contact us through this form or
                                email us directly to discuss potential collaboration opportunities, joint projects,
                                or sponsorship possibilities.
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item border-0 shadow-sm mb-3">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">
                                Where does ECCT operate in Tanzania?
                            </button>
                        </h2>
                        <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                ECCT primarily operates in coastal areas of Tanzania, with a focus on Dar es Salaam
                                and surrounding regions. However, we also work with communities in other parts of
                                the country and are always looking to expand our reach to areas where our
                                environmental conservation efforts are needed most.
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item border-0 shadow-sm">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq5">
                                How can I stay updated on ECCT's activities?
                            </button>
                        </h2>
                        <div id="faq5" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                You can stay updated by following us on social media, subscribing to our newsletter,
                                visiting our website regularly for news and updates, or joining our volunteer network.
                                We regularly share updates about our projects, upcoming events, and environmental
                                conservation tips.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Map Section -->
<section class="map-section py-5">
    <div class="container">
        <div class="row">
            <div class="col-12 text-center mb-4">
                <h3 class="h4 fw-bold">Find Us</h3>
                <p class="text-muted">Visit our office in Dar es Salaam</p>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <div class="map-container rounded shadow">
                    <!-- Replace with actual Google Maps embed or OpenStreetMap -->
                    <div class="placeholder-map bg-light d-flex align-items-center justify-content-center" style="height: 400px;">
                        <div class="text-center">
                            <i class="fas fa-map-marker-alt fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">Interactive Map</h5>
                            <p class="text-muted">Location: Dar es Salaam, Tanzania</p>
                            <a href="https://maps.google.com/?q=Dar+es+Salaam+Tanzania"
                                target="_blank" class="btn btn-primary">
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
    });
</script>

<?php include 'includes/footer.php'; ?>