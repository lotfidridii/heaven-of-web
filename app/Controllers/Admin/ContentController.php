<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\DB;
use App\Core\HttpException;
use App\Core\I18n;
use App\Core\Session;
use App\Core\Translations;
use App\Core\Upload;

/**
 * One CRUD controller for the four kinds of editable content.
 * Each type is described by a config: plain columns, translated fields (FR/EN/AR) and images.
 */
final class ContentController extends BaseController
{
    public const ICONS = ['browser', 'code', 'pen', 'palette', 'search', 'megaphone', 'award', 'users', 'bolt', 'heart', 'check', 'globe', 'smartphone', 'chart', 'layers', 'clock', 'image', 'mail', 'star', 'settings'];

    private array $c;

    public function __construct(private string $type)
    {
        parent::__construct();
        $this->c = self::config()[$type] ?? throw new HttpException(404);
    }

    public static function config(): array
    {
        return [
            'projects' => [
                'table' => 'projects', 'entity' => 'project',
                'title' => 'Réalisations', 'singular' => 'la réalisation', 'new' => 'Nouvelle réalisation',
                'fields' => [
                    'client' => ['label' => 'Client', 'type' => 'text', 'required' => true, 'help' => 'Nom de l’entreprise cliente.'],
                    'url' => ['label' => 'Adresse du site livré', 'type' => 'url', 'help' => 'Laisser vide si le projet n’a pas de site en ligne.'],
                    'completed_on' => ['label' => 'Date de réalisation', 'type' => 'month', 'help' => 'Mois et année, affichés en haut de la page du projet.'],
                    'technologies' => ['label' => 'Technologies (une ligne : Nom|Rôle)', 'type' => 'lines', 'rows' => 5, 'help' => 'Ex. : WordPress|CMS, PHP|Backend, Tailwind CSS|Styles. Section masquée si vide.'],
                    'service_id' => ['label' => 'Service concerné', 'type' => 'service', 'help' => 'Le projet apparaît dans « Réalisations liées » sur la page de ce service.'],
                    'palette' => ['label' => 'Palette de couleurs', 'type' => 'palette', 'help' => 'Les couleurs de la marque du client, affichées sur la page du projet. 8 couleurs maximum.'],
                    'slug' => ['label' => 'Adresse de la page', 'type' => 'slug', 'help' => 'Générée à partir du titre si vide. Ex. : mon-client-site-web'],
                    'featured' => ['label' => 'Mettre en avant (grande carte en tête de liste)', 'type' => 'checkbox'],
                    'published' => ['label' => 'Publié sur le site', 'type' => 'checkbox', 'default' => 1],
                ],
                'translated' => [
                    'title' => ['label' => 'Titre', 'type' => 'text', 'help' => 'Par défaut : le nom du client.'],
                    'category' => ['label' => 'Catégorie', 'type' => 'text', 'help' => 'Sert de filtre sur le site. Ex. : Site web, Logo, Réseaux sociaux.', 'datalist' => true],
                    'summary' => ['label' => 'Résumé', 'type' => 'textarea', 'rows' => 3, 'required' => true, 'help' => 'Une ou deux phrases affichées sur la carte.'],
                    'body' => ['label' => 'Description détaillée', 'type' => 'markdown', 'rows' => 8],
                    'tagline' => ['label' => 'Sous-titre (sous le nom du projet)', 'type' => 'text'],
                    'highlights' => ['label' => 'Points forts (une ligne : icône|texte)', 'type' => 'textarea', 'rows' => 4, 'help' => 'Affichés sous l’aperçu. Icônes : browser, mail, search, bolt, image, star, phone, shield, chart, layers.'],
                    'features' => ['label' => 'Fonctionnalités clés (une par ligne)', 'type' => 'textarea', 'rows' => 6],
                    'stats' => ['label' => 'Chiffres sur l’image (une ligne : valeur|libellé)', 'type' => 'textarea', 'rows' => 3, 'help' => 'Ex. : 5 000+|produits. Laisser vide si vous n’avez pas de chiffres vérifiés.'],
                    'results_text' => ['label' => 'Résultats — texte', 'type' => 'textarea', 'rows' => 3],
                    'results' => ['label' => 'Résultats — chiffres (une ligne : icône|valeur|libellé)', 'type' => 'textarea', 'rows' => 4, 'help' => 'Uniquement des résultats mesurés, ex. : chart|+40 %|de demandes de devis. Section masquée si vide.'],
                ],
                'images' => ['cover' => ['label' => 'Image principale', 'required' => true, 'help' => 'Format paysage conseillé (1920 × 1080). JPG, PNG ou WebP, 10 Mo max.']],
                'gallery' => true,
            ],
            'reviews' => [
                'table' => 'reviews', 'entity' => 'review',
                'title' => 'Avis clients', 'singular' => 'l’avis', 'new' => 'Nouvel avis',
                'fields' => [
                    'author' => ['label' => 'Nom du client', 'type' => 'text', 'required' => true],
                    'company' => ['label' => 'Entreprise', 'type' => 'text'],
                    'rating' => ['label' => 'Note', 'type' => 'rating', 'default' => 5],
                    'project_id' => ['label' => 'Projet concerné', 'type' => 'project'],
                    'published' => ['label' => 'Publié sur le site', 'type' => 'checkbox', 'default' => 1],
                ],
                'translated' => [
                    'text' => ['label' => 'Avis', 'type' => 'textarea', 'rows' => 5, 'required' => true],
                ],
                'images' => ['avatar' => ['label' => 'Photo ou logo', 'help' => 'Carré conseillé. Sinon, l’initiale du nom est affichée.']],
            ],
            'services' => [
                'table' => 'services', 'entity' => 'service',
                'title' => 'Services', 'singular' => 'le service', 'new' => 'Nouveau service',
                'fields' => [
                    'slug' => ['label' => 'Adresse de la page', 'type' => 'slug', 'help' => 'Mettez le métier et la ville pour le référencement local. Ex. : creation-site-web-sousse'],
                    'icon' => ['label' => 'Icône', 'type' => 'icon'],
                    'published' => ['label' => 'Publié sur le site', 'type' => 'checkbox', 'default' => 1],
                ],
                'translated' => [
                    'title' => ['label' => 'Titre', 'type' => 'text', 'required' => true],
                    'text' => ['label' => 'Description courte (page d’accueil)', 'type' => 'textarea', 'rows' => 3, 'required' => true],
                    'intro' => ['label' => 'Introduction de la page', 'type' => 'textarea', 'rows' => 3, 'help' => 'Une ou deux phrases sous le titre de la page du service.'],
                    'body' => ['label' => 'Contenu détaillé', 'type' => 'markdown', 'rows' => 12],
                    'included' => ['label' => 'Ce qui est inclus (une ligne par élément)', 'type' => 'textarea', 'rows' => 6],
                    'process' => ['label' => 'Étapes (une par ligne, « Titre : description »)', 'type' => 'textarea', 'rows' => 6],
                    'faq' => ['label' => 'Questions fréquentes', 'type' => 'textarea', 'rows' => 10, 'help' => 'Une question par bloc : la question sur la première ligne, la réponse dessous, puis une ligne vide avant la question suivante.'],
                    'meta_title' => ['label' => 'Titre Google', 'type' => 'text', 'help' => 'Environ 60 caractères, avec le service et la ville.'],
                    'meta_description' => ['label' => 'Description Google', 'type' => 'textarea', 'rows' => 2, 'help' => 'Environ 155 caractères.'],
                ],
                'images' => ['image' => ['label' => 'Image de la page', 'help' => 'Facultative. Affichée en haut de la page du service et lors des partages.']],
            ],
            'features' => [
                'table' => 'features', 'entity' => 'feature',
                'title' => 'Engagements', 'singular' => 'l’engagement', 'new' => 'Nouvel engagement',
                'fields' => [
                    'icon' => ['label' => 'Icône', 'type' => 'icon'],
                    'published' => ['label' => 'Publié sur le site', 'type' => 'checkbox', 'default' => 1],
                ],
                'translated' => [
                    'title' => ['label' => 'Titre', 'type' => 'text', 'required' => true],
                    'text' => ['label' => 'Description', 'type' => 'textarea', 'rows' => 3, 'required' => true],
                ],
                'images' => [],
            ],
        ];
    }

