🧥 Smart Wardrobe Manager

Intelligent wardrobe tracking, outfit rotation, and hybrid AI-assisted styling.

Smart Wardrobe Manager is a full-stack wardrobe intelligence system built with Laravel and Livewire.
It tracks your clothing lifecycle, prevents outfit repetition, detects colors automatically from photos, and suggests optimized combinations using a weighted scoring engine — optionally enhanced by AI.

✨ Core Features
📦 Wardrobe Management

Add clothing with image upload

Automatic dominant color detection from photo

Structured color family classification

Formality & season tagging

Clean / worn / dry-clean lifecycle tracking

Wear count tracking

Image previews

🧠 Intelligent Outfit Suggestion Engine

Deterministic, multi-factor scoring engine that considers:

Event type (office, wedding, casual, etc.)

Season match

Recency penalty

Wear frequency penalty

Color harmony scoring

Rotation window (anti-repeat logic)

Shalwar Kameez prioritization (culturally aware logic)

Supports hybrid AI enhancement:

Rule engine generates top 3 combinations

AI selects best option and explains reasoning

Fully optional (fallback works without API key)

🎨 Automatic Color Detection

When uploading an image:

Dominant RGB is calculated

Converted to HSL

Mapped to structured color family

User can override manually

Stored as:

color_family (used for scoring logic)

color_hex (used for UI display)

No external AI required.

📅 Calendar View

Monthly calendar layout

See what you wore each day

Click date → view outfit details

Encourages rotation awareness

🔁 Rotation & Anti-Repeat Logic

Configurable rotation window

Prevents wearing same combination too frequently

Promotes balanced wardrobe usage

⏰ Smart Reminders

Scheduled system that checks daily:

Overdue dry clean items

Unused clothing (configurable threshold)

Database notifications with throttling.

⚡ Performance Optimizations

Livewire lazy loading

Placeholder skeleton UIs

Dashboard caching

Wear count preloading (no N+1 queries)

Simple pagination

Optimized combination scoring

🔐 API Layer (Mobile-Ready)

RESTful API under /api/v1

Sanctum token authentication

Versioned routes

Structured API resources

JSON responses

Mobile client ready

🏗 Tech Stack

Backend

Laravel 12

Livewire

Laravel Sanctum

Pest (testing)

Intervention Image (color detection)

Frontend

Blade

Tailwind CSS

Flux UI components

Optional AI

OpenAI (gpt-4o-mini)

Hybrid rule + AI architecture

Database

MySQL / SQLite compatible

🧮 Suggestion Engine Overview

The suggestion engine works in deterministic stages:

1️⃣ Filtering

Status = clean

Formality matches event

Season matches current season

2️⃣ Individual Scoring
+5  formality match
+3  season match
+2  season = all
-5  worn in last 3 days
-3  worn in last 7 days
-0.5 × wearCount

3️⃣ Combination Scoring

For shirt + pant:

combo_score =
  (shirt_score + pant_score) / 2
  + color_compatibility_score


Color Harmony Rules:

Both neutral → +2

One neutral → +1

Same bright color → -2

Different colors → +0.5

4️⃣ Rotation Check

Combinations worn within rotation window are excluded.

5️⃣ AI (Optional)

Top 3 combinations sent to AI for final selection and explanation.

Fallback: Highest scoring combination is returned.

📱 Use Cases

Daily office outfit planning

Event-specific styling

Cultural attire prioritization (Jummah, Eid)

Avoid outfit repetition

Manage dry cleaning lifecycle

Track wardrobe usage efficiency

🚀 Installation
1️⃣ Clone Repository
git clone https://github.com/yourusername/smart-wardrobe-manager.git
cd smart-wardrobe-manager

2️⃣ Install Dependencies
composer install
npm install

3️⃣ Environment Setup

Copy .env.example:

cp .env.example .env
php artisan key:generate


Set database credentials.

4️⃣ Run Migrations
php artisan migrate

5️⃣ Storage Link
php artisan storage:link

6️⃣ Run Application
php artisan serve


Visit:

http://127.0.0.1:8000

🧪 Running Tests

Ensure GD extension is enabled.

Then run:

php artisan test


Includes:

Color detection tests

Deterministic family classification validation

🤖 Enabling AI (Optional)

Add to .env:

OPENAI_API_KEY=your_key_here
WARDROBE_ENABLE_AI=true


Without this:
System runs fully deterministic rule-based engine.

⚙ Configuration

config/wardrobe.php

Adjust:

rotation_days

recent_days_penalty

unused_days_threshold

color_families

neutral_colors

📌 Project Status

✔ Feature-complete web MVP
✔ API-ready
✔ Mobile-ready backend
✔ Deterministic intelligence engine
✔ Hybrid AI enhancement
✔ Tested color detection

🧠 Philosophy

This system is built around:

Deterministic logic first

AI as enhancement, not dependency

Rotation awareness

Cultural adaptability

Performance-conscious architecture

Structured extensibility

📄 License

MIT