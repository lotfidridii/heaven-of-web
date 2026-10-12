<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Csrf;
use App\Core\DB;
use App\Core\Mfa;
use App\Core\RateLimit;
use App\Core\Session;
use App\Core\Settings;
use App\Core\View;

/** Login, first-admin setup and account settings. Not derived from BaseController (login is public). */
final class AuthController
{
    public function __construct()
    {
        Session::start();
        header('Cache-Control: no-store');
        header('X-Robots-Tag: noindex, nofollow');
    }

    private function noAdmins(): bool
    {
        return (int) DB::value('SELECT COUNT(*) FROM admins') === 0;
    }

    public function loginForm(): void
    {
        if ($this->noAdmins()) redirect('/admin/setup');
        if (Auth::user()) redirect('/admin');
        echo View::render('admin/login', ['error' => flash('login_error'), 'email' => flash('login_email')], 'admin_auth');
    }

    public function login(): void
    {
        if (!Csrf::check()) {
            flash('login_error', 'La session a expiré, réessayez.');
            redirect('/admin/login');
        }
        $email = strtolower(trim((string) input('email', '')));
        $result = Auth::attempt($email, (string) ($_POST['password'] ?? ''));
        if ($result === Auth::MFA_REQUIRED) redirect('/admin/login/verify');
        if ($result !== true) {
            flash('login_error', $result);
            flash('login_email', $email);
            redirect('/admin/login');
        }
        $to = $_SESSION['_intended'] ?? '/admin';
        unset($_SESSION['_intended']);
        redirect(str_starts_with($to, '/admin') ? $to : '/admin');
    }

    /* ---------- Second step: authenticator code or recovery code ---------- */

    public function verifyForm(): void
    {
        if (Auth::user()) redirect('/admin');
        if (!Auth::pendingMfa()) {
            flash('login_error', 'La vérification a expiré. Reconnectez-vous.');
            redirect('/admin/login');
        }
        echo View::render('admin/mfa-verify', ['error' => flash('mfa_error'), 'pageTitle' => 'Vérification en deux étapes'], 'admin_auth');
    }

    public function verify(): void
    {
        $id = Auth::pendingMfa();
        if (!$id || !Csrf::check()) {
            flash('login_error', 'La vérification a expiré. Reconnectez-vous.');
            redirect('/admin/login');
        }
        if (!RateLimit::hit('mfa|' . client_ip() . '|' . $id, 5, 300)) {
            Auth::clearPendingMfa();
            flash('login_error', 'Trop de codes incorrects. Reconnectez-vous dans 5 minutes.');
            redirect('/admin/login');
        }
        $admin = DB::one('SELECT * FROM admins WHERE id = ?', [$id]);
        $before = $admin ? Mfa::remainingRecoveryCodes($id) : 0;
        if (!$admin || !Mfa::check($admin, (string) input('code', ''))) {
            flash('mfa_error', 'Code incorrect ou expiré. Vérifiez l’heure de votre téléphone, ou utilisez un code de secours.');
            redirect('/admin/login/verify');
        }
        RateLimit::clear('mfa|' . client_ip() . '|' . $id);
        Auth::login($id);
        $left = Mfa::remainingRecoveryCodes($id);
        if ($left < $before) {
            flash('toast', "Code de secours utilisé. Il vous en reste {$left}" . ($left <= 3 ? ' : générez-en de nouveaux dans « Compte ».' : '.'));
            flash('toast_type', $left <= 3 ? 'error' : 'success');
        }
        $to = $_SESSION['_intended'] ?? '/admin';
        unset($_SESSION['_intended']);
        redirect(str_starts_with($to, '/admin') ? $to : '/admin');
    }

    /* ---------- MFA management (Account page) ---------- */

    private function mfaBack(string $msg, string $type = 'success'): never
    {
        flash('toast', $msg);
        flash('toast_type', $type);
        redirect('/admin/account#mfa');
    }

    private function currentAdmin(): array
    {
        $user = Auth::require();
        Csrf::verify();
        return DB::one('SELECT * FROM admins WHERE id = ?', [$user['id']]);
    }

