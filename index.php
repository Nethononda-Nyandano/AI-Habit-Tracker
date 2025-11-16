<?php
// Start session for login tracking
session_start();

// Define project root
define('PROJECT_ROOT', __DIR__);

// Include Auth class
require_once PROJECT_ROOT . '/api/v1/auth.php';

// Initialize message variable
$message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'], $_POST['password'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $auth = new Auth($email, $password);
    $result = $auth->login();

    if ($result['status'] === 'success') {
        
        $_SESSION['user'] = $result['user'];
        $_SESSION['user_id'] = $result['user_id'];
        header('Location: user/index.php'); 
        exit;
    } else if(){
        header('Location: admin/index.php');
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Habit Tracker</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <!--icon-->
    
    <link
        href="https://fonts.googleapis.com/css2?family=Agdasima:wght@400;700&family=Lexend+Deca:wght@100..900&family=Space+Grotesk:wght@300..700&display=swap"
        rel="stylesheet">

    <style>
        .error {
            border-color: #ef4444 !important;
        }

        .valid {
            border-color: #10b981 !important;
        }

        .field-error {
            color: #ef4444;
            font-size: 0.75rem;
            margin-top: 0.25rem;
        }

        .focus-within\:ring-2:focus-within.error {
            --tw-ring-color: #ef4444 !important;
        }

        .focus-within\:ring-2:focus-within.valid {
            --tw-ring-color: #10b981 !important;
        }

        body {
            font-family: 'Lexend Deca', sans-serif;
        }

        /* Diagonal backgrounds for desktop */
        .diagonal-bg {
            position: absolute;
            inset: 0;
            clip-path: polygon(0 0, 60% 0, 40% 100%, 0% 100%);
            background-color: black;
        }

        .diagonal-bg-white {
            position: absolute;
            inset: 0;
            clip-path: polygon(60% 0, 100% 0, 100% 100%, 40% 100%);
            background-color: white;
        }

        /* Diagonal backgrounds for tablet */
        @media (max-width: 1024px) {
            .diagonal-bg {
                clip-path: polygon(0 0, 100% 0, 70% 100%, 0% 100%);
            }
            
            .diagonal-bg-white {
                clip-path: polygon(100% 0, 100% 100%, 70% 100%, 30% 0);
            }
        }

        /* Diagonal backgrounds for mobile */
        @media (max-width: 768px) {
            .diagonal-bg {
                clip-path: polygon(0 0, 100% 0, 100% 60%, 0 40%);
            }
            
            .diagonal-bg-white {
                clip-path: polygon(0 60%, 100% 40%, 100% 100%, 0 100%);
            }
        }

        .progress-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background-color: #d1d5db;
            display: inline-block;
            margin: 0 5px;
        }

        .progress-dot.active {
            background-color: #000;
        }

        /* Animation for form transitions */
        .form-transition {
            transition: all 0.3s ease-in-out;
        }
    </style>
</head>

