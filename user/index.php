<?php
session_start();
require_once __DIR__ . '/../helpers/Functions.php';
$functions = new Functions();
$functions->isLoggedIn();
$user = $functions->info();
$entries_count = $functions->getDiaryEntries();
$habitCount = $functions->getHabitCount();
$recent = $functions->getRecentHabit($user['user_id']);

?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Habit Tracker</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Agdasima:wght@400;700&family=Lexend+Deca:wght@100..900&family=Space+Grotesk:wght@300..700&display=swap"
        rel="stylesheet">

    <style>
        body {
            font-family: 'Lexend Deca', sans-serif;
        }

        /* Loader overlay */
        .loader-overlay {
            position: fixed;
            inset: 0;
            background: rgba(255, 255, 255, 0.7);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            display: none;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-fadeIn {
            animation: fadeIn 0.6s ease forwards;
        }

        .hidden {
            display: none;
        }

        .content {
            min-height: 60vh;
            padding: 20px;
        }

        @keyframes pulse-slow {

            0%,
            100% {
                transform: scale(1);
                opacity: 1;
            }

            50% {
                transform: scale(1.05);
                opacity: 0.9;
            }
        }

        .animate-pulse-slow {
            animation: pulse-slow 3s infinite;
        }

        ::-webkit-scrollbar {
            width: 6px;
        }

        ::-webkit-scrollbar-thumb {
            background-color: #cbd5e1;
            border-radius: 3px;
        }

        @media (prefers-color-scheme: dark) {
            ::-webkit-scrollbar-thumb {
                background-color: #475569;
            }
        }

        /* Floating words animation */
        @keyframes wordFloat {
            0% {
                transform: translateY(100vh) rotate(0deg);
                opacity: 0;
            }

            10% {
                opacity: 0.1;
            }

            90% {
                opacity: 0.08;
            }

            100% {
                transform: translateY(-100px) rotate(10deg);
                opacity: 0;
            }
        }

        /* Pulse animation for AI glow */
        @keyframes pulse-slow {

            0%,
            100% {
                transform: scale(1);
                opacity: 0.8;
            }

            50% {
                transform: scale(1.05);
                opacity: 1;
            }
        }

        .animate-pulse-slow {
            animation: pulse-slow 3s infinite;
        }

        /* Mobile menu styles */
        .mobile-menu {
            transform: translateX(-100%);
            transition: transform 0.3s ease-in-out;
        }

        .mobile-menu.open {
            transform: translateX(0);
        }

        .overlay {
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.3s ease-in-out;
        }

        .overlay.active {
            opacity: 1;
            visibility: visible;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .desktop-nav {
                display: none !important;
            }

            .mobile-nav-button {
                display: flex;
            }

            /* Hide desktop profile/notification buttons on mobile */
            header nav:last-child {
                display: none !important;
            }

            header img {}

            .content {
                padding: 10px;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .quick-actions-grid {
                grid-template-columns: 1fr 1fr;
            }

            .header-content {
                padding: 0 1rem;
            }
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .desktop-nav {
                display: none !important;
            }

            .mobile-nav-button {
                display: flex;
            }

            /* Hide desktop profile/notification buttons on mobile */
            header nav:last-child {
                display: none !important;
            }

            .content {
                padding: 10px;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .quick-actions-grid {
                grid-template-columns: 1fr 1fr;
            }

            .header-content {
                padding: 0 1rem;
            }

            /* Mobile logo positioning */
            .header-content>div:first-child {
                margin-left: auto;
            }
        }

        @media (min-width: 769px) {
            .mobile-nav-button {
                display: none !important;
            }

            .desktop-nav {
                display: flex !important;
            }

            /* Show desktop profile/notification buttons */
            header nav:last-child {
                display: flex !important;
            }

            /* Reset logo positioning for desktop */
            .header-content>div:first-child {
                margin-left: 0;
            }
        }
    </style>
</head>

<body class="bg-gray-100 min-h-screen">

    <!-- Mobile Menu Button -->
    <div class="mobile-nav-button fixed top-4 left-4 z-50 md:hidden">
        <button id="mobileMenuButton" class="p-2 rounded-md bg-white shadow-md">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16">
                </path>
            </svg>
        </button>
    </div>

    <!-- Mobile Menu Overlay -->
    <div id="mobileOverlay" class="overlay fixed inset-0 bg-black bg-opacity-50 z-40"></div>

    <!-- Mobile Menu -->
    <div id="mobileMenu" class="mobile-menu fixed top-0 left-0 h-full w-64 bg-white shadow-lg z-50 p-4">
        <div class="flex justify-between items-center mb-8">
            <div class="flex items-center">
                <img src="../assets/images/logo.svg" alt="Logo" class="h-10 w-10">
                <h1 class="text-sm font-bold text-gray-800 ml-2">Habit Tracker</h1>
            </div>
            <button id="closeMobileMenu" class="p-2">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                    </path>
                </svg>
            </button>
        </div>

        <nav class="space-y-4">
            <a href="#" data-section="home"
                class="nav-link flex items-center gap-3 p-3 rounded-lg hover:bg-gray-100 text-gray-700">
                <i class="fas fa-home w-5"></i>
                <span>Home</span>
            </a>

            <a href="#" data-section="habits"
                class="nav-link flex items-center gap-3 p-3 rounded-lg hover:bg-gray-100 text-gray-700">
                <i class="fas fa-tasks w-5"></i>
                <span>Habits</span>
            </a>

            <a href="#" data-section="notes"
                class="nav-link flex items-center gap-3 p-3 rounded-lg hover:bg-gray-100 text-gray-700">
                <i class="fas fa-book w-5"></i>
                <span>Diary</span>
            </a>

            <a href="#" data-section="chat"
                class="nav-link flex items-center gap-3 p-3 rounded-lg hover:bg-gray-100 text-gray-700">
                <img src="../assets/images/AI.png" alt="Chat Icon" class="w-5 h-5">
                <span>AI Assistant</span>
            </a>

            <div class="border-t pt-4 mt-4">
                <div class="flex items-center gap-3 p-3">
                    <img src="../assets/images/profile.svg" alt="Profile" class="h-8 w-8 rounded-full">
                    <div>
                        <p class="text-sm font-semibold text-gray-800">
                            <?php echo $functions->sanitizeInput(substr($user['name'], 0, 1) . " " . $user['surname']); ?>
                        </p>
                        <p class="text-[10px] text-gray-500"><?php echo $functions->sanitizeInput($user['email']); ?>
                        </p>
                    </div>
                </div>

                <button class="w-full flex items-center gap-3 p-3 rounded-lg hover:bg-gray-100 text-gray-700">
                    <i class="fas fa-cog w-5"></i>
                    <span>Settings</span>
                </button>

                <button class="w-full flex items-center gap-3 p-3 rounded-lg hover:bg-gray-100 text-gray-700">
                    <i class="fas fa-edit w-5"></i>
                    <span>Edit Profile</span>
                </button>

                <button class="w-full flex items-center gap-3 p-3 rounded-lg hover:bg-gray-100 text-gray-700">
                    <i class="fas fa-key w-5"></i>
                    <span>Reset Password</span>
                </button>

                <button id="logout" class="w-full flex items-center gap-3 p-3 rounded-lg hover:bg-red-100 text-red-600">
                    <i class="fas fa-sign-out-alt w-5"></i>
                    <span>Logout</span>
                </button>
            </div>
        </nav>
    </div>

    <header class="bg-white shadow p-4 flex justify-between items-center h-[100px] shodow-lg">
        <div class="flex items-center justify-between w-full md:justify-start md:gap-[100px] header-content">
            <!-- Mobile: Logo on the right, Desktop: Logo on the left -->
            <div class="flex items-center md:justify-center flex-col md:flex-row md:items-center order-2 md:order-1">
                <img src="../assets/images/logo.svg" alt="Logo" class="h-8 w-8 md:h-16 md:w-16">
                <h1 class="text-xl md:text-2xl font-bold text-gray-800 md:ml-4">Habit Tracker</h1>
            </div>

            <!-- Desktop Navigation - Hidden on mobile -->
            <nav class="desktop-nav hidden md:flex items-center justify-center gap-6 order-1 md:order-2">
                <a href="#" data-section="home"
                    class="nav-link relative text-gray-600 hover:text-gray-800 mx-2 flex flex-col items-center justify-center group">
                    <span>Home</span>
                    <span
                        class="absolute left-0 bottom-0 w-full h-[2px] bg-slate-500 scale-x-0 group-hover:scale-x-100 transition-transform duration-300 origin-left"></span>
                </a>

                <a href="#" data-section="habits"
                    class="nav-link relative text-gray-600 hover:text-gray-800 mx-2 flex flex-col items-center justify-center group">
                    <span>Habits</span>
                    <span
                        class="absolute left-0 bottom-0 w-full h-[2px] bg-slate-500 scale-x-0 group-hover:scale-x-100 transition-transform duration-300 origin-left"></span>
                </a>

                <a href="#" data-section="notes"
                    class="nav-link relative text-gray-600 hover:text-gray-800 mx-2 flex flex-col items-center justify-center group">
                    <span>Diary</span>
                    <span
                        class="absolute left-0 bottom-0 w-full h-[2px] bg-slate-500 scale-x-0 group-hover:scale-x-100 transition-transform duration-300 origin-left"></span>
                </a>

                <!--chat room-->
                <a href="#" data-section="chat"
                    class="nav-link relative font-bold text-gray-600 hover:text-gray-800 mx-2 flex flex-col items-center justify-center group">
                    <img src="../assets/images/AI.png" alt="Chat Icon" class="inline h-10 w-10 mr-1">
                    <span
                        class="absolute left-0 bottom-0 w-full h-[2px] bg-slate-500 scale-x-0 group-hover:scale-x-100 transition-transform duration-300 origin-left"></span>
                </a>
            </nav>
        </div>

        <!-- Desktop Profile/Notification Buttons - Hidden on mobile -->
        <nav class="hidden md:flex items-center justify-center">
            <!--notification-->
            <button id="notificationBtn"
                class="relative text-gray-600 hover:text-gray-800 mx-2 flex flex-col items-center justify-center focus:outline-none group">
                <img src="../assets/images/notifications.svg" alt="Notification Icon" class="inline h-10 w-10 mr-1">
                <span
                    class="absolute bottom-[-30px] left-1/2 -translate-x-1/2 bg-gray-300 text-slate-800 text-xs rounded px-2 py-1 opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none whitespace-nowrap z-10">
                    Notifications
                </span>
            </button>
            <button id="profileBtn" type="button"
                class="relative text-gray-600 hover:text-gray-800 mx-2 flex flex-col items-center justify-center focus:outline-none group">
                <img src="../assets/images/profile.svg" alt="User Icon" class="inline h-10 w-10 mr-1">
                <span
                    class="absolute bottom-[-30px] left-1/2 -translate-x-1/2 bg-gray-300 text-slate-800 text-xs rounded px-2 py-1 opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none whitespace-nowrap z-10">
                    Profile
                </span>
            </button>
        </nav>
    </header>

    <div class="loader-overlay" id="loaderOverlay">
        <div class="loader"></div>
    </div>

    <main class="container mx-auto p-4 mt-4 md:mt-0">

        <!-- Enhanced Home Section -->
        <section id="home" class="content">
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-800 mb-2">Welcome back,
                    <?php echo $functions->sanitizeInput($user['name']); ?>!
                    <input type="hidden" id="user-name" value="<?php echo $functions->sanitizeInput($user['name']); ?>">
                </h1>
                <p class="text-gray-600">Here's your daily overview</p>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8 stats-grid">
                <div class="bg-white rounded-sm shadow-md p-6 border-slate-800 border-2">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-500 text-sm">Active Habits</p>
                            <p class="text-2xl font-bold text-gray-800" id="activeHabitsCount"><?= $habitCount ?></p>
                        </div>
                        <div class="bg-blue-100 p-3 rounded-full">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 2v20M2 12h20" />
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-sm shadow-md p-6 border-slate-800 border-2">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-500 text-sm">This Week's Progress</p>
                            <p class="text-2xl font-bold text-gray-800" id="weeklyProgress">
                                <?php
                                $progress = ($habitCount + $entries_count) / 25 * 100;
                                echo $progress . "%";

                                ?>
                            </p>
                        </div>
                        <div class="p-3 rounded-full">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
                                <polyline points="22 4 12 14.01 9 11.01" />
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-sm shadow-md p-6 border-slate-800 border-2">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-500 text-sm">Diary Entries</p>
                            <p class="text-2xl font-bold text-gray-800" id="diaryEntriesCount">
                                <?php echo $functions->sanitizeInput($entries_count); ?>
                            </p>
                        </div>
                        <div class=" p-3 rounded-full">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                                <polyline points="14 2 14 8 20 8" />
                                <line x1="16" y1="13" x2="8" y2="13" />
                                <line x1="16" y1="17" x2="8" y2="17" />
                                <line x1="10" y1="9" x2="8" y2="9" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="bg-white rounded-sm shadow-md p-6 mb-8 border-slate-800 border-2">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">Quick Actions</h2>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 quick-actions-grid">
                    <a href="#" data-section="add-habit"
                        class="nav-link bg-gray-50 hover:bg-gray-100 p-4 rounded-sm text-center transition-colors border border-gray-800">
                        <div
                            class="bg-gray-100 p-2 rounded-full w-12 h-12 mx-auto mb-2 flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 2v20M2 12h20" />
                            </svg>
                        </div>
                        <p class="text-sm font-medium text-gray-800">Add Habit</p>
                    </a>

                    <a href="#" data-section="notes"
                        class="nav-link bg-gray-50 hover:bg-gray-100 p-4 rounded-sm text-center transition-colors border border-gray-800">
                        <div
                            class="bg-gray-100 p-2 rounded-full w-12 h-12 mx-auto mb-2 flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="w-6 h-6">
                                <path
                                    d="M10 3a1 1 0 011-1h2a1 1 0 011 1v1h3a2 2 0 012 2v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6a2 2 0 012-2h3V3z" />
                                <line x1="9" y1="7" x2="15" y2="7" />
                                <line x1="9" y1="11" x2="15" y2="11" />
                                <line x1="9" y1="15" x2="13" y2="15" />
                            </svg>
                        </div>
                        <p class="text-sm font-medium text-gray-800">Write Diary</p>
                    </a>

                    <a href="#" data-section="chat"
                        class="nav-link bg-gray-50 hover:bg-gray-100 p-4 rounded-sm text-center transition-colors border border-gray-800">
                        <div
                            class="bg-gray-100 p-2 rounded-full w-12 h-12 mx-auto mb-2 flex items-center justify-center">
                            <img src="../assets/images/AI.png" alt="AI Chat" class="w-6 h-6">
                        </div>
                        <p class="text-sm font-medium text-gray-800">AI Assistant</p>
                    </a>

                    <button id="profileBtn"
                        class="bg-gray-50 hover:bg-gray-100 p-4 rounded-sm text-center transition-colors border border-gray-800">
                        <div
                            class="bg-gray-100 p-2 rounded-full w-12 h-12 mx-auto mb-2 flex items-center justify-center">
                            <img src="../assets/images/profile.svg" alt="Profile" class="w-6 h-6">
                        </div>
                        <p class="text-sm font-medium text-gray-800">My Profile</p>
                    </button>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">Recent Activity</h2>
                <div id="recentActivity" class="space-y-3">
                    <div class="flex items-center justify-between py-2 border-b border-gray-100">
                        <div class="flex items-center justfiy-between">
                            <?php foreach ($recent as $rec): ?>
                                <div
                                    class="flex items-center w-full justify-between bg-white p-3 rounded-lg shadow-sm mb-2">
                                    <div class="flex items-center space-x-2">
                                        <div class="bg-green-100 p-2 rounded-full">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24"
                                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                                stroke-linejoin="round">
                                                <polyline points="20 6 9 17 4 12"></polyline>
                                            </svg>
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium text-gray-800">
                                                <?= htmlspecialchars($rec['habit_name']) ?>
                                            </p>
                                            <p class="text-xs text-gray-500"><?= htmlspecialchars($rec['created_at']) ?></p>
                                        </div>
                                    </div>

                                </div>
                            <?php endforeach; ?>

                        </div>
                        <!-- More activity items will be loaded here -->
                    </div>
                </div>
        </section>

        <!--============HABIT SECTIONS=============-->
        <!--habit section-->
        <section id="habits" class="content hidden">

            <input type="hidden" id="user-id" name="userId"
                value="<?php echo $functions->sanitizeInput($user['user_id']); ?>">


        </section>

        <section id="view-habit" class="content hidden">
            <div id="viewContent"></div>
            <div class="grid md:grid-cols-2 grid-cols-1 gap-8 p-6 bg-white text-black rounded-sm shadow-xl">
                <!-- Habit Details -->
                <div class="border border-black rounded-md p-6" id="viewHabitDetails">
                    <h3 class="text-3xl font-bold mb-4" id="viewHabitName"></h3>
                    <p id="viewHabitDescription" class="mb-3"></p>
                    <p class="text-sm italic mb-2">Frequency: <span id="viewHabitFrequency"></span></p>
                    <p class="text-xs text-gray-600">Created on: <span id="viewHabitCreated"></span></p>
                </div>

                <!-- AI Suggestion Box -->
                <div class="border border-black bg-gray-100 rounded-md p-6 flex flex-col justify-between">
                    <div>
                        <h4 class="text-2xl font-semibold mb-4 flex items-center gap-2">
                            <img src="../assets/images/AI.png" alt="AI Icon" class="w-12 h-12">
                            AI Suggestion
                        </h4>
                        <div id="aiSuggestions" class="text-gray-800 text-lg italic">
                            <p>Loading personalized suggestion...</p>
                        </div>
                    </div>
                    <div class="flex flex-wrap gap-3 mt-6">
                        <button id="refreshSuggestion"
                            class="bg-white text-black px-4 py-2 rounded-xl font-semibold hover:bg-gray-200 transition-all">Refresh</button>
                        <button id="readSuggestion"
                            class="bg-green-500 text-white px-4 py-2 rounded-xl font-semibold hover:bg-green-600 transition-all">▶
                            Read</button>
                        <button id="pauseSuggestion"
                            class="bg-red-500 text-white px-4 py-2 rounded-xl font-semibold hover:bg-red-600 transition-all">⏸
                            Pause</button>
                    </div>
                </div>
            </div>
        </section>


        <section id="edit-habit" class="content hidden">
            <div class="flex items-center mb-6">
                <a href="#" onclick="showSection('habits')"
                    class="inline-flex items-center gap-2 text-black hover:text-gray-700">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        class="h-6 w-6 inline-block">
                        <path d="M19 12H5M12 19l-7-7 7-7" />
                    </svg>

                </a>
                <h3 class="text-2xl font-medium ml-4">Edit Habit</h3>
            </div>

            <div class="grid md:grid-cols-2 grid-cols-1 gap-8">
                <!-- Form -->
                <div>
                    <form action="add-habit.php" method="POST" id="habit-form" class="w-full">
                        <input type="hidden" id="userId" name="userId"
                            value="<?php echo $functions->sanitizeInput($user['user_id']); ?>" />

                        <div class="mb-4">
                            <label for="habitName" class="block text-gray-700 font-semibold mb-2">Habit Name</label>
                            <input type="text" id="habitName" name="habitName" required
                                class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-slate-500">
                        </div>

                        <div class="mb-4">
                            <label for="habitDescription"
                                class="block text-gray-700 font-semibold mb-2">Description</label>
                            <textarea id="habitDescription" name="habitDescription" rows="3" required
                                class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-slate-500"></textarea>
                        </div>

                        <div class="mb-4">
                            <label for="habitFrequency" class="block text-gray-700 font-semibold mb-2">Frequency</label>
                            <select id="habitFrequency" name="habitFrequency" required
                                class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-slate-500">
                                <option value="" disabled selected>Select frequency</option>
                                <option value="daily">Daily</option>
                                <option value="weekly">Weekly</option>
                                <option value="monthly">Monthly</option>
                            </select>
                        </div>

                        <button type="submit"
                            class="bg-emerald-500 text-white px-6 py-3 rounded hover:bg-emerald-600 transition-colors font-semibold">
                            Save
                        </button>
                    </form>
                </div>

                <!-- AI Suggestion Box -->
                <div class="border border-black bg-gray-100 rounded-md p-6 flex flex-col justify-between">
                    <div>
                        <h4 class="text-2xl font-semibold mb-4 flex items-center gap-2">
                            <img src="../assets/images/AI.png" alt="AI Icon" class="w-12 h-12">
                            AI Suggestion
                        </h4>
                        <div id="editAISuggestions" class="text-gray-800 text-lg italic">
                            <p>Loading personalized suggestion...</p>
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-3 mt-6">
                        <button id="editRefresh"
                            class="bg-white text-black px-4 py-2 rounded-xl font-semibold hover:bg-gray-200 transition-all">Refresh</button>
                        <button id="editRead"
                            class="bg-green-500 text-white px-4 py-2 rounded-xl font-semibold hover:bg-green-600 transition-all">▶
                            Read</button>
                        <button id="editPause"
                            class="bg-red-500 text-white px-4 py-2 rounded-xl font-semibold hover:bg-red-600 transition-all">⏸
                            Pause</button>
                    </div>
                </div>
            </div>
        </section>




        <!--habit form-->
        <section id="add-habit" class="content hidden duration-300 ease-in">
            <div class="flex items-center mb-6">
                <a href="#" data-section="habits" class="nav-link">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        class="h-6 w-6 inline-block mr-2">
                        <path d="M19 12H5M12 19l-7-7 7-7" />
                    </svg>
                </a>
                <h3 class="text-2xl font-medium">New Habit</h3>
            </div>
            <form action="add-habit.php" method="POST" class="w-full">
                <input type="hidden" id="userId" name="userId"
                    value="<?php echo $functions->sanitizeInput($user['user_id']); ?>" />
                <div class="mb-4">
                    <label for="habitName" class="block text-gray-700 font-semibold mb-2">Habit Name</label>
                    <input type="text" id="habitName" name="habitName" required
                        class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-slate-500">
                </div>
                <div class="mb-4">
                    <label for="habitDescription" class="block text-gray-700 font-semibold mb-2">Description</label>
                    <textarea id="habitDescription" name="habitDescription" rows="3" required
                        class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-slate-500"></textarea>
                </div>
                <div class="mb-4">
                    <label for="habitFrequency" class="block text-gray-700 font-semibold mb-2">Frequency</label>
                    <select id="habitFrequency" name="habitFrequency" required
                        class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-slate-500">
                        <option value="" disabled selected>Select frequency</option>
                        <option value="daily">Daily</option>
                        <option value="weekly">Weekly</option>
                        <option value="monthly">Monthly</option>
                    </select>
                </div>
                <button type="submit" name="saveHabit"
                    class="bg-emerald-500 text-white px-6 py-3 rounded hover:bg-emerald-600 transition-colors font-semibold">
                    Save
                </button>
            </form>
        </section>

        <!-- Success Modal -->
        <div id="successModal"
            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center <?php echo isset($_GET['success']) && $_GET['success'] == 1 ? '' : 'hidden'; ?>">
            <div class="bg-white p-6 rounded-xl shadow-2xl w-96 text-center transform scale-90 opacity-0">
                <!-- Animated checkmark -->
                <svg class="mx-auto mb-4 w-20 h-20 text-green-500" viewBox="0 0 52 52">
                    <circle class="check-circle" cx="26" cy="26" r="25" fill="none" stroke="#34D399" stroke-width="2" />
                    <path class="checkmark" fill="none" stroke="#34D399" stroke-width="4" d="M14 27l7 7 16-16" />
                </svg>

                <h3 class="text-2xl font-bold mb-2 text-gray-800">Success!</h3>
                <p class="text-gray-600 mb-4">Habit saved successfully.</p>
            </div>
        </div>

        <style>
            /* Modal fade and pop animation */
            #successModal>div {
                animation: modal-enter 0.5s ease forwards;
            }

            @keyframes modal-enter {
                0% {
                    transform: scale(0.8);
                    opacity: 0;
                }

                60% {
                    transform: scale(1.05);
                    opacity: 1;
                }

                100% {
                    transform: scale(1);
                    opacity: 1;
                }
            }

            /* Animate check circle */
            .check-circle {
                stroke-dasharray: 157;
                stroke-dashoffset: 157;
                animation: circle-draw 0.6s ease forwards 0.3s;
            }

            @keyframes circle-draw {
                to {
                    stroke-dashoffset: 0;
                }
            }

            /* Animate checkmark */
            .checkmark {
                stroke-dasharray: 40;
                stroke-dashoffset: 40;
                animation: check-draw 0.4s ease forwards 0.9s;
            }

            @keyframes check-draw {
                to {
                    stroke-dashoffset: 0;
                }
            }
        </style>


        <script>
            // Auto-close modal after 3 seconds and remove ?success=1 from URL
            document.addEventListener('DOMContentLoaded', () => {
                const modal = document.getElementById('successModal');
                if (!modal.classList.contains('hidden')) {
                    setTimeout(() => {
                        modal.classList.add('hidden');
                        // Remove success=1 from URL
                        const url = new URL(window.location);
                        url.searchParams.delete('success');
                        window.history.replaceState({}, document.title, url);
                    }, 3000);
                }
            });
        </script>










        <!--notes-->
        <section id="notes" class="content hidden">
            <header class="flex justify-between items-center border-b pb-3 mb-6">
                <h1 class="text-sm md:text-3xl font-serif font-bold text-gray-900">My Diary</h1>
                <div class="space-x-2">
                    <button id="monthViewBtn"
                        class="px-4 py-2 bg-gray-900 text-white rounded-lg hover:bg-gray-800 transition">Month</button>
                    <button id="weekViewBtn"
                        class="px-4 py-2 bg-gray-900 text-white rounded-lg hover:bg-gray-800 transition">Week</button>
                    <button id="dayViewBtn"
                        class="px-4 py-2 bg-gray-900 text-white rounded-lg hover:bg-gray-800 transition">Day</button>
                </div>
            </header>

            <!-- Calendar Container -->
            <div id="calendarContainer"
                class="bg-white border border-gray-300 rounded-sm shadow-md p-5 transition-all overflow-x-auto"></div>

            <!-- Diary Form Section -->
            <section id="noteForm" class="content hidden mt-8">
                <div class="flex justify-between items-center mb-6 border-b pb-3">
                    <h2 id="monthTitle" class="text-2xl font-serif text-gray-900"></h2>
                    <button id="backToCalendarBtn"
                        class="px-5 py-2.5 bg-gray-900 text-white rounded-lg hover:bg-gray-800 transition flex items-center gap-2">
                        Back
                    </button>
                </div>

                <!-- Book Layout -->
                <div id="diaryBook"
                    class="relative w-full flex flex-col md:flex-row bg-white rounded-sm shadow-lg border border-gray-300 overflow-hidden">

                    <!-- Left Page -->
                    <form id="diaryForm" class="px-8 py-6 flex-1 space-y-6 bg-white">
                        <input type="hidden" id="userId" value="<?php echo $user['user_id']; ?>">
                        <input type="hidden" id="sentimentScore">
                        <input type="hidden" id="suggestedSongTitle">
                        <input type="hidden" id="suggestedSongArtist">

                        <!-- Date -->
                        <div>
                            <label class="text-gray-700 font-medium mb-1 block">Date</label>
                            <input type="date" id="entryDate"
                                class="border-b-2 border-gray-300 focus:border-gray-900 outline-none py-2 bg-transparent w-full">
                        </div>

                        <!-- Entry -->
                        <div class="flex flex-col relative">
                            <label class="text-gray-700 font-medium mb-1 block">Diary Entry</label>
                            <textarea id="entryText" rows="12" placeholder="Dear Diary..."
                                class="w-full border border-gray-200 focus:border-gray-900 rounded-sm font-serif text-gray-800 shadow-inner bg-gray-50 p-4 resize-none transition-all"
                                style="background-image: linear-gradient(white 95%, #e5e7eb 5%); background-size: 100% 2.5em; line-height: 2.5em;"></textarea>
                        </div>

                        <!-- Mood & Song Mood -->
                        <div class="grid md:grid-cols-2 gap-6">
                            <div>
                                <label class="text-gray-700 font-medium mb-1 block">Mood</label>
                                <select id="moodLevel"
                                    class="w-full border-b-2 border-gray-300 focus:border-gray-900 outline-none py-2 bg-transparent">
                                    <option value="">Select mood</option>
                                    <option>Very Sad</option>
                                    <option>Sad</option>
                                    <option>Neutral</option>
                                    <option>Happy</option>
                                    <option>Very Happy</option>
                                </select>
                            </div>
                            <div>
                                <label class="text-gray-700 font-medium mb-1 block">Song Mood</label>
                                <select id="songMood"
                                    class="w-full border-b-2 border-gray-300 focus:border-gray-900 outline-none py-2 bg-transparent">
                                    <option value="">Select</option>
                                    <option>Relaxed</option>
                                    <option>Motivated</option>
                                    <option>Sad</option>
                                    <option>Energetic</option>
                                </select>
                            </div>
                        </div>

                        <!-- Save -->
                        <div class="text-right">
                            <button id="saveEntryBtn"
                                class="bg-gray-900 text-white px-8 py-3 rounded-lg font-semibold hover:bg-gray-800 transition-colors shadow">
                                Save Entry
                            </button>
                        </div>
                    </form>

                    <!-- Right Page (AI Feedback) -->
                    <div id="songSuggestion" class="w-full md:w-80 bg-gray-50 border-l border-gray-300 p-5">
                        <h3 class="hidden text-lg font-semibold mb-2">🎵 Suggested Song</h3>
                        <p id="suggestedSong" class=" hidden text-gray-700 text-sm">Your suggested song will appear
                            here.</p>
                        <button id="playSongBtn"
                            class="hidden mt-4 px-5 py-2 bg-gray-900 text-white rounded-lg hover:bg-gray-800 transition shadow">Play</button>

                        <hr class="my-4 border-gray-300">

                        <h4 class="font-semibold text-gray-900 mb-2">💬 AI Reflection</h4>
                        <p id="aiReflection" class="text-gray-700 text-sm leading-relaxed">A motivational reflection
                            based on your entry will appear here after saving.</p>
                    </div>
                </div>

                <!-- Sentiment Bar -->
                <div class="relative w-full bg-gray-200 h-2 mt-6 rounded overflow-hidden">
                    <div id="sentimentBar" class="h-full bg-green-500 transition-all"></div>
                    <div class="absolute left-0 top-4 text-xs text-gray-600">Negative</div>
                    <div class="absolute right-0 top-4 text-xs text-gray-600">Positive</div>
                </div>
            </section>
        </section>

        <!-- Save Confirmation Modal -->
        <div id="saveModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
            <div class="bg-white rounded-xl p-6 w-96 text-center shadow-2xl relative">
                <!-- Animated SVG Checkmark -->
                <svg class="mx-auto mb-4 w-20 h-20 text-green-500" viewBox="0 0 52 52">
                    <circle class="check-circle" cx="26" cy="26" r="25" fill="none" stroke="#34D399" stroke-width="2" />
                    <path class="checkmark" fill="none" stroke="#34D399" stroke-width="4" d="M14 27l7 7 16-16" />
                </svg>

                <h3 class="text-2xl font-semibold mb-4 text-gray-800">✅ Entry Saved!</h3>
                <p class="text-gray-600 mb-6">Your habit progress has been recorded successfully.</p>
                <button id="closeModalBtn"
                    class="bg-green-500 text-white px-6 py-2 rounded-full hover:bg-green-600 transition-all duration-300 transform hover:scale-105">
                    Close
                </button>
            </div>
        </div>

        <style>
            /* Animate check circle */
            .check-circle {
                stroke-dasharray: 157;
                /* circumference of circle ~ 2πr */
                stroke-dashoffset: 157;
                animation: circle-draw 0.6s ease forwards;
            }

            @keyframes circle-draw {
                to {
                    stroke-dashoffset: 0;
                }
            }

            /* Animate checkmark */
            .checkmark {
                stroke-dasharray: 40;
                stroke-dashoffset: 40;
                animation: check-draw 0.4s ease forwards 0.6s;
            }

            @keyframes check-draw {
                to {
                    stroke-dashoffset: 0;
                }
            }

            /* Bounce modal entrance */
            #saveModal>div {
                transform: scale(0.5);
                opacity: 0;
                animation: modal-pop 0.4s forwards 0.5s;
            }

            @keyframes modal-pop {
                to {
                    transform: scale(1);
                    opacity: 1;
                }
            }
        </style>



        <section id="chat" class="content hidden bg-gray-50 dark:bg-gray-900">
            <div class="container mx-auto px-4 py-8 flex-1">
                <!-- Header -->
                <div class="text-center mb-8">
                    <div class="flex flex-col sm:flex-row justify-center items-center gap-4 mb-4">
                        <div class="relative">
                            <div
                                class="absolute inset-0 bg-gradient-to-br from-purple-500 to-blue-500 rounded-full blur-3xl opacity-30">
                            </div>
                            <img src="../assets/images/AI.png" alt="Chat Icon"
                                class="h-16 w-16 rounded-full shadow-xl relative z-10">
                        </div>
                        <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 dark:text-white">NETDEVBOT Conversations
                        </h1>
                    </div>
                    <div id="demoModeIndicator"
                        class="hidden text-sm text-gray-600 dark:text-gray-300 font-medium mt-2 bg-gray-100 dark:bg-gray-800 inline-block px-3 py-1 rounded-full">
                        <i class="fas fa-exclamation-triangle mr-1"></i> Demo Mode: Simulated responses
                    </div>
                </div>

                <!-- Tabs -->
                <div class="flex flex-col sm:flex-row justify-center gap-4 mb-8">
                    <button id="textChatTab"
                        class="px-6 py-3 bg-black text-white font-medium rounded-full shadow-md hover:shadow-lg transition-all duration-300 flex items-center justify-center gap-2">
                        <i class="fas fa-comment"></i> Text Chat
                    </button>
                    <button id="voiceChatTab"
                        class="px-6 py-3 bg-gray-200 text-gray-800 dark:bg-gray-700 dark:text-gray-200 font-medium rounded-full shadow-md hover:shadow-lg transition-all duration-300 flex items-center justify-center gap-2">
                        <i class="fas fa-microphone"></i> Voice Chat
                    </button>
                </div>

                <!-- TEXT CHAT -->
                <div id="textChatSection"
                    class="flex flex-col h-[500px] rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 overflow-hidden shadow-lg">
                    <div id="textChatMessages" class="flex-1 p-4 md:p-6 overflow-y-auto space-y-4"></div>
                    <div
                        class="p-4 border-t border-gray-300 dark:border-gray-700 flex items-center gap-2 bg-gray-50 dark:bg-gray-900">
                        <input type="text" id="textUserInput" placeholder="Type a message..."
                            class="flex-1 p-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all duration-300">
                        <button id="textSendBtn"
                            class="bg-purple-600 hover:bg-purple-700 text-white px-5 py-3 rounded-lg shadow-md flex items-center gap-2 transition-all duration-300">
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </div>
                </div>

                <!-- VOICE CHAT -->
                <div id="voiceChatSection"
                    class="hidden flex flex-col h-[500px] rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 overflow-hidden shadow-lg relative">

                    <!-- Speech Visualization -->
                    <div id="speechVisualization" class="absolute inset-0 pointer-events-none z-0 overflow-hidden">
                    </div>

                    <!-- Voice Messages -->
                    <div id="voiceChatMessages" class="flex-1 p-4 md:p-6 overflow-y-auto space-y-4 z-10"></div>

                    <!-- AI Avatar -->
                    <div class="flex flex-col items-center justify-center p-6 space-y-6 relative z-10">
                        <div class="relative flex items-center justify-center w-36 h-36 md:w-44 md:h-44">
                            <div
                                class="absolute inset-0 bg-gradient-to-tr from-purple-500 to-blue-500 rounded-full blur-2xl opacity-30 animate-pulse-slow">
                            </div>
                            <img id="aiAvatar" src="../assets/images/AI.png" alt="AI Avatar"
                                class="w-36 h-36 md:w-44 md:h-44 rounded-full shadow-2xl object-cover z-10 relative">
                            <!-- Sound Waves -->
                            <div class="absolute inset-0 flex items-center justify-center">
                                <div
                                    class="absolute w-16 h-16 border-2 border-purple-500 rounded-full animate-ping opacity-20">
                                </div>
                                <div class="absolute w-20 h-20 border-2 border-blue-500 rounded-full animate-ping opacity-20"
                                    style="animation-delay: 0.5s"></div>
                                <div class="absolute w-24 h-24 border-2 border-purple-500 rounded-full animate-ping opacity-20"
                                    style="animation-delay: 1s"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Emotion Display -->
                    <div id="emotionDisplay"
                        class="absolute bottom-4 left-4 bg-gray-900 dark:bg-gray-700 text-white px-4 py-2 rounded-lg text-sm flex items-center gap-2 shadow-md z-10">
                        <i class="fas fa-smile text-yellow-400"></i>
                        <span id="emotionText">Detecting emotion...</span>
                    </div>

                    <!-- Controls -->
                    <div
                        class="border-t border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 flex flex-col items-center py-6 gap-4 relative z-10">
                        <div id="voiceStatus" class="text-gray-600 dark:text-gray-300 text-sm text-center px-4">
                            <i class="fas fa-info-circle mr-1"></i> Hold <b>R</b> or press mic to start talking...
                        </div>
                        <button id="voiceBtn"
                            class="bg-purple-600 hover:bg-purple-700 text-white px-6 py-3 md:px-8 md:py-3 rounded-full shadow-md flex items-center gap-2 transition-all duration-300">
                            <i class="fas fa-microphone"></i> <span class="font-medium">Start Talking</span>
                        </button>
                        <div class="flex items-center gap-2">
                            <i class="fas fa-robot text-purple-600"></i>
                            <select id="voiceSelect"
                                class="border border-gray-300 dark:border-gray-600 p-2 rounded text-sm bg-white dark:bg-gray-700 text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-purple-500 transition-all duration-300">
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="mt-6 text-center text-sm text-gray-600 dark:text-gray-400">
                    <p>Powered by <span class="text-purple-600 font-medium">NETDEVBOT AI</span> • Made with <i
                            class="fas fa-heart text-red-500"></i></p>
                </div>
            </div>
        </section>






    </main>


    <!-- Notification Dropdown -->
    <div id="notificationDropdown" class="bg-white p-4 rounded-lg shadow-md absolute right-32 top-24 w-64 hidden z-50">
        <h2 class="text-lg font-semibold mb-2 text-gray-800">Notifications</h2>
        <ul class="space-y-2">
            <li class="text-gray-700">No new notifications.</li>
        </ul>
    </div>

    <!--Dropdown profile-->
    <div class="container mx-auto p-4">
        <div id="profileDropdown" class="bg-white p-6 rounded-lg shadow-md absolute right-10 top-24 w-64 hidden z-50">
            <div class="flex items-center gap-3 mb-4">
                <img src="../assets/images/profile.svg" alt="Profile"
                    class="h-8 w-8 rounded-full border border-gray-300 bg-gray-50">
                <div>
                    <p class="text-md font-semibold text-gray-800">
                        <?php echo $functions->sanitizeInput($user['surname']); ?>
                    </p>
                    <p class="text-[10px] text-gray-500"><?php echo $functions->sanitizeInput($user['email']); ?></p>
                </div>
            </div>
            <div class="border-t pt-4 mt-2 space-y-2">
                <button class="w-full flex items-center gap-2 px-4 py-2 rounded hover:bg-gray-100 text-gray-700">
                    <img src="../assets/images/settings.svg" alt="Settings" class="h-5 w-5">
                    Settings
                </button>
                <button class="w-full flex items-center gap-2 px-4 py-2 rounded hover:bg-gray-100 text-gray-700">
                    <img src="../assets/images/edit.svg" alt="Edit Profile" class="h-5 w-5">
                    Edit Profile
                </button>
                <button class="w-full flex items-center gap-2 px-4 py-2 rounded hover:bg-gray-100 text-gray-700">
                    <img src="../assets/images/reset1.svg" alt="Reset Password" class="h-5 w-5">
                    Reset Password
                </button>
                <a href="../logout.php" id="logout" class="w-full flex items-center gap-2 px-4 py-2 rounded hover:bg-red-100 text-red-600">
                    <img src="../assets/images/log-out.svg" alt="Logout" class="h-5 w-5">
                    Logout
                </a>
            </div>
        </div>
    </div>
  


    <!-- Delete Modal -->
    <div id="deleteModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center">
        <div class="bg-white rounded-lg p-6 w-96">
            <h3 class="text-lg font-semibold mb-4">Confirm Delete</h3>
            <p class="mb-4">Are you sure you want to delete this habit? This action cannot be undone.</p>
            <div class="flex justify-end gap-3">
                <button id="cancelDelete" class="px-4 py-2 rounded bg-gray-300 hover:bg-gray-400">Cancel</button>
                <button id="confirmDelete"
                    class="px-4 py-2 rounded bg-red-600 text-white hover:bg-red-700">Delete</button>
            </div>
        </div>
    </div>
    <!-- habit suc- Modal -->
    <div id="successHabitModal"
        class="fixed inset-0 bg-black bg-opacity-50 rounded-sm flex items-center justify-center hidden">
        <div class="bg-white p-6 rounded-2xl shadow-2xl w-full max-w-md text-center animate-fadeIn">
            <!-- Success Icon -->
            <div class="flex justify-center mb-4">
                <img src="../assets/images/correct-success-tick-svgrepo-com.svg" alt="Success" class="w-16 h-16">
            </div>

            <!-- Success Message -->
            <h2 class="text-xl font-bold text-gray-800 mb-2">Habit Added!</h2>
            <p class="text-gray-600 mb-6">Your habit has been added successfully.</p>

            <!-- Okay Button -->
            <button id="closeModal"
                class="bg-cyan-600 text-white font-semibold px-6 py-2 rounded-lg shadow hover:bg-cyan-700 transition-all">
                Okay
            </button>
        </div>
    </div>


    <!-- Success Modal -->
    <div id="successModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-xl shadow-lg p-6 w-80 text-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto w-12 h-12 text-green-500 mb-3" fill="none"
                viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
            <h2 class="text-lg font-semibold text-gray-800">Habit Updated!</h2>
            <p class="text-gray-500 text-sm mt-1">Your changes were saved successfully.</p>
            <button id="closeModalBtn"
                class="mt-4 bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg w-full">
                OK
            </button>
        </div>
    </div>

    <!-- scripts -->
    <script src="js/dropdowns.js"></script>
    <script src="js/navigation.js"></script>
    <script src="js/habit-functions.js"></script>
    <script src="js/CHAT.js"></script>

    <script>
        // =================== MOBILE MENU FUNCTIONALITY ===================
        const mobileMenuButton = document.getElementById('mobileMenuButton');
        const closeMobileMenu = document.getElementById('closeMobileMenu');
        const mobileMenu = document.getElementById('mobileMenu');
        const mobileOverlay = document.getElementById('mobileOverlay');
        const mobileLogout = document.getElementById('mobileLogout');

        function openMobileMenu() {
            mobileMenu.classList.add('open');
            mobileOverlay.classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function closeMobileMenuFunc() {
            mobileMenu.classList.remove('open');
            mobileOverlay.classList.remove('active');
            document.body.style.overflow = '';
        }

        mobileMenuButton.addEventListener('click', openMobileMenu);
        closeMobileMenu.addEventListener('click', closeMobileMenuFunc);
        mobileOverlay.addEventListener('click', closeMobileMenuFunc);

        // Close mobile menu when clicking on nav links
        document.querySelectorAll('#mobileMenu .nav-link').forEach(link => {
            link.addEventListener('click', closeMobileMenuFunc);
        });

        // Mobile logout functionality
        if (mobileLogout) {
            mobileLogout.addEventListener('click', function () {
                // Add your logout logic here
                window.location.href = '../api/v1/logout.php';
            });
        }

        // =================== FULL JS ===================

        // Variables
        const calendarContainer = document.getElementById("calendarContainer");
        const entryDate = document.getElementById("entryDate");
        const textArea = document.getElementById("entryText");
        const sentimentInput = document.getElementById("sentimentScore");
        const sentimentBar = document.getElementById("sentimentBar");
        const moodSelect = document.getElementById("moodLevel");
        const songMoodSelect = document.getElementById("songMood");
        const saveEntryBtn = document.getElementById("saveEntryBtn");
        const noteFormSection = document.getElementById("noteForm");
        const backToCalendarBtn = document.getElementById("backToCalendarBtn");
        const songSuggestionDiv = document.getElementById("songSuggestion");
        const suggestedSong = document.getElementById("suggestedSong");
        const aiReflection = document.getElementById("aiReflection");
        const suggestedSongTitleInput = document.getElementById("suggestedSongTitle");
        const suggestedSongArtistInput = document.getElementById("suggestedSongArtist");
        const OPENAI_API_KEY =
            "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";
        const userId = document.getElementById("userId").value;

        let diaryEntries = {};
        let typingTimer;
        let currentWeekStart;

        // =================== EVENT LISTENERS ===================
        document.getElementById("monthViewBtn").addEventListener("click", () => renderCalendar("month"));
        document.getElementById("weekViewBtn").addEventListener("click", () => renderCalendar("week"));
        document.getElementById("dayViewBtn").addEventListener("click", () => renderCalendar("day"));
        backToCalendarBtn.addEventListener("click", () => {
            noteFormSection.classList.add("hidden");
            calendarContainer.classList.remove("hidden");
        });
        document.getElementById("closeModalBtn").addEventListener("click", () => {
            document.getElementById("saveModal").classList.add("hidden");
        });

        // =================== FETCH ENTRIES ===================
        async function fetchEntries(month, year) {
            try {
                const res = await fetch(`../api/v1/getDiaryEntry.php?user_id=${userId}&month=${month}&year=${year}`);
                const data = await res.json();
                diaryEntries = data || {};
            } catch (e) { console.error(e); }
        }

        // =================== LOAD ENTRY ===================
        async function loadEntry(dateStr) {
            const [datePart] = dateStr.split(" ");
            entryDate.value = datePart;
            const data = diaryEntries[datePart] || {};
            textArea.value = data.entry_text || "";
            moodSelect.value = data.mood_level || "";
            songMoodSelect.value = data.song_mood || "";
            suggestedSong.textContent = data.suggested_song_title ? `${data.suggested_song_title} by ${data.suggested_song_artist}` : "Your suggested song will appear here.";
            updateSentimentBar(parseFloat(data.sentiment_score) || 0);
            aiReflection.textContent = data.ai_reflection || "A motivational reflection will appear here after saving.";
            calendarContainer.classList.add("hidden");
            noteFormSection.classList.remove("hidden");
        }

        // =================== SENTIMENT & AI ===================
        textArea.addEventListener("input", () => {
            clearTimeout(typingTimer);
            typingTimer = setTimeout(async () => {
                const text = textArea.value;
                const sentiment = await getSentiment(text);
                sentimentInput.value = sentiment;
                updateSentimentBar(sentiment);
                await generateSongSuggestion(sentiment, text);
                await generateReflection(text);
            }, 700);
        });

        async function getSentiment(text) {
            if (!text) return 0;
            try {
                const res = await fetch("https://api.openai.com/v1/chat/completions", {
                    method: "POST",
                    headers: { "Content-Type": "application/json", "Authorization": `Bearer ${OPENAI_API_KEY}` },
                    body: JSON.stringify({
                        model: "gpt-4o-mini",
                        messages: [
                            { role: "system", content: "You are a sentiment analyzer. Return a number -1 to 1 only." },
                            { role: "user", content: text }
                        ]
                    })
                });
                const data = await res.json();
                return parseFloat(data.choices[0].message.content) || 0;
            } catch (e) { console.error(e); return 0; }
        }

        function updateSentimentBar(score) {
            const percent = ((score + 1) / 2) * 100;
            sentimentBar.style.width = percent + "%";
            sentimentBar.className = "h-full transition-all " + (score > 0 ? "bg-green-500" : score < 0 ? "bg-red-500" : "bg-yellow-400");
        }

        async function generateSongSuggestion(sentiment, text) {
            if (!moodSelect.value) return;
            try {
                const res = await fetch("https://api.openai.com/v1/chat/completions", {
                    method: "POST",
                    headers: { "Content-Type": "application/json", "Authorization": `Bearer ${OPENAI_API_KEY}` },
                    body: JSON.stringify({
                        model: "gpt-4o-mini",
                        messages: [
                            { role: "system", content: "You are a song recommender. Suggest JSON with title and artist." },
                            { role: "user", content: `Mood: ${moodSelect.value}, sentiment: ${sentiment}, text: ${text}` }
                        ]
                    })
                });
                const data = await res.json();
                const song = JSON.parse(data.choices[0].message.content);
                suggestedSong.textContent = `${song.title} by ${song.artist}`;
                suggestedSongTitleInput.value = song.title;
                suggestedSongArtistInput.value = song.artist;
                songSuggestionDiv.classList.remove("hidden");
            } catch (e) { console.error(e); }
        }

        async function generateReflection(text) {
            if (!text) return "";
            try {
                const res = await fetch("https://api.openai.com/v1/chat/completions", {
                    method: "POST",
                    headers: { "Content-Type": "application/json", "Authorization": `Bearer ${OPENAI_API_KEY}` },
                    body: JSON.stringify({
                        model: "gpt-4o-mini",
                        messages: [
                            { role: "system", content: "You are a motivational coach. Reflect on user's entry in 2 sentences." },
                            { role: "user", content: text }
                        ]
                    })
                });
                const data = await res.json();
                const reflection = data.choices[0].message.content.trim();
                aiReflection.textContent = reflection;
                return reflection;
            } catch (e) { console.error(e); return ""; }
        }

        // =================== SAVE ENTRY ===================
        saveEntryBtn.addEventListener("click", async (e) => {
            e.preventDefault();
            const reflection = await generateReflection(textArea.value);

            const payload = {
                user_id: userId,
                date: entryDate.value,
                entry_text: textArea.value,
                mood_level: moodSelect.value,
                song_mood: songMoodSelect.value,
                sentiment_score: sentimentInput.value,
                suggested_song_title: suggestedSongTitleInput.value,
                suggested_song_artist: suggestedSongArtistInput.value,
                ai_reflection: reflection
            };

            try {
                const res = await fetch("../api/v1/saveDiaryEntry.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify(payload)
                });
                const data = await res.json();
                if (data.success) {
                    document.getElementById("saveModal").classList.remove("hidden");
                    await renderCalendar("month");
                    noteFormSection.classList.add("hidden");
                    calendarContainer.classList.remove("hidden");
                }
            } catch (e) { console.error(e); }
        });

        // =================== CALENDAR ===================
        async function renderCalendar(view = "month") {
            calendarContainer.innerHTML = "";
            const today = new Date();
            await fetchEntries(today.getMonth() + 1, today.getFullYear());
            if (view === "month") renderMonthView(today);
            else if (view === "week") renderWeekView(today);
            else if (view === "day") renderDayView(today);
        }

        // --- Month View ---
        function renderMonthView(today) {
            calendarContainer.innerHTML = "";
            const month = today.getMonth() + 1;
            const year = today.getFullYear();
            const firstDay = new Date(year, month - 1, 1).getDay();
            const daysInMonth = new Date(year, month, 0).getDate();

            const weekdays = ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"];
            const headerRow = document.createElement("div");
            headerRow.className = "grid grid-cols-7 gap-1 text-center font-semibold mb-1";
            weekdays.forEach(day => {
                const div = document.createElement("div");
                div.textContent = day;
                div.className = "py-1 border-b border-gray-300";
                headerRow.appendChild(div);
            });
            calendarContainer.appendChild(headerRow);

            const grid = document.createElement("div");
            grid.className = "grid grid-cols-7 gap-1";

            for (let i = 0; i < firstDay; i++) {
                const emptyCell = document.createElement("div"); emptyCell.className = "py-6"; grid.appendChild(emptyCell);
            }

            for (let d = 1; d <= daysInMonth; d++) {
                const dateStr = `${year}-${String(month).padStart(2, '0')}-${String(d).padStart(2, '0')}`;
                const dayCell = document.createElement("button");
                dayCell.textContent = d;
                dayCell.className = "py-6 flex flex-col justify-center items-center border rounded-sm hover:bg-gray-100 transition";
                const sentiment = diaryEntries[dateStr]?.sentiment_score || 0;
                if (sentiment > 0.3) dayCell.style.backgroundColor = "#22c55e";
                else if (sentiment < -0.3) dayCell.style.backgroundColor = "#ef4444";
                else if (sentiment !== 0) dayCell.style.backgroundColor = "#facc15";
                dayCell.onclick = () => loadEntry(dateStr);
                grid.appendChild(dayCell);
            }
            calendarContainer.appendChild(grid);
        }

        // --- Week View ---
        function renderWeekView(today) {
            calendarContainer.innerHTML = "";
            currentWeekStart = new Date(today); currentWeekStart.setDate(today.getDate() - today.getDay());

            const header = document.createElement("div"); header.className = "flex justify-between items-center mb-2";
            const prevBtn = document.createElement("button"); prevBtn.textContent = "<"; prevBtn.className = "px-3 py-1 bg-gray-900 text-white rounded hover:bg-gray-800 transition";
            const nextBtn = document.createElement("button"); nextBtn.textContent = ">"; nextBtn.className = "px-3 py-1 bg-gray-900 text-white rounded hover:bg-gray-800 transition";
            const title = document.createElement("h2"); title.className = "font-semibold"; header.appendChild(prevBtn); header.appendChild(title); header.appendChild(nextBtn);
            calendarContainer.appendChild(header);

            function updateWeekGrid() {
                calendarContainer.querySelectorAll(".week-grid").forEach(e => e.remove());
                const grid = document.createElement("div"); grid.className = "week-grid grid grid-cols-7 gap-1 border border-gray-300 p-2";
                const weekdays = ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"];

                for (let i = 0; i < 7; i++) {
                    const dayColumn = document.createElement("div"); dayColumn.className = "flex flex-col border border-gray-200 rounded overflow-hidden";
                    const d = new Date(currentWeekStart); d.setDate(currentWeekStart.getDate() + i);
                    const dateStr = `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`;

                    const dayHeader = document.createElement("div"); dayHeader.className = "bg-gray-100 text-center py-1 font-semibold border-b border-gray-300";
                    dayHeader.textContent = `${weekdays[d.getDay()]} ${d.getDate()}`; dayColumn.appendChild(dayHeader);

                    for (let hour = 0; hour < 24; hour++) {
                        const slot = document.createElement("button");
                        const hourLabel = (hour % 12 || 12) + (hour < 12 ? " AM" : " PM");
                        slot.textContent = hourLabel;
                        slot.className = "text-left px-2 py-1 border-b border-gray-200 hover:bg-gray-100 transition text-sm";
                        const ds = `${dateStr} ${hour}:00`;
                        slot.onclick = () => loadEntry(ds);
                        dayColumn.appendChild(slot);
                    }
                    grid.appendChild(dayColumn);
                }
                title.textContent = `Week of ${currentWeekStart.toLocaleDateString()}`;
                calendarContainer.appendChild(grid);
            }

            prevBtn.onclick = () => { currentWeekStart.setDate(currentWeekStart.getDate() - 7); updateWeekGrid(); };
            nextBtn.onclick = () => { currentWeekStart.setDate(currentWeekStart.getDate() + 7); updateWeekGrid(); };
            updateWeekGrid();
        }

        // --- Day View ---
        function renderDayView(today) {
            calendarContainer.innerHTML = "";
            const ds = `${today.getFullYear()}-${String(today.getMonth() + 1).padStart(2, '0')}-${String(today.getDate()).padStart(2, '0')}`;

            const grid = document.createElement("div"); grid.className = "grid gap-1 border border-gray-300 p-2";
            const dayHeader = document.createElement("div"); dayHeader.className = "bg-gray-100 text-center py-1 font-semibold border-b border-gray-300 mb-1";
            dayHeader.textContent = today.toDateString(); grid.appendChild(dayHeader);

            for (let hour = 0; hour < 24; hour++) {
                const slot = document.createElement("button");
                const hourLabel = (hour % 12 || 12) + (hour < 12 ? " AM" : " PM");
                slot.textContent = hourLabel;
                slot.className = "text-left px-2 py-1 border-b border-gray-200 hover:bg-gray-100 transition text-sm";
                slot.onclick = () => loadEntry(`${ds} ${hour}:00`);
                grid.appendChild(slot);
            }
            calendarContainer.appendChild(grid);
        }

        // --- INITIAL ---
        renderCalendar("month");
    </script>

</body>

</html>