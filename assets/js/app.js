// app.js — sidebar, modal hapus, lightbox, filter tabel, preview upload, paginasi client-side
document.addEventListener('DOMContentLoaded', function(){
  var sb=document.getElementById('sidebar'), ov=document.getElementById('sidebarOverlay'), tg=document.getElementById('sidebarToggle');
  function openSb(){ if(!sb) return; sb.classList.remove('-translate-x-full'); if(ov) ov.classList.remove('hidden'); }
  function closeSb(){ if(!sb) return; if(window.innerWidth<1024){ sb.classList.add('-translate-x-full'); if(ov) ov.classList.add('hidden'); } }
  if(tg) tg.addEventListener('click', function(){ sb.classList.contains('-translate-x-full')?openSb():closeSb(); });
  if(ov) ov.addEventListener('click', closeSb);

  // Filter tabel client-side: input[data-table-filter] -> tbody tr
  document.querySelectorAll('[data-table-filter]').forEach(function(input){
    var sel=input.getAttribute('data-table-filter');
    input.addEventListener('input', function(){
      var q=input.value.toLowerCase();
      document.querySelectorAll(sel+' tbody tr').forEach(function(tr){
        tr.style.display = tr.textContent.toLowerCase().includes(q)?'':'none';
      });
      document.querySelectorAll('[data-paginate]').forEach(setupPaginate);
    });
  });

  // Preview upload foto
  document.querySelectorAll('[data-img-preview]').forEach(function(fileInput){
    var target=document.querySelector(fileInput.getAttribute('data-img-preview'));
    fileInput.addEventListener('change', function(){
      var f=fileInput.files && fileInput.files[0];
      if(!f||!target) return;
      if(f.size>2*1024*1024){ alert('Ukuran foto maksimal 2 MB'); fileInput.value=''; return; }
      if(!/^image\/(jpeg|png|jpg)$/.test(f.type)){ alert('Format foto harus JPG/JPEG/PNG'); fileInput.value=''; return; }
      var r=new FileReader(); r.onload=function(e){ target.src=e.target.result; target.classList.remove('hidden'); }; r.readAsDataURL(f);
    });
  });

  // Paginasi client-side generik: <table data-paginate="10" data-paginate-nav="#navId">
  function setupPaginate(tbl){
    var per=parseInt(tbl.getAttribute('data-paginate')||'10',10);
    var navSel=tbl.getAttribute('data-paginate-nav');
    var nav=navSel?document.querySelector(navSel):null;
    if(!nav||!per) return;
    var all=Array.prototype.slice.call(tbl.querySelectorAll('tbody tr'));
    // Hormati filter teks: baris yang sedang disembunyikan filter (data-filtered=0) dikecualikan
    var filtered=all.filter(function(tr){ return tr.getAttribute('data-filtered')!=='0'; });
    if(filtered.length===0) filtered=all;
    var pages=Math.max(1,Math.ceil(filtered.length/per));
    var cur=parseInt(tbl.getAttribute('data-page')||'1',10);
    if(cur>pages) cur=pages;
    if(cur<1) cur=1;
    tbl.setAttribute('data-page',cur);
    filtered.forEach(function(tr,i){
      var p=Math.floor(i/per)+1;
      tr.style.display=(p===cur)?'':'none';
    });
    nav.innerHTML='';
    function btn(label,page,active,disabled){
      var b=document.createElement('button');
      b.type='button'; b.textContent=label;
      b.className='pg-btn'+(active?' active':'');
      b.disabled=!!disabled;
      if(disabled) b.style.opacity='.4';
      b.addEventListener('click',function(){ tbl.setAttribute('data-page',page); setupPaginate(tbl); });
      return b;
    }
    nav.appendChild(btn('‹',Math.max(1,cur-1),false,cur===1));
    for(var p=1;p<=pages;p++){ nav.appendChild(btn(String(p),p,p===cur,false)); }
    nav.appendChild(btn('›',Math.min(pages,cur+1),false,cur===pages));
  }
  document.querySelectorAll('[data-paginate]').forEach(setupPaginate);
  window.__ejPaginate=function(){ document.querySelectorAll('[data-paginate]').forEach(setupPaginate); };
});
function askDelete(url, text){
  var m=document.getElementById('confirmModal');
  document.getElementById('confirmYes').href=url;
  if(text) document.getElementById('confirmText').textContent=text;
  m.classList.remove('hidden'); m.classList.add('flex');
  return false;
}
function closeConfirm(){ var m=document.getElementById('confirmModal'); m.classList.add('hidden'); m.classList.remove('flex'); }
function openLightbox(src){ var m=document.getElementById('lightbox'); document.getElementById('lightboxImg').src=src; m.classList.remove('hidden'); m.classList.add('flex'); }
function closeLightbox(){ var m=document.getElementById('lightbox'); m.classList.add('hidden'); m.classList.remove('flex'); }
document.addEventListener('keydown', function(e){ if(e.key==='Escape'){ try{closeConfirm();closeLightbox();}catch(_){} var sb=document.getElementById('sidebar'); if(sb&&window.innerWidth<1024){ sb.classList.add('-translate-x-full'); var ov=document.getElementById('sidebarOverlay'); if(ov) ov.classList.add('hidden'); } } });
