<?php
$META_TITLE = 'Checkout'; $META_DESC = 'Order VentStudio Street Food for delivery across Your City, or collection from the van.';
require __DIR__ . '/../inc/head.php';
$sym = settings('currencySymbol','£');
$fee = (float)settings('deliveryFee', 2.99);
$min = (float)settings('minOrder', 15);
$free = (float)settings('freeDeliveryOver', 30);
$city = settings('deliveryCity','Your City');
$pcs = implode(', ', (array)settings('deliveryPostcodes', ['IP1','IP2','IP3','IP4','IP5']));
$fulf = (array)settings('fulfilment', ['delivery'=>true,'collection'=>true]);
$hours = (array)settings('hours', []);
$openFrom = (string)settings('ordersOpenFrom', '');
$slotMin = (int)settings('slotMinutes', 30);
$cancelled = isset($_GET['cancelled']);
$preorderMap = [];
foreach ((array)g('menu.groups') as $grp) {
  foreach ((array)($grp['items'] ?? []) as $it) {
    if (!empty($it['preorder'])) $preorderMap[$it['slug']] = (int)($it['preorderHours'] ?? 48);
  }
}
?>
<section class="page-hero" style="background:var(--griddle);color:var(--batter)">
  <div class="wrap"><p class="eyebrow" style="color:var(--honey)">Delivery across <?= e($city) ?> · or collect from the van</p>
  <h1 style="color:var(--batter)">Checkout</h1></div>
</section>

<section class="section">
  <div class="wrap">
    <?php if ($cancelled): ?><div class="notice">Payment cancelled — your order is still here whenever you're ready.</div><?php endif; ?>
    <?php if (!(bool)settings('orderingOpen', true)): ?><div class="notice" style="background:var(--hotsauce);color:var(--batter)"><strong>We're closed right now.</strong> You can still pre-order our BBQ specials (Whole/Half Smoked Chicken, BBQ Pork Ribs) — everything else reopens when we're back on.</div><?php endif; ?>
    <div class="checkout-grid" id="coRoot">
      <div class="co-card">
        <form id="coForm">
          <?php if (!empty($fulf['delivery']) && !empty($fulf['collection'])): ?>
          <div class="field"><span>How would you like it?</span>
            <div class="seg">
              <label class="seg-opt on"><input type="radio" name="fulfilment" value="delivery" checked> Delivery</label>
              <label class="seg-opt"><input type="radio" name="fulfilment" value="collection"> Collection</label>
            </div>
          </div>
          <?php else: ?><input type="hidden" name="fulfilment" value="<?= !empty($fulf['collection']) && empty($fulf['delivery']) ? 'collection':'delivery' ?>"><?php endif; ?>

          <div id="addrBlock">
            <label class="field"><span>Address line 1</span><input name="line1" autocomplete="address-line1"></label>
            <label class="field"><span>Address line 2 <small style="font-weight:400;color:#8a7f74">(optional)</small></span><input name="line2" autocomplete="address-line2"></label>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
              <label class="field"><span>Town/City</span><input name="city" value="<?= e($city) ?>" autocomplete="address-level2"></label>
              <label class="field"><span>Postcode</span><input name="postcode" placeholder="IP1 1AA" autocomplete="postal-code"></label>
            </div>
            <p class="cart-note" style="text-align:left;margin:-6px 0 14px">We currently deliver to <?= e($city) ?> only (<?= e($pcs) ?>).</p>
          </div>

          <label class="field"><span>Name</span><input name="name" required autocomplete="name"></label>
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
            <label class="field"><span>Phone</span><input name="phone" required inputmode="tel" autocomplete="tel"></label>
            <label class="field"><span>Email</span><input name="email" required type="email" autocomplete="email"></label>
          </div>
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
            <label class="field"><span>Day</span><input type="date" name="date" id="coDate"></label>
            <label class="field"><span>Time</span><select name="slot" id="coSlot"></select></label>
          </div>
          <p class="cart-note" style="text-align:left;margin:-6px 0 14px">Choose a day and time within our opening hours — up to 3 weeks ahead.</p>
          <div id="coPreNote" class="co-prenote" style="display:none">⏱ <strong>Pre-order items in your basket.</strong> The earliest slot is set to 48 hours ahead — pick any day and time from there.</div>
          <label class="field"><span>Notes <small style="font-weight:400;color:#8a7f74">(allergies, no jalapeños, gate code…)</small></span><textarea name="notes" rows="2"></textarea></label>

          <div class="allergy-box">
            <span class="allergy-box-title">⚠ Allergy notice</span>
            <p>Please inform us of any food allergies or intolerances before ordering. Food is prepared in a kitchen where allergens are handled.</p>
          </div>

          <div id="coErr" class="notice" style="background:var(--hotsauce);color:var(--batter);display:none"></div>
          <button class="btn btn-primary btn-block" id="payBtn" type="submit">Pay &amp; place order</button>
          <p class="cart-note" style="text-align:left;margin-top:12px">Secure card payment by Stripe. We'll email your confirmation and text you when it's on the way.</p>
        </form>
      </div>
      <div class="co-card co-summary">
        <h2 style="font-size:1.5rem;margin-bottom:16px">Your order</h2>
        <div id="coLines"></div>
        <div class="co-line" id="coSubRow"><span>Subtotal</span><span class="mono" id="coSub"><?= e($sym) ?>0.00</span></div>
        <div class="co-line" id="coFeeRow"><span>Delivery</span><span class="mono" id="coFee">—</span></div>
        <div class="co-total"><span>Total</span><strong id="coTotal"><?= e($sym) ?>0.00</strong></div>
        <p class="cart-note" id="coHint" style="text-align:left"></p>
        <p class="cart-note" style="text-align:left"><a href="<?= url('menu') ?>">← Add more from the menu</a></p>
      </div>
    </div>
    <div id="coEmpty" style="display:none;text-align:center;padding:40px 0">
      <p>Your basket is empty.</p><a class="btn btn-primary" href="<?= url('menu') ?>">Browse the menu</a>
    </div>
  </div>
