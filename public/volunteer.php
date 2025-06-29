<?php

/**
 * Volunteer Registration Page for ECCT Website
 */

define('ECCT_ROOT', __DIR__);
require_once 'includes/config.php';
require_once 'includes/database.php';
require_once 'includes/functions.php';

// Get database instance
$db = new Database();

// Page variables
$page_title = 'Become a Volunteer - ECCT';
$meta_description = 'Join ECCT as a volunteer and make a positive impact on environmental conservation in Tanzania. Apply now to get involved in our community initiatives.';
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
                'first_name' => ['required' => true, 'min_length' => 2, 'max_length' => 100, 'label' => 'First Name'],
                'last_name' => ['required' => true, 'min_length' => 2, 'max_length' => 100, 'label' => 'Last Name'],
                'email' => ['required' => true, 'email' => true, 'label' => 'Email'],
                'phone' => ['required' => true, 'min_length' => 10, 'label' => 'Phone Number'],
                'date_of_birth' => ['required' => true, 'label' => 'Date of Birth'],
                'address' => ['required' => true, 'min_length' => 10, 'label' => 'Address'],
                'city' => ['required' => true, 'label' => 'City'],
                'education_level' => ['required' => true, 'label' => 'Education Level'],
                'areas_of_interest' => ['required' => true, 'label' => 'Areas of Interest'],
                'motivation' => ['required' => true, 'min_length' => 50, 'label' => 'Motivation'],
                'emergency_contact_name' => ['required' => true, 'label' => 'Emergency Contact Name'],
                'emergency_contact_phone' => ['required' => true, 'label' => 'Emergency Contact Phone']
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
                'skills' => sanitize_input($_POST['skills'] ?? ''),
                'areas_of_interest' => sanitize_input($_POST['areas_of_interest'] ?? ''),
                'availability' => sanitize_input($_POST['availability'] ?? ''),
                'experience' => sanitize_input($_POST['experience'] ?? ''),
                'motivation' => sanitize_input($_POST['motivation'] ?? ''),
                'emergency_contact_name' => sanitize_input($_POST['emergency_contact_name'] ?? ''),
                'emergency_contact_phone' => sanitize_input($_POST['emergency_contact_phone'] ?? ''),
                'emergency_contact_relationship' => sanitize_input($_POST['emergency_contact_relationship'] ?? '')
            ];

            $validation_errors = validate_form($form_data, $validation_rules);

            // Check if email already exists
            if (empty($validation_errors) && $db->exists('volunteers', ['email' => $form_data['email']])) {
                $validation_errors['email'] = 'This email address has already been used for a volunteer application.';
            }

            // Validate age (must be 16 or older)
            if (empty($validation_errors['date_of_birth'])) {
                $age = date_diff(date_create($form_data['date_of_birth']), date_create('today'))->y;
                if ($age < 16) {
                    $validation_errors['date_of_birth'] = 'You must be at least 16 years old to volunteer.';
                }
            }

            if (empty($validation_errors)) {
                // Save to database
                $volunteer_id = $db->insert('volunteers', $form_data);

                if ($volunteer_id) {
                    // Send notification email
                    send_volunteer_notification($form_data);

                    $success_message = 'Thank you for your volunteer application! We will review your application and contact you soon.';
                    // Clear form data on success
                    $form_data = [];
                } else {
                    $error_message = 'There was an error processing your application. Please try again.';
                }
            } else {
                $error_message = 'Please correct the following errors: ' . implode(', ', $validation_errors);
            }
        }
    }
}

// Get volunteer statistics
$volunteer_stats = [
    'total_volunteers' => $db->count('volunteers', ['status' => ['approved', 'active']]),
    'active_campaigns' => $db->count('campaigns', ['status' => 'active']),
    'communities_served' => $db->getSetting('communities_served', '25')
];

include 'includes/header.php';
?>

<!-- Page Header -->
<section class="page-header bg-primary text-white py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h1 class="display-4 fw-bold mb-3">Become a Volunteer</h1>
                <p class="lead mb-0">
                    Join our community of passionate environmental champions and make a real difference in Tanzania
                </p>
            </div>
            <div class="col-lg-4 text-lg-end">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb justify-content-lg-end mb-0">
                        <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>" class="text-white-50">Home</a></li>
                        <li class="breadcrumb-item active text-white" aria-current="page">Volunteer</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</section>

