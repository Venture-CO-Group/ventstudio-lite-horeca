<?php /* Shared media picker overlay + wiring. Include once per page.
   Usage on any button:
     data-mediapick="#targetInput"   → open library, click a thumb to fill the input
     data-mediaupload="#targetInput"  → open file dialog, upload, fill the input
   Optional: data-preview="#el"       → set the <img> / preview box to the chosen path
*/
if (!defined('LT_MEDIA_PICKER')):
  define('LT_MEDIA_PICKER', 1);
  $__mp_csrf = lt_csrf();
?>
<div class="mp-overlay" id="mpOverlay">
  <div class="mp-card">
    <div class="mp-head"><strong>Choose an image</strong><button class="mp-close" type="button" id="mpClose">&times;</button></div>
    <div class="mp-body" id="mpBody">Loading…</div>
  </div>
</div>
<input type="file" id="mpFile" accept=".jpg,.jpeg,.png,.webp,.gif,.svg,.avif" style="display:none">
<script>
window.LtMedia = (function(){
  var CSRF = <?= json_encode($__mp_csrf) ?>;
  var ov = document.getElementById('mpOverlay'), body = document.getElementById('mpBody'), fileEl = document.getElementById('mpFile');
  var target = null, preview = null, loaded = false;

  function setValue(path){
    if (target){ target.value = path; target.dispatchEvent(new Event('input', {bubbles:true})); }
    if (preview){
      preview.classList.remove('empty');
      var img = preview.querySelector('img'); if(!img){ img=document.createElement('img'); preview.innerHTML=''; preview.appendChild(img); }
      img.src = path;
    }
  }
  function resolve(sel){ return sel ? document.querySelector(sel) : null; }
  function open(){ ov.classList.add('open'); if(loaded) return;
    fetch('media-list.php').then(function(r){return r.json();}).then(function(d){
      body.innerHTML=''; loaded=true;
      Object.keys(d).forEach(function(folder){
        if(!d[folder].files.length) return;
        var fh=document.createElement('div'); fh.className='mp-folder'; fh.textContent=d[folder].label; body.appendChild(fh);
        var grid=document.createElement('div'); grid.className='mp-grid';
        d[folder].files.forEach(function(f){
          var cell=document.createElement('button'); cell.type='button'; cell.className='mp-cell'; cell.title=f;
          var img=document.createElement('img'); img.loading='lazy'; img.src='/assets/img/'+folder+'/'+f; cell.appendChild(img);
          var nm=document.createElement('span'); nm.textContent=f; cell.appendChild(nm);
          cell.addEventListener('click', function(){ setValue('/assets/img/'+folder+'/'+f); close(); });
          grid.appendChild(cell);
        });
        body.appendChild(grid);
      });
    }).catch(function(){ body.textContent='Could not load the media library.'; });
  }
  function close(){ ov.classList.remove('open'); }
  document.getElementById('mpClose').addEventListener('click', close);
  ov.addEventListener('click', function(e){ if(e.target===ov) close(); });

  fileEl.addEventListener('change', function(){
    if(!fileEl.files.length) return;
    var fd=new FormData(); fd.append('csrf',CSRF); fd.append('file',fileEl.files[0]);
    var btnMsg = 'Uploading…';
    fetch('media-upload.php',{method:'POST',body:fd}).then(function(r){return r.json();}).then(function(j){
      if(j.ok){ setValue(j.path); loaded=false; } else { alert(j.error||'Upload failed'); }
      fileEl.value='';
    }).catch(function(){ alert('Upload failed'); fileEl.value=''; });
  });

  document.addEventListener('click', function(e){
    var pick = e.target.closest('[data-mediapick]');
    if(pick){ target=resolve(pick.getAttribute('data-mediapick')); preview=resolve(pick.getAttribute('data-preview')); open(); return; }
    var up = e.target.closest('[data-mediaupload]');
    if(up){ target=resolve(up.getAttribute('data-mediaupload')); preview=resolve(up.getAttribute('data-preview')); fileEl.click(); return; }
  });

  return { open:open, close:close, setTarget:function(t,p){ target=resolve(t); preview=resolve(p); } };
})();
</script>
<?php endif; ?>
