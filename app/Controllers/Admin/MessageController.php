<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\DB;
use App\Core\HttpException;
use App\Core\Mailer;

final class MessageController extends BaseController
{
    public const STATUSES = [
        'new' => 'Nouveau',
        'read' => 'Lu',
        'replied' => 'Répondu',
        'archived' => 'Archivé',
        'spam' => 'Spam',
    ];

    public function index(): void
    {
        $status = (string) input('status', 'inbox');
        $q = trim((string) input('q', ''));
        $page = max(1, (int) input('page', 1));
        $per = 25;

        [$where, $params] = $this->filters($status, $q);
        $total = (int) DB::value("SELECT COUNT(*) FROM messages WHERE {$where}", $params);
        $messages = DB::all(
            "SELECT id, name, email, company, service, message, status, created_at FROM messages
             WHERE {$where} ORDER BY created_at DESC LIMIT {$per} OFFSET " . (($page - 1) * $per),
            $params
        );
        $counts = array_column(DB::all('SELECT status, COUNT(*) n FROM messages GROUP BY status'), 'n', 'status');

        $this->render('messages/index', [
            'pageTitle' => 'Messages',
            'messages' => $messages,
            'status' => $status,
            'q' => $q,
            'page' => $page,
            'pages' => max(1, (int) ceil($total / $per)),
            'total' => $total,
            'counts' => $counts,
        ]);
    }

    /** @return array{0:string,1:array} */
    private function filters(string $status, string $q): array
    {
        $where = $status === 'all' ? '1=1' : ($status === 'inbox' ? "status IN ('new','read','replied')" : 'status = ?');
        $params = isset(self::STATUSES[$status]) ? [$status] : [];
        if ($q !== '') {
            $where .= ' AND (name LIKE ? OR email LIKE ? OR company LIKE ? OR message LIKE ?)';
            $like = '%' . addcslashes($q, '%_\\') . '%';
            array_push($params, $like, $like, $like, $like);
        }
        return [$where, $params];
    }

    public function show(string $id): void
    {
        $m = DB::one('SELECT * FROM messages WHERE id = ?', [(int) $id]);
        if (!$m) throw new HttpException(404);
        if ($m['status'] === 'new') {
            DB::run("UPDATE messages SET status = 'read', read_at = NOW() WHERE id = ?", [$m['id']]);
            $m['status'] = 'read';
            \App\Core\View::$shared['unreadCount'] = max(0, \App\Core\View::$shared['unreadCount'] - 1);
        }
        $replies = DB::all(
            'SELECT r.*, a.name admin_name FROM message_replies r LEFT JOIN admins a ON a.id = r.admin_id WHERE message_id = ? ORDER BY r.created_at',
            [$m['id']]
        );
        $neighbours = [
            'prev' => DB::value('SELECT id FROM messages WHERE created_at > ? AND status <> ? ORDER BY created_at ASC LIMIT 1', [$m['created_at'], 'spam']),
            'next' => DB::value('SELECT id FROM messages WHERE created_at < ? AND status <> ? ORDER BY created_at DESC LIMIT 1', [$m['created_at'], 'spam']),
        ];
        $subjectBase = ['fr' => 'Votre demande', 'en' => 'Your enquiry', 'ar' => 'طلبك'][$m['lang']] ?? 'Votre demande';
        $this->render('messages/show', [
            'pageTitle' => 'Message de ' . $m['name'],
            'm' => $m,
            'replies' => $replies,
            'neighbours' => $neighbours,
            'defaultSubject' => 'Re: ' . $subjectBase . ($m['service'] ? ' — ' . $m['service'] : '') . ' — Heaven of Web',
            'greeting' => ['fr' => "Bonjour {$m['name']},\n\n", 'en' => "Hello {$m['name']},\n\n", 'ar' => "مرحبًا {$m['name']}،\n\n"][$m['lang']] ?? "Bonjour {$m['name']},\n\n",
            'signature' => "\n\n— " . $this->user['name'] . "\nHeaven of Web",
        ]);
    }

    /** Send a reply by email from the admin and keep it in the thread. */
    public function reply(string $id): void
    {
        $m = DB::one('SELECT * FROM messages WHERE id = ?', [(int) $id]);
        if (!$m) throw new HttpException(404);
        $subject = trim((string) input('subject', ''));
        $body = trim((string) input('body', ''));
        $back = '/admin/messages/' . $m['id'];
        if ($subject === '' || mb_strlen($body) < 2) {
            $this->back($back, 'Ajoutez un objet et un message avant d’envoyer.', 'error');
        }

        $quoted = '<blockquote style="margin:24px 0 0;padding:12px 16px;border-inline-start:3px solid #DBDBDB;color:#5A6B7C;">'
            . '<p style="margin:0 0 6px;font-size:13px;">' . e($m['name']) . ' — ' . e(date('d/m/Y H:i', strtotime($m['created_at']))) . '</p>'
            . nl2br(e($m['message'])) . '</blockquote>';
        $html = '<div style="white-space:normal;">' . nl2br(e($body)) . '</div>' . $quoted;

        $result = Mailer::send(
            $m['email'],
            $subject,
            $html,
            setting('contact_email') ?: null,
            'Heaven of Web',
            $m['lang']
        );

        DB::insert('message_replies', [
            'message_id' => $m['id'],
            'admin_id' => $this->user['id'],
            'subject' => mb_substr($subject, 0, 255),
            'body' => $body,
            'sent_ok' => $result === true ? 1 : 0,
            'error' => $result === true ? null : mb_substr((string) $result, 0, 500),
        ]);

        if ($result !== true) {
            $this->back($back, 'L’email n’a pas pu être envoyé : ' . $result . '. Vérifiez les réglages SMTP dans app/config.php.', 'error');
        }
        DB::run("UPDATE messages SET status = 'replied' WHERE id = ?", [$m['id']]);
        $this->back($back, 'Réponse envoyée à ' . $m['email'] . '.');
    }

