<?php
$bulk = $_SESSION['bulk'] ?? null;
if (!$bulk || empty($bulk['parsed']['rows'])) {
    echo '<div class="alert alert-warning">No upload found. <a href="' . base_url() . 'bulk">Start again</a>.</div>';
    return;
}
$rows = $bulk['parsed']['rows'];
$validRows = array_values(array_filter($rows, fn($r) => empty($r['errors']) && !$r['truncated']));
$genCount = min(count($validRows), BULK_LIMIT);
?>

<h1 class="h3 mb-3">Download your barcodes</h1>
<p class="text-muted">Generated <strong><?= $genCount ?></strong> barcode<?= $genCount === 1 ? '' : 's' ?> from <code><?= htmlspecialchars($bulk['filename']) ?></code>.</p>

<div class="row g-3">
  <div class="col-md-6">
    <div class="card h-100">
      <div class="card-body">
        <h2 class="h6">Individual images</h2>
        <p class="small text-muted">A ZIP archive of one barcode per file.</p>
        <div class="d-grid gap-2">
          <a href="<?= base_url() ?>bulk/download?format=zip-png" class="btn btn-outline-primary">ZIP of PNG</a>
          <a href="<?= base_url() ?>bulk/download?format=zip-svg" class="btn btn-outline-primary">ZIP of SVG</a>
        </div>
      </div>
    </div>
  </div>
  <div class="col-md-6">
    <div class="card h-100">
      <div class="card-body">
        <h2 class="h6">Print-ready</h2>
        <p class="small text-muted">A single PDF with all barcodes laid out for label printing.</p>
        <div class="d-grid gap-2">
          <a href="<?= base_url() ?>bulk/download?format=pdf" class="btn btn-outline-primary">PDF label sheet</a>
        </div>
      </div>
    </div>
  </div>
  <div class="col-12">
    <div class="card">
      <div class="card-body">
        <h2 class="h6">Enriched data</h2>
        <p class="small text-muted">Original CSV with an extra <code>gs1_128_string</code> column containing the generated barcode string for each row.</p>
        <a href="<?= base_url() ?>bulk/download?format=csv" class="btn btn-outline-secondary">Download enriched CSV</a>
      </div>
    </div>
  </div>
</div>

<div class="mt-4">
  <a href="<?= base_url() ?>bulk" class="btn btn-link">Start another bulk import &raquo;</a>
</div>
