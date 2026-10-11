<?php
use App\Controllers\Admin\ContentController;
use App\Core\I18n;

$errors = flash('form_errors');
$val = static fn(string $name, $default = '') => $row[$name] ?? $default;
$action = '/admin/' . $type . '/' . ($id ?: 'new');
?>
<div class="toolbar">
  <a class="back" href="/admin/<?= $type ?>"><?= icon('arrow-left') ?><?= e($c['title']) ?></a>
  <?php if ($type === 'projects' && $id && !empty($row['slug'])): ?>
  <a class="btn btn-ghost btn-sm" href="/fr/realisations/<?= e($row['slug']) ?>" target="_blank" rel="noopener"><?= icon('external') ?>Voir sur le site</a>
  <?php endif; ?>
</div>

<?php if ($errors): ?>
<div class="alert is-error" role="alert"><strong>Le formulaire n’a pas été enregistré :</strong><br><?= nl2br(e($errors)) ?></div>
<?php endif; ?>

<form method="post" action="<?= $action ?>" enctype="multipart/form-data" class="editor" data-editor>
  <?= csrf_field() ?>
  <div class="grid-2 grid-wide-left">
    <div class="stack-lg">
      <section class="card">
        <header class="card-head">
          <h2>Textes</h2>
          <div class="lang-tabs" role="tablist" aria-label="Langue">
            <?php foreach (I18n::LANGS as $i => $l): ?>
            <button type="button" role="tab" id="tab-<?= $l ?>" aria-controls="panel-<?= $l ?>" aria-selected="<?= $i === 0 ? 'true' : 'false' ?>" data-lang-tab="<?= $l ?>">
              <?= strtoupper($l) ?><?php
                $filled = array_filter($tr[$l] ?? [], fn($v) => trim((string) $v) !== '');
                if ($l !== 'fr' && !$filled): ?><span class="dot" title="Pas encore traduit — le texte français sera affiché"></span><?php endif; ?>
            </button>
            <?php endforeach; ?>
          </div>
        </header>
        <?php foreach (I18n::LANGS as $i => $l): ?>
        <div class="lang-panel" role="tabpanel" id="panel-<?= $l ?>" aria-labelledby="tab-<?= $l ?>" <?= $i === 0 ? '' : 'hidden' ?>>
          <?php if ($l !== 'fr'): ?><p class="help help-top">Laissez un champ vide pour afficher le texte français à la place.</p><?php endif; ?>
          <?php foreach ($c['translated'] as $name => $f): $fid = "tr-{$l}-{$name}"; $value = $tr[$l][$name] ?? ''; ?>
          <div class="field">
            <label for="<?= $fid ?>"><?= e($f['label']) ?><?= !empty($f['required']) && $l === 'fr' ? ' <span class="req" aria-hidden="true">*</span>' : '' ?></label>
            <?php if (in_array($f['type'], ['textarea', 'markdown'], true)): ?>
              <textarea id="<?= $fid ?>" name="tr[<?= $l ?>][<?= $name ?>]" rows="<?= (int) ($f['rows'] ?? 4) ?>" dir="<?= $l === 'ar' ? 'rtl' : 'ltr' ?>" lang="<?= $l ?>" <?= !empty($f['required']) && $l === 'fr' ? 'required' : '' ?>><?= e($value) ?></textarea>
            <?php else: ?>
              <input id="<?= $fid ?>" type="text" name="tr[<?= $l ?>][<?= $name ?>]" value="<?= e($value) ?>" dir="<?= $l === 'ar' ? 'rtl' : 'ltr' ?>" lang="<?= $l ?>" <?= !empty($f['datalist']) ? 'list="dl-' . $name . '"' : '' ?> <?= !empty($f['required']) && $l === 'fr' ? 'required' : '' ?>>
            <?php endif; ?>
            <?php if ($f['type'] === 'markdown'): ?><p class="help">Mise en forme : **gras**, *italique*, ## Titre, - liste, [lien](https://…). Ligne vide = nouveau paragraphe.</p>
            <?php elseif (!empty($f['help']) && $l === 'fr'): ?><p class="help"><?= e($f['help']) ?></p><?php endif; ?>
          </div>
          <?php endforeach; ?>
        </div>
        <?php endforeach; ?>
        <?php if (!empty($categories)): ?>
        <datalist id="dl-category"><?php foreach ($categories as $cat): ?><option value="<?= e($cat) ?>"><?php endforeach; ?></datalist>
        <?php endif; ?>
      </section>

      <?php if (!empty($c['gallery'])): ?>
      <section class="card">
        <h2>Galerie</h2>
        <p class="help help-top">Captures supplémentaires affichées sur la page du projet.</p>
        <?php if ($images): ?>
        <ul class="gallery-admin">
          <?php foreach ($images as $img): ?>
          <li>
            <img src="<?= e(media($img['path'], 480)) ?>" alt="" loading="lazy">
            <button class="icon-btn icon-btn-danger" type="submit" form="del-img-<?= (int) $img['id'] ?>" title="Retirer cette image"><?= icon('trash') ?><span class="sr-only">Retirer</span></button>
          </li>
          <?php endforeach; ?>
        </ul>
        <?php endif; ?>
        <div class="field">
          <label class="dropzone" for="gallery">
            <?= icon('upload') ?><span>Ajouter des images</span><small>Plusieurs fichiers possibles · JPG, PNG, WebP</small>
            <input id="gallery" name="gallery[]" type="file" accept="image/jpeg,image/png,image/webp,image/gif" multiple data-preview="gallery-preview">
          </label>
          <div class="preview-strip" id="gallery-preview"></div>
        </div>
      </section>
      <?php endif; ?>
    </div>

    <aside class="stack-lg">
      <section class="card">
        <h2>Réglages</h2>
        <?php foreach ($c['fields'] as $name => $f): $fid = 'f-' . $name; $value = $val($name, $f['default'] ?? ''); ?>
        <div class="field <?= $f['type'] === 'checkbox' ? 'field-check' : '' ?>">
          <?php if ($f['type'] === 'checkbox'): ?>
            <input id="<?= $fid ?>" type="checkbox" name="<?= $name ?>" value="1" <?= $value ? 'checked' : '' ?>>
            <label for="<?= $fid ?>"><?= e($f['label']) ?></label>
          <?php else: ?>
            <label for="<?= $fid ?>"><?= e($f['label']) ?><?= !empty($f['required']) ? ' <span class="req" aria-hidden="true">*</span>' : '' ?></label>
            <?php if ($f['type'] === 'rating'): ?>
              <select id="<?= $fid ?>" name="<?= $name ?>">
                <?php for ($n = 5; $n >= 1; $n--): ?><option value="<?= $n ?>" <?= (int) $value === $n ? 'selected' : '' ?>><?= str_repeat('★', $n) . str_repeat('☆', 5 - $n) ?> (<?= $n ?>/5)</option><?php endfor; ?>
              </select>
            <?php elseif ($f['type'] === 'project'): ?>
              <select id="<?= $fid ?>" name="<?= $name ?>">
                <option value="">Aucun</option>
                <?php foreach ($projects as $p): ?><option value="<?= (int) $p['id'] ?>" <?= (int) $value === (int) $p['id'] ? 'selected' : '' ?>><?= e($p['client']) ?> — <?= e($p['slug']) ?></option><?php endforeach; ?>
              </select>
            <?php elseif ($f['type'] === 'month'): ?>
              <input id="<?= $fid ?>" type="month" name="<?= $name ?>" value="<?= e(substr((string) $value, 0, 7)) ?>">
            <?php elseif ($f['type'] === 'lines'): ?>
              <textarea id="<?= $fid ?>" name="<?= $name ?>" rows="<?= (int) ($f['rows'] ?? 4) ?>" dir="ltr"><?= e((string) $value) ?></textarea>
            <?php elseif ($f['type'] === 'service'): ?>
              <select id="<?= $fid ?>" name="<?= $name ?>">
                <option value="">Aucun</option>
                <?php foreach ($services ?? [] as $s): ?><option value="<?= (int) $s['id'] ?>" <?= (int) $value === (int) $s['id'] ? 'selected' : '' ?>><?= e($s['title'] ?? ('Service ' . $s['id'])) ?></option><?php endforeach; ?>
              </select>
            <?php elseif ($f['type'] === 'palette'):
              $colors = is_array($value) ? $value : (json_decode((string) $value, true) ?: []); ?>
              <div class="palette-editor" data-palette-editor>
                <ul data-palette-rows>
                  <?php foreach ($colors as $c): ?>
                  <li class="palette-row">
                    <input type="color" value="<?= e($c['hex']) ?>" aria-label="Couleur" data-swatch>
                    <input type="text" name="palette[hex][]" value="<?= e($c['hex']) ?>" maxlength="7" pattern="#?[0-9A-Fa-f]{6}" aria-label="Code hexadécimal" data-hex>
                    <input type="text" name="palette[name][]" value="<?= e($c['name'] ?? '') ?>" maxlength="40" placeholder="Nom (facultatif)" aria-label="Nom de la couleur">
                    <button class="icon-btn icon-btn-danger" type="button" data-palette-remove title="Retirer"><?= icon('trash') ?><span class="sr-only">Retirer</span></button>
                  </li>
                  <?php endforeach; ?>
                </ul>
                <template data-palette-template>
                  <li class="palette-row">
                    <input type="color" value="#FF4848" aria-label="Couleur" data-swatch>
                    <input type="text" name="palette[hex][]" value="#FF4848" maxlength="7" pattern="#?[0-9A-Fa-f]{6}" aria-label="Code hexadécimal" data-hex>
                    <input type="text" name="palette[name][]" value="" maxlength="40" placeholder="Nom (facultatif)" aria-label="Nom de la couleur">
                    <button class="icon-btn icon-btn-danger" type="button" data-palette-remove title="Retirer"><?= icon('trash') ?><span class="sr-only">Retirer</span></button>
                  </li>
                </template>
                <div class="palette-preview" data-palette-preview aria-hidden="true"></div>
                <button class="btn btn-ghost btn-sm" type="button" data-palette-add><?= icon('plus') ?>Ajouter une couleur</button>
              </div>
            <?php elseif ($f['type'] === 'icon'): ?>
              <div class="icon-picker" role="radiogroup" aria-label="<?= e($f['label']) ?>">
                <?php foreach (ContentController::ICONS as $ic): ?>
                <label title="<?= e($ic) ?>"><input type="radio" name="<?= $name ?>" value="<?= $ic ?>" <?= ($value ?: ContentController::ICONS[0]) === $ic ? 'checked' : '' ?>><span><?= icon($ic) ?><span class="sr-only"><?= e($ic) ?></span></span></label>
                <?php endforeach; ?>
              </div>
            <?php else: ?>
              <input id="<?= $fid ?>" type="<?= $f['type'] === 'slug' ? 'text' : e($f['type']) ?>" name="<?= $name ?>" value="<?= e((string) $value) ?>"
                <?= !empty($f['required']) ? 'required' : '' ?> <?= isset($f['min']) ? 'min="' . $f['min'] . '"' : '' ?> <?= isset($f['max']) ? 'max="' . $f['max'] . '"' : '' ?>
                <?= $f['type'] === 'slug' ? 'pattern="[a-z0-9-]*" placeholder="genere-automatiquement"' : '' ?> <?= $f['type'] === 'url' ? 'placeholder="https://"' : '' ?>>
            <?php endif; ?>
            <?php if (!empty($f['help'])): ?><p class="help"><?= e($f['help']) ?></p><?php endif; ?>
          <?php endif; ?>
        </div>
        <?php endforeach; ?>
      </section>

      <?php foreach ($c['images'] as $name => $f): $current = $val($name); ?>
      <section class="card">
        <h2><?= e($f['label']) ?><?= !empty($f['required']) ? ' <span class="req" aria-hidden="true">*</span>' : '' ?></h2>
        <div class="image-field">
          <div class="image-preview <?= $name === 'avatar' ? 'is-round' : '' ?>" id="preview-<?= $name ?>">
            <?php if ($current): ?><img src="<?= e(media($current, 960)) ?>" alt=""><?php else: ?><span class="muted">Aucune image</span><?php endif; ?>
          </div>
          <label class="btn btn-ghost btn-sm file-btn">
            <?= icon('upload') ?><?= $current ? 'Remplacer l’image' : 'Choisir une image' ?>
            <input type="file" name="<?= $name ?>" accept="image/jpeg,image/png,image/webp,image/gif" data-preview="preview-<?= $name ?>" <?= !empty($f['required']) && !$current ? 'required' : '' ?>>
          </label>
          <?php if ($current && empty($f['required'])): ?>
          <label class="field-check small"><input type="checkbox" name="remove_<?= $name ?>" value="1"> Retirer l’image</label>
          <?php endif; ?>
          <?php if (!empty($f['help'])): ?><p class="help"><?= e($f['help']) ?></p><?php endif; ?>
        </div>
      </section>
      <?php endforeach; ?>
    </aside>
  </div>

  <div class="savebar">
    <?php if ($id): ?>
    <button class="btn btn-danger-ghost btn-sm" type="submit" form="delete-form"><?= icon('trash') ?>Supprimer</button>
    <?php endif; ?>
    <span class="savebar-spacer"></span>
    <a class="btn btn-ghost" href="/admin/<?= $type ?>">Annuler</a>
    <button class="btn btn-ghost" type="submit" name="stay" value="1">Enregistrer et continuer</button>
    <button class="btn btn-primary" type="submit"><?= icon('check') ?>Enregistrer</button>
  </div>
</form>

<?php if ($id): ?>
<form id="delete-form" method="post" action="/admin/<?= $type ?>/<?= (int) $id ?>/delete" data-confirm="Supprimer définitivement cet élément ? Cette action est irréversible."><?= csrf_field() ?></form>
<?php endif; ?>
<?php foreach ($images ?? [] as $img): ?>
<form id="del-img-<?= (int) $img['id'] ?>" method="post" action="/admin/projects/images/<?= (int) $img['id'] ?>/delete" data-confirm="Retirer cette image de la galerie ?"><?= csrf_field() ?></form>
<?php endforeach; ?>
