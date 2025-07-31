<?php

/**
 * Enhanced Volunteer Registration Page for ECCT Website
 */

define('ECCT_ROOT', __DIR__);
require_once 'includes/config.php';
require_once 'includes/database.php';
require_once 'includes/functions.php';

// Get database instance
$db = new Database();

// Page variables
$page_title = 'Become a Volunteer - ECCT';
$meta_description = 'Join ECCT as a volunteer and make a positive impact on environmental conservation in Tanzania. Help us create cleaner, greener communities through beach cleanups, education programs, and sustainability initiatives.';
$page_class = 'volunteer-page';

$success_message = '';
$error_message = '';
$form_data = [];

// Process volunteer application
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf()) {
        $error_message = 'Invalid security token. Please try again.';
    } else {
        // Rate limiting
        if (!check_rate_limit('volunteer_application', 2, 3600)) { // 2 applications per hour
            $error_message = 'Too many applications submitted. Please try again later.';
        } else {
            // Validation rules
            $validation_rules = [
                'first_name' => ['required' => true, 'min_length' => 2, 'max_length' => 50, 'label' => 'First Name'],
                'last_name' => ['required' => true, 'min_length' => 2, 'max_length' => 50, 'label' => 'Last Name'],
                'email' => ['required' => true, 'email' => true, 'label' => 'Email'],
                'phone' => ['required' => true, 'min_length' => 10, 'label' => 'Phone Number'],
                'date_of_birth' => ['required' => true, 'label' => 'Date of Birth'],
                'address' => ['required' => true, 'min_length' => 10, 'label' => 'Address'],
                'city' => ['required' => true, 'label' => 'City'],
                'education_level' => ['required' => true, 'label' => 'Education Level'],
                'areas_of_interest' => ['required' => true, 'label' => 'Areas of Interest'],
                'motivation' => ['required' => true, 'min_length' => 50, 'label' => 'Why do you want to volunteer?'],
                'emergency_contact_name' => ['required' => true, 'label' => 'Emergency Contact Name'],
                'emergency_contact_phone' => ['required' => true, 'label' => 'Emergency Contact Phone'],
                'availability' => ['required' => true, 'label' => 'Availability']
            ];

            $form_data = [
                'first_name' => sanitize_input($_POST['first_name'] ?? ''),
                'last_name' => sanitize_input($_POST['last_name'] ?? ''),
                'email' => sanitize_input($_POST['email'] ?? ''),
                'phone' => sanitize_input($_POST['phone'] ?? ''),
                'date_of_birth' => sanitize_input($_POST['date_of_birth'] ?? ''),
                'gender' => sanitize_input($_POST['gender'] ?? ''),
                'address' => sanitize_input($_POST['address'] ?? ''),
                'city' => sanitize_input($_POST['city'] ?? ''),
                'region' => sanitize_input($_POST['region'] ?? ''),
                'education_level' => sanitize_input($_POST['education_level'] ?? ''),
                'occupation' => sanitize_input($_POST['occupation'] ?? ''),
                'areas_of_interest' => is_array($_POST['areas_of_interest'] ?? null) ? implode(', ', $_POST['areas_of_interest']) : '',
                'skills' => sanitize_input($_POST['skills'] ?? ''),
                'motivation' => sanitize_input($_POST['motivation'] ?? ''),
                'availability' => is_array($_POST['availability'] ?? null) ? implode(', ', $_POST['availability']) : '',
                'experience' => sanitize_input($_POST['experience'] ?? ''),
                'emergency_contact_name' => sanitize_input($_POST['emergency_contact_name'] ?? ''),
                'emergency_contact_phone' => sanitize_input($_POST['emergency_contact_phone'] ?? ''),
                'emergency_contact_relationship' => sanitize_input($_POST['emergency_contact_relationship'] ?? ''),
                'hear_about_us' => sanitize_input($_POST['hear_about_us'] ?? ''),
                'terms_accepted' => isset($_POST['terms_accepted']) ? 1 : 0
            ];

            // Validate terms acceptance
            if (!$form_data['terms_accepted']) {
                $validation_errors['terms_accepted'] = 'You must accept the terms and conditions';
            }

            $validation_errors = validate_form($form_data, $validation_rules);

            if (empty($validation_errors)) {
                // Check for duplicate email
                $existing_volunteer = $db->selectOne('volunteers', ['email' => $form_data['email']]);

                if ($existing_volunteer) {
                    $error_message = 'An application with this email already exists. Please contact us if you need to update your information.';
                } else {
                    // Save to database
                    $volunteer_data = array_merge($form_data, [
                        'status' => 'pending',
                        'applied_at' => date('Y-m-d H:i:s'),
                        'ip_address' => get_user_ip(),
                        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? ''
                    ]);

                    $volunteer_id = $db->insert('volunteers', $volunteer_data);

                    if ($volunteer_id) {
                        // Send confirmation email
                        send_volunteer_confirmation_email($form_data);

                        // Send notification to admin
                        send_admin_notification('New Volunteer Application', "A new volunteer application has been submitted by {$form_data['first_name']} {$form_data['last_name']}.");

                        $success_message = 'Thank you for your application! We will review it and contact you soon with next steps.';
                        // Clear form data on success
                        $form_data = [];
                    } else {
                        $error_message = 'There was an error processing your application. Please try again.';
                    }
                }
            } else {
                $error_message = 'Please correct the following errors: ' . implode(', ', $validation_errors);
            }
        }
    }
}

// Get volunteer statistics and testimonials
$approved_volunteers = $db->count('volunteers', ['status' => 'approved']);
$active_volunteers = $db->count('volunteers', ['status' => 'active']);

$volunteer_stats = [
    'total_volunteers' => $approved_volunteers + $active_volunteers,
    'active_campaigns' => $db->count('campaigns', ['status' => 'active']),
    'communities_served' => $db->getSetting('communities_served', '25'),
    'successful_campaigns' => $db->getSetting('successful_campaigns', '10')
];

