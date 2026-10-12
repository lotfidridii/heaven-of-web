<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Analytics;
use App\Core\Csrf;
use App\Core\DB;
use App\Core\HttpException;
use App\Core\I18n;
use App\Core\Mailer;
use App\Core\Push;
use App\Core\RateLimit;
use App\Core\Session;
use App\Core\Translations;
use App\Core\View;

final class SiteController
{
    /** "/" → language from the browser, French by default. */
    public function root(): void
    {
        redirect('/' . I18n::detect() . '/', 302);
    }

    public function home(string $lang): void
    {
        I18n::set($lang);
        Session::start(); // CSRF token for the contact form

        $projects = self::projects();
        $categories = array_values(array_unique(array_filter(array_column($projects, 'category'))));
        $reviews = Translations::attach('review', DB::all('SELECT * FROM reviews WHERE published = 1 ORDER BY sort, id DESC'));

        echo view('site/home', [
            'services' => Translations::attach('service', DB::all('SELECT * FROM services WHERE published = 1 ORDER BY sort, id')),
            'features' => Translations::attach('feature', DB::all('SELECT * FROM features WHERE published = 1 ORDER BY sort, id')),
            'projects' => $projects,
            'categories' => $categories,
            'reviews' => $reviews,
            'rating' => $reviews ? round(array_sum(array_column($reviews, 'rating')) / count($reviews), 1) : null,
            'highlights' => parse_pairs(setting('highlights')),
            'stats' => self::stats(count($projects)),
            'process' => parse_steps(setting('process')),
            'alternates' => self::alternates(''),
            'isHome' => true,
            'hasHero' => true,
        ]);
        Session::clearOld();
    }

    public function project(string $lang, string $slug): void
    {
        I18n::set($lang);
        $row = DB::one('SELECT * FROM projects WHERE slug = ? AND published = 1', [$slug]);
        if (!$row) throw new HttpException(404);
        $project = Translations::attach('project', [$row])[0];
        $project['title'] = ($project['title'] ?? '') ?: $project['client'];
        $images = DB::all('SELECT * FROM project_images WHERE project_id = ? ORDER BY sort, id', [$row['id']]);
        $service = $row['service_id'] ? DB::one('SELECT * FROM services WHERE id = ? AND published = 1 AND slug IS NOT NULL', [$row['service_id']]) : null;
        if ($service) $service = Translations::attach('service', [$service])[0];
        // Similar projects: same service first, then same category, then the rest.
        $others = array_values(array_filter(self::projects(), fn($p) => (int) $p['id'] !== (int) $row['id']));
        usort($others, fn($a, $b) => self::similarity($b, $project) <=> self::similarity($a, $project));

        $results = array_map(static function (string $line) {
            $p = array_map('trim', array_pad(explode('|', $line, 3), 3, ''));
            return $p[2] === '' ? ['icon' => 'chart', 'value' => $p[0], 'label' => $p[1]] : ['icon' => $p[0] ?: 'chart', 'value' => $p[1], 'label' => $p[2]];
        }, text_lines($project['results'] ?? ''));

        $date = null;
        if ($row['completed_on']) {
            $ts = strtotime($row['completed_on']);
            $date = class_exists(\IntlDateFormatter::class)
                ? (new \IntlDateFormatter(['fr' => 'fr_FR', 'en' => 'en_GB', 'ar' => 'ar'][$lang], \IntlDateFormatter::NONE, \IntlDateFormatter::NONE, null, null, 'MMMM y'))->format($ts)
                : date('m/Y', $ts);
            $date = mb_strtoupper(mb_substr($date, 0, 1)) . mb_substr($date, 1);
        } elseif ($row['year']) {
            $date = (string) $row['year'];
        }

        echo view('site/project', [
            'project' => $project,
            'images' => $images,
            'service' => $service,
            'others' => array_slice($others, 0, 3),
            'date' => $date,
            'highlights' => parse_pairs($project['highlights'] ?? ''),
            'features' => text_lines($project['features'] ?? ''),
            'imageStats' => parse_pairs($project['stats'] ?? ''),
            'results' => $results,
            'technologies' => parse_pairs($row['technologies'] ?? ''),
            'hasHero' => true,
            'jsonLd' => [[
                '@context' => 'https://schema.org',
                '@type' => 'BreadcrumbList',
                'itemListElement' => [
                    ['@type' => 'ListItem', 'position' => 1, 'name' => 'Heaven of Web', 'item' => absolute_url(lurl())],
                    ['@type' => 'ListItem', 'position' => 2, 'name' => setting('projects_title'), 'item' => absolute_url(lurl('realisations'))],
                    ['@type' => 'ListItem', 'position' => 3, 'name' => $project['title'], 'item' => absolute_url(lurl('realisations/' . $slug))],
                ],
            ]],
            'pageTitle' => $project['title'] . ' — ' . t('projects.meta_suffix'),
            'pageDescription' => $project['summary'] ?? '',
            'ogImage' => $project['cover'] ? absolute_url($project['cover']) : null,
            'alternates' => self::alternates('realisations/' . $slug),
        ]);
    }

