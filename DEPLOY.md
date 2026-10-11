# Deploying to Hostinger (shared hosting)

## 1. PHP settings (hPanel → Advanced → PHP Configuration)

- PHP version **8.2 or 8.3**.
- Extensions enabled: `pdo_mysql`, `gd`, `intl`, `mbstring`, `openssl`, `curl`, `gmp` (gmp makes push notifications faster), `exif`, `fileinfo`.
- `upload_max_filesize` 12M, `post_max_size` 40M.

## 2. Database (hPanel → Databases → MySQL)

1. Create a database and a user; note the name, user and password.
2. Either import `app/migrations/001_init.sql` then `002_seed.sql` with **phpMyAdmin**, or run `php app/cli/migrate.php` over SSH after step 4.

## 3. Mailbox (hPanel → Emails)

Create `contact@heavenofweb.fr` (the address used by the site). The SMTP server is `smtp.hostinger.com`, port 465, SSL, user = the full address.

## 4. Upload the files

The domain folder looks like `domains/heavenofweb.fr/`. Upload:

```
domains/heavenofweb.fr/
  app/            ← from the project
  vendor/         ← from the project (run `composer install --no-dev` first, or copy it from Docker)
  storage/        ← empty folder, must be writable
  public_html/    ← contents of the project's public_html (replace the old site)
```

Do not upload `node_modules/`, `docker/`, `tools/`, `.git/`.

> If `app/` cannot sit next to `public_html/`, upload `app/`, `vendor/` and `storage/` **inside** `public_html/`: `index.php` detects it, and `.htaccess` blocks web access to those folders.

## 5. Configuration

Copy `app/config.example.php` to `app/config.php` and fill in:

- `app.url` → `https://heavenofweb.fr`
- `app.secret` → a long random string (`php -r "echo bin2hex(random_bytes(32));"`)
- `app.setup_key` → a one-time password used to create the first admin
- `db.*` → the database from step 2
- `mail.*` → the mailbox from step 3 (`pass` = the mailbox password)

## 6. First admin

Open `https://heavenofweb.fr/admin`: with no admin yet you are sent to the setup page. Enter the `setup_key`, your name, email and a password (10+ characters). The page locks itself once an admin exists. (Over SSH you can use `php app/cli/create-admin.php` instead.)

## 7. After going live

1. **Admin → Contenus du site → Coordonnées**: add the real phone number, WhatsApp, social links. Empty values are hidden on the site.
2. **Admin → Contenus du site → Pages légales**: complete the `[à compléter]` parts (legal form, SIRET, address, publication director). Required by French law.
3. **Admin → Notifications** on your phone: install the admin app (Add to Home Screen) and tap “Activer les notifications”, then “Envoyer une notification de test”. On iPhone this only works from the installed app (iOS 16.4+).
4. Send yourself a message with the contact form and check that the email and the push notification arrive.
5. Google Search Console: add the domain and submit `https://heavenofweb.fr/sitemap.xml`.

## Updates

Upload the changed files. If a new `app/migrations/00X_*.sql` file appears, import it in phpMyAdmin (or run `php app/cli/migrate.php`). After changing `sw.js`, bump `VERSION` at its top so browsers drop old caches.

## Two-factor authentication (admin)

- Each admin turns it on in **Admin → Compte → Double authentification**: scan the QR code with an authenticator app (Google Authenticator, Microsoft Authenticator, 1Password…), enter a code, then keep the 10 recovery codes somewhere safe.
- **Obligatoire pour tous les administrateurs** on the same page forces every admin to set it up before using the admin panel.
- Authenticator secrets are encrypted with `app.secret`. **Do not change `app.secret` once MFA is in use**; otherwise run the reset below for each admin.
- Lost phone and recovery codes: `php app/cli/reset-mfa.php email@example.com` (over SSH), then the admin signs in with the password and sets MFA up again.
