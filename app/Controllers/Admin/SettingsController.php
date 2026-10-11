<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\I18n;
use App\Core\Settings;
use App\Core\Upload;

/** Site texts (per language), contact details, social links and site images. */
final class SettingsController extends BaseController
{
    public static function groups(): array
    {
        return [
            'contact' => [
                'title' => 'Coordonnées',
                'fields' => [
                    'contact_email' => ['label' => 'Email affiché sur le site', 'type' => 'email', 'help' => 'Aussi utilisé comme adresse de réponse des emails envoyés.'],
                    'contact_phone' => ['label' => 'Téléphone', 'type' => 'tel', 'help' => 'Laisser vide pour ne pas l’afficher.'],
                    'contact_whatsapp' => ['label' => 'WhatsApp', 'type' => 'tel', 'help' => 'Numéro au format international, ex. +33 6 12 34 56 78.'],
                    'notify_email' => ['label' => 'Recevoir les nouveaux messages sur', 'type' => 'email', 'help' => 'Par défaut : l’email affiché sur le site.'],
                    'company_since' => ['label' => 'Année de création', 'type' => 'text', 'help' => 'Affichée dans « À propos » si renseignée.'],
                    'service_areas' => ['label' => 'Communes desservies', 'type' => 'text', 'help' => 'Séparées par des virgules. Affichées sur chaque page de service (référencement local).'],
                ],
            ],
            'social' => [
                'title' => 'Réseaux sociaux',
                'fields' => [
                    'social_facebook' => ['label' => 'Facebook', 'type' => 'url'],
                    'social_instagram' => ['label' => 'Instagram', 'type' => 'url'],
                    'social_linkedin' => ['label' => 'LinkedIn', 'type' => 'url'],
                    'social_x' => ['label' => 'X (Twitter)', 'type' => 'url'],
                ],
            ],
            'images' => [
                'title' => 'Images du site',
                'images' => [
                    'img_hero' => ['label' => 'Photo de l’accroche (page d’accueil)', 'help' => 'Paysage, 1920 px de large conseillé. Assombrie automatiquement pour la lisibilité du texte.', 'width' => 1920],
                    'img_logo' => ['label' => 'Logo icône — fonds clairs', 'help' => 'Ailes + globe, fond transparent. En-tête après défilement.', 'width' => 600],
                    'img_logo_light' => ['label' => 'Logo icône — fonds sombres', 'help' => 'Version claire, fond transparent. En-tête sur la photo, menu mobile, écran de chargement.', 'width' => 600],
                    'img_logo_full_light' => ['label' => 'Logo complet — fonds sombres', 'help' => 'Avec « Heaven of Web ». Pied de page.', 'width' => 800],
                    'img_about' => ['label' => 'Image « À propos »', 'help' => 'Format carré conseillé.', 'width' => 1440],
                    'img_og' => ['label' => 'Image de partage (réseaux sociaux)', 'help' => '1200 × 630 px. Affichée quand un lien du site est partagé.', 'width' => 1200],
                ],
            ],
            'texts' => [
                'title' => 'Textes du site',
                'translated' => [
                    'company_city' => ['label' => 'Ville de l’agence', 'type' => 'text', 'help' => 'Adresse affichée, pied de page, données pour Google.'],
                    'company_country' => ['label' => 'Pays', 'type' => 'text'],
                    'service_region' => ['label' => 'Région desservie (pages services)', 'type' => 'text', 'help' => 'S’insère dans « nous intervenons dans tout le … ». Ex. : Sahel tunisien.'],
                    'hero_eyebrow' => ['label' => 'Petit titre au-dessus de l’accroche', 'type' => 'text'],
                    'hero_title' => ['label' => 'Accroche principale', 'type' => 'text'],
                    'hero_subtitle' => ['label' => 'Sous-titre de l’accroche', 'type' => 'textarea', 'rows' => 2],
                    'highlights' => ['label' => 'Points forts sous l’accroche (une ligne : icône|texte)', 'type' => 'textarea', 'rows' => 4, 'help' => 'Icônes possibles : rocket, shield, chart, headset, browser, pen, search, bolt, heart, award, users.'],
                    'stats' => ['label' => 'Chiffres clés « À propos » (une ligne : valeur|libellé)', 'type' => 'textarea', 'rows' => 3, 'help' => '{projects} est remplacé par le nombre de réalisations publiées. Ex. : +5|Années d’expérience'],
                    'process_title' => ['label' => 'Processus — titre', 'type' => 'text'],
                    'process_intro' => ['label' => 'Processus — introduction', 'type' => 'textarea', 'rows' => 2],
                    'process' => ['label' => 'Processus — étapes (une ligne : Titre : description)', 'type' => 'textarea', 'rows' => 5],
                    'about_title' => ['label' => 'À propos — titre', 'type' => 'text'],
                    'about_text' => ['label' => 'À propos — texte', 'type' => 'markdown', 'rows' => 6],
                    'about_points' => ['label' => 'À propos — points clés (un par ligne)', 'type' => 'textarea', 'rows' => 3],
                    'services_title' => ['label' => 'Services — titre', 'type' => 'text'],
                    'services_intro' => ['label' => 'Services — introduction', 'type' => 'textarea', 'rows' => 2],
                    'why_title' => ['label' => 'Engagements — titre', 'type' => 'text'],
                    'why_intro' => ['label' => 'Engagements — introduction', 'type' => 'textarea', 'rows' => 2],
                    'projects_title' => ['label' => 'Réalisations — titre', 'type' => 'text'],
                    'projects_intro' => ['label' => 'Réalisations — introduction', 'type' => 'textarea', 'rows' => 2],
                    'reviews_title' => ['label' => 'Avis — titre', 'type' => 'text'],
                    'contact_title' => ['label' => 'Contact — titre', 'type' => 'text'],
                    'contact_intro' => ['label' => 'Contact — introduction', 'type' => 'textarea', 'rows' => 2],
                ],
            ],
            'seo' => [
                'title' => 'Référencement',
                'translated' => [
                    'meta_title' => ['label' => 'Titre de la page d’accueil (Google)', 'type' => 'text', 'help' => '60 caractères environ.'],
                    'meta_description' => ['label' => 'Description (Google)', 'type' => 'textarea', 'rows' => 3, 'help' => '155 caractères environ.'],
                ],
            ],
            'legal' => [
                'title' => 'Pages légales',
                'translated' => [
                    'legal_notice' => ['label' => 'Mentions légales', 'type' => 'markdown', 'rows' => 14],
                    'privacy' => ['label' => 'Politique de confidentialité', 'type' => 'markdown', 'rows' => 14],
                ],
            ],
        ];
    }

