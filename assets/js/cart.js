/* VentStudio — client-side order cart with modifier rules & popups. */
(function () {
  "use strict";
  var KEY = "lt_cart_v1";
  var SYM = (window.LT && window.LT.currency) || "£";
  var RULES = (window.LT && window.LT.rules) || { extras: [], extraItems: [], bases: [], baseNames: {}, toppings: {}, toppingFor: {} };
  RULES.toppingFor = RULES.toppingFor || {};

  function load() { try { return JSON.parse(localStorage.getItem(KEY)) || {}; } catch (e) { return {}; } }
  function save(c) { localStorage.setItem(KEY, JSON.stringify(c)); }
  function money(v) { return SYM + (Math.round(v * 100) / 100).toFixed(2); }
  function esc(s) { var d = document.createElement("div"); d.textContent = s; return d.innerHTML; }
  function title(slug) { return (slug || "").split("-").map(function (w) { return w.charAt(0).toUpperCase() + w.slice(1); }).join(" "); }

  var cart = load();
  function keyFor(slug, note) { return note ? slug + "~" + note : slug; }
  function count() { return Object.keys(cart).reduce(function (n, k) { return n + cart[k].qty; }, 0); }
  function total() { return Object.keys(cart).reduce(function (s, k) { return s + cart[k].qty * cart[k].price; }, 0); }
  function hasSlug(slug) { return Object.keys(cart).some(function (k) { return cart[k].slug === slug; }); }

  function addLine(slug, baseName, price, note) {
    note = note || "";
    var k = keyFor(slug, note);
    var name = baseName + (note ? " — " + note : "");
    if (!cart[k]) cart[k] = { slug: slug, name: name, price: +price, qty: 0, note: note };
    cart[k].qty += 1; save(cart); render(); flash(); toast("Added " + name);
  }
  function setQty(k, q) { if (!cart[k]) return; cart[k].qty = Math.max(0, q); if (!cart[k].qty) delete cart[k]; save(cart); render(); }

  /* ---- rule handling on add ---- */
  function baseLinesInCart() {
    var seen = {}, out = [];
    Object.keys(cart).forEach(function (k) {
      var it = cart[k];
      if (RULES.bases.indexOf(it.slug) >= 0 && !it.note && !seen[it.name]) { seen[it.name] = 1; out.push(it.name); }
    });
    return out;
  }
  function handleAdd(slug, name, price) {
    if (RULES.toppings && RULES.toppings[slug]) {
      var need = RULES.toppings[slug];
      if (!hasSlug(need)) { toast("Add a " + title(need) + " first, then you can add this topping.", true); return; }
      addLine(slug, name, price, "with " + title(need)); return;
    }
    if (RULES.extras.indexOf(slug) >= 0) {
      var bases = baseLinesInCart();
      if (!bases.length) { toast("Add a burger, box, wrap or loaded fries first — extras go on those.", true); return; }
      pickBase(slug, name, price, bases); return;
    }
    if (RULES.bases.indexOf(slug) >= 0) { addLine(slug, name, price, ""); offerExtras(name); return; }
    if (RULES.toppingFor && RULES.toppingFor[slug]) { addLine(slug, name, price, ""); offerTopping(slug, name); return; }
    addLine(slug, name, price, "");
  }

  /* ---- modal + toast styles ---- */
  var css = ""
    + ".lt-modal{position:fixed;inset:0;z-index:130;display:none;align-items:center;justify-content:center;padding:18px}"
    + ".lt-modal.open{display:flex}"
    + ".lt-modal-ov{position:absolute;inset:0;background:rgba(27,21,18,.55)}"
    + ".lt-modal-card{position:relative;background:#FBEAD1;border-radius:16px;max-width:420px;width:100%;padding:22px;box-shadow:0 24px 60px rgba(0,0,0,.35);max-height:86vh;overflow:auto}"
    + ".lt-modal-card h3{font-family:'Bricolage',system-ui,sans-serif;font-weight:800;font-size:1.4rem;margin:0 0 4px;color:#1B1512}"
    + ".lt-modal-card p{margin:0 0 14px;color:#5d524b;font-size:.95rem}"
    + ".lt-opt{display:flex;justify-content:space-between;align-items:center;gap:10px;width:100%;text-align:left;background:#fff;border:1.5px solid rgba(27,21,18,.14);border-radius:12px;padding:12px 14px;margin-bottom:8px;cursor:pointer;font-family:'Hanken',system-ui,sans-serif;font-weight:600;font-size:1rem;color:#1B1512}"
    + ".lt-opt:hover{border-color:#E8431F}"
    + ".lt-opt .p{font-family:'SpaceMono',monospace;font-weight:700;white-space:nowrap}"
    + ".lt-modal-actions{display:flex;gap:10px;margin-top:8px}"
    + ".lt-btn{flex:1;border:none;border-radius:999px;padding:12px;font-weight:700;cursor:pointer;font-family:'Hanken',system-ui,sans-serif;font-size:1rem}"
    + ".lt-btn-primary{background:#E8431F;color:#FBEAD1}.lt-btn-ghost{background:transparent;border:2px solid #1B1512;color:#1B1512}"
    + ".lt-toast{position:fixed;left:50%;bottom:24px;transform:translateX(-50%) translateY(20px);background:#1B1512;color:#FBEAD1;padding:11px 20px;border-radius:999px;font-family:'Hanken',system-ui,sans-serif;font-weight:600;font-size:.92rem;z-index:140;opacity:0;transition:.2s;pointer-events:none;max-width:90vw;text-align:center}"
    + ".lt-toast.show{opacity:1;transform:translateX(-50%) translateY(0)}"
    + ".lt-toast.warn{background:#E8431F}";
  var st = document.createElement("style"); st.textContent = css; document.head.appendChild(st);

  var modal = document.createElement("div"); modal.className = "lt-modal";
  modal.innerHTML = '<div class="lt-modal-ov"></div><div class="lt-modal-card"><h3></h3><p></p><div class="lt-modal-body"></div><div class="lt-modal-actions"></div></div>';
  function mount() { if (!modal.parentNode && document.body) document.body.appendChild(modal); }
  if (document.readyState !== "loading") mount(); else document.addEventListener("DOMContentLoaded", mount);
  var mH = modal.querySelector("h3"), mP = modal.querySelector("p"), mBody = modal.querySelector(".lt-modal-body"), mAct = modal.querySelector(".lt-modal-actions");
  modal.querySelector(".lt-modal-ov").addEventListener("click", closeModal);
  function openModal() { mount(); modal.classList.add("open"); }
  function closeModal() { modal.classList.remove("open"); }

  function pickBase(slug, name, price, bases) {
    mH.textContent = "Add " + name + " to which item?";
    mP.textContent = "Your extra will be added to the item you choose.";
    mBody.innerHTML = "";
    bases.forEach(function (label) {
      var b = document.createElement("button"); b.type = "button"; b.className = "lt-opt";
      b.innerHTML = "<span>" + esc(label) + "</span><span class='p'>+ " + money(+price) + "</span>";
      b.addEventListener("click", function () { addLine(slug, name, price, "with " + label); closeModal(); open(); });
      mBody.appendChild(b);
    });
    mAct.innerHTML = "";
    var cancel = document.createElement("button"); cancel.type = "button"; cancel.className = "lt-btn lt-btn-ghost"; cancel.textContent = "Cancel";
    cancel.addEventListener("click", closeModal); mAct.appendChild(cancel);
    openModal();
  }
  function offerExtras(baseName) {
    if (!RULES.extraItems || !RULES.extraItems.length) { open(); return; }
    mH.textContent = "Add extras to your " + baseName + "?";
    mP.textContent = "Tap to add — or skip.";
    mBody.innerHTML = "";
    RULES.extraItems.forEach(function (x) {
      var b = document.createElement("button"); b.type = "button"; b.className = "lt-opt";
      b.innerHTML = "<span>" + esc(x.name) + "</span><span class='p'>+ " + money(x.price) + "</span>";
      b.addEventListener("click", function () { addLine(x.slug, x.name, x.price, "with " + baseName); });
      mBody.appendChild(b);
    });
    mAct.innerHTML = "";
    var no = document.createElement("button"); no.type = "button"; no.className = "lt-btn lt-btn-ghost"; no.textContent = "No thanks";
    no.addEventListener("click", closeModal);
    var done = document.createElement("button"); done.type = "button"; done.className = "lt-btn lt-btn-primary"; done.textContent = "Done";
    done.addEventListener("click", function () { closeModal(); open(); });
    mAct.appendChild(no); mAct.appendChild(done);
    openModal();
  }

  function offerTopping(baseSlug, baseName) {
    var top = RULES.toppingFor[baseSlug];
    if (!top) { open(); return; }
    mH.textContent = "Add a topping to your " + baseName + "?";
    mP.textContent = "Banana, strawberry, extra Nutella, whipped cream or marshmallows.";
    mBody.innerHTML = "";
    var b = document.createElement("button"); b.type = "button"; b.className = "lt-opt";
    b.innerHTML = "<span>" + esc(top.name) + "</span><span class='p'>+ " + money(top.price) + "</span>";
    b.addEventListener("click", function () { addLine(top.slug, top.name, top.price, "with " + baseName); });
    mBody.appendChild(b);
    mAct.innerHTML = "";
    var no = document.createElement("button"); no.type = "button"; no.className = "lt-btn lt-btn-ghost"; no.textContent = "No thanks";
    no.addEventListener("click", closeModal);
    var done = document.createElement("button"); done.type = "button"; done.className = "lt-btn lt-btn-primary"; done.textContent = "Done";
    done.addEventListener("click", function () { closeModal(); open(); });
    mAct.appendChild(no); mAct.appendChild(done);
    openModal();
  }

  var toastEl;
  function toast(msg, warn) {
    if (!toastEl) { toastEl = document.createElement("div"); toastEl.className = "lt-toast"; (document.body || document.documentElement).appendChild(toastEl); }
    toastEl.textContent = msg; toastEl.className = "lt-toast show" + (warn ? " warn" : "");
    clearTimeout(toastEl._t); toastEl._t = setTimeout(function () { toastEl.className = "lt-toast" + (warn ? " warn" : ""); }, 2800);
  }

  /* ---- drawer render ---- */
  function render() {
    var badge = document.getElementById("cartCount");
    var n = count();
    if (badge) { badge.textContent = n; badge.hidden = n === 0; }
    var wrap = document.getElementById("cartItems"), empty = document.getElementById("cartEmpty"), foot = document.getElementById("cartFoot"), totalEl = document.getElementById("cartTotal");
    if (!wrap) return;
    var keys = Object.keys(cart);
    wrap.innerHTML = "";
    if (!keys.length) { if (empty) empty.style.display = ""; if (foot) foot.hidden = true; return; }
    if (empty) empty.style.display = "none"; if (foot) foot.hidden = false;
    keys.forEach(function (k) {
      var it = cart[k], ek = encodeURIComponent(k);
      var row = document.createElement("div"); row.className = "cart-row";
      row.innerHTML =
        '<div class="ci-name">' + esc(it.name) + '<div class="ci-price mono">' + money(it.price) + '</div></div>' +
        '<div class="qty"><button data-dec="' + ek + '">−</button><span>' + it.qty + '</span><button data-inc="' + ek + '">+</button></div>';
      wrap.appendChild(row);
    });
    if (totalEl) totalEl.textContent = money(total());
  }

  var drawer;
  function open() { drawer = drawer || document.getElementById("cartDrawer"); if (drawer) { drawer.classList.add("open"); drawer.setAttribute("aria-hidden", "false"); } }
  function close() { drawer = drawer || document.getElementById("cartDrawer"); if (drawer) { drawer.classList.remove("open"); drawer.setAttribute("aria-hidden", "true"); } }
  function flash() { var b = document.getElementById("cartBtn"); if (b && b.animate) b.animate([{ transform: "scale(1)" }, { transform: "scale(1.25)" }, { transform: "scale(1)" }], { duration: 260 }); }

  document.addEventListener("click", function (e) {
    var a = e.target.closest("[data-add]");
    if (a) { e.preventDefault(); handleAdd(a.getAttribute("data-add"), a.getAttribute("data-name"), a.getAttribute("data-price")); return; }
    if (e.target.closest("#cartBtn")) { open(); render(); return; }
    if (e.target.closest("[data-cart-close]")) { close(); return; }
    var inc = e.target.closest("[data-inc]"); if (inc) { var ik = decodeURIComponent(inc.getAttribute("data-inc")); if (cart[ik]) setQty(ik, cart[ik].qty + 1); return; }
    var dec = e.target.closest("[data-dec]"); if (dec) { var dk = decodeURIComponent(dec.getAttribute("data-dec")); if (cart[dk]) setQty(dk, cart[dk].qty - 1); return; }
  });

  window.LTCart = {
    items: function () { return Object.keys(cart).map(function (k) { return { slug: cart[k].slug, name: cart[k].name, price: cart[k].price, qty: cart[k].qty, note: cart[k].note || "" }; }); },
    total: total, count: count, money: money, clear: function () { cart = {}; save(cart); render(); }
  };

  render();
})();
