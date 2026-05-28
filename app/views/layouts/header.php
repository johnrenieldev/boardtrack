<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($pageTitle ?? 'BoardTrack') ?></title>

  <!-- Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@600;700&display=swap" rel="stylesheet">

  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  <!-- Tailwind CSS & Design Tokens -->
  <link rel="stylesheet" href="<?= Router::asset('css/input.css') ?>">
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            brand: {
              50: '#eff6ff', 100: '#dbeafe', 200: '#bfdbfe', 300: '#93c5fd', 400: '#60a5fa',
              500: '#3b82f6', 600: '#2563eb', 700: '#1d4ed8', 800: '#1e40af', 900: '#1e3a8a',
            },
            success: {
              50: '#f0fdf4', 100: '#dcfce7', 200: '#bbf7d0', 300: '#86efac', 400: '#4ade80',
              500: '#22c55e', 600: '#16a34a', 700: '#15803d', 800: '#166534', 900: '#14532d',
            },
            warning: {
              50: '#fffbeb', 100: '#fef3c7', 200: '#fde68a', 300: '#fcd34d', 400: '#fbbf24',
              500: '#f59e0b', 600: '#d97706', 700: '#b45309', 800: '#92400e', 900: '#78350f',
            },
            danger: {
              50: '#fef2f2', 100: '#fee2e2', 200: '#fecaca', 300: '#fca5a5', 400: '#f87171',
              500: '#ef4444', 600: '#dc2626', 700: '#b91c1c', 800: '#991b1b', 900: '#7f1d1d',
            },
          },
          fontFamily: {
            sans: ['Inter', 'system-ui', 'sans-serif'],
            heading: ['Poppins', 'system-ui', 'sans-serif'],
          },
          boxShadow: {
            'xs': 'var(--shadow-xs)',
            'sm': 'var(--shadow-sm)',
            'md': 'var(--shadow-md)',
            'lg': 'var(--shadow-lg)',
          },
          borderRadius: {
            'xs': 'var(--radius-xs)',
            'sm': 'var(--radius-sm)',
            'md': 'var(--radius-md)',
            'lg': 'var(--radius-lg)',
            'xl': 'var(--radius-xl)',
          },
        }
      }
    }
  </script>

  <!-- Landing CSS -->
  <link rel="stylesheet" href="<?= Router::asset('css/landing.css') ?>">
</head>
<body>

<?php require APP_PATH . '/views/components/alerts.php'; ?>
