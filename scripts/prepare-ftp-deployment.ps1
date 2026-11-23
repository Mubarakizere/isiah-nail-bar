# ==============================================================================
# Isaiah Nail Bar — Production FTP Deployment Preparation Script
# ==============================================================================
$ErrorActionPreference = "Continue"

Write-Host "`n========================================================" -ForegroundColor Cyan
Write-Host "   Isaiah Nail Bar — Preparing FTP Deployment Packages   " -ForegroundColor Cyan
Write-Host "========================================================`n" -ForegroundColor Cyan

$RootDir = (Get-Location).Path
$DeployDir = Join-Path $RootDir "deploy"
$BackendDir = Join-Path $RootDir "backend"
$FrontendDir = Join-Path $RootDir "frontend"

# Clean/Re-create deploy folder
if (Test-Path $DeployDir) {
    Write-Host "Cleaning existing deploy directory..." -ForegroundColor Yellow
    Remove-Item -Path $DeployDir -Recurse -Force -ErrorAction SilentlyContinue
}
New-Item -ItemType Directory -Path $DeployDir -Force | Out-Null
New-Item -ItemType Directory -Path (Join-Path $DeployDir "backend") -Force | Out-Null
New-Item -ItemType Directory -Path (Join-Path $DeployDir "frontend") -Force | Out-Null

# ------------------------------------------------------------------------------
# STEP 1: PREPARE BACKEND (Laravel 12)
# ------------------------------------------------------------------------------
Write-Host "`n[1/4] Packaging Laravel Backend..." -ForegroundColor Green
$BackendTarget = Join-Path $DeployDir "backend"
$BackendIncludes = @("app", "bootstrap", "config", "database", "public", "resources", "routes", "storage", "vendor", "artisan", "composer.json")

foreach ($item in $BackendIncludes) {
    $src = Join-Path $BackendDir $item
    if (Test-Path $src) {
        Write-Host "  -> Copying $item..." -ForegroundColor Gray
        if ($item -eq "public") {
            $destPublic = Join-Path $BackendTarget "public"
            New-Item -ItemType Directory -Path $destPublic -Force | Out-Null
            Get-ChildItem -Path $src | Where-Object { $_.LinkType -eq $null } | ForEach-Object {
                Copy-Item -Path $_.FullName -Destination $destPublic -Recurse -Force
            }
        } elseif ($item -eq "storage") {
            $destStorage = Join-Path $BackendTarget "storage"
            New-Item -ItemType Directory -Path $destStorage -Force | Out-Null
            New-Item -ItemType Directory -Path (Join-Path $destStorage "app/public") -Force | Out-Null
            New-Item -ItemType Directory -Path (Join-Path $destStorage "app/private") -Force | Out-Null
            New-Item -ItemType Directory -Path (Join-Path $destStorage "framework/cache/data") -Force | Out-Null
            New-Item -ItemType Directory -Path (Join-Path $destStorage "framework/sessions") -Force | Out-Null
            New-Item -ItemType Directory -Path (Join-Path $destStorage "framework/views") -Force | Out-Null
            New-Item -ItemType Directory -Path (Join-Path $destStorage "logs") -Force | Out-Null
        } else {
            Copy-Item -Path $src -Destination $BackendTarget -Recurse -Force
        }
    }
}

# Clean dev sqlite if copied
$sqliteDev = Join-Path $BackendTarget "database/database.sqlite"
if (Test-Path $sqliteDev) { Remove-Item $sqliteDev -Force -ErrorAction SilentlyContinue }

Write-Host "  -> Compressing deploy/isaiah-backend.zip..." -ForegroundColor Gray
$BackendZip = Join-Path $DeployDir "isaiah-backend.zip"
& tar -a -cf "$BackendZip" -C "$BackendTarget" .
Write-Host "  ✓ Backend package ready: deploy/isaiah-backend.zip" -ForegroundColor Green

# ------------------------------------------------------------------------------
# STEP 2: PREPARE FRONTEND (Next.js 15 Standalone)
# ------------------------------------------------------------------------------
Write-Host "`n[2/4] Packaging Next.js Standalone Frontend..." -ForegroundColor Green
$StandaloneSrc = Join-Path $FrontendDir ".next\standalone"
$FrontendTarget = Join-Path $DeployDir "frontend"

Write-Host "  -> Copying standalone server..." -ForegroundColor Gray
Copy-Item -Path "$StandaloneSrc\*" -Destination $FrontendTarget -Recurse -Force

Write-Host "  -> Merging public and static assets..." -ForegroundColor Gray
$StaticSrc = Join-Path $FrontendDir ".next\static"
$StaticDest = Join-Path $FrontendTarget ".next\static"
if (Test-Path $StaticSrc) {
    Copy-Item -Path $StaticSrc -Destination $StaticDest -Recurse -Force
}

$PublicSrc = Join-Path $FrontendDir "public"
$PublicDest = Join-Path $FrontendTarget "public"
if (Test-Path $PublicSrc) {
    Copy-Item -Path $PublicSrc -Destination $PublicDest -Recurse -Force
}

