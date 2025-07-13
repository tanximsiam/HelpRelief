# HelpRelief

**HelpRelief** is a centralized disaster relief coordination platform built to streamline communication, resource allocation, and volunteer efforts among NGOs, volunteers, and service takers. Designed with a focus on real-time support and transparency, HelpRelief enables efficient response to natural or human-made disasters across Bangladesh.

---

## 🌟 Features

- User Roles: NGO, Volunteer, Service Taker, and Admin
- Task Assignment & Project Tracking
- Budget and Resource Management (NGOs)
- SOS Requests & Emergency Help Posts
- Volunteer Coordination (Physical/Financial)
- Alert System for Ongoing Disasters

---

## 🧭 Project Structure

```bash
helprelief/
├── backend/           # Laravel (API & DB)
├── frontend/          # Vue 3 (UI)
├── .env.example       # Sample env config
└── README.md          # Project Overview
```

##🌱 Branching Strategy

`main` – base skeleton

`prod` – latest stable deployment

`dev` – latest stable development

`feature/<feature-name>` – new features

`bugfix/<bug-name>` – patches and fixes

All changes should be merged into `dev` first, then into `prod` after testing.

---

##⚙️ Tech Stack & Dependencies

Backend:

    PHP 8.x, Laravel 10

    PostgreSQL / SQLite (dev)

    Composer

Frontend:

    Vue 3 + Vite

    TailwindCSS

    Axios

    Vue Router & Pinia

Dev Tools:

    Git

    Node.js & npm

    Laravel Artisan

    dotenv

---

##🚀 Getting Started
###1. Clone the Repo

```bash
git clone git@github.com:tanximsiam/HelpRelief.git
cd HelpRelief
```

###2. Backend Setup

```bash
cd backend
cp .env.example .env
composer install
php artisan key:generate
php artisan migrate
```

> _💡 Use SQLite for quick dev: set DB_CONNECTION=sqlite and define path in .env.

###3. Frontend Setup

```bash
cd ../frontend
npm install
npm run dev.
```