    /** One page per service, written for local search: content, steps, FAQ, area served, related work. */
    public function service(string $lang, string $slug): void
    {
        I18n::set($lang);
        $row = DB::one('SELECT * FROM services WHERE slug = ? AND published = 1', [$slug]);
        if (!$row) throw new HttpException(404);
        $s = Translations::attach('service', [$row])[0];

        $steps = parse_steps($s['process'] ?? '') ?: parse_steps(setting('process'));
        $faq = [];
        foreach (preg_split('/\n\s*\n/', str_replace("\r\n", "\n", trim((string) ($s['faq'] ?? '')))) as $block) {
            $blockLines = array_values(array_filter(array_map('trim', explode("\n", $block))));
            if (count($blockLines) >= 2) $faq[] = ['q' => $blockLines[0], 'a' => implode("\n", array_slice($blockLines, 1))];
        }
        $areas = array_values(array_filter(array_map('trim', explode(',', setting('service_areas')))));
        $related = array_values(array_filter(self::projects(), fn($p) => (int) ($p['service_id'] ?? 0) === (int) $row['id']));
        $others = Translations::attach('service', DB::all('SELECT * FROM services WHERE published = 1 AND id <> ? ORDER BY sort, id', [$row['id']]));

        $url = absolute_url(lurl('services/' . $slug));
        $city = setting('company_city', null, 'Sousse');
        $jsonLd = [[
            '@context' => 'https://schema.org',
            '@type' => 'Service',
            'name' => $s['title'] ?? '',
            'serviceType' => $s['title'] ?? '',
            'description' => ($s['meta_description'] ?? '') ?: ($s['text'] ?? ''),
            'url' => $url,
            'provider' => array_filter([
                '@type' => 'ProfessionalService',
                'name' => 'Heaven of Web',
                'url' => absolute_url(lurl()),
                'email' => setting('contact_email') ?: null,
                'telephone' => setting('contact_phone') ?: null,
                'address' => ['@type' => 'PostalAddress', 'addressLocality' => $city, 'addressCountry' => 'TN'],
            ]),
            'areaServed' => array_map(fn($a) => ['@type' => 'City', 'name' => $a], $areas ?: [$city]),
        ], [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Heaven of Web', 'item' => absolute_url(lurl())],
                ['@type' => 'ListItem', 'position' => 2, 'name' => t('nav.services'), 'item' => absolute_url(lurl() . '#services')],
                ['@type' => 'ListItem', 'position' => 3, 'name' => $s['title'] ?? '', 'item' => $url],
            ],
        ]];
        if ($faq) {
            $jsonLd[] = [
                '@context' => 'https://schema.org',
                '@type' => 'FAQPage',
                'mainEntity' => array_map(fn($f) => ['@type' => 'Question', 'name' => $f['q'], 'acceptedAnswer' => ['@type' => 'Answer', 'text' => $f['a']]], $faq),
            ];
        }