<!-- Volunteer Impact Stats -->
<section class="volunteer-stats py-5 bg-light">
    <div class="container">
        <div class="row text-center">
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="stat-item">
                    <div class="stat-icon text-primary mb-3">
                        <i class="fas fa-users fa-3x"></i>
                    </div>
                    <h3 class="fw-bold text-primary"><?php echo $volunteer_stats['total_volunteers']; ?>+</h3>
                    <p class="text-muted mb-0">Active Volunteers</p>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="stat-item">
                    <div class="stat-icon text-success mb-3">
                        <i class="fas fa-bullhorn fa-3x"></i>
                    </div>
                    <h3 class="fw-bold text-success"><?php echo $volunteer_stats['active_campaigns']; ?></h3>
                    <p class="text-muted mb-0">Active Campaigns</p>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="stat-item">
                    <div class="stat-icon text-info mb-3">
                        <i class="fas fa-map-marker-alt fa-3x"></i>
                    </div>
                    <h3 class="fw-bold text-info"><?php echo $volunteer_stats['communities_served']; ?>+</h3>
                    <p class="text-muted mb-0">Communities Served</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Why Volunteer Section -->
<section class="why-volunteer py-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-6 mb-5">
                <h2 class="section-title">Why Volunteer with ECCT?</h2>
                <p class="text-muted mb-4">
                    Join a community of dedicated individuals working together to protect Tanzania's environment
                    and create sustainable solutions for local communities.
                </p>

                <div class="benefit-list">
                    <div class="benefit-item d-flex align-items-start mb-4">
                        <div class="benefit-icon bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 60px; height: 60px; min-width: 60px;">
                            <i class="fas fa-heart fa-lg"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold mb-2">Make a Real Impact</h5>
                            <p class="text-muted mb-0">
                                Contribute directly to environmental conservation efforts and see the positive
                                changes in communities across Tanzania.
                            </p>
                        </div>
                    </div>

                    <div class="benefit-item d-flex align-items-start mb-4">
                        <div class="benefit-icon bg-success bg-opacity-10 text-success rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 60px; height: 60px; min-width: 60px;">
                            <i class="fas fa-graduation-cap fa-lg"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold mb-2">Learn New Skills</h5>
                            <p class="text-muted mb-0">
                                Gain valuable experience in environmental conservation, community development,
                                and project management.
                            </p>
                        </div>
                    </div>

                    <div class="benefit-item d-flex align-items-start mb-4">
                        <div class="benefit-icon bg-info bg-opacity-10 text-info rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 60px; height: 60px; min-width: 60px;">
                            <i class="fas fa-users fa-lg"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold mb-2">Build Connections</h5>
                            <p class="text-muted mb-0">
                                Connect with like-minded individuals, community leaders, and environmental
                                professionals from diverse backgrounds.
                            </p>
                        </div>
                    </div>

                    <div class="benefit-item d-flex align-items-start">
                        <div class="benefit-icon bg-warning bg-opacity-10 text-warning rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 60px; height: 60px; min-width: 60px;">
                            <i class="fas fa-clock fa-lg"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold mb-2">Flexible Opportunities</h5>
                            <p class="text-muted mb-0">
                                Choose volunteer opportunities that fit your schedule, skills, and interests,
                                from weekend events to ongoing projects.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="volunteer-image">
                    <img src="<?php echo ASSETS_PATH; ?>/images/volunteer-team.jpg"
                        alt="ECCT Volunteers" class="img-fluid rounded-4 shadow">
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Volunteer Opportunities -->
<section class="volunteer-opportunities py-5 bg-light">
    <div class="container">
        <div class="row">
            <div class="col-12 text-center mb-5">
                <h2 class="section-title">Volunteer Opportunities</h2>
                <p class="text-muted">Find the perfect way to contribute based on your interests and skills</p>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="opportunity-card card border-0 shadow-sm h-100">
                    <div class="card-body text-center">
                        <div class="opportunity-icon bg-primary bg-opacity-10 text-primary rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                            <i class="fas fa-broom fa-2x"></i>
                        </div>
                        <h5 class="card-title fw-bold">Beach & Community Cleanups</h5>
                        <p class="card-text text-muted">
                            Join our regular cleanup events to remove plastic waste from beaches, streets,
                            and community areas across Dar es Salaam.
                        </p>
                        <ul class="list-unstyled small text-muted">
                            <li>• Teaching experience helpful</li>
                            <li>• Training provided</li>
                            <li>• School and community visits</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-6 mb-4">
                <div class="opportunity-card card border-0 shadow-sm h-100">
                    <div class="card-body text-center">
                        <div class="opportunity-icon bg-info bg-opacity-10 text-info rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                            <i class="fas fa-seedling fa-2x"></i>
                        </div>
                        <h5 class="card-title fw-bold">Tree Planting & Restoration</h5>
                        <p class="card-text text-muted">
                            Participate in reforestation projects and help restore degraded ecosystems
                            in coastal and inland areas.
                        </p>
                        <ul class="list-unstyled small text-muted">
                            <li>• Outdoor activities</li>
                            <li>• Physical work involved</li>
                            <li>• Long-term impact projects</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-6 mb-4">
                <div class="opportunity-card card border-0 shadow-sm h-100">
                    <div class="card-body text-center">
                        <div class="opportunity-icon bg-warning bg-opacity-10 text-warning rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                            <i class="fas fa-camera fa-2x"></i>
                        </div>
                        <h5 class="card-title fw-bold">Documentation & Media</h5>
                        <p class="card-text text-muted">
                            Help document our activities through photography, videography, writing,
                            and social media management.
                        </p>
                        <ul class="list-unstyled small text-muted">
                            <li>• Creative skills required</li>
                            <li>• Flexible schedule</li>
                            <li>• Own equipment preferred</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-6 mb-4">
                <div class="opportunity-card card border-0 shadow-sm h-100">
                    <div class="card-body text-center">
                        <div class="opportunity-icon bg-danger bg-opacity-10 text-danger rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                            <i class="fas fa-clipboard-list fa-2x"></i>
                        </div>
                        <h5 class="card-title fw-bold">Project Coordination</h5>
                        <p class="card-text text-muted">
                            Support project planning, volunteer coordination, and administrative tasks
                            for our various environmental initiatives.
                        </p>
                        <ul class="list-unstyled small text-muted">
                            <li>• Organizational skills needed</li>
                            <li>• Leadership opportunities</li>
                            <li>• Office and field work</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-6 mb-4">
                <div class="opportunity-card card border-0 shadow-sm h-100">
                    <div class="card-body text-center">
                        <div class="opportunity-icon bg-secondary bg-opacity-10 text-secondary rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                            <i class="fas fa-laptop fa-2x"></i>
                        </div>
                        <h5 class="card-title fw-bold">Digital & Technical Support</h5>
                        <p class="card-text text-muted">
                            Contribute your technical skills in web development, data analysis,
                            graphic design, or IT support.
                        </p>
                        <ul class="list-unstyled small text-muted">
                            <li>• Technical expertise required</li>
                            <li>• Remote work possible</li>
                            <li>• Project-based assignments</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Volunteer Application Form -->
