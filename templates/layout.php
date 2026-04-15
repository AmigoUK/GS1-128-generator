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

<footer class="container my-5 text-center text-muted small">
  <p><?= htmlspecialchars(APP_NAME) ?> v<?= htmlspecialchars(APP_VERSION) ?> &middot; <a href="https://github.com/AmigoUK/GS1-128-generator" rel="noopener">GitHub</a> &middot; <a href="https://attv.uk/projects/gs1-128-barcode-generator.html" rel="noopener">About this project</a></p>
</footer>

</body>
</html>
