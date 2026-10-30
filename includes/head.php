<?php
// includes/head.php — dipakai semua halaman
// Variabel yang diharapkan: $title (string), $base (string, default '../' atau '' untuk root)
if (!isset($title)) $title = 'E-Jurnal SMK';
if (!isset($base)) $base = '../';
?>
<!DOCTYPE html>
<html lang="id" class="">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($title); ?> - E-Jurnal SMK</title>
<link rel="icon" type="image/png" href="<?= $base; ?>assets/img/Logo-SMK-Negeri-Purwosari.png">
<script>
// Cegah FOUC dark mode
(function(){try{var t=localStorage.getItem('ejurnal-theme');if(t==='dark'||(!t&&window.matchMedia('(prefers-color-scheme: dark)').matches)){document.documentElement.classList.add('dark')}}catch(e){}})();
</script>
<script src="https://cdn.tailwindcss.com"></script>
<script>
tailwind.config = {
  darkMode: 'class',
  theme: {
    extend: {
      fontFamily: { sans: ['Poppins','Inter','ui-sans-serif','system-ui','sans-serif'] },
      colors: {
        primary: {50:'#effaf8',100:'#d7f2ee',200:'#b0e5dd',300:'#7dd3c7',400:'#45bcad',500:'#0EA5A0',600:'#0b857f',700:'#0B7C6C',800:'#0d5f54',900:'#0c4f46'},
        ink: {DEFAULT:'#0F172A', muted:'#64748B'}
      },
      boxShadow: { card: '0 1px 2px 0 rgb(15 23 42 / 0.05), 0 4px 16px -4px rgb(15 23 42 / 0.08)' }
    }
  }
}
</script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= $base; ?>assets/css/style.css">
<style>
:root{
  --primary:#0EA5A0; --primary-dark:#0B7C6C; --primary-soft:#EFFAF8;
  --accent:#2563EB; --background:#F4F7F9; --surface:#FFFFFF;
  --text:#0F172A; --muted:#64748B; --border:#E6EDF2;
  --success:#16A34A; --warning:#D97706; --danger:#DC2626;
  --radius:16px;
}
.dark{ --background:#0B1220; --surface:#0F172A; --text:#F1F5F9; --muted:#94A3B8; --border:#1E293B; --primary-soft:rgba(14,165,160,.12); }
html{font-family:'Poppins','Inter',system-ui,sans-serif}
body{background:var(--background)}
::-webkit-scrollbar{width:10px;height:10px} ::-webkit-scrollbar-thumb{background:#b0e5dd;border-radius:8px} .dark ::-webkit-scrollbar-thumb{background:#334155}
</style>
</head>
