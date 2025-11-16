# AI-Habit Tracker


**AI-Habit Tracker** is a modern habit tracking system powered by **AI**, designed to help users track their habits, receive personalized suggestions, and gain insights into their mood and productivity.  

Users can enter their habits, interact with an AI-powered diary, and chat with an AI voice assistant with different accents. The system is built with **PHP, HTML, CSS, Tailwind CSS (CDN), JavaScript, OpenAI, and MySQL**.

---

## Badges

![PHP](https://img.shields.io/badge/PHP-8.2-777BB4?style=for-the-badge&logo=php)
![JavaScript](https://img.shields.io/badge/JavaScript-ES6-F7DF1E?style=for-the-badge&logo=javascript)
![TailwindCSS](https://img.shields.io/badge/TailwindCSS-CDN-38B2AC?style=for-the-badge&logo=tailwind-css)
![MySQL](https://img.shields.io/badge/MySQL-Database-4479A1?style=for-the-badge&logo=mysql)
![OpenAI](https://img.shields.io/badge/OpenAI-AI-412991?style=for-the-badge&logo=openai)
![License](https://img.shields.io/badge/License-MIT-green?style=for-the-badge)

---

## Table of Contents

- [About the Project](#about-the-project)
- [Key Features](#key-features)
- [Technology Stack](#technology-stack)
- [Screenshots](#screenshots)
- [Live Demo](#live-demo)
- [Installation Guide](#installation-guide)
- [AI Components](#ai-components)
- [Roadmap](#roadmap)
- [Contributing](#contributing)
- [License](#license)
- [Author](#author)

---

## About the Project

**AI-Habit Tracker** allows users to:

- Track personal habits and daily routines
- Receive AI suggestions on how to improve habits
- Maintain an AI-powered diary with mood/sentiment analysis
- Visualize diary entries using colored calendar days
- Interact with an AI voice chatbot with multiple accents

This project is designed for individuals who want a smart, interactive way to improve productivity, self-awareness, and personal growth.

---

## Key Features

### Habit Tracking
- Add, edit, and view habits
- Mark habits as complete or pending
- AI suggestions for habit improvement

### AI Diary
- Write daily diary entries
- AI analyzes mood (Happy / Mid / Sad)
- Assigns color-coded days in the calendar based on sentiment
- Provides advice and reflections on diary entries

### AI Chatbot
- Voice-to-voice conversation with AI
- Multiple accent options
- Context-aware responses

### Dashboard
- Visual overview of habits and diary insights
- Color-coded calendar for mood tracking
- Easy navigation between habits, diary, and AI chat

---

## Technology Stack

| Layer        | Technology |
|-------------|------------|
| Frontend    | HTML, CSS, Tailwind CSS (CDN), JavaScript |
| Backend     | PHP 8+ |
| Database    | MySQL |
| AI          | OpenAI API (ChatGPT + Sentiment Analysis + Voice) |
| Deployment  | XAMPP / Localhost or Live Server |

---

## Screenshots



| Login | Dashboard |
|-------|-----------|
| ![login](./assets/screenshots/landing.png) | ![dashboard](./assets//screenshots/dashboard.png) |

| Habits | Edit Habit | View Habit |
|--------|------------|------------|
| ![habits](./assets/screenshots/habits.png) | ![edit-habit](./assets/screenshots/edit-habit.png) | ![view-habit](./assets/screenshots/view-habit.png) |

| AI Chat | Diary Entry | Diary Calendar |
|---------|------------|----------------|
| ![ai-chat](./assets/screenshots/AI.png) | ![diary-entry](./assets/screenshots/diary-entry.png) | ![diary-calendar](./assets/screenshots/calendar.png) |

---

## Live Demo

Check out the live demo here:

[🔗 Live Demo](https://habit-trackers.infinityfreeapp.com/)

---

## Installation Guide

Follow these steps to install and run the project locally:

### 1. Clone the repository
```bash
git clone https://github.com/yourusername/AI-Habit-Tracker.git
cd AI-Habit-Tracker

### 2. Move the project to your server

-Place the project folder in your local server directory:

-xampp/htdocs/


-or your live server folder.

### 3. Import the database

-Open phpMyAdmin

-Create a new database, e.g., ai_habit_tracker

-Import the SQL file: schema.sql

### 4. Configure environment variables

##Create a .env file in the project root:

DB_HOST=localhost
DB_USER=root
DB_PASS=
DB_NAME=ai_habit_tracker
OPENAI_API_KEY=your_openai_api_key

### 5. Start the server

-Start Apache and MySQL in XAMPP (or your server)

-Open your browser and visit:

-http://localhost/AI-Habit-Tracker/

###6. Optional steps

-Ensure .env is included in .gitignore to prevent pushing secrets

-Replace your_openai_api_key with your actual OpenAI API key

## Author

**Nethononda Nyandano**  
Full-Stack Developer  

- **GitHub:** [https://github.com/Nethononda-Nyandano](https://github.com/Nethononda-Nyandano)  
- **Email:** nyandanonethononda8@gmail.com 
- **Location:** South Africa
- **Portfolio:**  

### About Me
I am a passionate developer focused on building practical solutions for real-world problems.  
This project, **AI-Habit Tracker**, was created to help users improve habits, track moods, and interact with AI for self-growth.  

###I enjoy:
- Building full-stack applications with PHP, JS, and Tailwind
- Integrating AI services like OpenAI
- Creating user-friendly dashboards and analytics
- Helping small businesses and individuals grow with technology

