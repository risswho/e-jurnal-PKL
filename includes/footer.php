<?php // includes/footer.php — tutup main + scripts ?>
</main>
<footer class="border-t px-4 py-4 text-center text-xs dark:text-slate-400" style="border-color:var(--border);background:var(--surface);color:var(--muted)">
  E-Jurnal SMK &bull; PKL / monitoring Siswa &bull; <?= date('Y'); ?>
</footer>
</div><!-- /main column -->
</div><!-- /flex -->
<div id="confirmModal" class="fixed inset-0 z-[60] hidden items-center justify-center bg-slate-950/60 p-4">
  <div class="card w-full max-w-sm !p-6">
    <h3 class="font-bold" style="font-family:'Poppins',sans-serif">Hapus data?</h3>
    <p id="confirmText" class="mt-1 text-sm" style="color:var(--muted)">Tindakan ini tidak dapat dibatalkan.</p>
    <div class="mt-5 flex gap-2">
      <button onclick="closeConfirm()" class="btn-ghost flex-1">Batal</button>
      <a id="confirmYes" href="#" class="flex-1 rounded-[.8rem] bg-red-600 px-3 py-2 text-center text-sm font-semibold text-white hover:bg-red-700">Ya, Hapus</a>
    </div>
  </div>
</div>
<div id="lightbox" class="fixed inset-0 z-[60] hidden items-center justify-center bg-slate-950/80 p-4" onclick="closeLightbox()">
  <img id="lightboxImg" src="" alt="Preview" class="max-h-[85vh] max-w-full rounded-2xl border border-white/20 shadow-2xl">
</div>
<script src="<?= isset($base)?$base:'../'; ?>assets/js/theme.js"></script>
<script src="<?= isset($base)?$base:'../'; ?>assets/js/app.js"></script>
</body>
</html>
