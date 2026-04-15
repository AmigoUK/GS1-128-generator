<?php
$bulk = $_SESSION['bulk'] ?? null;
if (!$bulk || empty($bulk['parsed']['rows'])) {
    echo '<div class="alert alert-warning">No upload found. <a href="' . base_url() . 'bulk">Start again</a>.</div>';
    return;
}
$rows = $bulk['parsed']['rows'];
$total = count($rows);
$validCount = 0; $errorCount = 0;
foreach ($rows as $r) {
    if ($r['truncated']) continue;
    if (empty($r['errors'])) $validCount++; else $errorCount++;
}
$truncatedCount = max(0, $total - BULK_LIMIT);
?>

<h1 class="h3 mb-1">Validation report</h1>
<p class="text-muted mb-3">Uploaded <code><?= htmlspecialchars($bulk['filename']) ?></code> &mdash; <?= $total ?> row<?= $total === 1 ? '' : 's' ?>.</p>

<div class="row g-3 mb-4">
  <div class="col-sm-3"><div class="card"><div class="card-body py-2"><div class="text-muted small">Total rows</div><div class="h4 mb-0"><?= $total ?></div></div></div></div>
  <div class="col-sm-3"><div class="card border-success"><div class="card-body py-2"><div class="text-muted small">Valid</div><div class="h4 mb-0 text-success"><?= $validCount ?></div></div></div></div>
  <div class="col-sm-3"><div class="card border-danger"><div class="card-body py-2"><div class="text-muted small">Errors</div><div class="h4 mb-0 text-danger"><?= $errorCount ?></div></div></div></div>
  <div class="col-sm-3"><div class="card border-secondary"><div class="card-body py-2"><div class="text-muted small">Above demo limit</div><div class="h4 mb-0 text-secondary"><?= $truncatedCount ?></div></div></div></div>
</div>

<?php if ($truncatedCount > 0): ?>
  <div class="alert alert-secondary"><strong>Demo limit reached.</strong> Only the first <?= BULK_LIMIT ?> rows will be generated. <?= $truncatedCount ?> additional row<?= $truncatedCount === 1 ? '' : 's' ?> shown below for reference.</div>
<?php endif; ?>

<table class="table table-sm align-middle">
  <thead>
    <tr><th>#</th><th>GTIN / EAN</th><th>Status</th><th>Details</th></tr>
  </thead>
  <tbody>
  <?php foreach ($rows as $i => $row):
    $rowNum = $i + 1;
    $idValue = $row['raw']['gtin14'] ?? ($row['raw']['ean13'] ?? '—');
    $isLocked = $row['truncated'];
    $isError = !$isLocked && !empty($row['errors']);
    $isOk = !$isLocked && empty($row['errors']);
  ?>
    <tr class="<?= $isLocked ? 'row-locked' : '' ?>">
      <td><?= $rowNum ?><?= $isLocked ? ' <span aria-label="locked" title="Above demo limit">&#128274;</span>' : '' ?></td>
      <td><code><?= htmlspecialchars($idValue) ?></code></td>
      <td>
        <?php if ($isLocked): ?>
          <span class="badge bg-secondary">Above limit</span>
        <?php elseif ($isOk): ?>
          <span class="badge bg-success">Valid</span>
        <?php else: ?>
          <span class="badge bg-danger">Error</span>
        <?php endif; ?>
      </td>
      <td>
        <?php if ($isOk): ?>
          <code><?= htmlspecialchars(assemble_gs1($row['resolved'])['hri']) ?></code>
        <?php elseif ($isError): ?>
          <ul class="mb-0 small">
            <?php foreach ($row['errors'] as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?>
          </ul>
        <?php else: ?>
          <span class="text-muted small">Upgrade to process all rows.</span>
        <?php endif; ?>
      </td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>

<div class="d-flex justify-content-between mt-4">
  <a href="<?= base_url() ?>bulk" class="btn btn-link">&laquo; Upload a different file</a>
  <a href="<?= base_url() ?>bulk/results" class="btn btn-primary <?= $validCount === 0 ? 'disabled' : '' ?>">Generate <?= min($validCount, BULK_LIMIT) ?> barcode<?= min($validCount, BULK_LIMIT) === 1 ? '' : 's' ?> &raquo;</a>
</div>
