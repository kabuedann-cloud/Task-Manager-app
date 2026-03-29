# Task Management API

Laravel 12 task management application with a Vue 3 + Inertia web UI and a JSON API that satisfies the take-home assignment requirements.

## Stack

- PHP 8.2
- Laravel 12
- MySQL / MariaDB
- Vue 3
- Inertia.js
- Vite
- PHPUnit

## Assignment Coverage

- Create tasks
- List tasks with optional `status` filter
- Update task status with forward-only transitions
- Delete tasks only when `status = done`
- Daily task report by `due_date`
- MySQL migration files
- SQL dump files: `task_manager_dump.sql` and `task_manager.sql`
- Seeder with sample task data

## Database Schema

The `tasks` table contains:

- `id`
- `title`
- `due_date`
- `priority` enum: `low`, `medium`, `high`
- `status` enum: `pending`, `in_progress`, `done`
- `created_at`
- `updated_at`

There is a unique constraint on `title + due_date`.

## API Endpoints

### Create Task

- `POST /api/tasks`

Rules:

- `title` cannot duplicate another task with the same `due_date`
- `priority` must be `low`, `medium`, or `high`
- `due_date` must be today or later

### List Tasks

- `GET /api/tasks`

Rules:

- Sorted by priority `high -> medium -> low`
- Within the same priority, sorted by `due_date` ascending
- Optional query param: `status`
- Returns meaningful JSON when no tasks exist

Example:

```json
{
  "message": "No tasks found.",
  "data": []
}
```

### Update Task Status

- `PATCH /api/tasks/{id}/status`

Rules:

- Allowed transitions:
  `pending -> in_progress -> done`
- Skipping and reverting are rejected

### Delete Task

- `DELETE /api/tasks/{id}`

Rules:

- Only tasks with `status = done` can be deleted
- Otherwise returns `403 Forbidden`

### Daily Report

- `GET /api/tasks/report?date=YYYY-MM-DD`

Returns counts grouped by priority and status for tasks due on the requested date.

Example:

```json
{
  "date": "2026-03-28",
  "summary": {
    "high": { "pending": 2, "in_progress": 1, "done": 0 },
    "medium": { "pending": 1, "in_progress": 0, "done": 3 },
    "low": { "pending": 0, "in_progress": 0, "done": 1 }
  }
}
```

## Local Setup

### Requirements

- PHP 8.2+
- Composer
- Node.js and npm
- MySQL or MariaDB

### 1. Install dependencies

```bash
composer install
npm install
```

### 2. Configure environment

```bash
cp .env.example .env
php artisan key:generate
```

Update `.env` for MySQL:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=task_manager
DB_USERNAME=root
DB_PASSWORD=
```

### 3. Create the database

```sql
CREATE DATABASE task_manager;
```

### 4. Run migrations and seeders

```bash
php artisan migrate --seed
```

If you prefer importing a dump instead:

```bash
mysql -u root -p task_manager < task_manager_dump.sql
```

### 5. Start the application

```bash
php artisan serve
npm run dev
```

Web UI:

- `http://127.0.0.1:8000`

## Example API Requests

### Create

```bash
curl -X POST http://127.0.0.1:8000/api/tasks \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{
    "title": "Finish take-home assignment",
    "due_date": "2026-04-10",
    "priority": "high"
  }'
```

### List

```bash
curl -X GET "http://127.0.0.1:8000/api/tasks?status=pending" \
  -H "Accept: application/json"
```

### Update Status

```bash
curl -X PATCH http://127.0.0.1:8000/api/tasks/1/status \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{
    "status": "in_progress"
  }'
```

### Delete

```bash
curl -X DELETE http://127.0.0.1:8000/api/tasks/1 \
  -H "Accept: application/json"
```

### Daily Report

```bash
curl -X GET "http://127.0.0.1:8000/api/tasks/report?date=2026-03-28" \
  -H "Accept: application/json"
```

## Tests

Run:

```bash
php artisan test
```

Coverage includes:

- task creation validation
- duplicate title plus due date rejection
- list sorting and filtering
- status transition enforcement
- delete restriction
- daily report summary

## Deployment

This repo includes `nixpacks.toml` and `Procfile` for Railway-style deployment with MySQL.

Typical deployment steps:

1. Provision a MySQL database.
2. Set the production `.env` values.
3. Run `php artisan migrate --force`.
4. Build frontend assets with `npm run build`.
5. Start the app with the provided process configuration.

## Hosted URL

- `https://daniel-maina-task-manager-challenge.up.railway.app`
