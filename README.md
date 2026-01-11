# Secure Drop API

A RESTful API for creating and retrieving "self-destructing" secure notes.

## Overview

This project implements a "Secure Drop" service where users can store sensitive information (secrets) and share them via a unique link. The secret is permanently deleted ("burned") once it is retrieved.

## Features

*   **Create Secret**: Store a text string with an optional expiration time (TTL).
*   **Retrieve Secret**: Get the decrypted text. The record is deleted immediately after reading.
*   **Burn on Read**: Secrets are one-time use only.
*   **Encryption**: All secrets are encrypted at rest using Laravel's encryption facilities.
*   **UUIDs**: Unique IDs for secrets to prevent enumeration.
*   **API Documentation**: Auto-generated documentation using Scribe.
*   **Dockerized**: Easy setup with Docker and Docker Compose.
*   **Architecture**: Follows strict Service-Repository pattern.

## Requirements

*   Docker & Docker Compose

## Installation & Running

1.  **Clone the repository** (if not already done).

2.  **Run with Docker Compose**:

    ```bash
    docker-compose up -d --build
    ```

    This command will:
    *   Build the application image.
    *   Start the Nginx web server, PHP-FPM app container, and MySQL database.
    *   Install PHP dependencies via Composer (if not present).
    *   Run database migrations.
    *   Expose the API at `http://localhost:8000`.

3.  **Access the Application**:

    *   **API Base URL**: `http://localhost:8000/api/v1`
    *   **Documentation**: `http://localhost:8000/docs`

## Deployment

The application is "Deployment Ready" for various platforms.

### Option 1: Heroku (Recommended for quick demo)

1.  **Install Heroku CLI** and login (`heroku login`).
2.  **Create an app**: `heroku create secure-drop-api`
3.  **Add Database**: `heroku addons:create heroku-postgresql:mini`
4.  **Set Environment Variables**:
    ```bash
    heroku config:set APP_KEY=$(php artisan key:generate --show)
    heroku config:set APP_DEBUG=false
    heroku config:set APP_URL=https://your-app-name.herokuapp.com
    ```
5.  **Deploy**:
    ```bash
    git push heroku main
    ```
6.  **Visit**: `https://your-app-name.herokuapp.com/docs`

### Option 2: DigitalOcean / VPS (Docker)

1.  **Provision a Droplet** (Ubuntu with Docker pre-installed).
2.  **Clone the repo** onto the server.
3.  **Set up `.env`**: Copy `.env.example` to `.env` and set production values.
4.  **Run**:
    ```bash
    docker-compose up -d --build
    ```
5.  **Access**: `http://YOUR_DROPLET_IP`

## Architecture Decisions

*   **Service-Repository Pattern**:
    *   `SecretController`: Handles HTTP requests and responses. Delegates business logic to the Service.
    *   `SecretService`: Contains business logic (encryption, checking expiration, burning on read). Delegates data access to the Repository.
    *   `SecretRepository`: Abstraction layer for database operations (Eloquent). Implements `SecretRepositoryInterface`.
*   **Database**: MySQL is used in the Docker environment for robustness, though SQLite is configured for local testing.
*   **Security**:
    *   Laravel's `Crypt` facade is used for AES-256-CBC encryption.
    *   UUIDs are used instead of auto-incrementing IDs to prevent ID guessing.
    *   Secrets are hard-deleted from the database upon retrieval.

## API Endpoints

### 1. Create a Secret

**POST** `/api/v1/secrets`

**Body:**
```json
{
    "text": "My super secret password",
    "ttl": 3600 // Optional: Time to live in seconds
}
```

**Response (201 Created):**
```json
{
    "id": "9b1deb4d-3b7d-4bad-9bdd-2b0d7b3dcb6d",
    "expires_at": "2026-01-11T13:00:00.000000Z",
    "link": "http://localhost:8000/api/v1/secrets/9b1deb4d-3b7d-4bad-9bdd-2b0d7b3dcb6d"
}
```

### 2. Retrieve a Secret

**GET** `/api/v1/secrets/{id}`

**Response (200 OK):**
```json
{
    "text": "My super secret password"
}
```

**Response (404 Not Found):**
```json
{
    "error": "Secret not found or already viewed."
}
```

## Running Tests

To run the feature tests:

```bash
php artisan test
```
