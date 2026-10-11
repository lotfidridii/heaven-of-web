<?php
/** @var array $steps [['title' => …, 'text' => …]] */
$icons = ['chat', 'pen', 'code', 'rocket', 'check', 'search', 'bolt'];
?>
<ol class="process" data-anim="process">
  <?php foreach ($steps as $i => $step): ?>
  <li class="process-step">
    <span class="process-icon<?= $i % 2 ? ' is-navy' : '' ?>"><?= icon($icons[$i % count($icons)]) ?></span>
    <h3><span class="process-num"><?= str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT) ?>.</span> <?= e($step['title']) ?></h3>
    <?php if ($step['text'] !== ''): ?><p><?= e($step['text']) ?></p><?php endif; ?>
  </li>
  <?php endforeach; ?>
</ol>
