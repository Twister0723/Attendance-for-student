<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include your database connection
$host = "localhost";
$database = "attendance-db";
$user = "root";
$password = "";
try {
    $pdo = new PDO("mysql:host=$host;dbname=$database", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
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

    // Only proceed if no validation errors
    if (empty($errors)) {
        try {
            $table = ($userType == "administrator") ? "tbladmin" : "tbllecture";
            
            // Fetch user
            $stmt = $pdo->prepare("SELECT * FROM $table WHERE emailAddress = :email LIMIT 1");
            $stmt->execute(['email' => $email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                // Check if user has already accepted privacy policy
                $privacyAccepted = $user['privacy_policy_accepted'] ?? 0;

                // ==== FIXED LOGIC ====
                // If user hasn't agreed AND this is the FIRST click (no policy checkbox shown yet)
                if (!$privacyAccepted && !isset($_POST['agree_to_policy'])) {
                    // Store that we need to show policy agreement on page reload
                    $_SESSION['needs_policy_agreement'] = true;
                    $_SESSION['valid_user_data'] = [
                        'email' => $email,
                        'user_type' => $userType,
                        'password' => $password
                    ];
                    
                    // Page will reload and show privacy checkbox
                } 
                // If user hasn't agreed AND this is the SECOND click (policy checkbox shown and checked)
                else if (!$privacyAccepted && $agreeToPolicy) {
                    // Update policy acceptance and log in
                    $updateStmt = $pdo->prepare("UPDATE $table SET privacy_policy_accepted = 1, policy_accepted_at = NOW() WHERE Id = :id");
                    $updateStmt->execute(['id' => $user['Id']]);
                    $privacyAccepted = 1;
                }
                // If user has already accepted policy (either now or previously)
                if ($privacyAccepted) {
                    $_SESSION['user'] = [
                        'id' => $user['Id'],
                        'email' => $user['emailAddress'],
                        'name' => $user['firstName'],
                        'role' => $userType,
                    ];

                    // Clear session data
                    unset($_SESSION['errors']);
                    unset($_SESSION['old']);
                    unset($_SESSION['needs_policy_agreement']);
                    unset($_SESSION['valid_user_data']);
                    
                    header('Location: home');
                    exit();
                }
            } else {
                $errors['login'] = 'Invalid email or password';
            }
        } catch (PDOException $e) {
            $errors['login'] = 'Database error: ' . $e->getMessage();
        }
    }

    // Store errors in session if any exist
    if (!empty($errors)) {
        $_SESSION['errors'] = $errors;
        $_SESSION['old'] = [
            'email' => $email,
            'user_type' => $userType,
            'agree_to_policy' => $agreeToPolicy
        ];
    }
}

// Clear any existing session data when loading the login page fresh
if (empty($_POST)) {
    unset($_SESSION['errors']);
    unset($_SESSION['old']);
    unset($_SESSION['needs_policy_agreement']);
    unset($_SESSION['valid_user_data']);
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

// Determine if we should show policy agreement
$showPolicyAgreement = false;
$typedEmail = $old['email'] ?? '';
$typedRole = $old['user_type'] ?? '';

// Show policy agreement when we have valid user data that needs policy agreement
if (isset($_SESSION['needs_policy_agreement']) && $_SESSION['needs_policy_agreement'] && isset($_SESSION['valid_user_data'])) {
    $showPolicyAgreement = true;
    $typedEmail = $_SESSION['valid_user_data']['email'];
    $typedRole = $_SESSION['valid_user_data']['user_type'];
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
        .policy-agreement {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            margin: 1.5rem 0;
            padding: 1rem;
            background: #f8fafc;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
        }
        .policy-required-message {
            background: #fef3c7;
            border: 1px solid #f59e0b;
            border-radius: 12px;
            padding: 1rem;
            margin: 1rem 0;
            text-align: center;
        }
        .readonly-field {
            background-color: #f8fafc !important;
            color: #64748b !important;
        }
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
            background: linear-gradient(135deg, #98cb0e 0%, #7ca80b 100%);
            color: white;
            padding: 1.5rem 2rem;
            border-radius: 20px 20px 0 0;
            text-align: center;
            position: sticky;
            top: 0;
            z-index: 10;
        }
        .policy-body {
            padding: 2rem;
            max-height: 60vh;
            overflow-y: auto;
            line-height: 1.6;
            color: #1e293b;
        }
        .policy-body h3 {
            color: #98cb0e;
            margin-top: 1.5rem;
            margin-bottom: 0.5rem;
            font-size: 1.1rem;
        }
        .policy-body h4 {
            color: #475569;
            margin: 1rem 0 0.5rem 0;
            font-size: 1rem;
        }
        .policy-body p {
            margin-bottom: 1rem;
            font-size: 0.95rem;
        }
        .policy-body ul {
            margin-bottom: 1rem;
            padding-left: 1.5rem;
        }
        .policy-body li {
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
        }
        .policy-footer {
            padding: 1.5rem 2rem;
            border-top: 1px solid #e2e8f0;
            text-align: center;
            background: #f8fafc;
            border-radius: 0 0 20px 20px;
            position: sticky;
            bottom: 0;
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
        .btn-policy {
            background: linear-gradient(135deg, #98cb0e 0%, #7ca80b 100%);
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
            box-shadow: 0 4px 15px rgba(152, 203, 14, 0.3);
        }
        .btn-policy:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(152, 203, 14, 0.4);
        }
        .policy-link {
            color: #98cb0e;
            text-decoration: none;
            font-weight: 600;
            cursor: pointer;
            transition: color 0.3s ease;
        }
        .policy-link:hover {
            color: #7ca80b;
            text-decoration: underline;
        }
        .highlight {
            background: #f0f9ff;
            padding: 1rem;
            border-radius: 8px;
            border-left: 4px solid #0ea5e9;
            margin: 1rem 0;
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
        
        <!-- Show message when policy agreement is required -->
        <?php if ($showPolicyAgreement): ?>
        <div class="policy-required-message">
            <p><i class="fas fa-exclamation-circle"></i> Please agree to the Privacy Policy to complete your login</p>
        </div>
        <?php endif; ?>
        
        <form method="POST" action="" id="loginForm">
            <div class="input-group">
                <i class="fas fa-user-tag"></i>
                <select name="user_type" required <?= $showPolicyAgreement ? 'disabled' : '' ?> autocomplete="user-type">
                    <option value="">Select User Type</option>
                    <option value="lecture" <?= $typedRole === 'lecture' ? 'selected' : '' ?>>Lecture</option>
                    <option value="administrator" <?= $typedRole === 'administrator' ? 'selected' : '' ?>>Administrator</option>
                </select>
                <?php display_error('user_type'); ?>
            </div>
            
            <div class="input-group">
                <i class="fas fa-envelope"></i>
                <input type="email" name="email" placeholder="Email Address" required 
                       value="<?= htmlspecialchars($typedEmail) ?>" 
                       <?= $showPolicyAgreement ? 'readonly class="readonly-field"' : '' ?>
                       autocomplete="email">
                <?php display_error('email'); ?>
            </div>
            
            <div class="input-group password">
                <i class="fas fa-lock"></i>
                <input type="password" name="password" placeholder="Password" required 
                       <?= $showPolicyAgreement ? 'readonly class="readonly-field"' : '' ?>
                       autocomplete="current-password">
                <?php display_error('password'); ?>
            </div>
            
            <?php if ($showPolicyAgreement): ?>
            <div class="policy-agreement">
                <input type="checkbox" name="agree_to_policy" id="agree_to_policy" autocomplete="off">
                <label for="agree_to_policy">
                    I have read and agree to the 
                    <span class="policy-link" onclick="openPolicyModal()">Privacy Policy</span>
                    and terms of service
                </label>
                
                <!-- Hidden fields to preserve data -->
                <input type="hidden" name="user_type" value="<?= htmlspecialchars($typedRole) ?>">
                <input type="hidden" name="email" value="<?= htmlspecialchars($typedEmail) ?>">
                <input type="hidden" name="password" value="<?= htmlspecialchars($_SESSION['valid_user_data']['password'] ?? '') ?>">
            </div>
            <?php endif; ?>
            
            <button type="submit" class="btn" name="login">
                <?= $showPolicyAgreement ? 'Complete Login' : 'Sign In' ?>
            </button>
        </form>
    </div>

    <!-- Enhanced Privacy Policy Modal -->
    <div class="policy-modal" id="policyModal">
        <div class="policy-content">
            <div class="policy-header">
                <h2><i class="fas fa-shield-alt"></i> Privacy Policy & Data Protection</h2>
                <button class="close-policy" onclick="closePolicyModal()">&times;</button>
            </div>
            <div class="policy-body">
                <div class="highlight">
                    <p><strong>📋 Overview:</strong> This Attendance Management System uses facial recognition technology to track attendance. By agreeing, you consent to the collection and processing of your biometric data for academic purposes.</p>
                </div>

                <h3>1. Data We Collect</h3>
                <h4>Personal Information</h4>
                <ul>
                    <li><strong>Identity Data:</strong> Name, student/lecturer ID, email address</li>
                    <li><strong>Academic Data:</strong> Course enrollment, class schedules, faculty information</li>
                    <li><strong>Biometric Data:</strong> Facial images and facial recognition data</li>
                    <li><strong>Attendance Records:</strong> Timestamped attendance with location context</li>
                </ul>

                <h4>Technical Information</h4>
                <ul>
                    <li><strong>System Usage:</strong> Login times, feature usage, session duration</li>
                    <li><strong>Device Data:</strong> IP address, browser type, operating system</li>
                    <li><strong>Performance Data:</strong> System interactions and response times</li>
                </ul>

                <h3>2. How We Use Your Data</h3>
                <h4>Primary Purposes</h4>
                <ul>
                    <li><strong>Attendance Tracking:</strong> Automated attendance recording using facial recognition</li>
                    <li><strong>Academic Management:</strong> Generating attendance reports for faculty and administration</li>
                    <li><strong>System Security:</strong> Preventing unauthorized access and ensuring data integrity</li>
                    <li><strong>Academic Compliance:</strong> Meeting institutional attendance requirements</li>
                </ul>

                <h4>Secondary Purposes</h4>
                <ul>
                    <li><strong>System Improvement:</strong> Enhancing facial recognition accuracy</li>
                    <li><strong>User Support:</strong> Troubleshooting and technical assistance</li>
                    <li><strong>Communication:</strong> Sending attendance notifications and system updates</li>
                </ul>

                <h3>3. Data Storage & Security</h3>
                <h4>Storage Duration</h4>
                <ul>
                    <li><strong>Facial Data:</strong> Encrypted and stored for active academic periods only</li>
                    <li><strong>Attendance Records:</strong> Maintained according to institutional retention policies</li>
                    <li><strong>Personal Information:</strong> Retained while you are an active student/lecturer</li>
                </ul>

                <h4>Security Measures</h4>
                <ul>
                    <li><strong>Encryption:</strong> All sensitive data encrypted in transit and at rest</li>
                    <li><strong>Access Controls:</strong> Role-based access to prevent unauthorized viewing</li>
                    <li><strong>Regular Audits:</strong> Security assessments and vulnerability testing</li>
                    <li><strong>Data Minimization:</strong> Collecting only necessary information</li>
                </ul>

                <h3>4. Your Rights & Choices</h3>
                <h4>Data Subject Rights</h4>
                <ul>
                    <li><strong>Access:</strong> Request a copy of your personal data</li>
                    <li><strong>Correction:</strong> Update inaccurate or incomplete information</li>
                    <li><strong>Deletion:</strong> Request data deletion (subject to academic requirements)</li>
                    <li><strong>Objection:</strong> Object to specific data processing activities</li>
                    <li><strong>Portability:</strong> Request data transfer where technically feasible</li>
                </ul>

                <h4>Consent Management</h4>
                <ul>
                    <li>You may withdraw consent, but this may affect system access</li>
                    <li>Withdrawal does not affect lawful processing before withdrawal</li>
                    <li>Alternative attendance methods may be available upon request</li>
                </ul>

                <h3>5. Data Sharing & Disclosure</h3>
                <h4>Internal Sharing</h4>
                <ul>
                    <li><strong>Faculty:</strong> Course instructors and academic supervisors</li>
                    <li><strong>Administration:</strong> Authorized administrative staff</li>
                    <li><strong>IT Department:</strong> System maintenance and support personnel</li>
                </ul>

                <h4>External Sharing</h4>
                <ul>
                    <li><strong>No Commercial Sharing:</strong> We do not sell or rent your data</li>
                    <li><strong>Legal Requirements:</strong> Disclosure only when legally required</li>
                    <li><strong>Service Providers:</strong> Trusted partners with strict data protection agreements</li>
                </ul>

                <h3>6. Biometric Data Specifics</h3>
                <h4>Facial Recognition Processing</h4>
                <ul>
                    <li>Facial images are converted to mathematical templates (not stored as photos)</li>
                    <li>Templates are encrypted and cannot be reverse-engineered to recreate images</li>
                    <li>Real-time processing with immediate template deletion after verification</li>
                    <li>Optional manual attendance available for those uncomfortable with facial recognition</li>
                </ul>

                <h3>7. Contact Information</h3>
                <p>For privacy concerns, data requests, or technical support:</p>
                <p>
                    <strong>Data Protection Officer</strong><br>
                    📧 Email: dpo@eclaroacademy.edu.ph<br>
                    📞 Phone: (02) 8XXX-XXXX<br>
                    🏢 Address: Eclaro Academy, [Your Campus Address]
                </p>

                <div class="highlight">
                    <p><strong>⚠️ Important:</strong> By agreeing to this policy, you acknowledge that you have read and understood how your data will be processed for attendance management purposes.</p>
                </div>
            </div>
            <div class="policy-footer">
                <button type="button" class="btn-policy" onclick="acceptPolicy()">
                    <i class="fas fa-check-circle"></i>
                    I Understand and Accept
                </button>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('loginForm')?.addEventListener('submit', function(e) {
            const policyCheckbox = document.getElementById('agree_to_policy');
            if (policyCheckbox && !policyCheckbox.checked) {
                e.preventDefault();
                alert('Please agree to the Privacy Policy to continue.');
                policyCheckbox.focus();
            }
        });

        function openPolicyModal() {
            document.getElementById('policyModal').style.display = 'flex';
            // Auto-scroll to top when opening
            document.querySelector('.policy-body').scrollTop = 0;
        }

        function closePolicyModal() {
            document.getElementById('policyModal').style.display = 'none';
        }

        function acceptPolicy() {
            const policyCheckbox = document.getElementById('agree_to_policy');
            if (policyCheckbox) {
                policyCheckbox.checked = true;
                closePolicyModal();
                
                // Highlight the agreement section
                const agreementDiv = document.querySelector('.policy-agreement');
                if (agreementDiv) {
                    agreementDiv.style.borderColor = '#98cb0e';
                    agreementDiv.style.background = 'rgba(152, 203, 14, 0.05)';
                }
            }
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
    </script>
</body>
</html>