// Volunteer opportunities data
$volunteer_opportunities = [
    [
        'icon' => 'fas fa-water',
        'title' => 'Beach & Marine Cleanup',
        'description' => 'Join our monthly beach cleanups in Dar es Salaam and help protect marine ecosystems from plastic pollution.',
        'commitment' => 'Monthly events, 3-4 hours',
        'skills' => 'Physical activity, teamwork'
    ],
    [
        'icon' => 'fas fa-graduation-cap',
        'title' => 'Environmental Education',
        'description' => 'Teach communities about environmental conservation, plastic waste reduction, and sustainable practices.',
        'commitment' => 'Weekly sessions, 2-3 hours',
        'skills' => 'Communication, teaching'
    ],
    [
        'icon' => 'fas fa-seedling',
        'title' => 'Tree Planting & Restoration',
        'description' => 'Participate in reforestation projects and help restore degraded landscapes across Tanzania.',
        'commitment' => 'Seasonal projects, varies',
        'skills' => 'Physical work, outdoor activities'
    ],
    [
        'icon' => 'fas fa-recycle',
        'title' => 'Waste Management Programs',
        'description' => 'Implement waste sorting, recycling initiatives, and circular economy projects in communities.',
        'commitment' => 'Ongoing, flexible',
        'skills' => 'Organization, innovation'
    ],
    [
        'icon' => 'fas fa-camera',
        'title' => 'Documentation & Media',
        'description' => 'Document our activities, create content for social media, and help raise awareness about our work.',
        'commitment' => 'Event-based, flexible',
        'skills' => 'Photography, writing, social media'
    ],
    [
        'icon' => 'fas fa-handshake',
        'title' => 'Community Outreach',
        'description' => 'Engage with local communities, organize events, and build partnerships for environmental initiatives.',
        'commitment' => 'Regular meetings, varies',
        'skills' => 'Communication, networking'
    ]
];

include 'includes/header.php';
?>

