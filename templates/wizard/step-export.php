<?php $current = 5; include __DIR__ . '/_progress.php'; ?>

<div class="row justify-content-center">
  <div class="col-lg-8">
    <h1 class="h3 mb-3">Download your barcode</h1>

    <div class="card mb-4">
      <div class="card-body text-center">
        <canvas id="final-preview" aria-label="Final barcode"></canvas>
        <div class="mt-2"><code id="final-hri" class="machine-string"></code></div>
      </div>
    </div>

    <p class="text-muted">Choose a format. PNG is best for screen sharing, SVG for crisp print at any size, PDF includes a label-friendly layout.</p>

    <div class="d-grid gap-2 d-md-flex">
      <button id="dl-png" class="btn btn-primary">Download PNG</button>
      <button id="dl-svg" class="btn btn-outline-primary">Download SVG</button>
      <button id="dl-pdf" class="btn btn-outline-primary">Download PDF</button>
    </div>

    <div class="d-flex justify-content-between mt-4">
      <a href="<?= base_url() ?>wizard/review" class="btn btn-link">&laquo; Back to review</a>
      <a href="<?= base_url() ?>" class="btn btn-link">Start a new barcode</a>
    </div>
  </div>
</div>

<script>
(function () {
  const state = Wizard.load();
  if (!state.gtin14 || !state.resolved) { window.location.href = '<?= base_url() ?>wizard/input'; return; }

  document.getElementById('final-hri').textContent = state.hri || '';
  BarcodePreview.render(document.getElementById('final-preview'), state.hri || '');

  function trigger(format) {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '<?= base_url() ?>api/generate';
    form.target = '_blank';
    const f = (name, val) => { const i = document.createElement('input'); i.type='hidden'; i.name=name; i.value=val; form.appendChild(i); };
    f('format', format);
    f('payload', JSON.stringify({ resolved: state.resolved, hri: state.hri }));
    document.body.appendChild(form); form.submit(); form.remove();
  }

  document.getElementById('dl-png').addEventListener('click', () => trigger('png'));
  document.getElementById('dl-svg').addEventListener('click', () => trigger('svg'));
  document.getElementById('dl-pdf').addEventListener('click', () => trigger('pdf'));
})();
</script>
