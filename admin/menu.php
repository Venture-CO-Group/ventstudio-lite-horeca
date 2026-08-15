<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/_layout.php';
require_once dirname(__DIR__) . '/inc/store.php';
lt_require_login();

$C = lt_content_load();
$menu = $C['menu'] ?? ['intro' => '', 'groups' => []];
$saved = isset($_GET['saved']);
$csrf = lt_csrf();

lt_admin_head('Menu');
lt_admin_sidebar('menu');
lt_admin_top('Ordering', 'Menu', '<button form="menuForm" type="submit" class="btn-studio primary">Save menu</button>');
?>
<style>
.mn-wrap{padding:22px 34px;max-width:1000px}
.mn-intro{margin-bottom:18px}
.mn-intro textarea{width:100%;border:1.5px solid var(--line-2);border-radius:12px;padding:12px;font-family:var(--sans);font-size:14px}
.mn-group{background:var(--card);border:1px solid var(--line);border-radius:var(--r);padding:18px;margin-bottom:18px;box-shadow:var(--shadow)}
.mn-ghead{display:flex;gap:10px;align-items:center;margin-bottom:12px}
.mn-ghead input{flex:1;border:1.5px solid var(--line-2);border-radius:10px;padding:9px 12px;font-weight:700;font-family:var(--display)}
.mn-ghead select{border:1.5px solid var(--line-2);border-radius:10px;padding:9px 10px}
.mn-note{width:100%;border:1.5px solid var(--line-2);border-radius:10px;padding:8px 10px;margin-bottom:12px;font-size:13px}
.mn-item{display:grid;grid-template-columns:1.4fr 2.2fr 90px 1fr 34px;gap:8px;align-items:center;margin-bottom:8px}
.mn-item input{border:1.5px solid var(--line-2);border-radius:9px;padding:8px 10px;font-size:13px;width:100%}
.mn-x{border:none;background:#fdecea;color:var(--err);border-radius:9px;padding:8px;cursor:pointer;font-weight:700}
.mn-add{border:1.5px dashed var(--line-2);background:none;border-radius:10px;padding:9px 14px;cursor:pointer;font-weight:600;color:var(--gray)}
.mn-ctrls{grid-column:1 / -1;display:flex;gap:18px;align-items:center;flex-wrap:wrap;margin-top:4px;padding-top:8px;border-top:1px dashed var(--line-2)}
.mn-chk{display:flex;align-items:center;gap:6px;font-size:12.5px;font-weight:600;color:var(--ink,#1B1512)}
.mn-chk input[type=number]{border:1.5px solid var(--line-2);border-radius:8px;padding:5px 8px;font-size:12.5px}
.mn-chk input[type=checkbox]{width:16px;height:16px}
.mn-hint-off{color:var(--gray);font-weight:500;font-size:11.5px}
.mn-labels{display:grid;grid-template-columns:1.4fr 2.2fr 90px 1fr 34px;gap:8px;font-size:11px;text-transform:uppercase;letter-spacing:.08em;color:var(--gray);font-weight:700;margin-bottom:6px}
.mn-saved{background:#e8f7ef;border:1px solid #b9e6cd;color:#1B7A46;border-radius:10px;padding:10px 14px;margin:0 34px 6px}
.mn-toolbar{display:flex;gap:10px;margin:14px 0}
</style>
<?php if ($saved): ?><div class="mn-saved">Menu saved — it's live on the site.</div><?php endif; ?>
<div class="mn-wrap">
  <form id="menuForm" method="post" action="menu-save.php">
    <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
    <input type="hidden" name="menu_json" id="menuJson">
    <div class="mn-intro">
      <label style="font-weight:700;display:block;margin-bottom:6px">Menu intro line</label>
      <textarea id="mnIntro" rows="2"><?= htmlspecialchars($menu['intro'] ?? '') ?></textarea>
    </div>
    <div id="mnGroups"></div>
    <div class="mn-toolbar">
      <button type="button" class="mn-add" id="addGroup">+ Add group</button>
    </div>
  </form>
</div>

<template id="tplItem">
  <div class="mn-item">
    <input class="i-name" placeholder="Item name">
    <input class="i-desc" placeholder="Description">
    <input class="i-price" placeholder="0.00" inputmode="decimal">
    <input class="i-tags" placeholder="tags, comma">
    <button type="button" class="mn-x" title="Remove">✕</button>
    <input class="i-allergens" placeholder="allergens (kept for records, not shown on site): gluten, milk, eggs, nuts, soya, mustard…" style="grid-column:1 / -1;margin-top:2px">
    <div class="mn-ctrls">
      <label class="mn-chk"><input type="checkbox" class="i-visible" checked> Show on website</label>
      <label class="mn-chk">Stock <input class="i-stock" type="number" min="0" step="1" placeholder="∞" style="width:74px"> <span class="mn-hint-off">blank = unlimited · 0 hides it</span></label>
      <label class="mn-chk"><input type="checkbox" class="i-preorder"> Pre-order only</label>
      <label class="mn-chk">Lead time (hrs) <input class="i-prehours" type="number" min="0" step="1" placeholder="48" style="width:64px"></label>
    </div>
  </div>
</template>

<script>
var MENU = <?= json_encode($menu, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) ?>;
var accents = ["hotsauce","honey","fresh","berry"];
function slugify(s){return (s||"").toLowerCase().replace(/[^a-z0-9]+/g,"-").replace(/^-|-$/g,"");}
var groupsEl = document.getElementById("mnGroups");

function addItemRow(container, it){
  var t = document.getElementById("tplItem").content.cloneNode(true);
  var row = t.querySelector(".mn-item");
  row.querySelector(".i-name").value = it.name||"";
  row.querySelector(".i-desc").value = it.desc||"";
  row.querySelector(".i-price").value = (it.price!=null?it.price:"");
  row.querySelector(".i-tags").value = (it.tags||[]).join(", ");
  row.querySelector(".i-allergens").value = (it.allergens||[]).join(", ");
  row.querySelector(".i-visible").checked = (it.visible!==false);
  row.querySelector(".i-stock").value = (it.stock!=null ? it.stock : "");
  row.querySelector(".i-preorder").checked = !!it.preorder;
  row.querySelector(".i-prehours").value = (it.preorderHours!=null ? it.preorderHours : "");
  row.dataset.slug = it.slug||"";
  row.querySelector(".mn-x").addEventListener("click",function(){row.remove();});
  container.appendChild(row);
}
function addGroup(grp){
  grp = grp||{title:"",accent:"hotsauce",note:"",items:[]};
  var wrap = document.createElement("div"); wrap.className="mn-group";
  wrap.innerHTML =
    '<div class="mn-ghead">'+
      '<input class="g-title" placeholder="Group title" value="'+esc(grp.title||"")+'">'+
      '<select class="g-accent">'+accents.map(function(a){return '<option '+(grp.accent===a?"selected":"")+'>'+a+'</option>';}).join("")+'</select>'+
      '<button type="button" class="mn-x g-del" title="Remove group">✕</button>'+
    '</div>'+
    '<input class="mn-note" placeholder="Optional note (e.g. Served with fries)" value="'+esc(grp.note||"")+'">'+
    '<div class="mn-labels"><span>Name</span><span>Description</span><span>Price</span><span>Tags</span><span></span></div>'+
    '<div class="g-items"></div>'+
    '<button type="button" class="mn-add g-additem" style="margin-top:8px">+ Add item</button>';
  groupsEl.appendChild(wrap);
  var items = wrap.querySelector(".g-items");
  (grp.items||[]).forEach(function(it){addItemRow(items,it);});
  wrap.querySelector(".g-additem").addEventListener("click",function(){addItemRow(items,{});});
  wrap.querySelector(".g-del").addEventListener("click",function(){wrap.remove();});
}
function esc(s){var d=document.createElement("div");d.textContent=s;return d.innerHTML.replace(/"/g,"&quot;");}
(MENU.groups||[]).forEach(addGroup);
document.getElementById("addGroup").addEventListener("click",function(){addGroup();});

document.getElementById("menuForm").addEventListener("submit",function(e){
  var groups=[];
  document.querySelectorAll(".mn-group").forEach(function(g){
    var title=g.querySelector(".g-title").value.trim();
    var accent=g.querySelector(".g-accent").value;
    var note=g.querySelector(".mn-note").value.trim();
    var items=[];
    g.querySelectorAll(".mn-item").forEach(function(r){
      var name=r.querySelector(".i-name").value.trim(); if(!name) return;
      var price=parseFloat(r.querySelector(".i-price").value)||0;
      var tags=r.querySelector(".i-tags").value.split(",").map(function(x){return x.trim();}).filter(Boolean);
      var allergens=r.querySelector(".i-allergens").value.split(",").map(function(x){return x.trim().toLowerCase();}).filter(Boolean);
      var slug=r.dataset.slug||slugify(name);
      var visible=r.querySelector(".i-visible").checked;
      var stockRaw=r.querySelector(".i-stock").value.trim();
      var stock=stockRaw===""?null:Math.max(0,parseInt(stockRaw,10)||0);
      var preorder=r.querySelector(".i-preorder").checked;
      var prehRaw=r.querySelector(".i-prehours").value.trim();
      var preorderHours=preorder?(parseInt(prehRaw,10)||48):null;
      items.push({slug:slug,name:name,desc:r.querySelector(".i-desc").value.trim(),price:price,tags:tags,allergens:allergens,visible:visible,stock:stock,preorder:preorder,preorderHours:preorderHours});
    });
    if(title) groups.push({id:slugify(title),title:title,accent:accent,note:note,items:items});
  });
  document.getElementById("menuJson").value=JSON.stringify({intro:document.getElementById("mnIntro").value.trim(),groups:groups});
});
</script>
<?php lt_admin_foot(); ?>
