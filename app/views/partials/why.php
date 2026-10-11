<ul class="why-grid" data-anim="stagger">
  <?php foreach ($features as $f): ?>
  <li class="why-card">
    <span class="icon-bubble"><?= icon($f['icon']) ?></span>
    <h3><?= e($f['title'] ?? '') ?></h3>
    <p><?= e($f['text'] ?? '') ?></p>
  </li>
  <?php endforeach; ?>
</ul>
