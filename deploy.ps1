# Add Heroku to PATH
$env:Path += ";C:\Program Files\Heroku\bin"

# Function to check if command exists
function Test-Command ($command) {
    return (Get-Command $command -ErrorAction SilentlyContinue) -ne $null
}

if (-not (Test-Command "heroku")) {
    Write-Host "Error: Heroku CLI not found. Please install Heroku CLI." -ForegroundColor Red
    exit 1
}

# Check login status
Write-Host "Checking Heroku login status..."
$whoami = heroku whoami 2>$null
if ($LASTEXITCODE -ne 0) {
    Write-Host "Not logged in. Please log in to Heroku:" -ForegroundColor Yellow
    heroku login
}

# Commit changes
Write-Host "Committing local changes..."
git add .
git commit -m "Prepare for Heroku deployment"

# Create Heroku app if not exists
$remotes = git remote
if ($remotes -notcontains "heroku") {
    Write-Host "Creating new Heroku app..."
    heroku create
} else {
    Write-Host "Heroku remote already exists."
}

# Set buildpack
Write-Host "Setting PHP buildpack..."
heroku buildpacks:set heroku/php

# Add PostgreSQL addon
Write-Host "Checking/Adding PostgreSQL..."
# This might fail if already exists, which is fine
heroku addons:create heroku-postgresql:mini

# Set Environment Variables
Write-Host "Setting environment variables..."
$appKey = Select-String -Path .env -Pattern "^APP_KEY=(.*)" | ForEach-Object { $_.Matches.Groups[1].Value }
if ($appKey) {
    heroku config:set APP_KEY=$appKey APP_DEBUG=false APP_ENV=production
} else {
    Write-Host "Warning: Could not find APP_KEY in .env" -ForegroundColor Yellow
}

# Push to Heroku
Write-Host "Deploying to Heroku (this may take a few minutes)..."
git push heroku main

# Run Migrations
Write-Host "Running database migrations..."
heroku run php artisan migrate --force

# Open App
Write-Host "Deployment complete! Opening app..."
heroku open
