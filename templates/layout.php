<?php /** @var string $title */ /** @var ?string $view */ ?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= htmlspecialchars($title ?? APP_NAME) ?></title>
<meta name="description" content="Generate standards-compliant GS1-128 (EAN-128) barcodes from EAN-13 or GTIN-14 with guided wizard or bulk CSV/XML import.">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
<link rel="stylesheet" href="<?= base_url() ?>assets/css/app.css">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous" defer></script>
<script src="https://cdn.jsdelivr.net/npm/bwip-js@4.5.1/dist/bwip-js-min.js" defer></script>
<script src="<?= base_url() ?>assets/js/validation.js" defer></script>
<script src="<?= base_url() ?>assets/js/barcode-preview.js" defer></script>
<script src="<?= base_url() ?>assets/js/wizard.js" defer></script>
<script src="<?= base_url() ?>assets/js/bulk-import.js" defer></script>
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

<footer class="container my-5 text-center text-muted small">
  <p><?= htmlspecialchars(APP_NAME) ?> v<?= htmlspecialchars(APP_VERSION) ?> &middot; <a href="https://github.com/AmigoUK/GS1-128-generator">GitHub</a></p>
</footer>

</body>
</html>
