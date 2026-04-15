<?php /** @var int $current */ ?>
<div class="step-progress d-flex" role="navigation" aria-label="Wizard progress">
  <?php foreach (['Base ID','Choose AIs','Fill data','Review','Export'] as $i => $label):
    $idx = $i + 1;
    $cls = $idx < $current ? 'step done' : ($idx === $current ? 'step active' : 'step');
  ?>
    <div class="<?= $cls ?>" aria-current="<?= $idx === $current ? 'step' : 'false' ?>">
      <strong><?= $idx ?>.</strong> <?= htmlspecialchars($label) ?>
    </div>
  <?php endforeach; ?>
</div>
