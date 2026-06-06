<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes, viewport-fit=cover">
  <meta name="mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="default">
  <title><?= htmlspecialchars($pageTitle ?? 'BoardTrack') ?></title>

  <!-- Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@600;700&display=swap" rel="stylesheet">
  <style>
    body { font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; }
  </style>

  <!-- Font Awesome -->
  <link rel="stylesheet" href="<?= Router::asset('css/font-awesome.min.css') ?>">

  <!-- Tailwind CSS & Design Tokens -->
  <link rel="stylesheet" href="<?= Router::asset('css/output.css') ?>">

  <!-- Landing CSS -->
  <link rel="stylesheet" href="<?= Router::asset('css/landing.css') ?>">

  <!-- Responsive Fixes for WebView & Mobile -->
  <link rel="stylesheet" href="<?= Router::asset('css/responsive-fixes.css') ?>">
</head>
<body>

<?php require APP_PATH . '/views/components/alerts.php'; ?>
