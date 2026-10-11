<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Analytics;
use App\Core\DB;

final class DashboardController extends BaseController
{
    public function index(): void
    {
        $days = in_array((int) input('range', 30), [7, 30, 90, 365], true) ? (int) input('range', 30) : 30;
        if (random_int(1, 30) === 1) Analytics::prune();

        $this->render('dashboard', [
            'pageTitle' => 'Tableau de bord',
            'loadCharts' => true,
            'days' => $days,
            'stats' => Analytics::stats($days),
            'today' => DB::one("SELECT COUNT(*) views, COUNT(DISTINCT visitor) visitors FROM visits WHERE type = 'pageview' AND day = CURDATE()"),
            'latest' => DB::all("SELECT id, name, email, service, message, status, created_at FROM messages WHERE status <> 'spam' ORDER BY created_at DESC LIMIT 5"),
            'counts' => [
                'projects' => (int) DB::value('SELECT COUNT(*) FROM projects WHERE published = 1'),
                'reviews' => (int) DB::value('SELECT COUNT(*) FROM reviews WHERE published = 1'),
                'rating' => DB::value('SELECT ROUND(AVG(rating), 1) FROM reviews WHERE published = 1'),
            ],
        ]);
    }
}