    public function index(): void
    {
        $tab = array_key_exists((string) input('tab'), self::groups()) ? (string) input('tab') : 'contact';
        $this->render('settings', ['pageTitle' => 'Contenus du site', 'groups' => self::groups(), 'tab' => $tab, 'errors' => flash('form_errors')]);
    }

    public function save(): void
    {
        $tab = (string) input('tab');
        $group = self::groups()[$tab] ?? null;
        if (!$group) $this->back('/admin/settings', 'Section inconnue.', 'error');
        $back = '/admin/settings?tab=' . $tab;
        $errors = [];

        foreach ($group['fields'] ?? [] as $key => $f) {
            $v = trim((string) ($_POST[$key] ?? ''));
            if ($v !== '' && $f['type'] === 'email' && !filter_var($v, FILTER_VALIDATE_EMAIL)) { $errors[] = "{$f['label']} : email invalide."; continue; }
            if ($v !== '' && $f['type'] === 'url' && !filter_var($v, FILTER_VALIDATE_URL)) { $errors[] = "{$f['label']} : adresse invalide (https://…)."; continue; }
            Settings::set($key, $v);
        }
        foreach ($group['translated'] ?? [] as $key => $f) {
            foreach (I18n::LANGS as $lang) {
                Settings::set($key, trim((string) ($_POST['tr'][$lang][$key] ?? '')), $lang);
            }
        }
        foreach ($group['images'] ?? [] as $key => $f) {
            $file = $_FILES[$key] ?? null;
            if (!$file || $file['error'] === UPLOAD_ERR_NO_FILE) continue;
            try {
                $path = Upload::image($file, 'site', $f['width'], in_array($key, ['img_about', 'img_hero'], true));
                $old = Settings::get($key);
                // Social networks don't all read WebP: keep a PNG copy of the share image.
                if ($key === 'img_og') $path = self::pngCopy($path);
                Settings::set($key, $path);
                Upload::delete($old);
            } catch (\RuntimeException $e) {
                $errors[] = "{$f['label']} : " . $e->getMessage();
            }
        }

        // The legal pages show their "last updated" date.
        if ($tab === 'legal') Settings::set('legal_updated', date('Y-m-d'));

        if ($errors) {
            flash('form_errors', implode("\n", $errors));
            $this->back($back, 'Certaines valeurs n’ont pas été enregistrées.', 'error');
        }
        $this->back($back, 'Modifications enregistrées.');
    }

    private static function pngCopy(string $webpPath): string
    {
        $img = imagecreatefromwebp(PUBLIC_PATH . '/' . $webpPath);
        $png = preg_replace('/\.webp$/', '.png', $webpPath);
        imagepng($img, PUBLIC_PATH . '/' . $png, 8);
        imagedestroy($img);
        // The WebP stays next to it; the PNG path is stored because some networks (LinkedIn) ignore WebP previews.
        return $png;
    }
}
