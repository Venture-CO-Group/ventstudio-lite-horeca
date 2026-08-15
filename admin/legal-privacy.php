<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/_layout.php';
require_once __DIR__ . '/_policy_defaults.php';
lt_require_login();
$csrf = lt_csrf();
$content = lt_content_load();
$priv = $content['legal']['privacy'] ?? ['title'=>['en'=>'','hu'=>'','es'=>''],'intro'=>['en'=>'','hu'=>'','es'=>''],'partnerDocs'=>[]];
lt_admin_head('Policies');
lt_admin_sidebar('privacy');
lt_admin_top('Vent Studio', 'Policies',
    '<a class="btn-studio" href="/en/policies" target="_blank">View page &nearr;</a><span class="lang-mini" id="pLang"></span><button class="btn-studio primary" id="pSave" type="button">Save</button>');
?>
<div class="admin-body">
  <div class="st-hint">Edit the intro text and the list of downloadable policy documents. Each document has a display name, an optional logo shown next to it, and a PDF you can upload or link.</div>

  <div class="section-card"><h2>Heading &amp; intro</h2><div class="card-body">
    <div class="field"><div class="field-label">Page title (<span class="lg"></span>)</div><input id="pTitle" class="txt"></div>
    <div class="field"><div class="field-label">Intro (HTML, <span class="lg"></span>)</div><textarea id="pIntro" style="min-height:120px"></textarea></div>
  </div></div>

  <div class="section-card"><h2>Policy documents</h2><div class="card-body">
    <div id="pDocs"></div>
    <div style="margin-top:12px;display:flex;gap:10px;flex-wrap:wrap">
      <button class="btn-studio btn-mini" type="button" id="pAdd">+ Add document</button>
      <button class="btn-studio btn-mini" type="button" id="pLoadDefaults">Load VentStudio policy set</button>
    </div>
    <p style="color:var(--gray);font-size:12.5px;margin-top:8px">“Load VentStudio policy set” fills the list with all product &amp; partner policies cloned from example.com (VentStudio docs use the locally-uploaded PDFs). Review, then click <strong>Save</strong>.</p>
  </div></div>
</div>

