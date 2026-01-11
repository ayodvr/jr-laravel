# Secure Drop API

Hey! This is a simple API I built for creating "burn-on-read" secure notes. Think of it like a self-destructing message service. You send a secret, get a link, and once someone opens it, it's gone forever.

## About this project

The main goal here was to build a secure way to share sensitive info (like passwords or API keys) without leaving a trace. It uses UUIDs for unguessable URLs and encrypts everything in the database.

## What it does

*   **Create Secret**: You POST some text, and I give you a one-time link. You can also set a TTL (time-to-live) if you want it to expire automatically.
*   **Burn on Read**: The moment the secret is retrieved, I delete it from the database. Poof.
*   **Encryption**: Everything is encrypted at rest using Laravel's encryption (AES-256-CBC). Even if the DB is compromised, the secrets are safe.
*   **Dockerized**: I've included a Docker setup so you can spin it up easily without messing with your local PHP version.

## Requirements

*   Docker & Docker Compose (or just a local PHP/MySQL setup if you prefer)

## How to run it

### Using Docker (Recommended)

1.  Clone this repo.
2.  Run the containers:
    ```bash
    docker-compose up -d --build
    ```
    This sets up Nginx, PHP-FPM, and MySQL. It might take a minute the first time to pull the images.

3.  That's it! The API will be at `http://localhost:8000/api/v1`.
    *   Docs are here: `http://localhost:8000/docs`

### Deployment

I've made it pretty easy to deploy this to a few places.

**Option 1: Heroku**
I added a PowerShell script (`deploy.ps1`) that handles the heavy lifting if you're on Windows.
1.  Login: `heroku login`
2.  Run: `.\deploy.ps1`

**Option 2: Render**
I really like Render for this kind of stuff. I included a `render.yaml` file so you can just connect your GitHub repo and it'll auto-configure the web service and the Postgres database.

**Option 3: Old School VPS**
If you have a DigitalOcean droplet or similar:
1.  Clone the repo.
2.  Copy `.env.example` to `.env` and fill in your DB details.
3.  `docker-compose up -d --build`

## How I built it (Architecture)

I tried to keep things clean using the **Service-Repository Pattern**:

*   **Controller**: Handles the HTTP stuff (requests/responses).
*   **Service**: This is where the magic happens (encryption, checking if it's expired, deleting it after read).
*   **Repository**: Just deals with the database.

I used **MySQL** for the Docker setup because it's robust, but SQLite works fine for local testing too.

## Endpoints

Here's a quick cheat sheet. For full details check the `/docs` endpoint.

### 1. Save a Secret
`POST /api/v1/secrets`
```json
{
    "text": "super_secret_stuff",
    "ttl": 3600 // optional, in seconds
}
```

### 2. Read a Secret
`GET /api/v1/secrets/{id}`

Returns the text and **deletes** the record. If you try to access it again, you'll get a 404.

## Testing

I wrote some feature tests to make sure the "burn" logic actually works.
Run them with:
```bash
php artisan test
```

## TODO / Future stuff

*   [ ] Add a cron job to clean up expired secrets that were never read (right now they sit there until accessed).
*   [ ] Maybe add a simple frontend? curl is fine but a UI would be nice.
