<?php
// Pull DB overrides for the current page slug (if seo_pages table exists)
if (!isset($_seoOverride)) {
    $_seoOverride = null;
    try {
        include_once $_SERVER['DOCUMENT_ROOT'] . '/php/models/Seo.php';
        $_seoModel    = new Seo();
        $_currentSlug = strtok($_SERVER['REQUEST_URI'] ?? '/', '?');
        $_seoOverride = $_seoModel->getPageBySlug($_currentSlug);
    } catch (Throwable $e) {
        // Table may not exist yet; fall back to page-level defaults silently
    }
}

if ($_seoOverride) {
    $seoTitle    = $_seoOverride['seo_title'];
    $description = $_seoOverride['seo_description'];
    if (!empty($_seoOverride['og_image'])) {
        $ogImage = $_seoOverride['og_image'];
    }
}
?>
<head>
  <meta charset="utf-8">
  <title><?= htmlspecialchars($seoTitle ?? ('EveryonesParking' . (isset($title) && $title ? ' - ' . $title : ''))) ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <?php if (!empty($description)): ?>
  <meta name="description" content="<?= htmlspecialchars($description) ?>">
  <?php endif; ?>

  <?php if (!empty($noIndex)): ?>
  <meta name="robots" content="noindex, nofollow">
  <?php else: ?>
  <meta name="robots" content="index, follow">
  <?php endif; ?>

  <?php
    $canonicalUrl = $canonical ?? ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'everyonesparking.com.au') . strtok($_SERVER['REQUEST_URI'] ?? '/', '?'));
  ?>
  <link rel="canonical" href="<?= htmlspecialchars($canonicalUrl) ?>">

  <!-- Open Graph -->
  <meta property="og:type" content="<?= htmlspecialchars($ogType ?? 'website') ?>">
  <meta property="og:title" content="<?= htmlspecialchars($ogTitle ?? $seoTitle ?? ('EveryonesParking' . (isset($title) && $title ? ' - ' . $title : ''))) ?>">
  <meta property="og:description" content="<?= htmlspecialchars($ogDescription ?? $description ?? 'Find and book affordable parking across Australia. List your driveway or garage and earn money with EveryonesParking.') ?>">
  <meta property="og:url" content="<?= htmlspecialchars($canonicalUrl) ?>">
  <meta property="og:site_name" content="EveryonesParking">
  <?php if (!empty($ogImage)): ?>
  <meta property="og:image" content="<?= htmlspecialchars($ogImage) ?>">
  <meta property="og:image:alt" content="<?= htmlspecialchars($ogImageAlt ?? 'EveryonesParking') ?>">
  <?php else: ?>
  <meta property="og:image" content="<?= (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'everyonesparking.com.au') ?>/images/og-default.png">
  <?php endif; ?>

  <!-- Twitter Card -->
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="<?= htmlspecialchars($ogTitle ?? $seoTitle ?? ('EveryonesParking' . (isset($title) && $title ? ' - ' . $title : ''))) ?>">
  <meta name="twitter:description" content="<?= htmlspecialchars($ogDescription ?? $description ?? 'Find and book affordable parking across Australia.') ?>">
  <?php if (!empty($ogImage)): ?>
  <meta name="twitter:image" content="<?= htmlspecialchars($ogImage) ?>">
  <?php endif; ?>

  <link href="https://api.mapbox.com/mapbox-gl-js/v3.17.0-beta.1/mapbox-gl.css" rel="stylesheet">
  <script src="https://api.mapbox.com/mapbox-gl-js/v3.17.0-beta.1/mapbox-gl.js"></script>

  <link rel="icon" type="image/x-icon" href="/images/favicon.ico">

  <link href="/css/output.css" rel="stylesheet">

  <script src="https://kit.fontawesome.com/01e87deab9.js" crossorigin="anonymous"></script>
  <script src="https://js.stripe.com/v3/"></script>

  <script src="/js/datePicker.js"></script>

</head>
