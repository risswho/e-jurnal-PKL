<?php
// includes/toast.php — render notifikasi ?success= / ?error= / ?deleted= secara konsisten
// Memerlukan Tailwind. Otomatis dismiss 5 detik.
$__toast_items = [];
if (isset($_GET['success']) && $_GET['success'] !== '') $__toast_items[] = ['ok' => true, 'msg' => is_string($_GET['success']) && $_GET['success'] !== '1' ? $_GET['success'] : 'Berhasil disimpan.'];
if (isset($_GET['deleted'])) $__toast_items[] = ['ok' => true, 'msg' => 'Data berhasil dihapus.'];
if (isset($_GET['error']) && $_GET['error'] !== '') $__toast_items[] = ['ok' => false, 'msg' => $_GET['error']];
if (!empty($__toast_items)):
?>
<div id="ejToast" class="mb-4 space-y-2">
<?php foreach ($__toast_items as $t): ?>
<div class="flex items-start gap-3 rounded-xl border px-4 py-3 text-sm font-medium <?= $t['ok'] ? 'border-emerald-200 bg-emerald-50 text-emerald-700 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-300' : 'border-rose-200 bg-rose-50 text-rose-700 dark:border-rose-500/30 dark:bg-rose-500/10 dark:text-rose-300'; ?>">
<span class="mt-0.5"><?= $t['ok'] ? '✓' : '⚠'; ?></span>
<span class="flex-1"><?= htmlspecialchars($t['msg']); ?></span>
<button onclick="this.parentElement.remove()" class="opacity-60 hover:opacity-100" aria-label="Tutup">✕</button>
</div>
<?php endforeach; ?>
</div>
<script>setTimeout(function(){var e=document.getElementById('ejToast');if(e)e.style.display='none';},6000);</script>
<?php endif; ?>
