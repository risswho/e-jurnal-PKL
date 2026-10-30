<?php session_start(); if(isset($_SESSION['login'])){ $r=$_SESSION['role']??''; if($r==='admin') header('Location: ../admin/dashboard.php'); elseif($r==='guru') header('Location: ../guru/dashboard.php'); elseif($r==='siswa') header('Location: ../siswa/dashboard.php'); if(isset($_SESSION['login'])) exit; } $error=$_GET['error']??''; $title='Masuk'; $base='../'; require_once __DIR__.'/../includes/head.php'; ?>
<link rel="preload" as="image" href="../assets/img/bg-sekolah.webp">
<style>
/* Login ala SIAKAD — foto sebagai background body, panel kiri solid */
body.login-siakad{
  background:#0B7C6C url('../assets/img/bg-sekolah.webp') center/cover no-repeat fixed;
  color:var(--text);
}
body.login-siakad::before{
  content:""; position:fixed; inset:0; pointer-events:none;
  background:linear-gradient(120deg, rgba(11,124,108,.78) 0%, rgba(14,165,160,.55) 45%, rgba(0,0,0,.65) 100%);
}
.login-wrap{ position:relative; z-index:1; }
.login-panel{ overflow:hidden; border-radius:24px; border:1px solid rgba(255,255,255,.5); background:rgba(255,255,255,.72); -webkit-backdrop-filter:blur(18px) saturate(1.4); backdrop-filter:blur(18px) saturate(1.4); box-shadow:0 24px 60px -20px rgb(0 0 0 / .45); animation:loginUp .35s ease both; }
.dark .login-panel{ background:rgba(15,23,42,.62); border-color:rgba(255,255,255,.14); }
@supports not ((backdrop-filter:blur(1px)) or (-webkit-backdrop-filter:blur(1px))){ .login-panel{ background:var(--surface); } }
@keyframes loginUp{ from{ opacity:0; transform:translateY(10px);} to{ opacity:1; transform:none;} }
.glass-input{ background:rgba(255,255,255,.65); border-color:rgba(255,255,255,.6); }
.glass-input::placeholder{ color:var(--muted); opacity:.85; }
.dark .glass-input{ background:rgba(15,23,42,.5); border-color:rgba(255,255,255,.14); }
.glass-error{ background:rgba(254,242,242,.8); }
.dark .glass-error{ background:rgba(220,38,38,.18); }
.login-foot{ border-color:rgba(255,255,255,.35); }
.dark .login-foot{ border-color:rgba(255,255,255,.12); }
.login-input{ height:48px; }
.login-input.has-left-icon{ padding-left:2.75rem; }
.login-accent{ height:4px; background:linear-gradient(90deg,#0B7C6C,#0EA5A0 55%,#2563EB 130%); }
.role-card{ border:1.5px solid rgba(255,255,255,.55); background:rgba(255,255,255,.55); -webkit-backdrop-filter:blur(8px); backdrop-filter:blur(8px); border-radius:14px; padding:.65rem .5rem; text-align:center; cursor:pointer; transition:border-color .15s ease, background .15s ease, transform .1s ease; display:block; }
.dark .role-card{ background:rgba(15,23,42,.45); border-color:rgba(255,255,255,.14); }
.role-card:hover{ border-color:var(--primary); }
.role-card:active{ transform:scale(.98); }
.role-card .role-icon{ display:flex; align-items:center; justify-content:center; height:2.25rem; width:2.25rem; margin:0 auto; border-radius:.75rem; background:var(--primary-soft); color:var(--primary-dark); }
.dark .role-card .role-icon{ color:#5EEAD4; }
input.peer:checked + .role-card{ border-color:var(--primary); background:var(--primary-soft); box-shadow:0 0 0 3px rgb(14 165 160 / .18); }
input.peer:checked + .role-card .role-icon{ background:var(--primary); color:#fff; }
input.peer:focus-visible + .role-card{ outline:2px solid var(--primary); outline-offset:2px; }
.btn-login{ background:linear-gradient(120deg,#0B7C6C,#0EA5A0); box-shadow:0 8px 20px -8px rgb(14 165 160 / .7); transition:transform .1s ease, filter .15s ease; }
.btn-login:hover{ filter:brightness(1.05); transform:translateY(-1px); }
.btn-login:active{ transform:scale(.99); }
.btn-login:disabled{ opacity:.7; transform:none; cursor:wait; }
@media (max-width:767px){
  body.login-siakad{ background-attachment:scroll; }
}
</style>
<body class="login-siakad min-h-screen">
<button id="themeToggle" class="fixed right-4 top-4 z-50 rounded-xl border bg-white/90 p-2.5 backdrop-blur dark:bg-slate-900/90" style="border-color:var(--border)" aria-label="Toggle tema">
<svg id="iconSun" class="hidden h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2m0 14v2m9-9h-2M5 12H3m15.36 6.36l-1.42-1.42M7.05 7.05L5.64 5.64m12.72 0l-1.41 1.41M7.05 16.95l-1.41 1.41M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
<svg id="iconMoon" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 12.8A9 9 0 1111.2 3 7 7 0 0021 12.8z"/></svg>
</button>
<div class="login-wrap mx-auto grid min-h-screen w-full max-w-lg items-center px-4 py-10">
<div class="login-panel !p-0 !rounded-3xl">
<div class="login-accent"></div>
<!-- Header hero centered -->
<div class="px-6 pb-2 pt-7 text-center sm:px-8">
<div class="mx-auto flex h-14 w-14 items-center justify-center overflow-hidden rounded-2xl bg-white shadow-md ring-1" style="--tw-ring-color:var(--border)"><img src="../assets/img/Logo-SMK-Negeri-Purwosari.png" alt="Logo SMK Negeri Purwosari" class="h-full w-full object-cover" onerror="this.style.display='none'"></div>
<h1 class="mt-3 font-extrabold leading-tight" style="font-family:'Poppins',sans-serif;font-size:1.35rem">E-Jurnal SMK</h1>
<p class="mt-1 text-xs sm:text-sm" style="color:var(--muted)">SMK Negeri Purwosari &mdash; Jurnal Harian PKL &amp; Bimbingan</p>
</div>
<!-- Form -->
<div class="px-6 py-6 sm:px-8">
<h2 class="text-center font-bold" style="font-family:'Poppins',sans-serif;font-size:1.05rem">Masuk ke akun Anda</h2>
<p class="mt-1 text-center text-sm" style="color:var(--muted)">Pilih peran &amp; masukkan kredensial.</p>
<?php if($error): ?><div class="glass-error mt-4 flex items-start gap-2 rounded-xl border border-rose-200 px-3 py-2.5 text-sm font-medium text-rose-700 dark:border-rose-500/30 dark:text-rose-300"><svg class="mt-0.5 h-4 w-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.3 3.9L1.8 18a2 2 0 001.7 3h17a2 2 0 001.7-3L13.7 3.9a2 2 0 00-3.4 0z"/></svg><span><?= htmlspecialchars($error); ?></span></div><?php endif; ?>
<form id="loginForm" action="proses_login.php" method="POST" class="mt-5 space-y-4">
<div>
<span class="form-label" id="roleLabel">Login sebagai</span>
<div class="grid grid-cols-3 gap-2" role="radiogroup" aria-labelledby="roleLabel">
<label>
<input type="radio" name="role" value="siswa" class="peer sr-only" required>
<span class="role-card">
<span class="role-icon"><svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M22 10L12 5 2 10l10 5 10-5zm-10 7v-5m-6 2v4c0 1.7 2.7 3 6 3s6-1.3 6-3v-4"/></svg></span>
<span class="mt-1.5 block text-sm font-bold">Siswa</span>
<span class="block text-[11px]" style="color:var(--muted)">NISN</span>
</span>
</label>
<label>
<input type="radio" name="role" value="guru" class="peer sr-only" required>
<span class="role-card">
<span class="role-icon"><svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7H4a2 2 0 00-2 2v10a2 2 0 002 2h16a2 2 0 002-2V9a2 2 0 00-2-2zm-9 9a3 3 0 100-6 3 3 0 000 6zm7 1H6"/></svg></span>
<span class="mt-1.5 block text-sm font-bold">Guru</span>
<span class="block text-[11px]" style="color:var(--muted)">NIP</span>
</span>
</label>
<label>
<input type="radio" name="role" value="admin" class="peer sr-only" required>
<span class="role-card">
<span class="role-icon"><svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg></span>
<span class="mt-1.5 block text-sm font-bold">Admin</span>
<span class="block text-[11px]" style="color:var(--muted)">Username</span>
</span>
</label>
</div>
<p id="roleHint" class="mt-1.5 text-center text-xs" style="color:var(--muted)">Siswa memakai NISN, guru memakai NIP, admin memakai username.</p>
</div>
<div><label id="labelUsername" for="username" class="form-label">NISN / NIP / Username</label>
<div class="relative">
<span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3" style="color:var(--muted)"><svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg></span>
<input type="text" name="username" id="username" placeholder="Pilih peran dulu…" required class="form-input glass-input login-input has-left-icon" autocomplete="username">
</div></div>
<div><label for="password" class="form-label">Password</label>
<div class="relative">
<span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3" style="color:var(--muted)"><svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg></span>
<input type="password" name="password" id="password" placeholder="Masukkan password" required class="form-input glass-input login-input has-left-icon !pr-11" autocomplete="current-password">
<button type="button" id="togglePw" class="absolute inset-y-0 right-0 px-3 hover:opacity-70" style="color:var(--muted)" aria-label="Tampilkan password"><svg id="eyeOpen" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.05 12S5.5 5.5 12 5.5 21.95 12 21.95 12 18.5 18.5 12 18.5 2.05 12 2.05 12z"/></svg><svg id="eyeClosed" class="hidden h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3l18 18M10.6 5.1A9.8 9.8 0 0112 5.5c6.5 0 9.95 6.5 9.95 6.5a17 17 0 01-3.2 3.9M6.6 6.6A16.4 16.4 0 002.05 12S5.5 18.5 12 18.5c1.5 0 2.9-.3 4.1-.9M9.9 9.9a3 3 0 004.2 4.2"/></svg></button>
</div></div>
<button type="submit" id="btnSubmit" class="btn-primary btn-login login-input w-full !rounded-xl">Masuk <span aria-hidden="true">&rarr;</span></button>
<p class="text-center text-xs" style="color:var(--muted)">Gunakan akun yang diberikan sekolah. Hubungi admin jika lupa kredensial.</p>
</form>
</div>
<div class="login-foot border-t px-6 py-3 text-center text-xs sm:px-8" style="color:var(--muted)">E-Jurnal SMK &bull; SMK Negeri Purwosari &bull; &copy; <?= date('Y'); ?></div>
</div>
</div>
<script src="../assets/js/theme.js"></script>
<script>
(function(){
var labelU=document.getElementById('labelUsername'),user=document.getElementById('username'),hint=document.getElementById('roleHint');
var radios=document.querySelectorAll('input[name="role"]');
function syncRole(v){ if(v==='siswa'){labelU.textContent='NISN';user.placeholder='Masukkan NISN';hint.textContent='Masuk sebagai Siswa dengan NISN.';} else if(v==='guru'){labelU.textContent='NIP';user.placeholder='Masukkan NIP';hint.textContent='Masuk sebagai Guru dengan NIP.';} else if(v==='admin'){labelU.textContent='Username';user.placeholder='Masukkan username';hint.textContent='Masuk sebagai Admin dengan username.';} else {labelU.textContent='NISN / NIP / Username';user.placeholder='Pilih peran dulu…';} }
radios.forEach(function(r){ r.addEventListener('change',function(){ syncRole(this.value); }); });
var pw=document.getElementById('password'),eyeO=document.getElementById('eyeOpen'),eyeC=document.getElementById('eyeClosed');
document.getElementById('togglePw').addEventListener('click',function(){ var show=pw.type==='password'; pw.type=show?'text':'password'; eyeO.classList.toggle('hidden',show); eyeC.classList.toggle('hidden',!show); });
var form=document.getElementById('loginForm'),btn=document.getElementById('btnSubmit');
form.addEventListener('submit',function(){ btn.disabled=true; btn.innerHTML='Memproses…'; });
})();
</script>
</body></html>