    public function mfaStart(): void
    {
        $this->currentAdmin();
        $_SESSION['mfa_setup'] = Mfa::newSecret();
        redirect('/admin/account#mfa');
    }

    public function mfaCancel(): void
    {
        $this->currentAdmin();
        unset($_SESSION['mfa_setup']);
        redirect('/admin/account#mfa');
    }

    public function mfaConfirm(): void
    {
        $admin = $this->currentAdmin();
        $secret = $_SESSION['mfa_setup'] ?? null;
        if (!$secret) $this->mfaBack('La configuration a expiré, recommencez.', 'error');
        $step = Mfa::verify($secret, (string) input('code', ''));
        if ($step === null) $this->mfaBack('Code incorrect. Saisissez le code à 6 chiffres affiché dans l’application.', 'error');
        DB::run('UPDATE admins SET mfa_secret = ?, mfa_enabled_at = NOW(), mfa_last_step = ? WHERE id = ?', [Mfa::encrypt($secret), $step, $admin['id']]);
        unset($_SESSION['mfa_setup']);
        $_SESSION['mfa_codes'] = Mfa::newRecoveryCodes((int) $admin['id']);
        $this->mfaBack('Double authentification activée. Conservez vos codes de secours.');
    }

    public function mfaRecovery(): void
    {
        $admin = $this->currentAdmin();
        if (!Mfa::enabled($admin) || !Mfa::check($admin, (string) input('code', ''))) {
            $this->mfaBack('Code incorrect : impossible de générer de nouveaux codes.', 'error');
        }
        $_SESSION['mfa_codes'] = Mfa::newRecoveryCodes((int) $admin['id']);
        $this->mfaBack('Nouveaux codes de secours générés. Les anciens ne fonctionnent plus.');
    }

    public function mfaDisable(): void
    {
        $admin = $this->currentAdmin();
        if (setting('mfa_required') === '1') $this->mfaBack('La double authentification est obligatoire pour tous les administrateurs.', 'error');
        if (!password_verify((string) ($_POST['password'] ?? ''), $admin['password_hash']) || !Mfa::check($admin, (string) input('code', ''))) {
            $this->mfaBack('Mot de passe ou code incorrect.', 'error');
        }
        Mfa::disable((int) $admin['id']);
        $this->mfaBack('Double authentification désactivée.');
    }

    public function mfaRequire(): void
    {
        $admin = $this->currentAdmin();
        $on = input('required') === '1';
        if ($on && !Mfa::enabled($admin)) $this->mfaBack('Activez d’abord la double authentification sur votre propre compte.', 'error');
        Settings::set('mfa_required', $on ? '1' : '0');
        $this->mfaBack($on ? 'La double authentification est désormais obligatoire pour tous les administrateurs.' : 'La double authentification n’est plus obligatoire.');
    }

    public function logout(): void
    {
        if (Csrf::check()) Auth::logout();
        redirect('/admin/login');
    }

    /** First admin account. Only available while the admins table is empty, and needs the setup key from config. */
    public function setupForm(): void
    {
        if (!$this->noAdmins()) redirect('/admin/login');
        echo View::render('admin/setup', ['error' => flash('setup_error')], 'admin_auth');
    }

    public function setup(): void
    {
        if (!$this->noAdmins()) redirect('/admin/login');
        $fail = function (string $msg): never {
            flash('setup_error', $msg);
            Session::keepOld(['name' => input('name'), 'email' => input('email')]);
            redirect('/admin/setup');
        };
        if (!Csrf::check()) $fail('La session a expiré, réessayez.');
        if (!RateLimit::hit('setup|' . client_ip(), 10, 3600)) $fail('Trop de tentatives, réessayez plus tard.');

        $key = (string) config('app.setup_key');
        if ($key === '' || $key === 'CHANGE-ME' || !hash_equals($key, (string) input('setup_key', ''))) {
            $fail('Clé d’installation incorrecte. Elle se trouve dans app/config.php (setup_key).');
        }
        $name = trim((string) input('name', ''));
        $email = strtolower(trim((string) input('email', '')));
        $password = (string) ($_POST['password'] ?? '');
        if ($name === '') $fail('Indiquez votre nom.');
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $fail('Adresse email invalide.');
        if (strlen($password) < 10) $fail('Le mot de passe doit contenir au moins 10 caractères.');

        $id = DB::insert('admins', ['name' => $name, 'email' => $email, 'password_hash' => password_hash($password, PASSWORD_DEFAULT)]);
        Session::clearOld();
        Auth::login($id);
        flash('toast', 'Compte administrateur créé. Bienvenue !');
        redirect('/admin');
    }

