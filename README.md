# Heaven of Web — website, admin panel and PWA

Plain PHP 8.1+ / MySQL site built for Hostinger shared hosting (no framework, no build step on the server).

- **Public site** (`/fr/`, `/en/`, `/ar/` — French by default, Arabic in RTL): hero with an interactive Three.js globe, about, services, why us, projects (filterable, one page per project), client reviews, contact form. Dark/light theme, server-rendered for SEO (hreflang, sitemap, JSON-LD, real 404s).
- **Admin** (`/admin`): cookieless traffic stats, contact messages with email replies, CRUD for projects / reviews / services / commitments (FR/EN/AR fields), site texts and images, legal pages, admin accounts, push notifications.
- **PWA**: the admin installs as a phone app and receives a push notification for every new message. The public site is installable and has an offline page.

## Run it locally (Docker)

The Docker test environment (`docker/`, `docker-compose.yml`) is kept on the development machine and is not part of this repository (see `.gitignore`). With those files in place:

```bash
docker compose up -d --build
```

| URL | What |
|---|---|
| http://localhost:8090 | the site |
| http://localhost:8090/admin | admin (local account: `DEV_ADMIN_EMAIL` / `DEV_ADMIN_PASSWORD` in `docker-compose.yml`) |
| http://localhost:8025 | Mailpit: every email the site sends lands here |
| http://localhost:8081 | phpMyAdmin |

On start the container installs Composer packages, applies migrations (`app/migrations/*.sql`) and creates the local admin if none exists.

Front-end libraries (three.js, GSAP, Lenis, Chart.js, fonts) are committed in `public_html/assets/`. To update them: `npm install` then `npm run vendor`.

## Layout

```
.env.example            configuration template → copy to .env (never committed)
app/                    PHP application (kept outside the web root)
  Core/                 router, DB, i18n, auth, mailer, push, uploads, analytics
  Controllers/          SiteController (public), Admin/* (back office)
  views/                templates (site, admin, emails)
  lang/                 interface strings: fr.php, en.php, ar.php
  migrations/           001_init.sql (schema), 002_seed.sql (content)
  cli/                  migrate, create-admin, make-icons, optimize-seed
  config.php            reads settings from .env (no secrets here)
public_html/            web root: index.php, assets/, uploads/, sw.js, manifests
vendor/                 Composer packages (PHPMailer, web-push) — upload it
storage/                error log
```

Useful commands (inside the container: `docker compose exec app …`):

```bash
php app/cli/migrate.php
php app/cli/create-admin.php "Name" email@example.com "password"
php app/cli/make-icons.php        # regenerate PWA icons from the logo
```

Deployment to Hostinger: see [DEPLOY.md](DEPLOY.md).
