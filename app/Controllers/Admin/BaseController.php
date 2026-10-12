<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Csrf;
use App\Core\DB;
use App\Core\Session;
use App\Core\View;

abstract class BaseController
{
    protected array $user;

    public function __construct()
    {
        Session::start();
        header('Cache-Control: no-store');
        header('X-Robots-Tag: noindex, nofollow');
        $this->user = Auth::require();
        // MFA required for everyone: admins without it can only reach the Account page to set it up.
        if (setting('mfa_required') === '1' && empty($this->user['mfa_enabled_at'])) {
            if (wants_json()) json_response(['ok' => false, 'error' => 'mfa_setup_required'], 403);
            flash('toast', 'Activez la double authentification pour accéder à l’administration.');
            flash('toast_type', 'error');
            redirect('/admin/account#mfa');
        }
        if (is_post()) Csrf::verify();
        View::$shared['adminUser'] = $this->user;
        self::shareLayout();
    }

    /** Data used by the admin layout on every page: unread counter and the latest new messages (header bell). */
    public static function shareLayout(): void
    {
        View::$shared['unreadCount'] = (int) DB::value("SELECT COUNT(*) FROM messages WHERE status = 'new'");
        View::$shared['latestUnread'] = DB::all("SELECT id, name, service, message, created_at FROM messages WHERE status = 'new' ORDER BY created_at DESC LIMIT 5");
    }

    protected function render(string $template, array $data = []): void
    {
        echo View::render('admin/' . $template, $data, 'admin');
    }

    /** Redirect back with a toast message. */
    protected function back(string $to, string $message, string $type = 'success'): never
    {
        flash('toast', $message);
        flash('toast_type', $type);
        redirect($to);
    }
}