</section>

<script>
window.LT = window.LT || {};
window.LT.delivery = { fee: <?= json_encode($fee) ?>, min: <?= json_encode($min) ?>, free: <?= json_encode($free) ?>, sym: <?= json_encode($sym) ?> };
window.LT.hours = <?= json_encode($hours ?: new stdClass()) ?>;
window.LT.ordersOpenFrom = <?= json_encode($openFrom) ?>;
window.LT.slotMin = <?= json_encode($slotMin ?: 30) ?>;
window.LT.preorder = <?= json_encode($preorderMap ?: new stdClass()) ?>;
window.LT.orderingOpen = <?= json_encode((bool)settings('orderingOpen', true)) ?>;
document.addEventListener('DOMContentLoaded', function(){
  var D = window.LT.delivery;
  var slotOk = true;
  function esc(s){var d=document.createElement('div');d.textContent=s;return d.innerHTML;}
  function mode(){ var r=document.querySelector('input[name=fulfilment]:checked'); return r?r.value:'delivery'; }
  function render(){
    var items = window.LTCart ? window.LTCart.items() : [];
    var root=document.getElementById('coRoot'), empty=document.getElementById('coEmpty');
    if(!items.length){ root.style.display='none'; empty.style.display='block'; return; }
    var lines=document.getElementById('coLines'); lines.innerHTML='';
    var sub=0;
    items.forEach(function(it){ sub+=it.qty*it.price;
      var el=document.createElement('div'); el.className='co-line';
      el.innerHTML='<span>'+it.qty+'× '+esc(it.name)+'</span><span class="mono">'+D.sym+(it.qty*it.price).toFixed(2)+'</span>';
      lines.appendChild(el);
    });
    var m=mode(), fee=0, total=sub, hint='';
    var addr=document.getElementById('addrBlock');
    if(m==='delivery'){
      addr.style.display='';
      if(sub < D.min){ hint='Minimum delivery order is '+D.sym+D.min.toFixed(2)+' — add '+D.sym+(D.min-sub).toFixed(2)+' more.'; }
      fee = sub >= D.free ? 0 : D.fee;
      total = sub+fee;
      document.getElementById('coFeeRow').style.display='';
      document.getElementById('coFee').textContent = fee>0 ? D.sym+fee.toFixed(2) : 'FREE';
      if(fee>0 && !hint) hint='Free delivery over '+D.sym+D.free.toFixed(2)+' (add '+D.sym+(D.free-sub).toFixed(2)+').';
    } else {
      addr.style.display='none';
      document.getElementById('coFeeRow').style.display='none';
      total=sub;
    }
    document.getElementById('coSub').textContent=D.sym+sub.toFixed(2);
    document.getElementById('coTotal').textContent=D.sym+total.toFixed(2);
    if(!slotOk && !hint) hint = "We're closed then — pick another day or time.";
    document.getElementById('coHint').textContent=hint;
    var btn=document.getElementById('payBtn');
    var PREO = window.LT.preorder||{};
    var closed = (window.LT.orderingOpen===false);
    var hasNonPre = items.some(function(it){ return !PREO[it.slug]; });
    if(closed && hasNonPre && !hint) hint="We're closed right now — only pre-order BBQ specials can be ordered. Remove the other items to continue.";
    var block = (m==='delivery' && sub < D.min) || !slotOk || (closed && hasNonPre);
    btn.disabled=block; btn.style.opacity=block?0.5:1;
    // segment styling
    document.querySelectorAll('.seg-opt').forEach(function(o){o.classList.toggle('on', o.querySelector('input').checked);});
  }
  var WD=['sun','mon','tue','wed','thu','fri','sat'];
  var HRS=window.LT.hours||{}, OPENFROM=window.LT.ordersOpenFrom||'', SLOT=window.LT.slotMin||30, PRE=window.LT.preorder||{};
  function iso(d){return d.getFullYear()+'-'+('0'+(d.getMonth()+1)).slice(-2)+'-'+('0'+d.getDate()).slice(-2);}
  function hm(s){var p=(s||'').split(':');return (+p[0])*60+(+(p[1]||0));}
  function dayHours(dateStr){var d=new Date(dateStr+'T00:00');var h=HRS[WD[d.getDay()]];return (h&&h.open)?h:null;}
  function preLeadH(){ var items=window.LTCart?window.LTCart.items():[]; var mx=0; items.forEach(function(it){var h=PRE[it.slug]; if(h&&h>mx) mx=h;}); return mx; }
  function preMinDate(){ var h=preLeadH(); return h? new Date(Date.now()+h*3600*1000) : null; }
  function firstOpen(){
    var start=new Date(); start.setHours(0,0,0,0);
    if(OPENFROM){var of=new Date(OPENFROM+'T00:00'); if(of>start) start=of;}
    var pm=preMinDate(); if(pm){ var pmd=new Date(pm); pmd.setHours(0,0,0,0); if(pmd>start) start=pmd; }
    for(var i=0;i<45;i++){var d=new Date(start); d.setDate(start.getDate()+i); if(dayHours(iso(d))) return iso(d);}
    return iso(start);
  }
  function buildSlots(dateStr){
    var sEl=document.getElementById('coSlot'); var h=dayHours(dateStr);
    if(!h){ sEl.innerHTML='<option value="">Closed this day</option>'; slotOk=false; return; }
    var from=hm(h.from), to=hm(h.to), out=[], todayStr=iso(new Date());
    if(dateStr===todayStr){ var nowm=new Date().getHours()*60+new Date().getMinutes()+20; if(nowm>from) from=Math.ceil(nowm/SLOT)*SLOT; }
    var pm=preMinDate();
    if(pm && dateStr===iso(pm)){ var pmm=Math.ceil((pm.getHours()*60+pm.getMinutes())/SLOT)*SLOT; if(pmm>from) from=pmm; }
    for(var t=from;t<=to-SLOT;t+=SLOT){ out.push(('0'+Math.floor(t/60)).slice(-2)+':'+('0'+(t%60)).slice(-2)); }
    if(dateStr===todayStr && !pm && out.length) out.unshift('ASAP');
    if(!out.length){ sEl.innerHTML='<option value="">No slots left</option>'; slotOk=false; return; }
    slotOk=true; sEl.innerHTML=out.map(function(s,i){return '<option'+(i===0?' selected':'')+'>'+s+'</option>';}).join('');
  }
  (function initTime(){
    var dEl=document.getElementById('coDate'); if(!dEl) return;
    var first=firstOpen(), maxD=new Date(first+'T00:00'); maxD.setDate(maxD.getDate()+21);
    dEl.min=first; dEl.max=iso(maxD); if(!dEl.value || dEl.value<first) dEl.value=first;
    var ph=preLeadH(), pn=document.getElementById('coPreNote');
    if(pn){ if(ph>0){ pn.style.display='block'; pn.innerHTML='⏱ <strong>Pre-order items in your basket.</strong> The earliest slot is set to '+ph+' hours ('+Math.round(ph/24)+' days) ahead — pick any day and time from there.'; } else { pn.style.display='none'; } }
    buildSlots(dEl.value);
    dEl.addEventListener('change', function(){ if(dEl.value<dEl.min) dEl.value=dEl.min; buildSlots(dEl.value); render(); });
  })();
  document.addEventListener('change', function(e){ if(e.target.name==='fulfilment') render(); });
  render();
  var form=document.getElementById('coForm');
  form && form.addEventListener('submit', function(e){
    e.preventDefault();
    var btn=document.getElementById('payBtn'), err=document.getElementById('coErr');
    var items=(window.LTCart?window.LTCart.items():[]).map(function(i){return {slug:i.slug,qty:i.qty,note:i.note||''};});
    if(!items.length){ err.style.display='block'; err.textContent='Your basket is empty.'; return; }
    var fd=new FormData(form), payload={items:items,fulfilment:mode()};
    ['name','phone','email','notes','line1','line2','city','postcode'].forEach(function(k){ payload[k]=fd.get(k)||''; });
    (function(){ var dt=fd.get('date'), sl=fd.get('slot')||'ASAP';
      payload.order_date = dt||''; payload.order_slot = fd.get('slot')||'';
      if(dt){ var d=new Date(dt+'T00:00'); var lbl=d.toLocaleDateString('en-GB',{weekday:'short',day:'numeric',month:'short'});
        payload.time = (sl==='ASAP') ? ('ASAP · '+lbl) : (lbl+' · '+sl); } else { payload.time = sl; } })();
    btn.disabled=true; btn.textContent='Taking you to payment…'; err.style.display='none';
    fetch('/checkout/',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(payload)})
      .then(function(r){return r.json();})
      .then(function(d){ if(d.url){ window.location.href=d.url; } else { err.style.display='block'; err.textContent=d.error||'Something went wrong.'; btn.disabled=false; btn.textContent='Pay & place order'; }})
      .catch(function(){ err.style.display='block'; err.textContent='Network error — please try again.'; btn.disabled=false; btn.textContent='Pay & place order'; });
  });
});
</script>
<?php require __DIR__ . '/../inc/footer.php'; ?>
