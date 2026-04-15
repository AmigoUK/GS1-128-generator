<?php
$current = 2; include __DIR__ . '/_progress.php';
$defs = ai_definitions();

$groups = [];
foreach ($defs as $code => $def) {
    if (in_array($code, ['01', '02'], true)) continue;
    $groups[$def['group']][$code] = $def;
}
$preselect = ['10', '15'];
?>

<h1 class="h3 mb-3">What data do you want to add?</h1>
<p class="text-muted">Pick the Application Identifiers to encode alongside the GTIN. Toggle as many as you need.</p>

<form id="select-ai-form">
<?php foreach ($groups as $group => $items): ?>
  <h2 class="h6 text-uppercase text-muted mt-4 mb-2"><?= htmlspecialchars($group) ?></h2>
  <div class="row g-3">
    <?php foreach ($items as $code => $def):
      $checked = in_array($code, $preselect, true) ? 'checked' : '';
    ?>
    <div class="col-md-6">
      <label class="card ai-card h-100">
        <div class="card-body py-3">
          <div class="form-check d-flex align-items-start gap-2">
            <input class="form-check-input mt-1 ai-toggle" type="checkbox" name="ai[]" value="<?= htmlspecialchars($code) ?>" id="ai-<?= htmlspecialchars($code) ?>" <?= $checked ?>>
            <div>
              <div class="fw-semibold">(<?= htmlspecialchars($code) ?>) <?= htmlspecialchars($def['name']) ?></div>
              <div class="small text-muted"><?= htmlspecialchars($def['description']) ?></div>
              <div class="small text-muted"><em>e.g. <?= htmlspecialchars($def['example']) ?></em></div>
            </div>
          </div>
        </div>
      </label>
    </div>
    <?php endforeach; ?>
  </div>
<?php endforeach; ?>

  <div class="d-flex justify-content-between mt-4">
    <a href="<?= base_url() ?>wizard/input" class="btn btn-link">&laquo; Back</a>
    <button type="submit" class="btn btn-primary">Next: Fill in data &raquo;</button>
  </div>
</form>

<script>
(function () {
  const form = document.getElementById('select-ai-form');
  const saved = Wizard.load();
  if (!saved.gtin14) { window.location.href = '<?= base_url() ?>wizard/input'; return; }

  function syncCards() {
    document.querySelectorAll('.ai-toggle').forEach(cb => {
      cb.closest('.ai-card').classList.toggle('selected', cb.checked);
    });
  }
  document.querySelectorAll('.ai-toggle').forEach(cb => cb.addEventListener('change', syncCards));

  if (Array.isArray(saved.selected_ais) && saved.selected_ais.length) {
    document.querySelectorAll('.ai-toggle').forEach(cb => cb.checked = saved.selected_ais.includes(cb.value));
  }
  syncCards();

  form.addEventListener('submit', (e) => {
    e.preventDefault();
    const selected = Array.from(document.querySelectorAll('.ai-toggle:checked')).map(cb => cb.value);
    Wizard.patch({ selected_ais: selected, ai_values: Wizard.load().ai_values || {} });
    if (selected.length === 0) {
      window.location.href = '<?= base_url() ?>wizard/review';
    } else {
      Wizard.patch({ ai_cursor: 0 });
      window.location.href = '<?= base_url() ?>wizard/ai-data';
    }
  });
})();
</script>
