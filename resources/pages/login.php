<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Handle user login logics
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'] ?? '';
    $userType = $_POST['user_type'] ?? '';
    $agreeToPolicy = isset($_POST['agree_to_policy']) ? true : false;

    // Validation
    if (empty($userType)) {
        $errors['user_type'] = 'Please select a user type';
    }

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Please enter a valid email address';
    }

    if (empty($password)) {
        $errors['password'] = 'Please enter your password';
    }

    if (!$agreeToPolicy) {
        $errors['policy'] = 'You must agree to the Privacy Policy to continue';
    }

    // Only proceed if no validation errors
    if (empty($errors)) {
        if ($userType == "administrator") {
            $stmt = $pdo->prepare("SELECT * FROM tbladmin WHERE emailAddress = :email");
        } elseif ($userType == "lecture") {
            $stmt = $pdo->prepare("SELECT * FROM tbllecture WHERE emailAddress = :email");
        }
        
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user'] = [
                'id' => $user['Id'],
                'email' => $user['emailAddress'],
                'name' => $user['firstName'],
                'role' => $userType,
            ];

            // Clear any previous errors
            unset($_SESSION['errors']);
            
            header('Location: home');
            exit();
        } else {
            $errors['login'] = 'Invalid email or password';
        }
    }

    // Store errors in session if any exist
    if (!empty($errors)) {
        $_SESSION['errors'] = $errors;
        // Keep form values for better UX
        $_SESSION['old'] = [
            'email' => $email,
            'user_type' => $userType,
            'agree_to_policy' => $agreeToPolicy
        ];
    }
}

// Retrieve errors from session
if (isset($_SESSION['errors'])) {
    $errors = $_SESSION['errors'];
    unset($_SESSION['errors']);
}

// Retrieve old form values
$old = $_SESSION['old'] ?? [];
unset($_SESSION['old']);

function display_error($error, $is_main = false) {
    global $errors;
    if (isset($errors[$error])) {
        $class = $is_main ? 'error-main' : 'error';
        echo '<div class="' . $class . '">
                <p>' . htmlspecialchars($errors[$error]) . '</p>
              </div>';
    }
}

function get_old_value($field, $default = '') {
    global $old;
    return isset($old[$field]) ? htmlspecialchars($old[$field]) : $default;
}