<style>
    /* Volunteer Page Styles */
    .volunteer-hero {
        position: relative;
        min-height: 70vh;
        display: flex;
        align-items: center;
        background: linear-gradient(rgba(0, 0, 0, 0.6), rgba(32, 136, 54, 0.7)),
            url('<?php echo SITE_URL; ?>/assets/images/youth-club/_RIS0386.jpg');
        background-size: cover;
        background-position: center;
        background-attachment: fixed;
        color: #ffffff;
    }

    .volunteer-hero h1 {
        font-size: 3.5rem;
        font-weight: 800;
        text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.7);
        color: #ffffff;
        margin-bottom: 1.5rem;
    }

    .volunteer-hero p {
        font-size: 1.2rem;
        text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.5);
        margin-bottom: 2rem;
        max-width: 600px;
        margin-left: auto;
        margin-right: auto;
    }

    .hero-badge {
        background: linear-gradient(135deg, #208836, rgba(255, 255, 255, 0.2));
        color: white;
        padding: 12px 24px;
        border-radius: 50px;
        display: inline-block;
        font-weight: 600;
        margin-bottom: 2rem;
        border: 2px solid rgba(255, 255, 255, 0.3);
    }

    .volunteer-btn {
        background: linear-gradient(135deg, #208836, #155a24);
        color: white;
        padding: 15px 35px;
        border: none;
        border-radius: 50px;
        font-weight: 600;
        text-decoration: none;
        display: inline-block;
        margin: 0 10px;
        transition: all 0.3s ease;
        box-shadow: 0 5px 15px rgba(32, 136, 54, 0.3);
    }

    .volunteer-btn:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(32, 136, 54, 0.4);
        color: white;
    }

    .volunteer-btn-outline {
        background: transparent;
        color: white;
        border: 2px solid white;
    }

    .volunteer-btn-outline:hover {
        background: white;
        color: #208836;
    }

    .impact-stats {
        background: #f8f9fa;
        padding: 80px 0;
    }

    .stat-card {
        background: white;
        border-radius: 15px;
        padding: 40px 30px;
        text-align: center;
        height: 100%;
        transition: all 0.3s ease;
        border: 1px solid rgba(32, 136, 54, 0.1);
    }

    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 30px rgba(32, 136, 54, 0.15);
    }

    .stat-icon {
        font-size: 3rem;
        color: #208836;
        margin-bottom: 20px;
    }

    .stat-number {
        font-size: 2.5rem;
        font-weight: 800;
        color: #208836;
        display: block;
        margin-bottom: 10px;
    }

    .stat-label {
        color: #6c757d;
        font-weight: 500;
    }

    .opportunities-section {
        padding: 80px 0;
        background: white;
    }

    .opportunity-card {
        background: white;
        border-radius: 15px;
        padding: 30px;
        height: 100%;
        transition: all 0.3s ease;
        border: 1px solid #e9ecef;
        margin-bottom: 30px;
    }

    .opportunity-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 35px rgba(32, 136, 54, 0.1);
        border-color: #208836;
    }

    .opportunity-icon {
        width: 70px;
        height: 70px;
        background: linear-gradient(135deg, #208836, #155a24);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.5rem;
        margin-bottom: 20px;
    }

    .section-title {
        font-size: 2.5rem;
        font-weight: 700;
        color: #2c3e50;
        margin-bottom: 20px;
        text-align: center;
    }

    .section-subtitle {
        font-size: 1.1rem;
        color: #6c757d;
        margin-bottom: 50px;
        text-align: center;
        max-width: 600px;
        margin-left: auto;
        margin-right: auto;
    }

    .form-section {
        background: #f8f9fa;
        padding: 80px 0;
    }

    .form-card {
        background: white;
        border-radius: 15px;
        padding: 40px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    }

    .form-group {
        margin-bottom: 25px;
    }

    .form-label {
        font-weight: 600;
        color: #2c3e50;
        margin-bottom: 8px;
    }

    .form-control {
        border: 2px solid #e9ecef;
        border-radius: 10px;
        padding: 12px 15px;
        transition: all 0.3s ease;
    }

    .form-control:focus {
        border-color: #208836;
        box-shadow: 0 0 0 0.2rem rgba(32, 136, 54, 0.25);
    }

    .btn-submit {
        background: linear-gradient(135deg, #208836, #155a24);
        color: white;
        border: none;
        padding: 15px 40px;
        border-radius: 50px;
        font-weight: 600;
        transition: all 0.3s ease;
        width: 100%;
    }

    .btn-submit:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(32, 136, 54, 0.3);
    }

    .alert {
        border-radius: 10px;
        border: none;
        padding: 15px 20px;
        margin-bottom: 20px;
    }

    .alert-success {
        background: linear-gradient(135deg, #d4edda, #c3e6cb);
        color: #155724;
    }

    .alert-danger {
        background: linear-gradient(135deg, #f8d7da, #f5c6cb);
        color: #721c24;
    }

    /* Stepper Form Styles */
    .stepper-header {
        margin-bottom: 40px;
    }

    .stepper-progress {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
        position: relative;
    }

    .stepper-progress::before {
        content: '';
        position: absolute;
        top: 30px;
        left: 5%;
        right: 5%;
        height: 2px;
        background: #e9ecef;
        z-index: 0;
    }

    .step {
        display: flex;
        flex-direction: column;
        align-items: center;
        position: relative;
        z-index: 1;
        min-width: 100px;
    }

    .step-icon {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        background: #e9ecef;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #6c757d;
        font-size: 1.2rem;
        margin-bottom: 10px;
        transition: all 0.3s ease;
    }

    .step.active .step-icon {
        background: #208836;
        color: white;
    }

    .step.completed .step-icon {
        background: #28a745;
        color: white;
    }

    .step-title {
        font-size: 0.9rem;
        font-weight: 600;
        color: #6c757d;
        text-align: center;
        transition: color 0.3s ease;
    }

    .step.active .step-title {
        color: #208836;
    }

    .step.completed .step-title {
        color: #28a745;
    }

    .step-content {
        display: none;
        animation: fadeIn 0.3s ease;
    }

    .step-content.active {
        display: block;
    }

    .step-heading {
        font-size: 1.8rem;
        font-weight: 700;
        color: #2c3e50;
        margin-bottom: 10px;
    }

    .step-description {
        color: #6c757d;
        margin-bottom: 30px;
        font-size: 1.1rem;
    }

    .checkbox-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 15px;
        margin-bottom: 20px;
    }

    .checkbox-item {
        position: relative;
    }

    .checkbox-item input[type="checkbox"] {
        display: none;
    }

    .checkbox-item label {
        display: flex;
        align-items: center;
        padding: 15px 20px;
        border: 2px solid #e9ecef;
        border-radius: 10px;
        cursor: pointer;
        transition: all 0.3s ease;
        background: white;
    }

    .checkbox-item label:hover {
        border-color: #208836;
        background: #f8f9fa;
    }

    .checkbox-item input[type="checkbox"]:checked+label {
        border-color: #208836;
        background: rgba(32, 136, 54, 0.1);
        color: #208836;
    }

    .checkbox-item label i {
        font-size: 1.2rem;
        margin-right: 10px;
        width: 20px;
        text-align: center;
    }

    .custom-checkbox {
        display: flex;
        align-items: flex-start;
        margin-bottom: 15px;
    }

    .custom-checkbox input[type="checkbox"] {
        margin-right: 10px;
        margin-top: 4px;
    }

    .step-navigation {
        display: flex;
        justify-content: space-between;
        margin-top: 40px;
        padding-top: 30px;
        border-top: 1px solid #e9ecef;
    }

    .btn-nav {
        background: #208836;
        color: white;
        border: none;
        padding: 12px 30px;
        border-radius: 25px;
        font-weight: 600;
        transition: all 0.3s ease;
        cursor: pointer;
    }

    .btn-nav:hover {
        background: #155a24;
        transform: translateY(-2px);
    }

    .btn-nav:disabled {
        background: #6c757d;
        cursor: not-allowed;
        transform: none;
    }

    .btn-prev {
        background: #6c757d;
    }

    .btn-prev:hover {
        background: #5a6268;
    }

    .submit-section {
        text-align: center;
        margin-top: 30px;
    }

    .submit-note {
        margin-top: 15px;
        color: #6c757d;
        font-size: 0.9rem;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(20px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @media (max-width: 768px) {
        .volunteer-hero h1 {
            font-size: 2.5rem;
        }

        .volunteer-hero p {
            font-size: 1rem;
        }

        .volunteer-btn {
            padding: 12px 25px;
            font-size: 0.9rem;
            margin: 5px;
            display: block;
            text-align: center;
        }

        .stat-card {
            padding: 30px 20px;
            margin-bottom: 20px;
        }

        .stepper-progress {
            flex-wrap: wrap;
            gap: 20px;
        }

        .step {
            min-width: 80px;
        }

        .step-icon {
            width: 50px;
            height: 50px;
            font-size: 1rem;
        }

        .checkbox-grid {
            grid-template-columns: 1fr;
        }

        .step-navigation {
            flex-direction: column;
            gap: 15px;
        }
    }
</style>

<script>
    let currentStep = 1;
    const totalSteps = 5;

    function showStep(step) {
        // Hide all step contents
        document.querySelectorAll('.step-content').forEach(content => {
            content.classList.remove('active');
        });

        // Show current step content
        const currentContent = document.querySelector(`.step-content[data-step="${step}"]`);
        if (currentContent) {
            currentContent.classList.add('active');
        }

        // Update step indicators
        document.querySelectorAll('.step').forEach((stepEl, index) => {
            stepEl.classList.remove('active', 'completed');
            if (index + 1 < step) {
                stepEl.classList.add('completed');
            } else if (index + 1 === step) {
                stepEl.classList.add('active');
            }
        });

        // Update navigation buttons
        const prevBtn = document.querySelector('.btn-prev');
        const nextBtn = document.querySelector('.btn-next');

        if (prevBtn) {
            prevBtn.style.display = step === 1 ? 'none' : 'inline-block';
        }

        if (nextBtn) {
            if (step === totalSteps) {
                nextBtn.style.display = 'none';
            } else {
                nextBtn.style.display = 'inline-block';
                nextBtn.textContent = step === totalSteps - 1 ? 'Review' : 'Next';
            }
        }
    }

    function nextStep() {
        if (validateCurrentStep() && currentStep < totalSteps) {
            currentStep++;
            showStep(currentStep);
        }
    }

    function previousStep() {
        if (currentStep > 1) {
            currentStep--;
            showStep(currentStep);
        }
    }

    function validateCurrentStep() {
        const currentContent = document.querySelector(`.step-content[data-step="${currentStep}"]`);
        if (!currentContent) return true;

        const requiredFields = currentContent.querySelectorAll('input[required], select[required], textarea[required]');
        let isValid = true;

        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                field.classList.add('is-invalid');
                isValid = false;
            } else {
                field.classList.remove('is-invalid');
            }
        });

        // Validate checkboxes for interests and availability
        if (currentStep === 3) {
            const interests = currentContent.querySelectorAll('input[name="interests[]"]:checked');
            const availability = currentContent.querySelectorAll('input[name="availability[]"]:checked');

            if (interests.length === 0) {
                alert('Please select at least one area of interest.');
                isValid = false;
            }

            if (availability.length === 0) {
                alert('Please select your availability.');
                isValid = false;
            }
        }

        return isValid;
    }

    // Initialize stepper when page loads
    document.addEventListener('DOMContentLoaded', function() {
        showStep(1);

        // Add real-time validation
        document.querySelectorAll('input[required], select[required], textarea[required]').forEach(field => {
            field.addEventListener('blur', function() {
                if (this.value.trim()) {
                    this.classList.remove('is-invalid');
                }
            });
        });
    });
</script>

<!-- Hero Section -->
<section class="volunteer-hero">
    <div class="container">
        <div class="row justify-content-center text-center">
            <div class="col-lg-8">
                <div class="hero-badge">
                    <i class="fas fa-hands-helping me-2"></i>Join Our Mission
                </div>
                <h1>Become a Volunteer</h1>
                <p>Join our community of passionate environmental champions and make a real difference in Tanzania. No matter how small your contribution, everything helps conserve our environment.</p>
                <div class="hero-buttons">
                    <a href="#application-form" class="volunteer-btn">
                        <i class="fas fa-edit me-2"></i>Apply Now
                    </a>
                    <a href="#opportunities" class="volunteer-btn volunteer-btn-outline">
                        <i class="fas fa-search me-2"></i>View Opportunities
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Impact Statistics -->
<section class="impact-stats">
    <div class="container">
        <div class="row text-center">
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <span class="stat-number"><?php echo $volunteer_stats['total_volunteers']; ?>+</span>
                    <div class="stat-label">Active Volunteers</div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-flag"></i>
                    </div>
                    <span class="stat-number"><?php echo $volunteer_stats['successful_campaigns']; ?>+</span>
                    <div class="stat-label">Successful Campaigns</div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-map-marker-alt"></i>
                    </div>
                    <span class="stat-number"><?php echo $volunteer_stats['communities_served']; ?>+</span>
                    <div class="stat-label">Communities Served</div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-bullhorn"></i>
                    </div>
                    <span class="stat-number"><?php echo $volunteer_stats['active_campaigns']; ?>+</span>
                    <div class="stat-label">Active Campaigns</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Why Volunteer Section -->
<section class="why-volunteer py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 mb-5 mb-lg-0">
                <h2 class="display-5 fw-bold mb-4">Why Volunteer with ECCT?</h2>
                <p class="lead text-muted mb-4">
                    Environmental Conservation Community of Tanzania (ECCT) is officially recognized as Tanzania's Environmental Ambassador,
                    working tirelessly to empower local communities in creating cleaner, greener, and more sustainable environments.
                </p>

                <div class="benefit-list">
                    <div class="benefit-item d-flex mb-3">
                        <div class="benefit-icon bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                            <i class="fas fa-heart"></i>
                        </div>
                        <div>
                            <h5 class="mb-1">Make Real Impact</h5>
                            <p class="text-muted mb-0">Directly contribute to environmental conservation and see tangible results in communities.</p>
                        </div>
                    </div>

                    <div class="benefit-item d-flex mb-3">
                        <div class="benefit-icon bg-success bg-opacity-10 text-success rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                            <i class="fas fa-users"></i>
                        </div>
                        <div>
                            <h5 class="mb-1">Join a Community</h5>
                            <p class="text-muted mb-0">Connect with like-minded individuals passionate about environmental conservation.</p>
                        </div>
                    </div>

                    <div class="benefit-item d-flex mb-3">
                        <div class="benefit-icon bg-warning bg-opacity-10 text-warning rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                            <i class="fas fa-graduation-cap"></i>
                        </div>
                        <div>
                            <h5 class="mb-1">Learn & Grow</h5>
                            <p class="text-muted mb-0">Develop new skills, gain experience, and enhance your environmental knowledge.</p>
                        </div>
                    </div>

                    <div class="benefit-item d-flex">
                        <div class="benefit-icon bg-info bg-opacity-10 text-info rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                            <i class="fas fa-certificate"></i>
                        </div>
                        <div>
                            <h5 class="mb-1">Recognition & Certificates</h5>
                            <p class="text-muted mb-0">Receive certificates and recognition for your valuable contribution to the environment.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="volunteer-image-grid">
                    <div class="row g-3">
                        <div class="col-6">
                            <img src="<?php echo ASSETS_PATH; ?>/images/volunteers/beach-cleanup.jpg"
                                alt="Beach cleanup volunteers"
                                class="img-fluid rounded-3 shadow">
                        </div>
                        <div class="col-6">
                            <img src="<?php echo ASSETS_PATH; ?>/images/volunteers/tree-planting.jpg"
                                alt="Tree planting activity"
                                class="img-fluid rounded-3 shadow">
                        </div>
                        <div class="col-12">
                            <img src="<?php echo ASSETS_PATH; ?>/images/volunteers/community-education.jpg"
                                alt="Community education program"
                                class="img-fluid rounded-3 shadow">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Volunteer Opportunities -->
<section id="opportunities" class="volunteer-opportunities py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="display-5 fw-bold mb-4">Volunteer Opportunities</h2>
            <p class="lead text-muted">
                Choose from various volunteer opportunities that match your interests, skills, and availability.
            </p>
        </div>

        <div class="row">
            <?php foreach ($volunteer_opportunities as $opportunity): ?>
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="opportunity-card bg-white p-4 rounded-3 shadow-sm h-100">
                        <div class="opportunity-icon text-primary mb-3">
                            <i class="<?php echo $opportunity['icon']; ?> fa-2x"></i>
                        </div>
                        <h4 class="mb-3"><?php echo $opportunity['title']; ?></h4>
                        <p class="text-muted mb-3"><?php echo $opportunity['description']; ?></p>

                        <div class="opportunity-details">
                            <div class="detail-item mb-2">
                                <i class="fas fa-clock text-muted me-2"></i>
                                <small class="text-muted"><?php echo $opportunity['commitment']; ?></small>
                            </div>
                            <div class="detail-item">
                                <i class="fas fa-tools text-muted me-2"></i>
                                <small class="text-muted"><?php echo $opportunity['skills']; ?></small>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Testimonials -->
<section class="testimonials py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="display-5 fw-bold mb-4">What Our Volunteers Say</h2>
        </div>

        <div class="row">
            <div class="col-lg-4 mb-4">
                <div class="testimonial-card bg-white p-4 rounded-3 shadow-sm h-100">
                    <div class="testimonial-content mb-3">
                        <i class="fas fa-quote-left text-primary fa-2x mb-3"></i>
                        <p class="text-muted">
                            "Volunteering with ECCT has been life-changing. I've learned so much about environmental conservation
                            and made a real impact in my community. The beach cleanups are especially rewarding!"
                        </p>
                    </div>
                    <div class="testimonial-author d-flex align-items-center">
                        <div class="author-avatar bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                            <i class="fas fa-user"></i>
                        </div>
                        <div>
                            <h6 class="mb-0">Sarah Mwanga</h6>
                            <small class="text-muted">Environmental Education Volunteer</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 mb-4">
                <div class="testimonial-card bg-white p-4 rounded-3 shadow-sm h-100">
                    <div class="testimonial-content mb-3">
                        <i class="fas fa-quote-left text-primary fa-2x mb-3"></i>
                        <p class="text-muted">
                            "The sense of community and purpose I found through ECCT is incredible. Every cleanup,
                            every tree we plant, every person we educate - it all makes a difference."
                        </p>
                    </div>
                    <div class="testimonial-author d-flex align-items-center">
                        <div class="author-avatar bg-success bg-opacity-10 text-success rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                            <i class="fas fa-user"></i>
                        </div>
                        <div>
                            <h6 class="mb-0">James Mkuu</h6>
                            <small class="text-muted">Beach Cleanup Volunteer</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 mb-4">
                <div class="testimonial-card bg-white p-4 rounded-3 shadow-sm h-100">
                    <div class="testimonial-content mb-3">
                        <i class="fas fa-quote-left text-primary fa-2x mb-3"></i>
                        <p class="text-muted">
                            "As a student, volunteering with ECCT has given me valuable experience and skills.
                            I've grown professionally while contributing to something meaningful."
                        </p>
                    </div>
                    <div class="testimonial-author d-flex align-items-center">
                        <div class="author-avatar bg-warning bg-opacity-10 text-warning rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                            <i class="fas fa-user"></i>
                        </div>
                        <div>
                            <h6 class="mb-0">Grace Kimani</h6>
                            <small class="text-muted">Student Volunteer</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Application Form -->
<section id="application-form" class="application-form py-5 bg-light">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="form-header text-center mb-5">
                    <h2 class="display-5 fw-bold mb-4">Apply to Volunteer</h2>
                    <p class="lead text-muted">
                        Ready to make a difference? Fill out the application form below and join our environmental mission.
                    </p>
                </div>

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

                <div class="form-card">
                    <!-- Progress Steps -->
                    <div class="stepper-header">
                        <div class="stepper-progress">
                            <div class="step active" data-step="1">
                                <div class="step-icon">
                                    <i class="fas fa-user"></i>
                                </div>
                                <div class="step-title">Personal Info</div>
                            </div>
                            <div class="step" data-step="2">
                                <div class="step-icon">
                                    <i class="fas fa-map-marker-alt"></i>
                                </div>
                                <div class="step-title">Location</div>
                            </div>
                            <div class="step" data-step="3">
                                <div class="step-icon">
                                    <i class="fas fa-heart"></i>
                                </div>
                                <div class="step-title">Interests</div>
                            </div>
                            <div class="step" data-step="4">
                                <div class="step-icon">
                                    <i class="fas fa-comment"></i>
                                </div>
                                <div class="step-title">About You</div>
                            </div>
                            <div class="step" data-step="5">
                                <div class="step-icon">
                                    <i class="fas fa-check"></i>
                                </div>
                                <div class="step-title">Complete</div>
                            </div>
                        </div>
                    </div>

                    <form method="POST" action="" class="stepper-form">
                        <?php echo csrf_field(); ?>

                        <!-- Step 1: Personal Information -->
                        <div class="step-content active" data-step="1">
                            <h4 class="step-heading">
                                <i class="fas fa-user me-2"></i>Personal Information
                            </h4>
                            <p class="step-description">Tell us about yourself to get started.</p>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="first_name" class="form-label">First Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="first_name" name="first_name"
                                        value="<?php echo htmlspecialchars($form_data['first_name'] ?? ''); ?>" required>
                                    <div class="invalid-feedback">Please provide your first name.</div>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="last_name" class="form-label">Last Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="last_name" name="last_name"
                                        value="<?php echo htmlspecialchars($form_data['last_name'] ?? ''); ?>" required>
                                    <div class="invalid-feedback">Please provide your last name.</div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control" id="email" name="email"
                                        value="<?php echo htmlspecialchars($form_data['email'] ?? ''); ?>" required>
                                    <div class="invalid-feedback">Please provide a valid email address.</div>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="phone" class="form-label">Phone Number <span class="text-danger">*</span></label>
                                    <input type="tel" class="form-control" id="phone" name="phone"
                                        value="<?php echo htmlspecialchars($form_data['phone'] ?? ''); ?>" required>
                                    <div class="invalid-feedback">Please provide your phone number.</div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="date_of_birth" class="form-label">Date of Birth <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" id="date_of_birth" name="date_of_birth"
                                        value="<?php echo htmlspecialchars($form_data['date_of_birth'] ?? ''); ?>" required>
                                    <div class="invalid-feedback">Please provide your date of birth.</div>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label for="gender" class="form-label">Gender</label>
                                    <select class="form-select" id="gender" name="gender">
                                        <option value="">Select Gender</option>
                                        <option value="male" <?php echo ($form_data['gender'] ?? '') === 'male' ? 'selected' : ''; ?>>Male</option>
                                        <option value="female" <?php echo ($form_data['gender'] ?? '') === 'female' ? 'selected' : ''; ?>>Female</option>
                                        <option value="other" <?php echo ($form_data['gender'] ?? '') === 'other' ? 'selected' : ''; ?>>Other</option>
                                    </select>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label for="education_level" class="form-label">Education Level <span class="text-danger">*</span></label>
                                    <select class="form-select" id="education_level" name="education_level" required>
                                        <option value="">Select Education Level</option>
                                        <option value="primary" <?php echo ($form_data['education_level'] ?? '') === 'primary' ? 'selected' : ''; ?>>Primary Education</option>
                                        <option value="secondary" <?php echo ($form_data['education_level'] ?? '') === 'secondary' ? 'selected' : ''; ?>>Secondary Education</option>
                                        <option value="diploma" <?php echo ($form_data['education_level'] ?? '') === 'diploma' ? 'selected' : ''; ?>>Diploma</option>
                                        <option value="bachelor" <?php echo ($form_data['education_level'] ?? '') === 'bachelor' ? 'selected' : ''; ?>>Bachelor's Degree</option>
                                        <option value="master" <?php echo ($form_data['education_level'] ?? '') === 'master' ? 'selected' : ''; ?>>Master's Degree</option>
                                        <option value="phd" <?php echo ($form_data['education_level'] ?? '') === 'phd' ? 'selected' : ''; ?>>PhD</option>
                                    </select>
                                    <div class="invalid-feedback">Please select your education level.</div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="occupation" class="form-label">Current Occupation</label>
                                <input type="text" class="form-control" id="occupation" name="occupation"
                                    value="<?php echo htmlspecialchars($form_data['occupation'] ?? ''); ?>"
                                    placeholder="Student, Teacher, Engineer, etc.">
                            </div>
                        </div>

                </div>

                <!-- Step 2: Location Information -->
                <div class="step-content" data-step="2">
                    <h4 class="step-heading">
                        <i class="fas fa-map-marker-alt me-2"></i>Location Information
                    </h4>
                    <p class="step-description">Help us understand where you're located for volunteer opportunities near you.</p>

                    <div class="mb-3">
                        <label for="address" class="form-label">Address <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="address" name="address" rows="2" required
                            placeholder="Street address or location description"><?php echo htmlspecialchars($form_data['address'] ?? ''); ?></textarea>
                        <div class="invalid-feedback">Please provide your address.</div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="city" class="form-label">City <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="city" name="city"
                                value="<?php echo htmlspecialchars($form_data['city'] ?? ''); ?>" required>
                            <div class="invalid-feedback">Please provide your city.</div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="region" class="form-label">Region</label>
                            <select class="form-select" id="region" name="region">
                                <option value="">Select Region</option>
                                <option value="arusha" <?php echo ($form_data['region'] ?? '') === 'arusha' ? 'selected' : ''; ?>>Arusha</option>
                                <option value="dar-es-salaam" <?php echo ($form_data['region'] ?? '') === 'dar-es-salaam' ? 'selected' : ''; ?>>Dar es Salaam</option>
                                <option value="dodoma" <?php echo ($form_data['region'] ?? '') === 'dodoma' ? 'selected' : ''; ?>>Dodoma</option>
                                <option value="geita" <?php echo ($form_data['region'] ?? '') === 'geita' ? 'selected' : ''; ?>>Geita</option>
                                <option value="iringa" <?php echo ($form_data['region'] ?? '') === 'iringa' ? 'selected' : ''; ?>>Iringa</option>
                                <option value="kagera" <?php echo ($form_data['region'] ?? '') === 'kagera' ? 'selected' : ''; ?>>Kagera</option>
                                <option value="katavi" <?php echo ($form_data['region'] ?? '') === 'katavi' ? 'selected' : ''; ?>>Katavi</option>
                                <option value="kigoma" <?php echo ($form_data['region'] ?? '') === 'kigoma' ? 'selected' : ''; ?>>Kigoma</option>
                                <option value="kilimanjaro" <?php echo ($form_data['region'] ?? '') === 'kilimanjaro' ? 'selected' : ''; ?>>Kilimanjaro</option>
                                <option value="lindi" <?php echo ($form_data['region'] ?? '') === 'lindi' ? 'selected' : ''; ?>>Lindi</option>
                                <option value="manyara" <?php echo ($form_data['region'] ?? '') === 'manyara' ? 'selected' : ''; ?>>Manyara</option>
                                <option value="mara" <?php echo ($form_data['region'] ?? '') === 'mara' ? 'selected' : ''; ?>>Mara</option>
                                <option value="mbeya" <?php echo ($form_data['region'] ?? '') === 'mbeya' ? 'selected' : ''; ?>>Mbeya</option>
                                <option value="morogoro" <?php echo ($form_data['region'] ?? '') === 'morogoro' ? 'selected' : ''; ?>>Morogoro</option>
                                <option value="mtwara" <?php echo ($form_data['region'] ?? '') === 'mtwara' ? 'selected' : ''; ?>>Mtwara</option>
                                <option value="mwanza" <?php echo ($form_data['region'] ?? '') === 'mwanza' ? 'selected' : ''; ?>>Mwanza</option>
                                <option value="njombe" <?php echo ($form_data['region'] ?? '') === 'njombe' ? 'selected' : ''; ?>>Njombe</option>
                                <option value="pwani" <?php echo ($form_data['region'] ?? '') === 'pwani' ? 'selected' : ''; ?>>Pwani</option>
                                <option value="rukwa" <?php echo ($form_data['region'] ?? '') === 'rukwa' ? 'selected' : ''; ?>>Rukwa</option>
                                <option value="ruvuma" <?php echo ($form_data['region'] ?? '') === 'ruvuma' ? 'selected' : ''; ?>>Ruvuma</option>
                                <option value="shinyanga" <?php echo ($form_data['region'] ?? '') === 'shinyanga' ? 'selected' : ''; ?>>Shinyanga</option>
                                <option value="simiyu" <?php echo ($form_data['region'] ?? '') === 'simiyu' ? 'selected' : ''; ?>>Simiyu</option>
                                <option value="singida" <?php echo ($form_data['region'] ?? '') === 'singida' ? 'selected' : ''; ?>>Singida</option>
                                <option value="songwe" <?php echo ($form_data['region'] ?? '') === 'songwe' ? 'selected' : ''; ?>>Songwe</option>
                                <option value="tabora" <?php echo ($form_data['region'] ?? '') === 'tabora' ? 'selected' : ''; ?>>Tabora</option>
                                <option value="tanga" <?php echo ($form_data['region'] ?? '') === 'tanga' ? 'selected' : ''; ?>>Tanga</option>
                            </select>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Step 3: Interests & Availability -->
            <div class="step-content" data-step="3">
                <h4 class="step-heading">
                    <i class="fas fa-heart me-2"></i>Volunteer Interests & Availability
                </h4>
                <p class="step-description">Let us know what you're passionate about and when you're available.</p>

                <div class="mb-4">
                    <label class="form-label">Areas of Interest <span class="text-danger">*</span></label>
                    <p class="text-muted small mb-3">Select all areas you're interested in volunteering for:</p>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="interest_beach_cleanup"
                                    name="areas_of_interest[]" value="Beach & Marine Cleanup">
                                <label class="form-check-label" for="interest_beach_cleanup">
                                    <i class="fas fa-water text-primary me-2"></i>Beach & Marine Cleanup
                                </label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="interest_education"
                                    name="areas_of_interest[]" value="Environmental Education">
                                <label class="form-check-label" for="interest_education">
                                    <i class="fas fa-graduation-cap text-primary me-2"></i>Environmental Education
                                </label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="interest_tree_planting"
                                    name="areas_of_interest[]" value="Tree Planting & Restoration">
                                <label class="form-check-label" for="interest_tree_planting">
                                    <i class="fas fa-seedling text-primary me-2"></i>Tree Planting & Restoration
                                </label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="interest_waste_management"
                                    name="areas_of_interest[]" value="Waste Management Programs">
                                <label class="form-check-label" for="interest_waste_management">
                                    <i class="fas fa-recycle text-primary me-2"></i>Waste Management Programs
                                </label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="interest_media"
                                    name="areas_of_interest[]" value="Documentation & Media">
                                <label class="form-check-label" for="interest_media">
                                    <i class="fas fa-camera text-primary me-2"></i>Documentation & Media
                                </label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="interest_outreach"
                                    name="areas_of_interest[]" value="Community Outreach">
                                <label class="form-check-label" for="interest_outreach">
                                    <i class="fas fa-handshake text-primary me-2"></i>Community Outreach
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label">Availability <span class="text-danger">*</span></label>
                    <p class="text-muted small mb-3">When are you available to volunteer?</p>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="availability_weekdays"
                                    name="availability[]" value="Weekdays">
                                <label class="form-check-label" for="availability_weekdays">
                                    Weekdays (Monday - Friday)
                                </label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="availability_weekends"
                                    name="availability[]" value="Weekends">
                                <label class="form-check-label" for="availability_weekends">
                                    Weekends (Saturday - Sunday)
                                </label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="availability_morning"
                                    name="availability[]" value="Morning">
                                <label class="form-check-label" for="availability_morning">
                                    Morning (6AM - 12PM)
                                </label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="availability_afternoon"
                                    name="availability[]" value="Afternoon">
                                <label class="form-check-label" for="availability_afternoon">
                                    Afternoon (12PM - 6PM)
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="skills" class="form-label">Skills & Expertise</label>
                    <textarea class="form-control" id="skills" name="skills" rows="3"
                        placeholder="List any relevant skills, languages, or expertise you have..."><?php echo htmlspecialchars($form_data['skills'] ?? ''); ?></textarea>
                    <div class="form-text">Examples: Photography, Teaching, First Aid, Social Media, Computer Skills, etc.</div>
                </div>
            </div>

        </div>

        <!-- Step 4: About You -->
        <div class="step-content" data-step="4">
            <h4 class="step-heading">
                <i class="fas fa-comment me-2"></i>About You
            </h4>
            <p class="step-description">Tell us more about yourself and your motivations.</p>

            <div class="mb-4">
                <label for="motivation" class="form-label">Why do you want to volunteer with ECCT? <span class="text-danger">*</span></label>
                <textarea class="form-control" id="motivation" name="motivation" rows="4" required
                    placeholder="Tell us about your motivation to join our environmental conservation efforts..."><?php echo htmlspecialchars($form_data['motivation'] ?? ''); ?></textarea>
                <div class="invalid-feedback">Please tell us why you want to volunteer.</div>
                <div class="form-text">Minimum 50 characters</div>
            </div>

            <div class="mb-3">
                <label for="experience" class="form-label">Previous Volunteer Experience</label>
                <textarea class="form-control" id="experience" name="experience" rows="3"
                    placeholder="Describe any previous volunteer work, environmental activities, or community involvement..."><?php echo htmlspecialchars($form_data['experience'] ?? ''); ?></textarea>
                <div class="form-text">Include any environmental, community, or social volunteer work</div>
            </div>

            <div class="mb-3">
                <label for="hear_about_us" class="form-label">How did you hear about ECCT?</label>
                <select class="form-select" id="hear_about_us" name="hear_about_us">
                    <option value="">Select one</option>
                    <option value="social_media" <?php echo ($form_data['hear_about_us'] ?? '') === 'social_media' ? 'selected' : ''; ?>>Social Media</option>
                    <option value="website" <?php echo ($form_data['hear_about_us'] ?? '') === 'website' ? 'selected' : ''; ?>>Website</option>
                    <option value="friend_family" <?php echo ($form_data['hear_about_us'] ?? '') === 'friend_family' ? 'selected' : ''; ?>>Friend/Family</option>
                    <option value="event" <?php echo ($form_data['hear_about_us'] ?? '') === 'event' ? 'selected' : ''; ?>>Event/Activity</option>
                    <option value="school_university" <?php echo ($form_data['hear_about_us'] ?? '') === 'school_university' ? 'selected' : ''; ?>>School/University</option>
                    <option value="news_media" <?php echo ($form_data['hear_about_us'] ?? '') === 'news_media' ? 'selected' : ''; ?>>News/Media</option>
                    <option value="other" <?php echo ($form_data['hear_about_us'] ?? '') === 'other' ? 'selected' : ''; ?>>Other</option>
                </select>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label class="form-label">Emergency Contact Name *</label>
                    <input type="text" class="form-control" name="emergency_contact_name"
                        value="<?php echo htmlspecialchars($form_data['emergency_contact_name'] ?? ''); ?>" required>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label class="form-label">Emergency Contact Phone *</label>
                    <input type="tel" class="form-control" name="emergency_contact_phone"
                        value="<?php echo htmlspecialchars($form_data['emergency_contact_phone'] ?? ''); ?>" required>
                </div>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">Emergency Contact Relationship</label>
            <input type="text" class="form-control" name="emergency_contact_relationship"
                value="<?php echo htmlspecialchars($form_data['emergency_contact_relationship'] ?? ''); ?>"
                placeholder="Parent, Spouse, Sibling, Friend, etc.">
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="emergency_contact_name" class="form-label">Contact Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="emergency_contact_name" name="emergency_contact_name"
                    value="<?php echo htmlspecialchars($form_data['emergency_contact_name'] ?? ''); ?>" required>
                <div class="invalid-feedback">Please provide emergency contact name.</div>
            </div>

            <div class="col-md-6 mb-3">
                <label for="emergency_contact_phone" class="form-label">Contact Phone <span class="text-danger">*</span></label>
                <input type="tel" class="form-control" id="emergency_contact_phone" name="emergency_contact_phone"
                    value="<?php echo htmlspecialchars($form_data['emergency_contact_phone'] ?? ''); ?>" required>
                <div class="invalid-feedback">Please provide emergency contact phone.</div>
            </div>
        </div>

        <div class="mb-3">
            <label for="emergency_contact_relationship" class="form-label">Relationship</label>
            <input type="text" class="form-control" id="emergency_contact_relationship" name="emergency_contact_relationship"
                value="<?php echo htmlspecialchars($form_data['emergency_contact_relationship'] ?? ''); ?>"
                placeholder="Parent, Spouse, Sibling, Friend, etc.">
        </div>
    </div>

    </div>

    <!-- Step 5: Complete -->
    <div class="step-content" data-step="5">
        <h4 class="step-heading">
            <i class="fas fa-check me-2"></i>Complete Your Application
        </h4>
        <p class="step-description">Review and submit your volunteer application.</p>

        <div class="form-group">
            <div class="custom-checkbox">
                <input type="checkbox" id="agree_terms" name="agree_terms" required>
                <label for="agree_terms">
                    I agree to the <a href="#" class="text-primary">Terms and Conditions</a> and
                    <a href="#" class="text-primary">Privacy Policy</a> *
                </label>
            </div>
        </div>

        <div class="form-group">
            <div class="custom-checkbox">
                <input type="checkbox" id="newsletter" name="newsletter"
                    <?php echo ($form_data['newsletter'] ?? false) ? 'checked' : ''; ?>>
                <label for="newsletter">
                    I would like to receive updates about ECCT activities and volunteer opportunities
                </label>
            </div>
        </div>

        <div class="submit-section">
            <button type="submit" class="btn-submit">
                <i class="fas fa-paper-plane me-2"></i>
                Submit Application
            </button>
            <p class="submit-note">
                <i class="fas fa-info-circle me-1"></i>
                We will review your application and contact you within 5-7 business days.
            </p>
        </div>
    </div>

    <!-- Navigation Buttons -->
    <div class="step-navigation">
        <button type="button" class="btn-nav btn-prev" onclick="previousStep()">
            <i class="fas fa-arrow-left me-2"></i>Previous
        </button>
        <button type="button" class="btn-nav btn-next" onclick="nextStep()">
            Next<i class="fas fa-arrow-right ms-2"></i>
        </button>
    </div>
    </form>
    </div>
    </div>
    </div>
    </div>
</section>

<!-- Call to Action -->
<section class="cta-section py-5 bg-primary text-white">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h3 class="display-6 fw-bold mb-3">Ready to Make a Difference?</h3>
                <p class="lead mb-0">
                    Join our mission today and be part of Tanzania's environmental conservation movement.
                    Every action counts towards creating a cleaner, greener future.
                </p>
            </div>
            <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                <a href="#application-form" class="btn btn-light btn-lg me-3">
                    <i class="fas fa-edit me-2"></i>Apply Now
                </a>
                <a href="<?php echo SITE_URL; ?>/contact" class="btn btn-outline-light btn-lg">
                    <i class="fas fa-envelope me-2"></i>Contact Us
                </a>
            </div>
        </div>
    </div>
</section>

<style>
    .hero-section {
        position: relative;
        min-height: 60vh;
    }

    .stat-card {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15) !important;
    }

    .opportunity-card {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        border-left: 4px solid transparent;
    }

    .opportunity-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15) !important;
        border-left-color: var(--bs-primary);
    }

    .testimonial-card {
        transition: transform 0.3s ease;
    }

    .testimonial-card:hover {
        transform: translateY(-3px);
    }

    .form-section {
        border-bottom: 1px solid #eee;
        padding-bottom: 2rem;
    }

    .form-section:last-child {
        border-bottom: none;
        padding-bottom: 0;
    }

    .section-title {
        position: relative;
        padding-bottom: 0.5rem;
    }

    .section-title::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        width: 50px;
        height: 2px;
        background-color: var(--bs-primary);
    }

    .volunteer-image-grid img {
        transition: transform 0.3s ease;
    }

    .volunteer-image-grid img:hover {
        transform: scale(1.05);
    }

    .animate-fade-in {
        animation: fadeIn 0.8s ease-in-out;
    }

    .animate-fade-in-delay {
        animation: fadeIn 0.8s ease-in-out 0.2s both;
    }

    .animate-fade-in-delay-2 {
        animation: fadeIn 0.8s ease-in-out 0.4s both;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(20px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .application-card {
        position: relative;
    }

    .application-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, var(--bs-primary), var(--bs-success));
        border-radius: 3px 3px 0 0;
    }

    @media (max-width: 768px) {
        .hero-section {
            min-height: 50vh;
        }

        .display-3 {
            font-size: 2.5rem;
        }

        .hero-buttons .btn {
            margin-bottom: 1rem;
            width: 100%;
        }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Form validation
        const form = document.querySelector('.volunteer-form');

        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }

            // Check if at least one interest is selected
            const interests = form.querySelectorAll('input[name="areas_of_interest[]"]:checked');
            if (interests.length === 0) {
                event.preventDefault();
                alert('Please select at least one area of interest.');
                return;
            }

            // Check if at least one availability option is selected
            const availability = form.querySelectorAll('input[name="availability[]"]:checked');
            if (availability.length === 0) {
                event.preventDefault();
                alert('Please select your availability.');
                return;
            }

            form.classList.add('was-validated');
        });

        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Character counter for motivation field
        const motivationField = document.getElementById('motivation');
        if (motivationField) {
            const createCounter = (field, minLength) => {
                const counter = document.createElement('div');
                counter.className = 'form-text text-muted';
                counter.style.textAlign = 'right';
                field.parentNode.appendChild(counter);

                const updateCounter = () => {
                    const length = field.value.length;
                    counter.textContent = `${length} / ${minLength} characters minimum`;

                    if (length >= minLength) {
                        counter.classList.remove('text-danger');
                        counter.classList.add('text-success');
                    } else {
                        counter.classList.remove('text-success');
                        counter.classList.add('text-warning');
                    }
                };

                field.addEventListener('input', updateCounter);
                updateCounter();
            };

            createCounter(motivationField, 50);
        }
    });
</script>

<?php include 'includes/footer.php'; ?>