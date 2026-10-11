<?php
use App\Core\I18n;
use App\Core\Settings;

$group = $groups[$tab];
?>
<nav class="tabs tabs-scroll" aria-label="Sections">
  <?php foreach ($groups as $key => $g): ?>
  <a href="/admin/settings?tab=<?= $key ?>" <?= $key === $tab ? 'aria-current="page"' : '' ?>><?= e($g['title']) ?></a>
  <?php endforeach; ?>
</nav>

<?php if ($errors): ?><div class="alert is-error" role="alert"><?= nl2br(e($errors)) ?></div><?php endif; ?>

<form method="post" action="/admin/settings" enctype="multipart/form-data" class="editor" data-editor>
  <?= csrf_field() ?>
  <input type="hidden" name="tab" value="<?= e($tab) ?>">

  <?php if (!empty($group['fields'])): ?>
  <section class="card">
    <div class="form-grid">
      <?php foreach ($group['fields'] as $key => $f): ?>
      <div class="field">
        <label for="s-<?= $key ?>"><?= e($f['label']) ?></label>
        <input id="s-<?= $key ?>" type="<?= e($f['type']) ?>" name="<?= $key ?>" value="<?= e(Settings::raw($key)) ?>" <?= $f['type'] === 'url' ? 'placeholder="https://"' : '' ?>>
        <?php if (!empty($f['help'])): ?><p class="help"><?= e($f['help']) ?></p><?php endif; ?>
      </div>
      <?php endforeach; ?>
    </div>
  </section>
  <?php endif; ?>

  <?php if (!empty($group['images'])): ?>
  <div class="grid-3">
    <?php foreach ($group['images'] as $key => $f): $current = Settings::raw($key); ?>
    <section class="card">
      <h2><?= e($f['label']) ?></h2>
      <div class="image-field">
        <div class="image-preview <?= in_array($key, ['img_logo_light', 'img_logo_full_light'], true) ? 'is-contain' : '' ?>" id="preview-<?= $key ?>">
          <?php if ($current): ?><img src="<?= e(media($current)) ?>" alt=""><?php else: ?><span class="muted">Aucune image</span><?php endif; ?>
        </div>
        <label class="btn btn-ghost btn-sm file-btn"><?= icon('upload') ?>Remplacer l’image
          <input type="file" name="<?= $key ?>" accept="image/jpeg,image/png,image/webp" data-preview="preview-<?= $key ?>">
        </label>
        <p class="help"><?= e($f['help']) ?></p>
      </div>
    </section>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

  <?php if (!empty($group['translated'])): ?>
  <section class="card">
    <header class="card-head">
      <h2><?= e($group['title']) ?></h2>
      <div class="lang-tabs" role="tablist" aria-label="Langue">
        <?php foreach (I18n::LANGS as $i => $l): ?>
        <button type="button" role="tab" id="tab-<?= $l ?>" aria-controls="panel-<?= $l ?>" aria-selected="<?= $i === 0 ? 'true' : 'false' ?>" data-lang-tab="<?= $l ?>"><?= strtoupper($l) ?></button>
        <?php endforeach; ?>
      </div>
    </header>
    <?php foreach (I18n::LANGS as $i => $l): ?>
    <div class="lang-panel" role="tabpanel" id="panel-<?= $l ?>" aria-labelledby="tab-<?= $l ?>" <?= $i === 0 ? '' : 'hidden' ?>>
      <?php if ($l !== 'fr'): ?><p class="help help-top">Champ vide = le texte français est affiché.</p><?php endif; ?>
      <?php foreach ($group['translated'] as $key => $f): $fid = "s-{$l}-{$key}"; $value = Settings::raw($key, $l); ?>
      <div class="field">
        <label for="<?= $fid ?>"><?= e($f['label']) ?></label>
        <?php if ($f['type'] === 'text'): ?>
          <input id="<?= $fid ?>" type="text" name="tr[<?= $l ?>][<?= $key ?>]" value="<?= e($value) ?>" dir="<?= $l === 'ar' ? 'rtl' : 'ltr' ?>" lang="<?= $l ?>" <?= $l !== 'fr' ? 'placeholder="' . e(Settings::raw($key, 'fr')) . '"' : '' ?>>
        <?php else: ?>
          <textarea id="<?= $fid ?>" name="tr[<?= $l ?>][<?= $key ?>]" rows="<?= (int) ($f['rows'] ?? 3) ?>" dir="<?= $l === 'ar' ? 'rtl' : 'ltr' ?>" lang="<?= $l ?>"><?= e($value) ?></textarea>
        <?php endif; ?>
        <?php if ($f['type'] === 'markdown'): ?><p class="help">Mise en forme : **gras**, *italique*, ## Titre, - liste, [lien](https://…).</p>
        <?php elseif (!empty($f['help'])): ?><p class="help"><?= e($f['help']) ?></p><?php endif; ?>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endforeach; ?>
  </section>
  <?php endif; ?>

  <div class="savebar">
    <span class="savebar-spacer"></span>
    <button class="btn btn-primary" type="submit"><?= icon('check') ?>Enregistrer</button>
  </div>
</form>