    /** Colour rows from the palette editor → JSON list of {hex, name}, or null when empty. */
    private static function palette(array $input): ?string
    {
        $out = [];
        foreach (array_values($input['hex'] ?? []) as $i => $hex) {
            $hex = strtoupper(trim((string) $hex));
            if (!preg_match('/^#?[0-9A-F]{6}$/', $hex)) continue;
            $out[] = ['hex' => '#' . ltrim($hex, '#'), 'name' => mb_substr(trim((string) ($input['name'][$i] ?? '')), 0, 40)];
            if (count($out) === 8) break;
        }
        return $out ? json_encode($out, JSON_UNESCAPED_UNICODE) : null;
    }

    public function index(): void
    {
        $rows = DB::all("SELECT * FROM `{$this->c['table']}` ORDER BY sort, id DESC");
        $rows = Translations::attach($this->c['entity'], $rows, 'fr');
        $this->render('content/index', ['pageTitle' => $this->c['title'], 'type' => $this->type, 'c' => $this->c, 'rows' => $rows]);
    }

    public function form(?int $id = null): void
    {
        $row = $id ? DB::one("SELECT * FROM `{$this->c['table']}` WHERE id = ?", [$id]) : null;
        if ($id && !$row) throw new HttpException(404);
        $tr = $id ? Translations::allLangs($this->c['entity'], $id) : array_fill_keys(I18n::LANGS, []);

        // After a validation error, show what the admin typed.
        $old = $_SESSION['_old'] ?? null;
        if ($old) {
            $row = array_merge($row ?? [], $old['fields'] ?? []);
            $tr = array_replace_recursive($tr, $old['tr'] ?? []);
            Session::clearOld();
        }

        $extra = [];
        if ($this->type === 'projects') {
            $extra['images'] = $id ? DB::all('SELECT * FROM project_images WHERE project_id = ? ORDER BY sort, id', [$id]) : [];
            $extra['services'] = Translations::attach('service', DB::all('SELECT id FROM services ORDER BY sort, id'), 'fr');
            $extra['categories'] = array_column(DB::all("SELECT DISTINCT value FROM translations WHERE entity = 'project' AND field = 'category' AND lang = 'fr' AND value <> '' ORDER BY value"), 'value');
        }
        if ($this->type === 'reviews') {
            $extra['projects'] = DB::all('SELECT id, client, slug FROM projects ORDER BY sort, id');
        }

        $this->render('content/form', [
            'pageTitle' => $id ? 'Modifier ' . $this->c['singular'] : $this->c['new'],
            'type' => $this->type, 'c' => $this->c, 'row' => $row, 'tr' => $tr, 'id' => $id,
        ] + $extra);
    }

