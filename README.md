# HelpRelief

**HelpRelief** is a centralized disaster relief coordination platform built to streamline communication, resource allocation, and volunteer efforts among NGOs, volunteers, and service takers. Designed with a focus on real-time support and transparency, HelpRelief enables efficient response to natural or human-made disasters across Bangladesh.


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

## ⚙️ Tech Stack & Dependencies

### Requirements
* PHP

  for windows
    ```powershell
    # Run as administrator...
    Set-ExecutionPolicy Bypass -Scope Process -Force; [System.Net.ServicePointManager]::SecurityProtocol = [System.Net.ServicePointManager]::SecurityProtocol -bor 3072; iex ((New-Object System.Net.WebClient).DownloadString('https://php.new/install/windows/8.4'))
    ```

  for linux `/bin/bash -c "$(curl -fsSL https://php.new/install/linux/8.4)"`

  for macOS `/bin/bash -c "$(curl -fsSL https://php.new/install/mac/8.4)"`

* Node.js ≥ 18, npm ((NodeSource)[https://github.com/nodesource/distributions])
* Composer `sudo apt install composer`
* Laravel Installer `composer global require laravel/installer`
* MySQL or SQLite (for local DB, Optional. Use SQLite if MySQL not configured)

---

## 🚀 Getting Started
### Clone the Repo

```bash
git clone git@github.com:tanximsiam/HelpRelief.git
cd HelpRelief
```

### Setups

Backend:
```bash
cd backend

# Install PHP dependencies
composer install

# Create environment file
cp .env.example .env

# SQLite (simplest for local dev):
touch database/database.sqlite
# Then update .env:
# DB_CONNECTION=sqlite
# DB_DATABASE=/absolute/path/to/database/database.sqlite

# Or use MySQL by configuring .env accordingly

# Generate Laravel app key
php artisan key:generate

# Run DB migrations
php artisan migrate

# Start local server
php artisan serve
```

Frontend:
```bash
cd frontend

# Install Node dependencies
npm install

# Set Backend API
cp .env.example .env
# Set VITE_BACKEND_URI=http://127.0.0.1:8000/api or wherever your local backend is hosted

# Start Vue dev server
npm run dev
```