    public function account(): void
    {
        $user = Auth::require();
        View::$shared['adminUser'] = $user;
        BaseController::shareLayout();
        $admins = DB::all('SELECT id, name, email, last_login_at, mfa_enabled_at FROM admins ORDER BY id');
        $me = DB::one('SELECT * FROM admins WHERE id = ?', [$user['id']]);

        // MFA setup in progress: QR code + manual key. Recovery codes are shown once, right after they are generated.
        $setup = null;
        if (!empty($_SESSION['mfa_setup'])) {
            $secret = $_SESSION['mfa_setup'];
            $setup = ['qr' => Mfa::qrSvg(Mfa::uri($secret, $me['email'])), 'key' => Mfa::formatSecret($secret)];
        }
        $codes = $_SESSION['mfa_codes'] ?? null;
        unset($_SESSION['mfa_codes']);

        echo View::render('admin/account', [
            'admins' => $admins,
            'pageTitle' => 'Compte',
            'mfaEnabled' => Mfa::enabled($me),
            'mfaSince' => $me['mfa_enabled_at'],
            'mfaRemaining' => Mfa::remainingRecoveryCodes((int) $me['id']),
            'mfaRequired' => setting('mfa_required') === '1',
            'mfaSetup' => $setup,
            'recoveryCodes' => $codes,
        ], 'admin');
    }

    public function updateAccount(): void
    {
        $user = Auth::require();
        Csrf::verify();
        $back = function (string $msg, string $type = 'error'): never {
            flash('toast', $msg);
            flash('toast_type', $type);
            redirect('/admin/account');
        };

        if (input('action') === 'add_admin') {
            $name = trim((string) input('new_name', ''));
            $email = strtolower(trim((string) input('new_email', '')));
            $password = (string) ($_POST['new_password'] ?? '');
            if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) $back('Nom et email valides requis pour le nouvel administrateur.');
            if (strlen($password) < 10) $back('Le mot de passe doit contenir au moins 10 caractères.');
            if (DB::value('SELECT id FROM admins WHERE email = ?', [$email])) $back('Un administrateur utilise déjà cet email.');
            DB::insert('admins', ['name' => $name, 'email' => $email, 'password_hash' => password_hash($password, PASSWORD_DEFAULT)]);
            $back("Administrateur {$email} ajouté.", 'success');
        }

        if (input('action') === 'delete_admin') {
            $id = (int) input('admin_id');
            if ($id === (int) $user['id']) $back('Vous ne pouvez pas supprimer votre propre compte.');
            DB::run('DELETE FROM admins WHERE id = ?', [$id]);
            $back('Administrateur supprimé.', 'success');
        }

        $name = trim((string) input('name', ''));
        $email = strtolower(trim((string) input('email', '')));
        if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) $back('Nom et email valides requis.');
        if (DB::value('SELECT id FROM admins WHERE email = ? AND id <> ?', [$email, $user['id']])) $back('Cet email est déjà utilisé.');

        $current = DB::one('SELECT password_hash FROM admins WHERE id = ?', [$user['id']]);
        $new = (string) ($_POST['new_password_self'] ?? '');
        if ($new !== '') {
            if (!password_verify((string) ($_POST['current_password'] ?? ''), $current['password_hash'])) $back('Mot de passe actuel incorrect.');
            if (strlen($new) < 10) $back('Le nouveau mot de passe doit contenir au moins 10 caractères.');
            DB::run('UPDATE admins SET password_hash = ? WHERE id = ?', [password_hash($new, PASSWORD_DEFAULT), $user['id']]);
        }
        DB::run('UPDATE admins SET name = ?, email = ? WHERE id = ?', [$name, $email, $user['id']]);
        $back($new !== '' ? 'Profil et mot de passe mis à jour.' : 'Profil mis à jour.', 'success');
    }
}
