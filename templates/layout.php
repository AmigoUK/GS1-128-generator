<?php /** @var string $title */ /** @var ?string $view */ /** @var array $meta */
$canonical = current_absolute_url();
?>
<!doctype html>
<html lang="en-GB">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= htmlspecialchars($title ?? APP_NAME) ?></title>
<meta name="description" content="<?= htmlspecialchars($meta['description'] ?? '') ?>">
<?php if (!empty($meta['noindex'])): ?>
<meta name="robots" content="noindex,follow">
<?php else: ?>
<meta name="robots" content="index,follow,max-image-preview:large">
<?php endif; ?>
<meta name="author" content="Tomasz Lewandowski">
<meta name="theme-color" content="#0f2747">
<link rel="canonical" href="<?= htmlspecialchars($canonical) ?>">

<!-- Open Graph -->
<meta property="og:type" content="website">
<meta property="og:site_name" content="<?= htmlspecialchars(APP_NAME) ?>">
<meta property="og:title" content="<?= htmlspecialchars($title) ?>">
<meta property="og:description" content="<?= htmlspecialchars($meta['description'] ?? '') ?>">
<meta property="og:url" content="<?= htmlspecialchars($canonical) ?>">
<meta property="og:image" content="<?= htmlspecialchars(og_image_url()) ?>">
<meta property="og:locale" content="en_GB">

<!-- Twitter Card -->
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:site" content="@attvuk">
<meta name="twitter:title" content="<?= htmlspecialchars($title) ?>">
<meta name="twitter:description" content="<?= htmlspecialchars($meta['description'] ?? '') ?>">
<meta name="twitter:image" content="<?= htmlspecialchars(og_image_url()) ?>">

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
<link rel="stylesheet" href="<?= base_url() ?>assets/css/app.css">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous" defer></script>
<script src="https://cdn.jsdelivr.net/npm/bwip-js@4.5.1/dist/bwip-js-min.js" defer></script>
<script src="<?= base_url() ?>assets/js/validation.js" defer></script>
<script src="<?= base_url() ?>assets/js/barcode-preview.js" defer></script>
<script src="<?= base_url() ?>assets/js/wizard.js" defer></script>
<script src="<?= base_url() ?>assets/js/bulk-import.js" defer></script>

<!-- Schema.org SoftwareApplication (site-wide) -->
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "SoftwareApplication",
  "name": "GS1-128 Barcode Generator",
  "url": "<?= htmlspecialchars(absolute_base_url()) ?>",
  "applicationCategory": "BusinessApplication",
  "operatingSystem": "Web",
  "offers": {"@type": "Offer", "price": "0", "priceCurrency": "GBP"},
  "description": "Free online GS1-128 (EAN-128) barcode generator with guided wizard and bulk CSV/XML import. Validates EAN-13 / GTIN-14 check digits and assembles standards-compliant supply-chain barcodes.",
  "author": {"@type": "Person", "name": "Tomasz Lewandowski", "url": "https://attv.uk"},
  "codeRepository": "https://github.com/AmigoUK/GS1-128-generator",
  "programmingLanguage": "PHP"
}
</script>
</head>
<body>
<nav class="navbar navbar-expand-md navbar-dark bg-dark mb-4">
  <div class="container">
    <a class="navbar-brand" href="<?= base_url() ?>"><strong>GS1-128</strong> Generator</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav" aria-controls="nav" aria-expanded="false" aria-label="Toggle navigation"><span class="navbar-toggler-icon"></span></button>
    <div class="collapse navbar-collapse" id="nav">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item"><a class="nav-link" href="<?= base_url() ?>wizard">Create a barcode</a></li>
        <li class="nav-item"><a class="nav-link" href="<?= base_url() ?>bulk">Bulk import</a></li>
      </ul>
    </div>
  </div>
</nav>

<main class="container">
<?php if ($view && file_exists($view)) { include $view; } else { ?>
  <div class="alert alert-warning"><h1 class="h4">Page not found</h1><p>Try the <a href="<?= base_url() ?>">home page</a>.</p></div>
<?php } ?>
</main>