<div class="toast" id="toast"></div>
<input type="file" id="pdfFile" accept=".pdf,.doc,.docx" style="display:none">
<?php require __DIR__ . '/_media_picker.php'; ?>
<script>
window.LT_PRIV = <?= json_encode($priv, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
window.LT_CSRF = <?= json_encode($csrf) ?>;
window.LT_POLICY_DEFAULTS = <?= json_encode(lt_policy_defaults(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
</script>
<script>
(function(){
  var S = window.LT_PRIV || {};
  S.title = S.title || {en:"",hu:"",es:""}; S.intro = S.intro || {en:"",hu:"",es:""};
  S.docs = (S.partnerDocs || []).map(function(d){
    return { group: d.group || "", label: d.label || {en:"",hu:"",es:""}, pdf: (d.pdf && (d.pdf.en||d.pdf.hu||d.pdf.es)) || "", logo: d.logo || "" };
  });
  var CSRF = window.LT_CSRF, LANG = "en", LANGS = [["en","English"],["hu","Magyar"],["es","Español"]];
  var $ = function(s){ return document.querySelector(s); };
  function el(t,c){ var n=document.createElement(t); if(c)n.className=c; return n; }
  function imgUrl(v){ return v ? (/^https?:|^\//.test(v) ? v : "/assets/img/"+v) : ""; }
  var pdfRow = -1;

  function renderLang(){
    var bar=$("#pLang"); bar.innerHTML="";
    LANGS.forEach(function(l){ var b=el("button","lang-pill"+(l[0]===LANG?" on":"")); b.type="button"; b.textContent=l[1];
      b.addEventListener("click",function(){ LANG=l[0]; renderAll(); }); bar.appendChild(b); });
    document.querySelectorAll(".lg").forEach(function(x){ x.textContent=LANG; });
  }
  function renderHead(){
    var t=$("#pTitle"); t.value=S.title[LANG]||""; t.oninput=function(){ S.title[LANG]=t.value; };
    var i=$("#pIntro"); i.value=S.intro[LANG]||""; i.oninput=function(){ S.intro[LANG]=i.value; };
  }
  function renderDocs(){
    var host=$("#pDocs"); host.innerHTML="";
    if(!S.docs.length){ host.innerHTML='<p style="color:var(--gray)">No documents yet.</p>'; }
    S.docs.forEach(function(d,i){
      var row=el("div","doc-row");
      // logo
      var lg=el("div","doc-logo-cell");
      var prev=el("div","img-thumb"+(d.logo?"":" empty")); prev.id="dlogo"+i;
      if(d.logo){ var im=el("img"); im.src=imgUrl(d.logo); prev.appendChild(im); }
      lg.appendChild(prev);
      // fields
      var fields=el("div","doc-fields");
      var grp=el("input","txt"); grp.placeholder="Group / partner (e.g. AS Roma)"; grp.value=d.group||"";
      grp.addEventListener("input",function(){ d.group=grp.value; });
      var nm=el("input","txt"); nm.placeholder="Display name ("+LANG+")"; nm.value=(d.label&&d.label[LANG])||"";
      nm.addEventListener("input",function(){ d.label=d.label||{}; d.label[LANG]=nm.value; });
      var pdfWrap=el("div","doc-pdf");
      var pdf=el("input","txt"); pdf.placeholder="/assets/doc/… or https://…"; pdf.value=d.pdf||""; pdf.id="dpdf"+i;
      pdf.addEventListener("input",function(){ d.pdf=pdf.value; });
      var upBtn=el("button","btn-studio btn-mini"); upBtn.type="button"; upBtn.textContent="Upload PDF";
      upBtn.addEventListener("click",function(){ pdfRow=i; $("#pdfFile").click(); });
      pdfWrap.appendChild(pdf); pdfWrap.appendChild(upBtn);
      fields.appendChild(grp); fields.appendChild(nm); fields.appendChild(pdfWrap);
      // logo buttons
      var logoBtns=el("div","doc-logo-btns");
      var pick=el("button","btn-studio btn-mini"); pick.type="button"; pick.textContent="Logo…";
      pick.addEventListener("click",function(){ window.LtMedia.setTarget("#dlogoInput"+i,"#dlogo"+i); window.LtMedia.open(); pick._i=i; });
      // hidden input to receive picker value, mapped to model
      var hidden=el("input"); hidden.type="hidden"; hidden.id="dlogoInput"+i; hidden.value=d.logo?imgUrl(d.logo):"";
      hidden.addEventListener("input",function(){ d.logo=hidden.value.replace(/^\/?assets\/img\//,""); var im2=$("#dlogo"+i); im2.classList.remove("empty"); im2.innerHTML='<img src="'+hidden.value+'">'; });
      var upl=el("button","btn-studio btn-mini"); upl.type="button"; upl.textContent="Upload logo"; upl.setAttribute("data-mediaupload","#dlogoInput"+i); upl.setAttribute("data-preview","#dlogo"+i);
      logoBtns.appendChild(pick); logoBtns.appendChild(upl); logoBtns.appendChild(hidden);
      // controls
      var ctr=el("div","doc-ctr");
      var up=el("button","btn-studio btn-mini"); up.type="button"; up.textContent="↑"; up.addEventListener("click",function(){ if(i>0){ var t=S.docs[i]; S.docs[i]=S.docs[i-1]; S.docs[i-1]=t; renderDocs(); }});
      var dn=el("button","btn-studio btn-mini"); dn.type="button"; dn.textContent="↓"; dn.addEventListener("click",function(){ if(i<S.docs.length-1){ var t=S.docs[i]; S.docs[i]=S.docs[i+1]; S.docs[i+1]=t; renderDocs(); }});
      var rm=el("button","btn-studio btn-mini btn-danger"); rm.type="button"; rm.textContent="✕"; rm.addEventListener("click",function(){ if(confirm("Remove this document?")){ S.docs.splice(i,1); renderDocs(); }});
      ctr.appendChild(up); ctr.appendChild(dn); ctr.appendChild(rm);

      row.appendChild(lg); row.appendChild(fields); row.appendChild(logoBtns); row.appendChild(ctr);
      host.appendChild(row);
    });
  }
  function renderAll(){ renderLang(); renderHead(); renderDocs(); }

  $("#pAdd").addEventListener("click",function(){ S.docs.push({group:"",label:{en:"",hu:"",es:""},pdf:"",logo:""}); renderDocs(); });
  $("#pLoadDefaults").addEventListener("click",function(){
    if(S.docs.length && !confirm("Replace the current document list with the full VentStudio policy set?")) return;
    S.docs = (window.LT_POLICY_DEFAULTS||[]).map(function(d){ return { group:d.group||"", label:d.label||{en:"",hu:"",es:""}, pdf:d.pdf||"", logo:d.logo||"" }; });
    renderDocs();
  });

  // PDF upload
  $("#pdfFile").addEventListener("change",function(){
    if(!this.files.length||pdfRow<0) return;
    var fd=new FormData(); fd.append("csrf",CSRF); fd.append("file",this.files[0]);
    var idx=pdfRow;
    fetch("doc-upload.php",{method:"POST",body:fd}).then(function(r){return r.json();}).then(function(j){
      if(j.ok){ S.docs[idx].pdf=j.path; var inp=document.getElementById("dpdf"+idx); if(inp) inp.value=j.path; toast("PDF uploaded"); }
      else toast(j.error||"Upload failed",true);
    }).catch(function(){ toast("Upload failed",true); });
    this.value="";
  });

  function toast(m,err){ var t=$("#toast"); t.textContent=m; t.className="toast show"+(err?" err":""); setTimeout(function(){ t.className="toast"+(err?" err":""); },2400); }
  $("#pSave").addEventListener("click",function(){
    var payload={ title:S.title, intro:S.intro, docs:S.docs.map(function(d){ return {group:d.group,label:d.label,pdf:d.pdf,logo:d.logo}; }) };
    fetch("legal-save.php",{method:"POST",headers:{"Content-Type":"application/json","X-CSRF":CSRF},body:JSON.stringify(payload)})
      .then(function(r){return r.json();}).then(function(j){ toast(j.ok?"Saved — privacy page updated":(j.error||"Save failed"),!j.ok); })
      .catch(function(){ toast("Network error — not saved",true); });
  });

  renderAll();
})();
</script>
<?php lt_admin_foot();
