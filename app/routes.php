<?php
declare(strict_types=1);

use App\Controllers\Admin;
use App\Controllers\ApiController;
use App\Controllers\SiteController;
use App\Core\Router;

return static function (Router $r): void {
    $L = '{lang:fr|en|ar}';

    // Public site
    $r->get('/', [SiteController::class, 'root']);
    $r->get("/{$L}", [SiteController::class, 'home']);
    $r->get("/{$L}/realisations", [SiteController::class, 'projectsIndex']);
    $r->get("/{$L}/realisations/{slug:[a-z0-9-]+}", [SiteController::class, 'project']);
    $r->get("/{$L}/services/{slug:[a-z0-9-]+}", [SiteController::class, 'service']);
    $r->get("/{$L}/mentions-legales", [SiteController::class, 'legal']);
    $r->get("/{$L}/confidentialite", [SiteController::class, 'privacy']);
    $r->get("/{$L}/plan-du-site", [SiteController::class, 'sitemapPage']);
    $r->post("/{$L}/contact", [SiteController::class, 'contact']);
    $r->get('/sitemap.xml', [SiteController::class, 'sitemap']);
    $r->post('/api/hit', [ApiController::class, 'hit']);

    // Admin
    $r->get('/admin/login', [Admin\AuthController::class, 'loginForm']);
    $r->post('/admin/login', [Admin\AuthController::class, 'login']);
    $r->post('/admin/logout', [Admin\AuthController::class, 'logout']);
    $r->get('/admin/login/verify', [Admin\AuthController::class, 'verifyForm']);
    $r->post('/admin/login/verify', [Admin\AuthController::class, 'verify']);
    $r->post('/admin/account/mfa/start', [Admin\AuthController::class, 'mfaStart']);
    $r->post('/admin/account/mfa/cancel', [Admin\AuthController::class, 'mfaCancel']);
    $r->post('/admin/account/mfa/confirm', [Admin\AuthController::class, 'mfaConfirm']);
    $r->post('/admin/account/mfa/recovery', [Admin\AuthController::class, 'mfaRecovery']);
    $r->post('/admin/account/mfa/disable', [Admin\AuthController::class, 'mfaDisable']);
    $r->post('/admin/account/mfa/require', [Admin\AuthController::class, 'mfaRequire']);
    $r->get('/admin/setup', [Admin\AuthController::class, 'setupForm']);
    $r->post('/admin/setup', [Admin\AuthController::class, 'setup']);
    $r->get('/admin/account', [Admin\AuthController::class, 'account']);
    $r->post('/admin/account', [Admin\AuthController::class, 'updateAccount']);

    $r->get('/admin', [Admin\DashboardController::class, 'index']);

    $r->get('/admin/messages', [Admin\MessageController::class, 'index']);
    $r->get('/admin/messages/export', [Admin\MessageController::class, 'export']);
    $r->get('/admin/messages/{id:\d+}', [Admin\MessageController::class, 'show']);
    $r->post('/admin/messages/{id:\d+}/reply', [Admin\MessageController::class, 'reply']);
    $r->post('/admin/messages/{id:\d+}/status', [Admin\MessageController::class, 'status']);
    $r->post('/admin/messages/{id:\d+}/delete', [Admin\MessageController::class, 'delete']);
    $r->get('/admin/api/unread', [Admin\MessageController::class, 'unread']);

    // Projects, reviews, services and "why us" items share one CRUD controller.
    foreach (['projects', 'reviews', 'services', 'features'] as $type) {
        $r->get("/admin/{$type}", fn() => (new Admin\ContentController($type))->index());
        $r->get("/admin/{$type}/new", fn() => (new Admin\ContentController($type))->form());
        $r->post("/admin/{$type}/new", fn() => (new Admin\ContentController($type))->save());
        $r->get("/admin/{$type}/{id:\d+}", fn(string $id) => (new Admin\ContentController($type))->form((int) $id));
        $r->post("/admin/{$type}/{id:\d+}", fn(string $id) => (new Admin\ContentController($type))->save((int) $id));
        $r->post("/admin/{$type}/{id:\d+}/delete", fn(string $id) => (new Admin\ContentController($type))->delete((int) $id));
        $r->post("/admin/{$type}/{id:\d+}/toggle", fn(string $id) => (new Admin\ContentController($type))->toggle((int) $id));
        $r->post("/admin/{$type}/reorder", fn() => (new Admin\ContentController($type))->reorder());
    }
    $r->post('/admin/projects/images/{id:\d+}/delete', fn(string $id) => (new Admin\ContentController('projects'))->deleteImage((int) $id));

    $r->get('/admin/settings', [Admin\SettingsController::class, 'index']);
    $r->post('/admin/settings', [Admin\SettingsController::class, 'save']);

    $r->get('/admin/notifications', [Admin\PushController::class, 'index']);
    $r->post('/admin/push/subscribe', [Admin\PushController::class, 'subscribe']);
    $r->post('/admin/push/unsubscribe', [Admin\PushController::class, 'unsubscribe']);
    $r->post('/admin/push/test', [Admin\PushController::class, 'test']);
    $r->post('/admin/push/{id:\d+}/delete', [Admin\PushController::class, 'delete']);
};