    public function save(?int $id = null): void
    {
        $existing = $id ? DB::one("SELECT * FROM `{$this->c['table']}` WHERE id = ?", [$id]) : null;
        if ($id && !$existing) throw new HttpException(404);
        $formUrl = "/admin/{$this->type}/" . ($id ?: 'new');

        $data = [];
        $errors = [];
        foreach ($this->c['fields'] as $name => $f) {
            $v = $_POST[$name] ?? null;
            $v = match ($f['type']) {
                'checkbox' => empty($v) ? 0 : 1,
                'number' => ($v === '' || $v === null) ? null : max($f['min'] ?? PHP_INT_MIN, min($f['max'] ?? PHP_INT_MAX, (int) $v)),
                'rating' => max(1, min(5, (int) $v)),
                'project', 'service' => ((int) $v) ?: null,
                'palette' => self::palette($_POST['palette'] ?? []),
                'month' => preg_match('/^\d{4}-\d{2}$/', (string) $v) ? $v . '-01' : null,
                'lines' => trim(str_replace("\r\n", "\n", (string) $v)) ?: null,
                'icon' => in_array($v, self::ICONS, true) ? $v : self::ICONS[0],
                'url' => trim((string) $v) === '' ? null : trim((string) $v),
                default => trim((string) $v),
            };
            if ($f['type'] === 'url' && $v !== null && !filter_var($v, FILTER_VALIDATE_URL)) $errors[] = "{$f['label']} : adresse invalide (elle doit commencer par https://).";
            if (!empty($f['required']) && ($v === '' || $v === null)) $errors[] = "{$f['label']} est obligatoire.";
            if ($f['type'] !== 'slug') $data[$name] = $v;
        }

        $tr = [];
        foreach (I18n::LANGS as $lang) {
            foreach ($this->c['translated'] as $name => $f) {
                $tr[$lang][$name] = trim((string) ($_POST['tr'][$lang][$name] ?? ''));
            }
        }
        foreach ($this->c['translated'] as $name => $f) {
            if (!empty($f['required']) && $tr['fr'][$name] === '') $errors[] = "{$f['label']} (français) est obligatoire.";
        }

        // Slug for projects: from the field, else the French title, else the client name; unique.
        if (isset($this->c['fields']['slug'])) {
            $slug = slugify((string) ($_POST['slug'] ?? '') ?: ($tr['fr']['title'] ?: ($data['client'] ?? '')));
            $base = $slug;
            $i = 2;
            while (DB::value("SELECT id FROM `{$this->c['table']}` WHERE slug = ? AND id <> ?", [$slug, $id ?? 0])) {
                $slug = $base . '-' . $i++;
            }
            $data['slug'] = $slug;
        }

        // Images
        $newImages = [];
        foreach ($this->c['images'] as $name => $f) {
            $file = $_FILES[$name] ?? null;
            if ($file && $file['error'] !== UPLOAD_ERR_NO_FILE) {
                try {
                    $newImages[$name] = Upload::image($file, $this->type, $name === 'avatar' ? 400 : 1920, $name !== 'avatar');
                } catch (\RuntimeException $e) {
                    $errors[] = "{$f['label']} : " . $e->getMessage();
                }
            } elseif (!empty($_POST['remove_' . $name]) && empty($f['required'])) {
                $newImages[$name] = null;
            } elseif (!empty($f['required']) && empty($existing[$name])) {
                $errors[] = "{$f['label']} est obligatoire.";
            }
        }

        if ($errors) {
            foreach ($newImages as $path) Upload::delete($path);
            Session::keepOld(['fields' => $data, 'tr' => $tr]);
            flash('form_errors', implode("\n", $errors));
            redirect($formUrl);
        }

        foreach ($newImages as $name => $path) {
            if ($existing && !empty($existing[$name])) Upload::delete($existing[$name]);
            $data[$name] = $path;
        }

        if ($id) {
            DB::update($this->c['table'], $data, $id);
        } else {
            $data['sort'] = (int) DB::value("SELECT COALESCE(MAX(sort), 0) + 1 FROM `{$this->c['table']}`");
            $id = DB::insert($this->c['table'], $data);
        }
        Translations::save($this->c['entity'], $id, $tr);

        if (!empty($this->c['gallery'])) {
            $sort = (int) DB::value('SELECT COALESCE(MAX(sort), 0) FROM project_images WHERE project_id = ?', [$id]);
            $failed = 0;
            foreach (Upload::list('gallery') as $file) {
                try {
                    DB::insert('project_images', ['project_id' => $id, 'path' => Upload::image($file, 'projects'), 'sort' => ++$sort]);
                } catch (\RuntimeException) {
                    $failed++;
                }
            }
            if ($failed) $this->back("/admin/{$this->type}/{$id}", "Enregistré, mais {$failed} image(s) de la galerie ont été refusées (format ou poids).", 'error');
        }

        $this->back(input('stay') ? "/admin/{$this->type}/{$id}" : "/admin/{$this->type}", 'Modifications enregistrées.');
    }

