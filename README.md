# 🧥 Smart Wardrobe Manager

> Intelligent wardrobe tracking, outfit rotation, and hybrid AI-assisted
> styling.

Smart Wardrobe Manager is a full-stack wardrobe intelligence system
built with Laravel and Livewire. It tracks your clothing lifecycle,
prevents outfit repetition, detects colors automatically from photos,
and suggests optimized combinations using a weighted scoring engine ---
optionally enhanced by AI.

------------------------------------------------------------------------

## ✨ Features

### 📦 Wardrobe Management

-   Add clothing with image upload
-   Automatic dominant color detection from photo
-   Structured color family classification
-   Formality & season tagging
-   Clean / worn / dry-clean lifecycle tracking
-   Wear count tracking
-   Image previews

### 🧠 Intelligent Outfit Suggestion Engine

Deterministic multi-factor scoring system that considers: - Event type
(office, wedding, casual, etc.) - Season match - Recency penalty - Wear
frequency penalty - Color harmony scoring - Rotation window (anti-repeat
logic) - Shalwar Kameez prioritization logic

Optional Hybrid AI layer: - Rule engine generates top 3 combinations -
AI selects best option and explains reasoning - Fully optional (fallback
works without API key)

### 🎨 Automatic Color Detection

When uploading an image: 1. Dominant RGB is calculated 2. Converted to
HSL 3. Mapped to structured color family 4. User can override manually

Stored as: - `color_family` (used for scoring) - `color_hex` (used for
UI display)

No external AI required.

### 📅 Calendar View

-   Monthly calendar layout
-   View what you wore each day
-   Click date → view outfit details
-   Encourages rotation awareness

### 🔁 Rotation & Anti-Repeat

-   Configurable rotation window
-   Prevents repeating same combination too frequently
-   Promotes balanced wardrobe usage

### ⏰ Smart Reminders

Scheduled daily checks for: - Overdue dry clean items - Unused clothing
(configurable threshold)

------------------------------------------------------------------------

## 🏗 Tech Stack

### Backend

-   Laravel 12
-   Livewire
-   Laravel Sanctum
-   Pest (Testing)
-   Intervention Image

### Frontend

-   Blade
-   Tailwind CSS
-   Flux UI

### Optional AI

-   OpenAI (gpt-4o-mini)
-   Hybrid rule + AI architecture

------------------------------------------------------------------------

## 🧮 Suggestion Engine Overview

### Filtering

-   Status = clean
-   Formality matches event
-   Season matches current season

### Individual Scoring

+5 formality match\
+3 season match\
+2 season = all\
-5 worn in last 3 days\
-3 worn in last 7 days\
-0.5 × wearCount

### Combination Scoring

(combo_score) = (shirt_score + pant_score) / 2\
+ color_compatibility_score

Color Harmony Rules: - Both neutral → +2 - One neutral → +1 - Same
bright color → -2 - Different colors → +0.5

### Rotation Check

Combinations worn within rotation window are excluded.

------------------------------------------------------------------------

## 🚀 Installation

Clone repository:

git clone https://github.com/yourusername/smart-wardrobe-manager.git cd
smart-wardrobe-manager

Install dependencies:

composer install npm install

Setup environment:

cp .env.example .env php artisan key:generate

Run migrations:

php artisan migrate

Link storage:

php artisan storage:link

Run app:

php artisan serve

Visit: http://127.0.0.1:8000

------------------------------------------------------------------------

## 🧪 Running Tests

Ensure GD extension is enabled.

php artisan test

------------------------------------------------------------------------

## 🤖 Enabling AI (Optional)

Add to .env:

OPENAI_API_KEY=your_key_here\
WARDROBE_ENABLE_AI=true

Without this, the system runs fully deterministic rule-based engine.

------------------------------------------------------------------------

## 📌 Status

✔ Feature-complete web MVP\
✔ API-ready\
✔ Mobile-ready backend\
✔ Deterministic intelligence engine\
✔ Hybrid AI enhancement\
✔ Tested color detection

------------------------------------------------------------------------

## 📄 License

MIT