<section class="volunteer-application py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="application-form">
                    <div class="text-center mb-5">
                        <h2 class="section-title">Volunteer Application</h2>
                        <p class="text-muted">
                            Ready to make a difference? Fill out our application form and join the ECCT family.
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

                    <form method="POST" action="" class="needs-validation" novalidate>
                        <?php echo csrf_field(); ?>

                        <!-- Personal Information -->
                        <div class="form-section mb-5">
                            <h4 class="section-heading mb-4">Personal Information</h4>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="first_name" class="form-label">First Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="first_name" name="first_name"
                                        value="<?php echo htmlspecialchars($form_data['first_name'] ?? ''); ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="last_name" class="form-label">Last Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="last_name" name="last_name"
                                        value="<?php echo htmlspecialchars($form_data['last_name'] ?? ''); ?>" required>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control" id="email" name="email"
                                        value="<?php echo htmlspecialchars($form_data['email'] ?? ''); ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="phone" class="form-label">Phone Number <span class="text-danger">*</span></label>
                                    <input type="tel" class="form-control" id="phone" name="phone"
                                        value="<?php echo htmlspecialchars($form_data['phone'] ?? ''); ?>" required>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="date_of_birth" class="form-label">Date of Birth <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" id="date_of_birth" name="date_of_birth"
                                        value="<?php echo htmlspecialchars($form_data['date_of_birth'] ?? ''); ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="gender" class="form-label">Gender</label>
                                    <select class="form-select" id="gender" name="gender">
                                        <option value="">Prefer not to say</option>
                                        <option value="male" <?php echo (($form_data['gender'] ?? '') === 'male') ? 'selected' : ''; ?>>Male</option>
                                        <option value="female" <?php echo (($form_data['gender'] ?? '') === 'female') ? 'selected' : ''; ?>>Female</option>
                                        <option value="other" <?php echo (($form_data['gender'] ?? '') === 'other') ? 'selected' : ''; ?>>Other</option>
                                    </select>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="address" class="form-label">Address <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="address" name="address" rows="2" required><?php echo htmlspecialchars($form_data['address'] ?? ''); ?></textarea>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="city" class="form-label">City <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="city" name="city"
                                        value="<?php echo htmlspecialchars($form_data['city'] ?? ''); ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="region" class="form-label">Region</label>
                                    <select class="form-select" id="region" name="region">
                                        <option value="">Select Region</option>
                                        <option value="Dar es Salaam" <?php echo (($form_data['region'] ?? '') === 'Dar es Salaam') ? 'selected' : ''; ?>>Dar es Salaam</option>
                                        <option value="Mwanza" <?php echo (($form_data['region'] ?? '') === 'Mwanza') ? 'selected' : ''; ?>>Mwanza</option>
                                        <option value="Arusha" <?php echo (($form_data['region'] ?? '') === 'Arusha') ? 'selected' : ''; ?>>Arusha</option>
                                        <option value="Dodoma" <?php echo (($form_data['region'] ?? '') === 'Dodoma') ? 'selected' : ''; ?>>Dodoma</option>
                                        <option value="Mbeya" <?php echo (($form_data['region'] ?? '') === 'Mbeya') ? 'selected' : ''; ?>>Mbeya</option>
                                        <option value="Morogoro" <?php echo (($form_data['region'] ?? '') === 'Morogoro') ? 'selected' : ''; ?>>Morogoro</option>
                                        <option value="Tanga" <?php echo (($form_data['region'] ?? '') === 'Tanga') ? 'selected' : ''; ?>>Tanga</option>
                                        <option value="Other" <?php echo (($form_data['region'] ?? '') === 'Other') ? 'selected' : ''; ?>>Other</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Background Information -->
                        <div class="form-section mb-5">
                            <h4 class="section-heading mb-4">Background Information</h4>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="education_level" class="form-label">Education Level <span class="text-danger">*</span></label>
                                    <select class="form-select" id="education_level" name="education_level" required>
                                        <option value="">Select Education Level</option>
                                        <option value="Primary" <?php echo (($form_data['education_level'] ?? '') === 'Primary') ? 'selected' : ''; ?>>Primary Education</option>
                                        <option value="Secondary" <?php echo (($form_data['education_level'] ?? '') === 'Secondary') ? 'selected' : ''; ?>>Secondary Education</option>
                                        <option value="Certificate" <?php echo (($form_data['education_level'] ?? '') === 'Certificate') ? 'selected' : ''; ?>>Certificate</option>
                                        <option value="Diploma" <?php echo (($form_data['education_level'] ?? '') === 'Diploma') ? 'selected' : ''; ?>>Diploma</option>
                                        <option value="Bachelor" <?php echo (($form_data['education_level'] ?? '') === 'Bachelor') ? 'selected' : ''; ?>>Bachelor's Degree</option>
                                        <option value="Master" <?php echo (($form_data['education_level'] ?? '') === 'Master') ? 'selected' : ''; ?>>Master's Degree</option>
                                        <option value="PhD" <?php echo (($form_data['education_level'] ?? '') === 'PhD') ? 'selected' : ''; ?>>PhD</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="occupation" class="form-label">Occupation</label>
                                    <input type="text" class="form-control" id="occupation" name="occupation"
                                        value="<?php echo htmlspecialchars($form_data['occupation'] ?? ''); ?>"
                                        placeholder="Your current job or student status">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="skills" class="form-label">Skills & Expertise</label>
                                <textarea class="form-control" id="skills" name="skills" rows="3"
                                    placeholder="List any relevant skills, languages, technical abilities, or professional experience"><?php echo htmlspecialchars($form_data['skills'] ?? ''); ?></textarea>
                            </div>
                        </div>

                        <!-- Volunteer Information -->
                        <div class="form-section mb-5">
                            <h4 class="section-heading mb-4">Volunteer Information</h4>

                            <div class="mb-3">
                                <label for="areas_of_interest" class="form-label">Areas of Interest <span class="text-danger">*</span></label>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-check mb-2">
                                            <input class="form-check-input" type="checkbox" name="areas_of_interest[]" value="Beach Cleanups" id="interest_beach">
                                            <label class="form-check-label" for="interest_beach">Beach & Community Cleanups</label>
                                        </div>
                                        <div class="form-check mb-2">
                                            <input class="form-check-input" type="checkbox" name="areas_of_interest[]" value="Environmental Education" id="interest_education">
                                            <label class="form-check-label" for="interest_education">Environmental Education</label>
                                        </div>
                                        <div class="form-check mb-2">
                                            <input class="form-check-input" type="checkbox" name="areas_of_interest[]" value="Tree Planting" id="interest_trees">
                                            <label class="form-check-label" for="interest_trees">Tree Planting & Restoration</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-check mb-2">
                                            <input class="form-check-input" type="checkbox" name="areas_of_interest[]" value="Documentation" id="interest_media">
                                            <label class="form-check-label" for="interest_media">Documentation & Media</label>
                                        </div>
                                        <div class="form-check mb-2">
                                            <input class="form-check-input" type="checkbox" name="areas_of_interest[]" value="Project Coordination" id="interest_coordination">
                                            <label class="form-check-label" for="interest_coordination">Project Coordination</label>
                                        </div>
                                        <div class="form-check mb-2">
                                            <input class="form-check-input" type="checkbox" name="areas_of_interest[]" value="Technical Support" id="interest_technical">
                                            <label class="form-check-label" for="interest_technical">Digital & Technical Support</label>
                                        </div>
                                    </div>
                                </div>
                                <input type="hidden" name="areas_of_interest" id="areas_of_interest_hidden"
                                    value="<?php echo htmlspecialchars($form_data['areas_of_interest'] ?? ''); ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="availability" class="form-label">Availability</label>
                                <select class="form-select" id="availability" name="availability">
                                    <option value="">Select your availability</option>
                                    <option value="Weekends only" <?php echo (($form_data['availability'] ?? '') === 'Weekends only') ? 'selected' : ''; ?>>Weekends only</option>
                                    <option value="Weekdays only" <?php echo (($form_data['availability'] ?? '') === 'Weekdays only') ? 'selected' : ''; ?>>Weekdays only</option>
                                    <option value="Both weekdays and weekends" <?php echo (($form_data['availability'] ?? '') === 'Both weekdays and weekends') ? 'selected' : ''; ?>>Both weekdays and weekends</option>
                                    <option value="Flexible" <?php echo (($form_data['availability'] ?? '') === 'Flexible') ? 'selected' : ''; ?>>Flexible schedule</option>
                                    <option value="Special events only" <?php echo (($form_data['availability'] ?? '') === 'Special events only') ? 'selected' : ''; ?>>Special events only</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="experience" class="form-label">Previous Volunteer Experience</label>
                                <textarea class="form-control" id="experience" name="experience" rows="3"
                                    placeholder="Describe any previous volunteer work or community involvement"><?php echo htmlspecialchars($form_data['experience'] ?? ''); ?></textarea>
                            </div>

                            <div class="mb-3">
                                <label for="motivation" class="form-label">Why do you want to volunteer with ECCT? <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="motivation" name="motivation" rows="4"
                                    placeholder="Tell us about your motivation to join ECCT and contribute to environmental conservation"
                                    required><?php echo htmlspecialchars($form_data['motivation'] ?? ''); ?></textarea>
                                <div class="form-text">Minimum 50 characters required.</div>
                            </div>
                        </div>

                        <!-- Emergency Contact -->
                        <div class="form-section mb-5">
                            <h4 class="section-heading mb-4">Emergency Contact</h4>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="emergency_contact_name" class="form-label">Emergency Contact Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="emergency_contact_name" name="emergency_contact_name"
                                        value="<?php echo htmlspecialchars($form_data['emergency_contact_name'] ?? ''); ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="emergency_contact_phone" class="form-label">Emergency Contact Phone <span class="text-danger">*</span></label>
                                    <input type="tel" class="form-control" id="emergency_contact_phone" name="emergency_contact_phone"
                                        value="<?php echo htmlspecialchars($form_data['emergency_contact_phone'] ?? ''); ?>" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="emergency_contact_relationship" class="form-label">Relationship</label>
                                <select class="form-select" id="emergency_contact_relationship" name="emergency_contact_relationship">
                                    <option value="">Select relationship</option>
                                    <option value="Parent" <?php echo (($form_data['emergency_contact_relationship'] ?? '') === 'Parent') ? 'selected' : ''; ?>>Parent</option>
                                    <option value="Spouse" <?php echo (($form_data['emergency_contact_relationship'] ?? '') === 'Spouse') ? 'selected' : ''; ?>>Spouse</option>
                                    <option value="Sibling" <?php echo (($form_data['emergency_contact_relationship'] ?? '') === 'Sibling') ? 'selected' : ''; ?>>Sibling</option>
                                    <option value="Friend" <?php echo (($form_data['emergency_contact_relationship'] ?? '') === 'Friend') ? 'selected' : ''; ?>>Friend</option>
                                    <option value="Other" <?php echo (($form_data['emergency_contact_relationship'] ?? '') === 'Other') ? 'selected' : ''; ?>>Other</option>
                                </select>
                            </div>
                        </div>

                        <!-- Terms and Conditions -->
                        <div class="form-section mb-4">
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="terms" required>
                                <label class="form-check-label" for="terms">
                                    I agree to the <a href="<?php echo SITE_URL; ?>/terms-of-service.php" target="_blank">Terms of Service</a>
                                    and <a href="<?php echo SITE_URL; ?>/privacy-policy.php" target="_blank">Privacy Policy</a>
                                </label>
                            </div>

                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="communications">
                                <label class="form-check-label" for="communications">
                                    I would like to receive updates about ECCT activities and volunteer opportunities via email
                                </label>
                            </div>
                        </div>

                        <div class="text-center">
                            <button type="submit" class="btn btn-primary btn-lg px-5">
                                <i class="fas fa-paper-plane me-2"></i>
                                Submit Application
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
    // Handle checkbox groups for areas of interest
    document.addEventListener('DOMContentLoaded', function() {
        const checkboxes = document.querySelectorAll('input[name="areas_of_interest[]"]');
        const hiddenInput = document.getElementById('areas_of_interest_hidden');

        function updateHiddenInput() {
            const checked = Array.from(checkboxes)
                .filter(cb => cb.checked)
                .map(cb => cb.value);
            hiddenInput.value = checked.join(', ');
        }

        checkboxes.forEach(checkbox => {
            checkbox.addEventListener('change', updateHiddenInput);
        });

        // Initialize on page load
        updateHiddenInput();

        // Form validation
        const form = document.querySelector('.needs-validation');
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        });
    });
</script>

<?php include 'includes/footer.php'; ?>uted">
<li>• Weekend events</li>
<li>• No experience required</li>
<li>• Equipment provided</li>
</ul>
</div>
</div>
</div>

<div class="col-lg-4 col-md-6 mb-4">
    <div class="opportunity-card card border-0 shadow-sm h-100">
        <div class="card-body text-center">
            <div class="opportunity-icon bg-success bg-opacity-10 text-success rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                <i class="fas fa-chalkboard-teacher fa-2x"></i>
            </div>
            <h5 class="card-title fw-bold">Environmental Education</h5>
            <p class="card-text text-muted">
                Help educate communities about environmental conservation, sustainable practices,
                and climate change awareness.
            </p>
            <ul class="list-unstyled small text-m