    public function delete(int $id): void
    {
        $row = DB::one("SELECT * FROM `{$this->c['table']}` WHERE id = ?", [$id]);
        if (!$row) throw new HttpException(404);
        foreach (array_keys($this->c['images']) as $name) Upload::delete($row[$name] ?? null);
        if (!empty($this->c['gallery'])) {
            foreach (DB::all('SELECT path FROM project_images WHERE project_id = ?', [$id]) as $img) Upload::delete($img['path']);
        }
        Translations::delete($this->c['entity'], $id);
        DB::run("DELETE FROM `{$this->c['table']}` WHERE id = ?", [$id]);
        $this->back("/admin/{$this->type}", 'Suppression effectuée.');
    }

    public function toggle(int $id): void
    {
        DB::run("UPDATE `{$this->c['table']}` SET published = 1 - published WHERE id = ?", [$id]);
        $published = (int) DB::value("SELECT published FROM `{$this->c['table']}` WHERE id = ?", [$id]);
        if (wants_json()) json_response(['ok' => true, 'published' => $published]);
        $this->back("/admin/{$this->type}", $published ? 'Publié sur le site.' : 'Masqué du site.');
    }

    /** Save a new order: JSON body {"ids":[3,1,2]}. */
    public function reorder(): void
    {
        $ids = json_decode(file_get_contents('php://input') ?: '[]', true)['ids'] ?? [];
        $stmt = DB::pdo()->prepare("UPDATE `{$this->c['table']}` SET sort = ? WHERE id = ?");
        foreach (array_values($ids) as $i => $id) $stmt->execute([$i + 1, (int) $id]);
        json_response(['ok' => true]);
    }

    public function deleteImage(int $imageId): void
    {
        $img = DB::one('SELECT * FROM project_images WHERE id = ?', [$imageId]);
        if (!$img) throw new HttpException(404);
        Upload::delete($img['path']);
        DB::run('DELETE FROM project_images WHERE id = ?', [$imageId]);
        if (wants_json()) json_response(['ok' => true]);
        $this->back('/admin/projects/' . $img['project_id'], 'Image supprimée.');
    }
}