        echo view('site/service', [
            's' => $s,
            'included' => text_lines($s['included'] ?? ''),
            'features' => Translations::attach('feature', DB::all('SELECT * FROM features WHERE published = 1 ORDER BY sort, id')),
            'services' => Translations::attach('service', DB::all('SELECT * FROM services WHERE published = 1 ORDER BY sort, id')),
            'hasHero' => true,
            'steps' => $steps,
            'faq' => $faq,
            'areas' => $areas,
            'related' => $related,
            'others' => $others,
            'pageTitle' => ($s['meta_title'] ?? '') ?: ($s['title'] ?? ''),
            'fullTitle' => !empty($s['meta_title']),
            'pageDescription' => ($s['meta_description'] ?? '') ?: ($s['text'] ?? ''),
            'ogImage' => $row['image'] ? absolute_url($row['image']) : null,
            'jsonLd' => $jsonLd,
            'alternates' => self::alternates('services/' . $slug),
        ]);
    }

    /** All published projects, filterable by category. */
    public function projectsIndex(string $lang): void
    {
        I18n::set($lang);
        $projects = self::projects();
        echo view('site/projects', [
            'projects' => $projects,
            'categories' => array_values(array_unique(array_filter(array_column($projects, 'category')))),
            'pageTitle' => setting('projects_title'),
            'pageDescription' => setting('projects_intro'),
            'alternates' => self::alternates('realisations'),
            'hasHero' => true,
        ]);
    }

    private static function similarity(array $a, array $b): int
    {
        return ((int) ($a['service_id'] ?? 0) === (int) ($b['service_id'] ?? -1) ? 2 : 0)
            + (($a['category'] ?? '') !== '' && ($a['category'] ?? '') === ($b['category'] ?? '') ? 1 : 0);
    }

    /** "value|label" lines from the stats setting; {projects} is the number of published projects. */
    private static function stats(int $projectCount): array
    {
        return array_map(
            fn($p) => ['value' => str_replace('{projects}', (string) $projectCount, $p[0]), 'label' => $p[1]],
            parse_pairs(setting('stats'))
        );
    }

    public function legal(string $lang): void
    {
        $this->textPage($lang, 'legal_notice', 'legal.title', 'mentions-legales');
    }

    public function privacy(string $lang): void
    {
        $this->textPage($lang, 'privacy', 'privacy.title', 'confidentialite');
    }

    private function textPage(string $lang, string $setting, string $titleKey, string $path): void
    {
        I18n::set($lang);
        $updated = setting('legal_updated');
        echo view('site/legal', [
            'title' => t($titleKey),
            'subtitle' => t($titleKey . '_sub', ['site' => preg_replace('#^https?://#', '', config('app.url'))]),
            'doc' => md_sections(setting($setting)),
            'updated' => $updated ? self::longDate($updated) : null,
            'pageTitle' => t($titleKey),
            'alternates' => self::alternates($path),
            'hasHero' => true,
        ]);
    }

    /** "Plan du site": every public page, grouped. */
    public function sitemapPage(string $lang): void
    {
        I18n::set($lang);
        echo view('site/sitemap', [
            'services' => Translations::attach('service', DB::all('SELECT id, slug, icon FROM services WHERE published = 1 AND slug IS NOT NULL ORDER BY sort, id')),
            'projects' => self::projects(),
            'pageTitle' => t('sitemap.title'),
            'alternates' => self::alternates('plan-du-site'),
            'hasHero' => true,
        ]);
    }

    /** Localised "11 octobre 2026". */
    private static function longDate(string $date): string
    {
        $ts = strtotime($date);
        if (!class_exists(\IntlDateFormatter::class)) return date('d/m/Y', $ts);
        $locale = ['fr' => 'fr_FR', 'en' => 'en_GB', 'ar' => 'ar'][lang()] ?? 'fr_FR';
        return (string) (new \IntlDateFormatter($locale, \IntlDateFormatter::LONG, \IntlDateFormatter::NONE))->format($ts);
    }

    /** Contact form: JSON for the JS submit, redirect with flash for the no-JS fallback. */
    public function contact(string $lang): void
    {
        I18n::set($lang);
        Session::start();
        $json = wants_json();
        $back = lurl() . '#contact';

        if (!Csrf::check()) {
            $json ? json_response(['ok' => false, 'message' => t('contact.error.expired')], 403) : null;
            flash('contact_error', t('contact.error.expired'));
            redirect($back);
        }

        // Anti-spam trap: bots fill the hidden field. The message is kept in the admin "Spam" folder
        // (never silently lost, in case a browser filled it), but no notification or email is sent.
        $trapped = trim((string) ($_POST['hp_trap'] ?? '')) !== '';

        $data = [
            'name' => mb_substr(trim((string) input('name', '')), 0, 120),
            'email' => mb_substr(trim((string) input('email', '')), 0, 190),
            'phone' => mb_substr(trim((string) input('phone', '')), 0, 40),
            'company' => mb_substr(trim((string) input('company', '')), 0, 150),
            'service' => mb_substr(trim((string) input('service', '')), 0, 120),
            'budget' => mb_substr(trim((string) input('budget', '')), 0, 60),
            'message' => mb_substr(trim((string) input('message', '')), 0, 5000),
        ];
        $errors = [];
        if ($data['name'] === '') $errors['name'] = t('contact.error.required');
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) $errors['email'] = t('contact.error.email');
        if (mb_strlen($data['message']) < 10) $errors['message'] = t('contact.error.message_short');
        if ($data['phone'] !== '' && !preg_match('/^[0-9 +().-]{6,40}$/', $data['phone'])) $errors['phone'] = t('contact.error.phone');
        if (empty($_POST['consent'])) $errors['consent'] = t('contact.error.consent');

        if ($errors) {
            if ($json) json_response(['ok' => false, 'errors' => $errors, 'message' => t('contact.error.check')], 422);
            Session::keepOld($data);
            flash('contact_error', t('contact.error.check'));
            redirect($back);
        }

        if (!RateLimit::hit('contact|' . client_ip(), 5, 3600)) {
            $msg = t('contact.error.rate');
            if ($json) json_response(['ok' => false, 'message' => $msg], 429);
            flash('contact_error', $msg);
            redirect($back);
        }

        $id = DB::insert('messages', $data + [
            'lang' => $lang,
            'status' => $trapped ? 'spam' : 'new',
            'ip_hash' => ip_hash(),
            'user_agent' => mb_substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255),
        ]);
        if ($trapped) error_log("Contact form: message #{$id} filed as spam (anti-spam field filled).");
        else Analytics::record(['type' => 'contact', 'path' => lurl(), 'lang' => $lang]);

        // Answer the visitor first, then do the slow work (push + emails).
        $successMsg = t('contact.success.text', ['name' => $data['name']]);
        if ($json) {
            self::respondAndContinue(['ok' => true, 'message' => $successMsg]);
        }
        if (!$trapped) self::notify($id, $data, $lang);

        if (!$json) {
            flash('contact_success', $successMsg);
            redirect($back);
        }
    }

    /** Push notification to admins + notification email + acknowledgement to the visitor. */
    private static function notify(int $id, array $data, string $lang): void
    {
        $adminUrl = absolute_url('admin/messages/' . $id);
        try {
            Push::send([
                'title' => 'Nouveau message de ' . $data['name'],
                'body' => str_excerpt($data['message'], 140),
                'url' => '/admin/messages/' . $id,
                'tag' => 'message-' . $id,
            ]);
        } catch (\Throwable $e) {
            error_log('Push: ' . $e->getMessage());
        }

        $to = setting('notify_email') ?: setting('contact_email') ?: config('mail.from');
        Mailer::send(
            $to,
            'Nouveau message de ' . $data['name'] . ($data['service'] ? ' — ' . $data['service'] : ''),
            View::render('emails/notify', ['m' => $data + ['id' => $id, 'lang' => $lang], 'adminUrl' => $adminUrl]),
            $data['email'],
            $data['name']
        );

        Mailer::send(
            $data['email'],
            t('mail.ack.subject'),
            View::render('emails/ack', ['m' => $data]),
            setting('contact_email') ?: null,
            'Heaven of Web',
            $lang
        );
    }

    /** Send the JSON response to the browser now and keep running (PHP-FPM / LiteSpeed). */
    private static function respondAndContinue(array $data): void
    {
        ignore_user_abort(true);
        $body = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store');
        header('Content-Length: ' . strlen($body));
        header('Connection: close');
        echo $body;
        if (session_status() === PHP_SESSION_ACTIVE) session_write_close();
        if (function_exists('fastcgi_finish_request')) {
            fastcgi_finish_request();
        } elseif (function_exists('litespeed_finish_request')) {
            litespeed_finish_request();
        } else {
            while (ob_get_level() > 0) ob_end_flush();
            flush();
        }
    }

    public function sitemap(): void
    {
        $urls = [];
        $add = static function (string $path, ?string $lastmod = null) use (&$urls): void {
            $alts = [];
            foreach (I18n::LANGS as $l) $alts[$l] = absolute_url(lurl($path, $l));
            foreach (I18n::LANGS as $l) $urls[] = ['loc' => $alts[$l], 'alts' => $alts, 'lastmod' => $lastmod];
        };
        $add('');
        $add('realisations');
        foreach (DB::all('SELECT slug, updated_at FROM projects WHERE published = 1 ORDER BY sort') as $p) {
            $add('realisations/' . $p['slug'], substr($p['updated_at'], 0, 10));
        }
        foreach (DB::all('SELECT slug FROM services WHERE published = 1 AND slug IS NOT NULL ORDER BY sort') as $s) {
            $add('services/' . $s['slug']);
        }
        $add('plan-du-site');
        $add('mentions-legales');
        $add('confidentialite');

        header('Content-Type: application/xml; charset=utf-8');
        echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">' . "\n";
        foreach ($urls as $u) {
            echo '  <url><loc>' . e($u['loc']) . '</loc>';
            if ($u['lastmod']) echo '<lastmod>' . e($u['lastmod']) . '</lastmod>';
            foreach ($u['alts'] as $l => $href) echo '<xhtml:link rel="alternate" hreflang="' . $l . '" href="' . e($href) . '"/>';
            echo '<xhtml:link rel="alternate" hreflang="x-default" href="' . e($u['alts']['fr']) . '"/>';
            echo "</url>\n";
        }
        echo '</urlset>';
    }

    /** Published projects with translations; the title falls back to the client name. */
    private static function projects(): array
    {
        $rows = Translations::attach('project', DB::all('SELECT * FROM projects WHERE published = 1 ORDER BY sort, id DESC'));
        return array_map(function ($p) {
            $p['title'] = ($p['title'] ?? '') ?: $p['client'];
            return $p;
        }, $rows);
    }

    /** hreflang alternates for the same page in every language. */
    private static function alternates(string $path): array
    {
        $out = [];
        foreach (I18n::LANGS as $l) $out[$l] = lurl($path, $l);
        View::$shared['currentPath'] = $path;
        return $out;
    }
}
