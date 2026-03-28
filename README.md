# Task Management Application

A full-stack Task Management web application built with **Laravel 12**, **Vue 3**, and **Inertia.js**. The project implements a complete task lifecycle with strict business rules, a good UI, and a RESTful API layer.

---

## Tech Stack

| Layer | Technology |
|---|---|
| Backend | PHP 8.2, Laravel 12 |
| Frontend | Vue 3, Inertia.js |
| Styling | Tailwind CSS v4 |
| Database | MySQL / MariaDB |
| Build Tool | Vite |
| Testing | PHPUnit (Laravel Feature Tests) |

---

## Features

- Create tasks with a **title**, **due date**, and **priority** (Low / Medium / High)
- Tasks follow a strict **status progression**: `Pending → In Progress → Done`
- Duplicate task titles on the same due date are **rejected**
- Past due dates are **rejected** on creation
- Completed (`Done`) tasks **cannot be deleted**
- Filter tasks by status on the dashboard
- **Daily performance report** with completion rate and status breakdown
- Fully **mobile-responsive** UI with light and dark mode support
- OOP architecture using **Service/Action classes** and **API Resources**

---

## Project Structure

```
app/
├── Actions/
│   ├── CreateTaskAction.php         # Handles task creation logic
│   └── UpdateTaskStatusAction.php   # Enforces status transition rules
├── Enums/
│   ├── PriorityEnum.php             # low | medium | high
│   └── StatusEnum.php               # pending | in_progress | done
├── Http/
│   ├── Controllers/TaskController.php
│   ├── Requests/StoreTaskRequest.php
│   └── Resources/
│       ├── TaskResource.php
│       └── TaskReportResource.php
└── Models/Task.php

resources/js/Pages/Tasks/
├── Index.vue    # Main dashboard
└── Report.vue   # Daily report page
```

---

## Business Rules

| Rule | Details |
|---|---|
| Unique constraint | A task's `title` must be unique per `due_date` |
| Due date | Must be today or a future date |
| Priority | Must be one of: `low`, `medium`, `high` |
| Status | Can only move forward: `pending → in_progress → done` |
| Deletion | Tasks with status `done` cannot be deleted |

---

## Routes

| Method | URL | Description |
|---|---|---|
| `GET` | `/` | Task dashboard |
| `POST` | `/tasks` | Create a new task |
| `PATCH` | `/tasks/{id}/status` | Advance task status |
| `DELETE` | `/tasks/{id}` | Delete a task (not if done) |
| `GET` | `/tasks/report` | Daily performance report |

---

## Database

- **Database:** MySQL / MariaDB
- **Dump file:** `task_manager_dump.sql` (included in the project root)

### Schema

```sql
tasks
  id            BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
  title         VARCHAR(255) NOT NULL
  due_date      DATE NOT NULL
  priority      ENUM('low', 'medium', 'high') NOT NULL
  status        ENUM('pending', 'in_progress', 'done') DEFAULT 'pending'
  created_at    TIMESTAMP
  updated_at    TIMESTAMP
```

---

## Local Setup

### Requirements
- PHP 8.2+
- MySQL / MariaDB
- Composer
- Node.js & npm

### Steps

**1. Clone the repository**
```bash
git clone <repository-url>
cd Cytonn
```

**2. Install dependencies**
```bash
composer install
npm install
```

**3. Configure environment**
```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env` and set your database credentials:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=task_manager
DB_USERNAME=root
DB_PASSWORD=
```

**4. Import the database**
```bash
mysql -u root -e "CREATE DATABASE task_manager;"
mysql -u root task_manager < task_manager_dump.sql
```

Or run migrations fresh:
```bash
php artisan migrate
```

**5. Start the application**
```bash
# Terminal 1 — PHP server
php artisan serve

# Terminal 2 — Vite assets
npm run dev
```

Visit `http://127.0.0.1:8000`

---

## Running Tests

```bash
php artisan test
```

The test suite covers:
- Required field validation
- Unique title + due date constraint
- Past due date rejection
- Status progression enforcement
- Deletion restriction on completed tasks
- Daily report endpoint

---

## Example API Requests

### 1. Create a Task
```bash
curl -X POST http://127.0.0.1:8000/api/tasks \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "title": "New important task",
    "due_date": "2026-04-10",
    "priority": "high"
  }'
```

### 2. Update Task Status
```bash
curl -X PATCH http://127.0.0.1:8000/api/tasks/1/status \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"status": "in_progress"}'
```

### 3. Delete a Completed Task
```bash
curl -X DELETE http://127.0.0.1:8000/api/tasks/1 \
  -H "Accept: application/json"
```

### 4. Get Daily Report
```bash
curl -X GET "http://127.0.0.1:8000/api/tasks/report?date=2026-03-28" \
  -H "Accept: application/json"
```

---

## Deployment (Railway)

This application is fully configured for seamless containerized deployment on Railway.

**Deploying on Railway:**
1. Create a new Railway project and provision a **MySQL Database**.
2. Connect your GitHub repository to an Empty Service.
3. Add the following Environment Variables under the Variables tab:
   - `DB_CONNECTION=mysql`
   - `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` (Copy these from your provisioned MySQL service)
   - `APP_KEY` (Generate offline using `php artisan key:generate`)
   - `APP_ENV=production`
   - `APP_URL=https://your-production-url.app`
4. Deploy! The project includes a `nixpacks.toml` and `Procfile` which Railway will automatically detect and use to:
   - Configure the PHP/Node.js environment
   - Run `npm run build` for Vite assets
   - Execute database migrations (`php artisan migrate --force`) automatically upon deployment.

---

## Live Demo

[https://daniel-maina-task-manager-challenge.up.railway.app](https://daniel-maina-task-manager-challenge.up.railway.app)