function get_old_checkbox($field) {
    global $old;
    return isset($old[$field]) && $old[$field] ? 'checked' : '';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login to Access Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="resources/assets/css/login_styles.css">
    <style>
        /* Privacy Policy Modal Styles */
        .policy-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            z-index: 1000;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .policy-content {
            background: white;
            border-radius: 20px;
            padding: 0;
            max-width: 800px;
            max-height: 80vh;
            width: 100%;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.3);
            position: relative;
            overflow: hidden;
        }

        .policy-header {
            background: linear-gradient(135deg, #7c3aed 0%, #5b21b6 100%);
            color: white;
            padding: 1.5rem 2rem;
            border-radius: 20px 20px 0 0;
            text-align: center;
        }

        .policy-header h2 {
            margin: 0;
            font-size: 1.5rem;
            font-weight: 600;
        }

        .policy-body {
            padding: 2rem;
            max-height: 50vh;
            overflow-y: auto;
            line-height: 1.6;
            color: #1e293b;
        }

        .policy-body h3 {
            color: #7c3aed;
            margin-top: 1.5rem;
            margin-bottom: 0.5rem;
            font-size: 1.1rem;
        }

        .policy-body p {
            margin-bottom: 1rem;
        }

        .policy-body ul {
            margin-bottom: 1rem;
            padding-left: 1.5rem;
        }

        .policy-body li {
            margin-bottom: 0.5rem;
        }

        .policy-footer {
            padding: 1.5rem 2rem;
            border-top: 1px solid #e2e8f0;
            text-align: center;
            background: #f8fafc;
            border-radius: 0 0 20px 20px;
        }

        .close-policy {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background: rgba(255, 255, 255, 0.2);
            border: none;
            color: white;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 1.2rem;
            transition: all 0.3s ease;
        }

        .close-policy:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: scale(1.1);
        }

        .policy-agreement {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            margin: 1.5rem 0;
            padding: 1rem;
            background: #f8fafc;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            transition: all 0.3s ease;
        }

        .policy-agreement.checked {
            border-color: #7c3aed;
            background: rgba(124, 58, 237, 0.05);
        }

        .policy-agreement input[type="checkbox"] {
            margin-top: 2px;
            transform: scale(1.2);
            cursor: pointer;
        }

        .policy-agreement label {
            font-size: 0.9rem;
            line-height: 1.4;
            color: #475569;
            cursor: pointer;
            user-select: none;
        }

        .policy-link {
            color: #7c3aed;
            text-decoration: none;
            font-weight: 600;
            cursor: pointer;
            transition: color 0.3s ease;
        }

        .policy-link:hover {
            color: #5b21b6;
            text-decoration: underline;
        }

        .btn-policy {
            background: linear-gradient(135deg, #7c3aed 0%, #5b21b6 100%);
            color: white;
            border: none;
            padding: 1rem 2rem;
            border-radius: 12px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 600;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            width: 100%;
            box-shadow: 0 4px 15px rgba(124, 58, 237, 0.3);
        }

        .btn-policy:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(124, 58, 237, 0.4);
        }

        .btn-policy:disabled {
            background: #9ca3af;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        /* Enhanced error styling for policy */
        .error-policy {
            color: #e11d48;
            font-size: 0.85rem;
            margin-top: 0.5rem;
            padding: 0.5rem;
            background: rgba(225, 29, 72, 0.1);
            border-radius: 8px;
            border: 1px solid rgba(225, 29, 72, 0.2);
        }

        /* Loading state for login button */
        .btn.loading {
            position: relative;
            pointer-events: none;
        }

        .btn.loading::after {
            content: '';
            position: absolute;
            width: 20px;
            height: 20px;
            border: 2px solid transparent;
            border-top: 2px solid white;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            right: 20px;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <!-- School Logo -->
    <div class="school-logo">
        <img src="resources/images/school-logo.png" alt="School Logo" class="logo-img">
        <div class="logo-text">
            <h2>ECLARO ACADEMY INC.</h2>
            <p>Education Excellence</p>
        </div>
    </div>

    <div class="container" id="signIn">
        <h1 class="form-title">Sign In</h1>
        
        <?php display_error('login', true); ?>
        
        <form method="POST" action="" id="loginForm">
            <div class="input-group">
                <i class="fas fa-user-tag"></i>
                <select name="user_type" id="user_type" required>
                    <option value="">Select User Type</option>
                    <option value="lecture" <?= get_old_value('user_type') === 'lecture' ? 'selected' : '' ?>>Lecture</option>
                    <option value="administrator" <?= get_old_value('user_type') === 'administrator' ? 'selected' : '' ?>>Administrator</option>
                </select>
                <?php display_error('user_type'); ?>
            </div>
            
            <div class="input-group">
                <i class="fas fa-envelope"></i>
                <input type="email" name="email" id="email" placeholder="Email Address" required 
                       value="<?= get_old_value('email') ?>">
                <?php display_error('email'); ?>
            </div>
            
            <div class="input-group password">
                <i class="fas fa-lock"></i>
                <input type="password" name="password" id="password" placeholder="Password" required>
                <i id="eye" class="fas fa-eye" title="Show Password"></i>
                <?php display_error('password'); ?>
            </div>
            
            <!-- Privacy Policy Agreement -->
            <div class="policy-agreement" id="policyAgreement">
                <input type="checkbox" name="agree_to_policy" id="agree_to_policy" <?= get_old_checkbox('agree_to_policy') ?>>
                <label for="agree_to_policy">
                    I have read and agree to the 
                    <span class="policy-link" onclick="openPolicyModal()">Privacy Policy</span>
                    and terms of service
                </label>
            </div>
            <?php 
            if (isset($errors['policy'])) {
                echo '<div class="error-policy">' . htmlspecialchars($errors['policy']) . '</div>';
            }
            ?>
            
            <p class="recover">
                <a href="#">Forgot Password?</a>
            </p>
            
            <button type="submit" class="btn" name="login" id="loginBtn">
                Sign In
            </button>
        </form>
        
        <p class="or">
            ---------- or ---------
        </p>
        
        <div class="icons">
            <i class="fab fa-google"></i>
            <i class="fab fa-facebook"></i>
        </div>
    </div>

    <!-- Privacy Policy Modal -->
    <div class="policy-modal" id="policyModal">
        <div class="policy-content">
            <div class="policy-header">
                <h2>Privacy Policy</h2>
                <button class="close-policy" onclick="closePolicyModal()">&times;</button>
            </div>
            <div class="policy-body">
                <h3>1. Information We Collect</h3>
                <p>Our Attendance Management System collects the following information:</p>
                
                <h4>Personal Information</h4>
                <ul>
                    <li><strong>Student/Lecturer Data:</strong> Name, email address, registration number, faculty, course information</li>
                    <li><strong>Facial Recognition Data:</strong> Captured images for attendance tracking</li>
                    <li><strong>Academic Information:</strong> Course enrollment, class schedules, attendance records</li>
                </ul>
                
                <h4>Automatically Collected Information</h4>
                <ul>
                    <li><strong>Usage Data:</strong> Login times, system interactions, feature usage</li>
                    <li><strong>Technical Data:</strong> IP address, browser type, device information</li>
                    <li><strong>Attendance Records:</strong> Timestamped attendance data with location context</li>
                </ul>

                <h3>2. How We Use Your Information</h3>
                <p>We use the collected information for the following purposes:</p>
                <ul>
                    <li><strong>Attendance Tracking:</strong> To accurately record and monitor student attendance</li>
                    <li><strong>Academic Management:</strong> To generate reports for faculty and administration</li>
                    <li><strong>System Improvement:</strong> To enhance facial recognition accuracy and system performance</li>
                    <li><strong>Communication:</strong> To send attendance notifications and system updates</li>
                    <li><strong>Security:</strong> To prevent unauthorized access and ensure system integrity</li>
                </ul>

                <h3>3. Data Storage and Security</h3>
                <h4>Storage</h4>
                <ul>
                    <li>Facial images are stored securely on our servers with encryption</li>
                    <li>Personal data is retained only as long as necessary for academic purposes</li>
                    <li>Attendance records are maintained according to institutional retention policies</li>
                </ul>
                
                <h4>Security Measures</h4>
                <ul>
                    <li>Encryption of sensitive data in transit and at rest</li>
                    <li>Regular security audits and vulnerability assessments</li>
                    <li>Access controls and authentication mechanisms</li>
                    <li>Secure deletion of data when no longer needed</li>
                </ul>

                <h3>4. Your Rights and Choices</h3>
                <p>You have the following rights regarding your data:</p>
                <ul>
                    <li><strong>Access:</strong> Request a copy of your personal data</li>
                    <li><strong>Correction:</strong> Update or correct inaccurate information</li>
                    <li><strong>Deletion:</strong> Request deletion of your data (subject to academic requirements)</li>
                    <li><strong>Opt-out:</strong> Opt out of non-essential communications</li>
                    <li><strong>Complaint:</strong> File a complaint with relevant authorities</li>
                </ul>

                <h3>5. Contact Information</h3>
                <p>If you have any questions about this Privacy Policy, please contact:</p>
                <p><strong>Data Protection Officer</strong><br>
                Email: dpo@yourinstitution.edu<br>
                Phone: (555) 123-4567</p>
            </div>
            <div class="policy-footer">
                <button type="button" class="btn-policy" onclick="acceptPolicy()">
                    <i class="fas fa-check"></i>
                    I Understand and Accept
                </button>
            </div>
        </div>
    </div>

    <script>
        // Password visibility toggle
        document.getElementById('eye').addEventListener('click', function() {
            const passwordInput = document.getElementById('password');
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            this.classList.toggle('fa-eye');
            this.classList.toggle('fa-eye-slash');
        });

        // Form submission handling
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const btn = document.getElementById('loginBtn');
            const policyCheckbox = document.getElementById('agree_to_policy');
            
            // Check if policy is agreed
            if (!policyCheckbox.checked) {
                e.preventDefault();
                // Show error and scroll to policy section
                policyCheckbox.scrollIntoView({ behavior: 'smooth', block: 'center' });
                // Add visual feedback
                document.getElementById('policyAgreement').style.borderColor = '#e11d48';
                document.getElementById('policyAgreement').style.background = 'rgba(225, 29, 72, 0.05)';
                
                // Remove feedback after 2 seconds
                setTimeout(() => {
                    document.getElementById('policyAgreement').style.borderColor = '#e2e8f0';
                    document.getElementById('policyAgreement').style.background = '#f8fafc';
                }, 2000);
                
                // Reset button state immediately
                resetLoginButton();
                return;
            }

            // Only show loading if form is valid
            btn.classList.add('loading');
            btn.innerHTML = 'Signing In...';
            
            // Add a timeout to reset button in case form submission fails
            setTimeout(() => {
                resetLoginButton();
            }, 5000); // Reset after 5 seconds if still loading
        });

        // Function to reset login button
        function resetLoginButton() {
            const btn = document.getElementById('loginBtn');
            btn.classList.remove('loading');
            btn.innerHTML = 'Sign In';
        }

        // Update policy agreement style when checkbox changes
        document.getElementById('agree_to_policy').addEventListener('change', function() {
            const agreementDiv = document.getElementById('policyAgreement');
            if (this.checked) {
                agreementDiv.classList.add('checked');
            } else {
                agreementDiv.classList.remove('checked');
            }
        });

        // Initialize checkbox state on page load
        document.addEventListener('DOMContentLoaded', function() {
            resetLoginButton();
            
            // Set initial checkbox state
            const policyCheckbox = document.getElementById('agree_to_policy');
            const agreementDiv = document.getElementById('policyAgreement');
            if (policyCheckbox.checked) {
                agreementDiv.classList.add('checked');
            }
            
            const inputs = document.querySelectorAll('input, select');
            inputs.forEach(input => {
                input.addEventListener('focus', function() {
                    this.parentElement.style.transform = 'scale(1.02)';
                });
                
                input.addEventListener('blur', function() {
                    this.parentElement.style.transform = 'scale(1)';
                });
            });
        });

        // Privacy Policy Modal Functions
        function openPolicyModal() {
            document.getElementById('policyModal').style.display = 'flex';
        }

        function closePolicyModal() {
            document.getElementById('policyModal').style.display = 'none';
        }

        function acceptPolicy() {
            const policyCheckbox = document.getElementById('agree_to_policy');
            const agreementDiv = document.getElementById('policyAgreement');
            
            console.log('Accept Policy clicked'); // Debug log
            console.log('Checkbox found:', policyCheckbox); // Debug log
            
            policyCheckbox.checked = true;
            agreementDiv.classList.add('checked');
            closePolicyModal();
            
            console.log('Checkbox checked:', policyCheckbox.checked); // Debug log
            
            // Scroll to the checkbox to show it's checked
            policyCheckbox.scrollIntoView({ 
                behavior: 'smooth', 
                block: 'center' 
            });
            
            // Remove any error styling
            agreementDiv.style.borderColor = '#7c3aed';
            agreementDiv.style.background = 'rgba(124, 58, 237, 0.05)';
        }

        // Close modal when clicking outside
        document.getElementById('policyModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closePolicyModal();
            }
        });

        // Close modal with Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closePolicyModal();
            }
        });

        // Auto-reset button if user navigates away
        window.addEventListener('beforeunload', function() {
            resetLoginButton();
        });
    </script>
</body>
</html>