<body class="bg-gray-100 min-h-screen flex flex-col justify-center items-center p-4">

    <!-- Header -->
    <header class="fixed top-0 left-0 w-full z-10 bg-white shadow">
        <div class="container mx-auto p-4 flex  h-[80px] md:h-[100px]">
            <div class="flex items-center gap-4 md:gap-[100px]">
                <div class="flex flex-col md:flex-row items-center justify-center">
                    <img src="assets/images/logo.svg" alt="Logo" class="h-10 w-10 md:h-16 md:w-16">
                    <h1 class="text-sm md:text-2xl font-bold text-gray-800 mt-2 md:mt-0 md:ml-4">Habit Tracker</h1>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Container -->
    <div class="relative mt-24 md:mt-30 w-full max-w-[1400px] h-[600px] md:h-[700px] rounded-md shadow-2xl overflow-hidden flex">

        <!-- Diagonal Backgrounds - Always visible -->
        <div class="diagonal-bg"></div>
        <div class="diagonal-bg-white"></div>

        <!-- Content Container -->
        <div class="relative w-full h-full flex flex-col md:flex-row">

            <!-- Left Side (Black) -->
            <div class="w-full md:w-1/2 h-1/2 md:h-full relative flex flex-col items-center justify-center px-6 md:px-10 py-8 md:py-0 text-white">
                <!-- Watermark Image -->
                <img src="assets/images/AI.png" alt="AI Logo"
                    class="absolute inset-0 w-full h-full object-contain opacity-20 pointer-events-none">

                <!-- Text Content -->
                <div class="text-center relative z-10">
                    <h1 class="text-2xl md:text-4xl font-bold mb-2">Welcome to AI Habit Tracker!</h1>
                    <p class="text-gray-50 text-sm md:text-lg">
                        Discover a smarter way to build and maintain habits. Track your routines, understand your
                        emotions, and let AI guide you towards a happier, healthier life.
                    </p>
                </div>
            </div>

            <!-- Right Side (White) -->
            <div class="w-full md:w-1/2 h-1/2 md:h-full flex flex-col items-center justify-center px-6 md:px-10 py-8 md:py-0 text-gray-800">

                <!-- Login Form -->
                <div id="loginForm" class="w-full max-w-sm form-transition">
                    <h2 class="text-2xl md:text-3xl font-bold mb-6">Login</h2>

                    <?php if ($message): ?>
                        <div class="bg-red-100 text-red-700 p-2 mb-4 rounded"><?php echo htmlspecialchars($message); ?></div>
                    <?php endif; ?>

                    <form class="space-y-4" method="POST">
                        <div class="flex items-center border rounded-lg px-3 py-2 focus-within:ring-2 focus-within:ring-black">
                            <img src="assets/images/email.svg" alt="Email Icon" class="inline h-6 w-6 mr-2">
                            <input type="email" name="email" placeholder="Email" class="focus:outline-none w-full" required />
                        </div>

                        <div class="flex items-center border rounded-lg px-3 py-2 focus-within:ring-2 focus-within:ring-black">
                            <img src="assets/images/password.svg" alt="Password Icon" class="inline h-6 w-6 mr-2">
                            <input type="password" name="password" placeholder="Password" class="focus:outline-none w-full" required />
                        </div>

                        <button type="submit" name="login" class="w-full px-4 py-2 bg-black text-white rounded-lg hover:bg-gray-800">
                            Login
                        </button>
                    </form>

                    <div class="mt-4 flex justify-between text-sm">
                        <p>
                            Don't have an account?
                            <button type="button" onclick="showRegister()" class="text-blue-600 font-semibold">Register</button>
                        </p>
                        <button type="button" onclick="showForgotPassword()" class="text-blue-600 font-semibold">Forgot Password?</button>
                    </div>
                </div>

                <!-- Register Form -->
                <div id="registerForm" class="w-full max-w-sm hidden form-transition">
                    <h2 class="text-2xl md:text-3xl font-bold mb-6">Register</h2>

                    <!-- Progress Indicator -->
                    <div class="flex justify-center mb-4">
                        <span class="progress-dot active" id="dot1"></span>
                        <span class="progress-dot" id="dot2"></span>
                        <span class="progress-dot" id="dot3"></span>
                    </div>

                    <form id="multiStepForm" class="space-y-4">

                        <!-- Phase 1 -->
                        <div class="space-y-4 phase" id="phase1">
                            <div>
                                <div
                                    class="flex items-center border rounded-lg px-3 py-2 focus-within:ring-2 focus-within:ring-black">
                                    <img src="assets/images/name.svg" alt="Name Icon" class="inline h-6 w-6 mr-2">
                                    <input type="text" placeholder="Name" name="name" id="name"
                                        class="focus:outline-none w-full">
                                </div>
                                <div class="field-error hidden" id="nameError"></div>
                            </div>

                            <div>
                                <div
                                    class="flex items-center border rounded-lg px-3 py-2 focus-within:ring-2 focus-within:ring-black">
                                    <img src="assets/images/person-name.svg" alt="Surname Icon"
                                        class="inline h-6 w-6 mr-2">
                                    <input type="text" placeholder="Surname" name="surname" id="surname"
                                        class="focus:outline-none w-full">
                                </div>
                                <div class="field-error hidden" id="surnameError"></div>
                            </div>

                            <div>
                                <div
                                    class="flex items-center border rounded-lg px-3 py-2 focus-within:ring-2 focus-within:ring-black">
                                    <img src="assets/images/email.svg" alt="Email Icon" class="inline h-6 w-6 mr-2">
                                    <input type="email" placeholder="Email" name="email" id="email"
                                        class="focus:outline-none w-full">
                                </div>
                                <div class="field-error hidden" id="emailError"></div>
                            </div>

                            <div>
                                <div
                                    class="flex items-center border rounded-lg px-3 py-2 focus-within:ring-2 focus-within:ring-black">
                                    <img src="assets/images/phone.svg" alt="Phone Icon" class="inline h-6 w-6 mr-2">
                                    <input type="tel" placeholder="Phone" name="phone" id="phone"
                                        class="focus:outline-none w-full">
                                </div>
                                <div class="field-error hidden" id="phoneError"></div>
                            </div>

                            <button type="button" onclick="validatePhase1()"
                                class="w-full px-4 py-2 bg-black text-white rounded-lg hover:bg-gray-800">Next</button>
                        </div>

                        <!-- Phase 2 -->
                        <div class="space-y-4 phase hidden" id="phase2">
                            <div>
                                <div
                                    class="flex items-center border rounded-lg px-3 py-2 focus-within:ring-2 focus-within:ring-black">
                                    <img src="assets/images/address.svg" alt="Address Icon" class="inline h-6 w-6 mr-2">
                                    <input type="text" placeholder="Street Address" name="street" id="street"
                                        class="focus:outline-none w-full">
                                </div>
                                <div class="field-error hidden" id="streetError"></div>
                            </div>

                            <div class="flex flex-col md:flex-row gap-4">
                                <div class="w-full">
                                    <div
                                        class="flex items-center border rounded-lg px-3 py-2 focus-within:ring-2 focus-within:ring-black">
                                        <img src="assets/images/city.svg" alt="City Icon" class="inline h-6 w-6 mr-2">
                                        <input type="text" placeholder="City" name="city" id="city"
                                            class="focus:outline-none w-full">
                                    </div>
                                    <div class="field-error hidden" id="cityError"></div>
                                </div>

                                <div class="w-full">
                                    <div
                                        class="flex items-center border rounded-lg px-3 py-2 focus-within:ring-2 focus-within:ring-black">
                                        <img src="assets/images/postal.svg" alt="Postal Icon"
                                            class="inline h-6 w-6 mr-2">
                                        <input type="text" placeholder="Postal Code" name="postal" id="postal"
                                            class="focus:outline-none w-full">
                                    </div>
                                    <div class="field-error hidden" id="postalError"></div>
                                </div>
                            </div>

                            <div>
                                <div
                                    class="flex items-center border rounded-lg px-3 py-2 focus-within:ring-2 focus-within:ring-black">
                                    <img src="assets/images/state.svg" alt="State Icon" class="inline h-6 w-6 mr-2">
                                    <input type="text" placeholder="Province/State" name="province" id="province"
                                        class="focus:outline-none w-full">
                                </div>
                                <div class="field-error hidden" id="provinceError"></div>
                            </div>

                            <div class="flex justify-between">
                                <button type="button" onclick="prevPhase(2)"
                                    class="px-4 py-2 bg-gray-400 text-white rounded-lg hover:bg-gray-500">
                                    <img src="assets/images/back-arrow1.svg" alt="Left Arrow"
                                        class="inline h-4 w-4 mr-2">
                                    Back
                                </button>
                                <button type="button" onclick="validatePhase2()"
                                    class="px-4 py-2 bg-black text-white rounded-lg hover:bg-gray-800">
                                    Next
                                    <img src="assets/images/next-arrow1.svg" alt="Right Arrow"
                                        class="inline h-4 w-5 ml-2">
                                </button>
                            </div>
                        </div>

                        <!-- Phase 3 -->
                        <div class="space-y-4 phase hidden" id="phase3">
                            <div>
                                <div
                                    class="flex items-center border rounded-lg px-3 py-2 focus-within:ring-2 focus-within:ring-black">
                                    <img src="assets/images/password.svg" alt="Password Icon"
                                        class="inline h-6 w-6 mr-2">
                                    <input type="password" placeholder="Password" name="password" id="password"
                                        class="focus:outline-none w-full">
                                </div>
                                <div class="field-error hidden" id="passwordError"></div>
                            </div>

                            <div>
                                <div
                                    class="flex items-center border rounded-lg px-3 py-2 focus-within:ring-2 focus-within:ring-black">
                                    <img src="assets/images/password.svg" alt="Confirm Password Icon"
                                        class="inline h-6 w-6 mr-2">
                                    <input type="password" placeholder="Confirm Password" name="confirmPassword"
                                        id="confirmPassword" class="focus:outline-none w-full">
                                </div>
                                <div class="field-error hidden" id="confirmPasswordError"></div>
                            </div>

                            <div class="flex gap-2">
                                <button type="button" onclick="prevPhase(3)"
                                    class="px-4 py-2 bg-gray-400 text-white rounded-lg hover:bg-gray-500">
                                    <img src="assets/images/back-arrow1.svg" alt="Left Arrow"
                                        class="inline h-4 w-4 mr-2">
                                    Back
                                </button>
                                <button type="submit"
                                    class="px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600">Register</button>
                            </div>
                        </div>

                    </form>
                    <p class="mt-4 text-sm">Already have an account?
                        <button onclick="showLogin()" class="text-blue-600 font-semibold">Login</button>
                    </p>
                </div>

                <!-- Forgot Password Form -->
                <div id="forgotPasswordForm" class="w-full max-w-sm hidden form-transition">
                    <h2 class="text-2xl md:text-3xl font-bold mb-6">Reset Password</h2>
                    
                    <!-- Email Input Step -->
                    <div id="emailStep" class="space-y-4">
                        <p class="text-gray-600 mb-4">Enter your email address and we'll send you a reset link.</p>
                        
                        <div class="flex items-center border rounded-lg px-3 py-2 focus-within:ring-2 focus-within:ring-black">
                            <img src="assets/images/email.svg" alt="Email Icon" class="inline h-6 w-6 mr-2">
                            <input type="email" id="resetEmail" placeholder="Enter your email" class="focus:outline-none w-full">
                        </div>
                        <div class="field-error hidden" id="resetEmailError"></div>
                        
                        <button type="button" onclick="sendResetLink()" class="w-full px-4 py-2 bg-black text-white rounded-lg hover:bg-gray-800">
                            Send Reset Link
                        </button>
                    </div>

                    <!-- Reset Password Step -->
                    <div id="resetStep" class="space-y-4 hidden">
                        <p class="text-gray-600 mb-4">Enter your new password below.</p>
                        
                        <div class="flex items-center border rounded-lg px-3 py-2 focus-within:ring-2 focus-within:ring-black">
                            <img src="assets/images/password.svg" alt="Password Icon" class="inline h-6 w-6 mr-2">
                            <input type="password" id="newPassword" placeholder="New Password" class="focus:outline-none w-full">
                        </div>
                        <div class="field-error hidden" id="newPasswordError"></div>
                        
                        <div class="flex items-center border rounded-lg px-3 py-2 focus-within:ring-2 focus-within:ring-black">
                            <img src="assets/images/password.svg" alt="Confirm Password Icon" class="inline h-6 w-6 mr-2">
                            <input type="password" id="confirmNewPassword" placeholder="Confirm New Password" class="focus:outline-none w-full">
                        </div>
                        <div class="field-error hidden" id="confirmNewPasswordError"></div>
                        
                        <div class="flex gap-2">
                            <button type="button" onclick="backToEmailStep()" class="px-4 py-2 bg-gray-400 text-white rounded-lg hover:bg-gray-500 flex-1">
                                Back
                            </button>
                            <button type="button" onclick="resetPassword()" class="px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 flex-1">
                                Reset Password
                            </button>
                        </div>
                    </div>

                    <p class="mt-4 text-sm">
                        Remember your password?
                        <button onclick="showLogin()" class="text-blue-600 font-semibold">Login</button>
                    </p>
                </div>

            </div>
        </div>
    </div>

    <footer class=" w-full justify-center items-center mt-10 bottom-0">
        <p class="text-sm text-center text-gray-400 opacity-30">Developed by Nethononda N</p>

    </footer>

   <!-- Success Modal -->
   <div id="successModal" class="hidden z-50 fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 backdrop-blur-sm transition-opacity duration-300">
        <div class="bg-white rounded-2xl shadow-xl p-8 max-w-sm w-full mx-4 text-center transform scale-95 transition-transform duration-300">
            <img src="assets/images/correct-success-tick-svgrepo-com.svg" alt="Success Icon" class="h-16 w-16 mx-auto mb-4 animate-bounce">
            <h2 class="text-2xl font-extrabold text-green-600 mb-2">Success!</h2>
            <p id="successMessage" class="text-gray-700 mb-6"></p>
            <button 
                class="bg-green-600 text-white px-6 py-2 rounded-lg font-semibold hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition"
                onclick="document.getElementById('successModal').classList.add('hidden')">
                Close
            </button>
        </div>
    </div>

    <!-- Error Modal -->
    <div id="errorModal" class="hidden z-50 fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 backdrop-blur-sm transition-opacity duration-300">
        <div class="bg-white rounded-2xl shadow-xl p-8 max-w-sm w-full mx-4 text-center transform scale-95 transition-transform duration-300">
            <h2 class="text-2xl font-extrabold text-red-600 mb-2">Error</h2>
            <p id="errorMessage" class="text-gray-700 mb-6"></p>
            <button 
                class="bg-red-600 text-white px-6 py-2 rounded-lg font-semibold hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition"
                onclick="document.getElementById('errorModal').classList.add('hidden')">
                Close
            </button>
        </div>
    </div>

    <script>
        // Form validation functions
        function validateEmail(email) {
            const re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
            return re.test(String(email).toLowerCase());
        }

        function validatePhone(phone) {
            const re = /^[\+]?[0-9][\d]{0,15}$/;
            return re.test(phone.replace(/[\s\-\(\)]/g, ''));
        }

        function validatePassword(password) {
            // At least 8 characters, 1 uppercase, 1 lowercase, 1 number
            const re = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/;
            return re.test(password);
        }

        function showError(fieldId, errorId, message) {
            const field = document.getElementById(fieldId);
            if (field) {
                field.parentElement.classList.add('error');
                field.parentElement.classList.remove('valid');
            }
            const errorElement = document.getElementById(errorId);
            if (errorElement) {
                errorElement.textContent = message;
                errorElement.classList.remove('hidden');
            }
        }

        function showValid(fieldId, errorId) {
            const field = document.getElementById(fieldId);
            if (field) {
                field.parentElement.classList.remove('error');
                field.parentElement.classList.add('valid');
            }
            const errorElement = document.getElementById(errorId);
            if (errorElement) {
                errorElement.classList.add('hidden');
            }
        }

        // Form navigation functions
        function showLogin() {
            document.getElementById("registerForm").classList.add("hidden");
            document.getElementById("forgotPasswordForm").classList.add("hidden");
            document.getElementById("loginForm").classList.remove("hidden");
            resetForms();
        }

        function showRegister() {
            document.getElementById("loginForm").classList.add("hidden");
            document.getElementById("forgotPasswordForm").classList.add("hidden");
            document.getElementById("registerForm").classList.remove("hidden");
            resetForms();
        }

        function showForgotPassword() {
            document.getElementById("loginForm").classList.add("hidden");
            document.getElementById("registerForm").classList.add("hidden");
            document.getElementById("forgotPasswordForm").classList.remove("hidden");
            resetForms();
            showEmailStep();
        }

        function showEmailStep() {
            document.getElementById('resetStep').classList.add('hidden');
            document.getElementById('emailStep').classList.remove('hidden');
        }

        function showResetStep() {
            document.getElementById('emailStep').classList.add('hidden');
            document.getElementById('resetStep').classList.remove('hidden');
        }

        function backToEmailStep() {
            showEmailStep();
        }

        // Forgot Password Functions
        function sendResetLink() {
            const email = document.getElementById('resetEmail').value.trim();
            let isValid = true;

            // Validate email
            if (email === '') {
                showError('resetEmail', 'resetEmailError', 'Email is required');
                isValid = false;
            } else if (!validateEmail(email)) {
                showError('resetEmail', 'resetEmailError', 'Please enter a valid email address');
                isValid = false;
            } else {
                showValid('resetEmail', 'resetEmailError');
            }

            if (isValid) {
                // Simulate API call to send reset link
                document.getElementById('successMessage').innerText = 'Password reset link has been sent to your email!';
                document.getElementById('successModal').classList.remove('hidden');
                
                // In a real application, you would make an API call here
                // For demo purposes, we'll show the reset step after a delay
                setTimeout(() => {
                    showResetStep();
                }, 2000);
            }
        }

        function resetPassword() {
            const newPassword = document.getElementById('newPassword').value;
            const confirmPassword = document.getElementById('confirmNewPassword').value;
            let isValid = true;

            // Validate new password
            if (newPassword === '') {
                showError('newPassword', 'newPasswordError', 'New password is required');
                isValid = false;
            } else if (!validatePassword(newPassword)) {
                showError('newPassword', 'newPasswordError', 'Password must be at least 8 characters with uppercase, lowercase, and number');
                isValid = false;
            } else {
                showValid('newPassword', 'newPasswordError');
            }

            // Validate confirm password
            if (confirmPassword === '') {
                showError('confirmNewPassword', 'confirmNewPasswordError', 'Please confirm your password');
                isValid = false;
            } else if (confirmPassword !== newPassword) {
                showError('confirmNewPassword', 'confirmNewPasswordError', 'Passwords do not match');
                isValid = false;
            } else {
                showValid('confirmNewPassword', 'confirmNewPasswordError');
            }

            if (isValid) {
                // Simulate API call to reset password
                document.getElementById('successMessage').innerText = 'Your password has been reset successfully!';
                document.getElementById('successModal').classList.remove('hidden');
                
                // Redirect to login after success
                setTimeout(() => {
                    showLogin();
                }, 2000);
            }
        }

        // Registration Form Functions
        function validatePhase1() {
            let isValid = true;

            // Validate name
            const name = document.getElementById('name').value.trim();
            if (name === '') {
                showError('name', 'nameError', 'Name is required');
                isValid = false;
            } else if (name.length < 2) {
                showError('name', 'nameError', 'Name must be at least 2 characters');
                isValid = false;
            } else {
                showValid('name', 'nameError');
            }

            // Validate surname
            const surname = document.getElementById('surname').value.trim();
            if (surname === '') {
                showError('surname', 'surnameError', 'Surname is required');
                isValid = false;
            } else if (surname.length < 2) {
                showError('surname', 'surnameError', 'Surname must be at least 2 characters');
                isValid = false;
            } else {
                showValid('surname', 'surnameError');
            }

            // Validate email
            const email = document.getElementById('email').value.trim();
            if (email === '') {
                showError('email', 'emailError', 'Email is required');
                isValid = false;
            } else if (!validateEmail(email)) {
                showError('email', 'emailError', 'Please enter a valid email address');
                isValid = false;
            } else {
                showValid('email', 'emailError');
            }

            // Validate phone
            const phone = document.getElementById('phone').value.trim();
            if (phone === '') {
                showError('phone', 'phoneError', 'Phone number is required');
                isValid = false;
            } else if (!validatePhone(phone)) {
                showError('phone', 'phoneError', 'Please enter a valid phone number');
                isValid = false;
            } else {
                showValid('phone', 'phoneError');
            }

            if (isValid) {
                nextPhase(1);
                updateProgress(2);
            }

            return isValid;
        }

        function validatePhase2() {
            let isValid = true;

            // Validate street
            const street = document.getElementById('street').value.trim();
            if (street === '') {
                showError('street', 'streetError', 'Street address is required');
                isValid = false;
            } else if (street.length < 5) {
                showError('street', 'streetError', 'Please enter a valid street address');
                isValid = false;
            } else {
                showValid('street', 'streetError');
            }

            // Validate city
            const city = document.getElementById('city').value.trim();
            if (city === '') {
                showError('city', 'cityError', 'City is required');
                isValid = false;
            } else if (city.length < 2) {
                showError('city', 'cityError', 'Please enter a valid city name');
                isValid = false;
            } else {
                showValid('city', 'cityError');
            }

            // Validate postal code
            const postal = document.getElementById('postal').value.trim();
            if (postal === '') {
                showError('postal', 'postalError', 'Postal code is required');
                isValid = false;
            } else if (postal.length < 3) {
                showError('postal', 'postalError', 'Please enter a valid postal code');
                isValid = false;
            } else {
                showValid('postal', 'postalError');
            }

            // Validate province/state
            const province = document.getElementById('province').value.trim();
            if (province === '') {
                showError('province', 'provinceError', 'Province/State is required');
                isValid = false;
            } else if (province.length < 2) {
                showError('province', 'provinceError', 'Please enter a valid province/state');
                isValid = false;
            } else {
                showValid('province', 'provinceError');
            }

            if (isValid) {
                nextPhase(2);
                updateProgress(3);
            }

            return isValid;
        }

        function validatePhase3() {
            let isValid = true;

            // Validate password
            const password = document.getElementById('password').value;
            if (password === '') {
                showError('password', 'passwordError', 'Password is required');
                isValid = false;
            } else if (!validatePassword(password)) {
                showError('password', 'passwordError', 'Password must be at least 8 characters with uppercase, lowercase, and number');
                isValid = false;
            } else {
                showValid('password', 'passwordError');
            }

            // Validate confirm password
            const confirmPassword = document.getElementById('confirmPassword').value;
            if (confirmPassword === '') {
                showError('confirmPassword', 'confirmPasswordError', 'Please confirm your password');
                isValid = false;
            } else if (confirmPassword !== password) {
                showError('confirmPassword', 'confirmPasswordError', 'Passwords do not match');
                isValid = false;
            } else {
                showValid('confirmPassword', 'confirmPasswordError');
            }

            return isValid;
        }

        function nextPhase(current) {
            document.getElementById('phase' + current).classList.add('hidden');
            document.getElementById('phase' + (current + 1)).classList.remove('hidden');
        }

        function prevPhase(current) {
            document.getElementById('phase' + current).classList.add('hidden');
            document.getElementById('phase' + (current - 1)).classList.remove('hidden');
            updateProgress(current - 1);
        }

        function updateProgress(phase) {
            // Reset all dots
            document.getElementById('dot1').classList.remove('active');
            document.getElementById('dot2').classList.remove('active');
            document.getElementById('dot3').classList.remove('active');

            // Activate dots up to current phase
            for (let i = 1; i <= phase; i++) {
                document.getElementById('dot' + i).classList.add('active');
            }
        }

        function resetForms() {
            // Reset registration form
            document.getElementById('phase1').classList.remove('hidden');
            document.getElementById('phase2').classList.add('hidden');
            document.getElementById('phase3').classList.add('hidden');
            updateProgress(1);

            // Clear all fields
            const inputs = document.querySelectorAll('input');
            inputs.forEach(input => {
                input.value = '';
                input.parentElement.classList.remove('error', 'valid');
            });

            // Clear all error messages
            const errors = document.querySelectorAll('.field-error');
            errors.forEach(error => {
                error.classList.add('hidden');
                error.textContent = '';
            });

            // Reset forgot password form to email step
            showEmailStep();
        }

        // Form submission for registration
        document.getElementById('multiStepForm').addEventListener('submit', function (e) {
            e.preventDefault();

            if (validatePhase3()) {
                const formData = {
                    name: document.getElementById('name').value.trim(),
                    surname: document.getElementById('surname').value.trim(),
                    email: document.getElementById('email').value.trim(),
                    phone: document.getElementById('phone').value.trim(),
                    street: document.getElementById('street').value.trim(),
                    city: document.getElementById('city').value.trim(),
                    postal: document.getElementById('postal').value.trim(),
                    province: document.getElementById('province').value.trim(),
                    password: document.getElementById('password').value
                };

                async function submitData(data) {
                    document.getElementById('errorMessage').innerText = '';
                    document.getElementById('successMessage').innerText = '';
                    
                    try {
                        const response = await fetch('api/v1/register.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify(data) 
                        });

                        const result = await response.json();

                        if (!response.ok) {
                            if (result.errors) {
                                for (const field in result.errors) {
                                    const fieldElement = document.getElementById(field);
                                    if (fieldElement) {
                                        fieldElement.classList.add('border-red-500');
                                        let errorEl = document.createElement('p');
                                        errorEl.classList.add('text-red-500', 'text-sm');
                                        errorEl.innerText = result.errors[field];
                                        if (!fieldElement.nextElementSibling || !fieldElement.nextElementSibling.classList.contains('text-red-500')) {
                                            fieldElement.parentNode.appendChild(errorEl);
                                        }
                                    }
                                }
                            } else {
                                document.getElementById('errorMessage').innerText = result.error || 'Something went wrong';
                            }
                            document.getElementById('errorModal').classList.remove('hidden');
                            return;
                        }

                        document.getElementById('successModal').classList.remove('hidden');
                        document.getElementById('successMessage').innerText = result.message || "Registration successful!";
                        document.getElementById('multiStepForm').reset();
                        showLogin();

                    } catch (error) {
                        console.error('Error:', error);
                        document.getElementById('errorMessage').innerText = error.message;
                        document.getElementById('errorModal').classList.remove('hidden');
                    }
                }

                submitData(formData);
            }
        });

        // Event listeners for real-time validation
        document.getElementById('email').addEventListener('blur', function () {
            const email = this.value.trim();
            if (email !== '' && !validateEmail(email)) {
                showError('email', 'emailError', 'Please enter a valid email address');
            } else if (email !== '') {
                showValid('email', 'emailError');
            }
        });

        document.getElementById('phone').addEventListener('blur', function () {
            const phone = this.value;
            if (phone !== '' && !validatePhone(phone)) {
                showError('phone', 'phoneError', 'Please enter a valid phone number');
            } else if (phone !== '') {
                showValid('phone', 'phoneError');
            }
        });

        document.getElementById('confirmPassword').addEventListener('input', function () {
            const password = document.getElementById('password').value;
            const confirmPassword = this.value;

            if (confirmPassword !== '' && confirmPassword !== password) {
                showError('confirmPassword', 'confirmPasswordError', 'Passwords do not match');
            } else if (confirmPassword !== '') {
                showValid('confirmPassword', 'confirmPasswordError');
            }
        });

        document.getElementById('confirmNewPassword').addEventListener('input', function () {
            const newPassword = document.getElementById('newPassword').value;
            const confirmPassword = this.value;

            if (confirmPassword !== '' && confirmPassword !== newPassword) {
                showError('confirmNewPassword', 'confirmNewPasswordError', 'Passwords do not match');
            } else if (confirmPassword !== '') {
                showValid('confirmNewPassword', 'confirmNewPasswordError');
            }
        });
    </script>

</body>

</html>