<footer class="container my-5 small text-muted">
  <div class="row gy-3 align-items-start">

    <div class="col-md-4 text-center text-md-start">
      <h2 class="h6 text-uppercase text-muted mb-2" style="letter-spacing:.05em;font-size:.75rem;">More projects</h2>
      <ul class="list-unstyled mb-0">
        <li><a href="https://attv.uk/projects.html" target="_blank" rel="noopener">Browse all projects on attv.uk</a></li>
        <li><a href="https://attv.uk/projects/flyingplan.html" target="_blank" rel="noopener">FlyingPlan &mdash; drone flight management</a></li>
        <li><a href="https://attv.uk/projects/cmms.html" target="_blank" rel="noopener">CMMS &mdash; multi-site maintenance system</a></li>
        <li><a href="https://attv.uk/projects/nextstep-crm.html" target="_blank" rel="noopener">NextStep CRM &mdash; lightweight CRM for small teams</a></li>
      </ul>
    </div>

    <div class="col-md-4 text-center">
      <h2 class="h6 text-uppercase text-muted mb-2" style="letter-spacing:.05em;font-size:.75rem;">Follow Tomasz</h2>
      <p class="mb-2">Computer Science student building practical digital tools.</p>
      <p class="mb-0">
        <a href="https://www.linkedin.com/in/attvuk/" target="_blank" rel="noopener" class="me-2" aria-label="LinkedIn">
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16" aria-hidden="true"><path d="M0 1.146C0 .513.526 0 1.175 0h13.65C15.474 0 16 .513 16 1.146v13.708c0 .633-.526 1.146-1.175 1.146H1.175C.526 16 0 15.487 0 14.854V1.146zm4.943 12.248V6.169H2.542v7.225h2.401zm-1.2-8.212c.837 0 1.358-.554 1.358-1.248-.015-.709-.52-1.248-1.342-1.248S2.4 3.226 2.4 3.934c0 .694.521 1.248 1.327 1.248h.016zm4.908 8.212V9.359c0-.216.016-.432.08-.586.173-.431.568-.878 1.232-.878.869 0 1.216.662 1.216 1.634v3.865h2.401V9.25c0-2.22-1.184-3.252-2.764-3.252-1.274 0-1.845.7-2.165 1.193v.025h-.016a5.54 5.54 0 0 1 .016-.025V6.169h-2.4c.03.678 0 7.225 0 7.225h2.4z"/></svg>
          LinkedIn
        </a>
        &middot;
        <a href="https://github.com/AmigoUK" target="_blank" rel="noopener" class="ms-2" aria-label="GitHub">
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16" aria-hidden="true"><path d="M8 0C3.58 0 0 3.58 0 8c0 3.54 2.29 6.53 5.47 7.59.4.07.55-.17.55-.38 0-.19-.01-.82-.01-1.49-2.01.37-2.53-.49-2.69-.94-.09-.23-.48-.94-.82-1.13-.28-.15-.68-.52-.01-.53.63-.01 1.08.58 1.23.82.72 1.21 1.87.87 2.33.66.07-.52.28-.87.51-1.07-1.78-.2-3.64-.89-3.64-3.95 0-.87.31-1.59.82-2.15-.08-.2-.36-1.02.08-2.12 0 0 .67-.21 2.2.82.64-.18 1.32-.27 2-.27.68 0 1.36.09 2 .27 1.53-1.04 2.2-.82 2.2-.82.44 1.1.16 1.92.08 2.12.51.56.82 1.27.82 2.15 0 3.07-1.87 3.75-3.65 3.95.29.25.54.73.54 1.48 0 1.07-.01 1.93-.01 2.2 0 .21.15.46.55.38A8.012 8.012 0 0 0 16 8c0-4.42-3.58-8-8-8z"/></svg>
          GitHub
        </a>
      </p>
    </div>

    <div class="col-md-4 text-center text-md-end">
      <h2 class="h6 text-uppercase text-muted mb-2" style="letter-spacing:.05em;font-size:.75rem;">This tool</h2>
      <p class="mb-1"><strong><?= htmlspecialchars(APP_NAME) ?></strong> v<?= htmlspecialchars(APP_VERSION) ?></p>
      <p class="mb-0">
        <a href="https://github.com/AmigoUK/GS1-128-generator" target="_blank" rel="noopener">Source on GitHub</a><br>
        <a href="https://attv.uk/projects/gs1-128-barcode-generator.html" target="_blank" rel="noopener">About this project</a>
      </p>
    </div>

  </div>

  <hr class="my-4">
  <p class="text-center mb-0">&copy; <?= date('Y') ?> Tomasz Lewandowski. Built with purpose.</p>
</footer>

</body>
</html>