    public function status(string $id): void
    {
        $status = (string) input('status');
        if (!isset(self::STATUSES[$status])) throw new HttpException(422);
        DB::run('UPDATE messages SET status = ? WHERE id = ?', [$status, (int) $id]);
        if (wants_json()) json_response(['ok' => true]);
        $label = ['new' => 'Marqué comme non lu.', 'read' => 'Marqué comme lu.', 'archived' => 'Message archivé.', 'spam' => 'Message déplacé dans les spams.', 'replied' => 'Marqué comme répondu.'][$status];
        $this->back(in_array($status, ['archived', 'spam', 'new'], true) ? '/admin/messages' : '/admin/messages/' . (int) $id, $label);
    }

    /** Apply one action to several messages (checkbox selection in the list). */
    public function bulk(): void
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', (array) ($_POST['ids'] ?? [])))));
        $action = (string) input('action', '');
        $back = (string) input('back', '/admin/messages');
        if (!str_starts_with($back, '/admin/messages')) $back = '/admin/messages';
        if (!$ids) $this->back($back, 'Aucun message sélectionné.', 'error');

        $in = implode(',', array_fill(0, count($ids), '?'));
        $n = count($ids);
        $plural = $n > 1 ? 's' : '';
        switch ($action) {
            case 'read':
                DB::run("UPDATE messages SET status = 'read', read_at = COALESCE(read_at, NOW()) WHERE id IN ($in) AND status <> 'replied'", $ids);
                DB::run("UPDATE messages SET status = 'replied' WHERE id IN ($in) AND id IN (SELECT message_id FROM message_replies WHERE sent_ok = 1)", $ids);
                $msg = "{$n} message{$plural} marqué{$plural} comme lu{$plural}.";
                break;
            case 'new':
                DB::run("UPDATE messages SET status = 'new' WHERE id IN ($in)", $ids);
                $msg = "{$n} message{$plural} marqué{$plural} comme non lu{$plural}.";
                break;
            case 'archived':
            case 'spam':
                DB::run("UPDATE messages SET status = ? WHERE id IN ($in)", [$action, ...$ids]);
                $msg = $action === 'spam' ? "{$n} message{$plural} déplacé{$plural} dans les spams." : "{$n} message{$plural} archivé{$plural}.";
                break;
            case 'delete':
                DB::run("DELETE FROM messages WHERE id IN ($in)", $ids);
                $msg = "{$n} message{$plural} supprimé{$plural} définitivement.";
                break;
            default:
                $this->back($back, 'Action inconnue.', 'error');
        }
        $this->back($back, $msg);
    }

    public function delete(string $id): void
    {
        DB::run('DELETE FROM messages WHERE id = ?', [(int) $id]);
        $this->back('/admin/messages', 'Message supprimé définitivement.');
    }

    /** CSV export (Excel-friendly: UTF-8 BOM, semicolon separator). */
    public function export(): void
    {
        [$where, $params] = $this->filters((string) input('status', 'all'), trim((string) input('q', '')));
        $rows = DB::all("SELECT id, created_at, name, email, phone, company, service, budget, lang, status, message FROM messages WHERE {$where} ORDER BY created_at DESC", $params);
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="messages-' . date('Y-m-d') . '.csv"');
        $out = fopen('php://output', 'w');
        fwrite($out, "\xEF\xBB\xBF");
        fputcsv($out, ['ID', 'Date', 'Nom', 'Email', 'Téléphone', 'Entreprise', 'Service', 'Budget', 'Langue', 'Statut', 'Message'], ';');
        foreach ($rows as $r) {
            $r['status'] = self::STATUSES[$r['status']] ?? $r['status'];
            // Neutralise spreadsheet formulas in user-provided values.
            fputcsv($out, array_map(fn($v) => is_string($v) && preg_match('/^[=+\-@]/', $v) ? "'" . $v : $v, $r), ';');
        }
        fclose($out);
    }

    /** Unread counter for the admin badge (polled by admin.js). */
    public function unread(): void
    {
        $items = array_map(fn($m) => [
            'id' => (int) $m['id'],
            'name' => $m['name'],
            'service' => $m['service'],
            'excerpt' => str_excerpt($m['message'], 70),
            'ago' => time_ago($m['created_at']),
        ], \App\Core\View::$shared['latestUnread']);
        json_response(['count' => \App\Core\View::$shared['unreadCount'], 'latest' => $items[0] ?? null, 'items' => $items]);
    }
}