Write-Host "  -> Compressing deploy/isaiah-frontend.zip..." -ForegroundColor Gray
$FrontendZip = Join-Path $DeployDir "isaiah-frontend.zip"
& tar -a -cf "$FrontendZip" -C "$FrontendTarget" .
Write-Host "  ✓ Frontend package ready: deploy/isaiah-frontend.zip" -ForegroundColor Green

# ------------------------------------------------------------------------------
# STEP 3: ENVIRONMENT TEMPLATES & APACHE ROUTING
# ------------------------------------------------------------------------------
Write-Host "`n[3/4] Generating production templates..." -ForegroundColor Green

$BackendEnvTemplate = @"
APP_NAME="Isaiah Nail Bar"
APP_ENV=production
APP_KEY=base64:Ne3Zpdq/cc96FWkMa/Xm7tRFFsetSXhLTq+4Q0SRyJg=
APP_DEBUG=false
APP_URL=https://isaiahnailbar.com
APP_TIMEZONE=Africa/Kigali

# Production Database (MySQL on cPanel)
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=stockcen_isaiahnailbar
DB_USERNAME=stockcen_isaiahuser
DB_PASSWORD=YourStrongDatabasePasswordHere

# Session & Cache
SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=true
CACHE_STORE=database

# Queue & Background Workers
QUEUE_CONNECTION=database
DB_QUEUE_RETRY_AFTER=120

# Storage path for cPanel (writes directly to public_html/storage)
FILESYSTEM_DISK=public
FILESYSTEM_PUBLIC_ROOT=/home/stockcen/public_html/storage

# WeFlexfy Mobile Money & Card Payments
WEFLEXFY_ACCESS_KEY=3EzVQ0aoAC6I42A5DnaDLIKWn5IpO7PViS4TzadeD7uTY7wrYmKkeNYHDfVv3qKylVNBu5brt1PSrXTcO0hcdb7CPjCAvb4sdbAIUSGCHrSQtTvZ1H4KA1YLOcB28rMBce3wzByYBxQjqGYBCZkGj3X3qUtKMV2NrXF5
WEFLEXFY_SECRET_KEY=kE6RZCp5IArF4kO25bb9HthEXqbeTgDbBByAoX8hrR6bohf5y5IMhd5mPreye4GpZDQ1xVuLKuUptnUdQ4QguWq8vrKBkgKkaEnqEIBAQwa1aGGOv5K1JambkyYHmFZTpMWqXIHUI38ROYNHXSw9OW8lggM2xtQO47gZ
WEFLEXFY_BASE_URL=https://api.weflexfy.com
WEFLEXFY_RECIPIENT_NUMBER=+250788421063

# SMTP Mail Server (cPanel Webmail)
MAIL_MAILER=smtp
MAIL_HOST=mail.isaiahnailbar.com
MAIL_PORT=465
MAIL_USERNAME=info@isaiahnailbar.com
MAIL_PASSWORD=EwzN4LHBW!Np!wz
MAIL_ENCRYPTION=ssl
MAIL_FROM_ADDRESS=info@isaiahnailbar.com
MAIL_FROM_NAME="Isaiah Nail Bar"
"@

Set-Content -Path (Join-Path $DeployDir ".env.production.backend") -Value $BackendEnvTemplate -Encoding utf8

$FrontendEnvTemplate = @"
PORT=3000
NODE_ENV=production
LARAVEL_API_URL=http://127.0.0.1:8000
NEXT_PUBLIC_API_URL=/api/v1
NEXT_PUBLIC_SITE_URL=https://isaiahnailbar.com
"@

Set-Content -Path (Join-Path $DeployDir ".env.production.frontend") -Value $FrontendEnvTemplate -Encoding utf8

$RootHtaccess = @"
<IfModule mod_rewrite.c>
    RewriteEngine On

    # Enforce HTTPS
    RewriteCond %{HTTPS} off
    RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]

    # Protect sensitive files and directories
    RewriteRule ^(\.env|\.git|composer\.|package\.|deploy) - [F,L,NC]
</IfModule>
"@

Set-Content -Path (Join-Path $DeployDir "root.htaccess") -Value $RootHtaccess -Encoding utf8

# ------------------------------------------------------------------------------
# STEP 4: SUMMARY
# ------------------------------------------------------------------------------
Write-Host "`n[4/4] Deployment packages successfully built!" -ForegroundColor Cyan
Write-Host "--------------------------------------------------------" -ForegroundColor Cyan
Write-Host "  -> deploy/isaiah-backend.zip       (Upload & extract for Laravel)" -ForegroundColor White
Write-Host "  -> deploy/isaiah-frontend.zip      (Upload & extract for Next.js standalone)" -ForegroundColor White
Write-Host "  -> deploy/.env.production.backend  (Production settings for Laravel)" -ForegroundColor White
Write-Host "  -> deploy/.env.production.frontend (Production settings for Next.js)" -ForegroundColor White
Write-Host "  -> deploy/root.htaccess            (Apache/cPanel root helper)" -ForegroundColor White
Write-Host "--------------------------------------------------------`n" -ForegroundColor Cyan
