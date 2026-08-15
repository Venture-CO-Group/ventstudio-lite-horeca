(function () {
  "use strict";
  var state = (window.LT && window.LT.content) || {};
  var CSRF  = (window.LT && window.LT.csrf) || "";

  var GROUPS = (window.LT && window.LT.groups) || [
    { id: "home",    label: "Home page", group: "Pages",         sections: ["hero", "benefits", "ugc", "testimonials", "brands", "howItWorks", "partners", "numbers", "gallery", "newsletter", "carousels", "letstalk", "followUs"] },
    { id: "about",   label: "About",     group: "Pages",         sections: ["about"] },
    { id: "team",    label: "Team",      group: "Pages",         sections: ["team"] },
    { id: "faq",     label: "FAQ",       group: "Pages",         sections: ["faq"] },
    { id: "contact", label: "Contact & Demo", group: "Pages",    sections: ["contact", "demo"] },
    { id: "legal",   label: "Legal",     group: "Pages",         sections: ["legal"] },
    { id: "global",  label: "Global",    group: "Configuration", sections: ["nav", "footer", "seo", "settings"] }
  ];
  var SEC = {
    hero: "Hero", benefits: "What VentStudio brings", ugc: "UGC statement", howItWorks: "How it works", testimonials: "Testimonials",
    brands: "Global brands", partners: "Rights holders", numbers: "VentStudio in numbers", gallery: "Gallery (home grid)",
    newsletter: "Newsletter", carousels: "Photo carousels", letstalk: "Let's talk", followUs: "Follow us",
    about: "About page", team: "Team", faq: "FAQ", contact: "Contact form", demo: "Book a demo", legal: "Legal pages",
    nav: "Navigation", footer: "Footer", seo: "SEO defaults", settings: "Site settings"
  };
  var FIELD = {
    headline: "Headline", subheadline: "Subheadline", ctaLabel: "Button label", ctaUrl: "Button link",
    title: "Title", subtitle: "Subtitle", body: "Text", lead: "Lead", sub: "Sub-text",
    intro: "Intro", tagline: "Tagline", heading: "Heading", quote: "Quote", name: "Name",
    org: "Organisation", role: "Role", q: "Question", a: "Answer", value: "Value", label: "Label",
    placeholder: "Placeholder", button: "Button", description: "Meta description",
    fName: "Field: Name", fEmail: "Field: Email", fCompany: "Field: Company", fGoals: "Field: Message",
    fSubmit: "Submit button", consent: "Consent line", ctaTitle: "CTA heading",
    brandName: "Brand name", contactEmail: "Contact e-mail", calendlyUrl: "Calendly link",
    gtmId: "Google Tag Manager ID (GTM-XXXXXXX)",
    defaultLang: "Default language (en/hu/es)", url: "Link (URL)", logo: "Logo image",
    photo: "Photo", image: "Image", alt: "Alt text", email: "E-mail",
    linkedin: "LinkedIn URL", facebook: "Facebook URL", instagram: "Instagram URL", tiktok: "TikTok URL",
    social: "Social links", smtp: "E-mail sending (SMTP)", mailchimp: "Mailchimp newsletter",
    host: "SMTP host", port: "SMTP port", secure: "Security (tls / ssl / none)", user: "SMTP username",
    pass: "SMTP password", from: "Sender address", to: "Deliver messages to",
    apiKey: "API key", audienceId: "Audience ID", doubleOptIn: "Double opt-in (true/false)",
    active: "Visible (1 = yes, 0 = no)", cover: "Cover image", duration: "Duration", provider: "Provider",
    purpose: "Purpose", pdf: "PDF link", partnerDocs: "Partner documents", cookieList: "Cookies",
    sections: "Sections", items: "Items", steps: "Steps", stats: "Stats", photos: "Photos",
    one: "Carousel one", two: "Carousel two", newsletterBtn: "Newsletter button", pageIntro: "Page intro",
    victory: "Victory line", companyName: "Company name", offices: "Offices", lines: "Address lines", city: "City",
    contactCta: "Contact button", demoCta: "Demo button", gallery: "Gallery label",
    team: "Team label", blog: "Blog label", faq: "FAQ label", about: "About label"
  };
  var IMG_KEYS = ["image", "photo", "logo", "cover"];
  var LANGS = [["en", "English"], ["hu", "Magyar"], ["es", "Español"]];
  var LANG = "en";

  function el(t, c) { var n = document.createElement(t); if (c) n.className = c; return n; }
  function labelFor(k) { return FIELD[k] || (isNaN(k) ? (String(k).charAt(0).toUpperCase() + String(k).slice(1)) : "#" + (parseInt(k, 10) + 1)); }
  function isPair(v) { return v && typeof v === "object" && !Array.isArray(v) && ("en" in v) && (("hu" in v) || ("es" in v)); }
  function singular(s) { return (s || "Item").replace(/s$/, "").replace(/\(.*/, "").trim() || "Item"; }
  function getAt(p) { return p.reduce(function (o, k) { return o[k]; }, state); }
  function setAt(p, v) { var o = state; for (var i = 0; i < p.length - 1; i++) o = o[p[i]]; o[p[p.length - 1]] = v; }
  function autoGrow(ta) { ta.style.height = "auto"; ta.style.height = Math.min(ta.scrollHeight + 2, 480) + "px"; }

  /* ---------- fields ---------- */
  function pairField(label, path) {
    var wrap = el("div", "field");
    var lb = el("div", "field-label"); lb.textContent = label;
    var tag = el("span", "lang-tag"); tag.textContent = LANG.toUpperCase(); lb.appendChild(tag);
    wrap.appendChild(lb);
    var ta = el("textarea", "txt-area");
    ta.value = (getAt(path)[LANG] != null ? getAt(path)[LANG] : "");
    ta.addEventListener("input", function () { getAt(path)[LANG] = ta.value; autoGrow(ta); });
    setTimeout(function () { autoGrow(ta); }, 0);
    wrap.appendChild(ta);
    return wrap;
  }
  function imageField(label, path, val) {
    var wrap = el("div", "field img-field");
    var lb = el("div", "field-label"); lb.textContent = label; wrap.appendChild(lb);
    var row = el("div", "img-row");
    var th = el("div", "img-thumb");
    var im = document.createElement("img");
    function setThumb(v) {
      if (!v) { th.classList.add("empty"); im.removeAttribute("src"); return; }
      th.classList.remove("empty");
      im.src = /^https?:|^\//.test(v) ? v : "/assets/img/" + v;
    }
    im.onerror = function () { th.classList.add("empty"); };
    th.appendChild(im); setThumb(val);
    var inp = el("input", "txt"); inp.type = "text"; inp.value = val == null ? "" : val;
    inp.addEventListener("input", function () { setAt(path, inp.value); setThumb(inp.value); });
    var btn = el("button", "btn-studio btn-mini"); btn.type = "button"; btn.textContent = "Browse…";
    btn.addEventListener("click", function () { openMediaPicker(function (rel) { inp.value = rel; setAt(path, rel); setThumb(rel); }); });
    row.appendChild(th); row.appendChild(inp); row.appendChild(btn);
    wrap.appendChild(row); return wrap;
  }
  function singleField(label, path, val) {
    var wrap = el("div", "field single");
    var lb = el("div", "field-label"); lb.textContent = label; wrap.appendChild(lb);
    var inp = el("input", "txt"); inp.type = "text"; inp.value = val == null ? "" : val;
    inp.addEventListener("input", function () {
      var v = inp.value;
      if (typeof val === "number" && v !== "" && !isNaN(v)) v = parseFloat(v);
      if (typeof val === "boolean") v = /^(1|true|yes)$/i.test(v);
      setAt(path, v);
    });
    wrap.appendChild(inp); return wrap;
  }
  function itemTitle(item, label, idx) {
    if (item && typeof item === "object") {
      var cand = ["name", "title", "label", "heading", "alt", "q", "quote", "city", "value"];
      for (var i = 0; i < cand.length; i++) {
        var v = item[cand[i]];
        if (isPair(v)) v = v.en || v.hu || v.es;
        if (typeof v === "string" && v.trim()) return v.trim().slice(0, 60);
      }
    } else if (typeof item === "string" && item.trim()) return item.trim().slice(0, 60);
    return singular(label) + " " + (idx + 1);
  }
  function itemThumb(item) {
    if (item && typeof item === "object")
      for (var i = 0; i < IMG_KEYS.length; i++) {
        var v = item[IMG_KEYS[i]];
        if (typeof v === "string" && v) return /^https?:|^\//.test(v) ? v : "/assets/img/" + v;
      }
    return null;
  }

  function arrayField(label, arr, path) {
    var wrap = el("div", "field arr");
    var lb = el("div", "field-label"); lb.textContent = label + " (" + arr.length + ")"; wrap.appendChild(lb);
    var list = el("div", "arr-list");
    arr.forEach(function (item, idx) {
      var box = el("div", "arr-item collapsed");
      box.draggable = false;
      var head = el("div", "arr-head");
      var grip = el("span", "arr-grip"); grip.innerHTML = "⋮⋮"; grip.title = "Drag to reorder";
      var tw = el("span", "arr-thumbwrap");
      var thumb = itemThumb(item);
      if (thumb) { var ti = document.createElement("img"); ti.src = thumb; ti.className = "arr-thumb"; tw.appendChild(ti); }
      var tag = el("span", "arr-tag"); tag.textContent = itemTitle(item, label, idx);
      var ctr = el("div", "arr-ctr");
      var rm = el("button", "btn-studio btn-mini btn-danger"); rm.type = "button"; rm.textContent = "✕"; rm.title = "Remove";
      rm.addEventListener("click", function (e) { e.stopPropagation(); if (confirm("Remove this item?")) { getAt(path).splice(idx, 1); refresh(); } });
      ctr.appendChild(rm);
      head.appendChild(grip); head.appendChild(tw); head.appendChild(tag); head.appendChild(ctr);
      head.addEventListener("click", function () { box.classList.toggle("collapsed"); });
      box.appendChild(head);
      var body = el("div", "arr-body");
      if (item && typeof item === "object" && !isPair(item)) buildNode(item, path.concat(idx), body);
      else if (isPair(item)) body.appendChild(pairField("Value", path.concat(idx)));
      else body.appendChild(singleField("Value", path.concat(idx), item));
      box.appendChild(body);

      /* drag & drop reorder */
      grip.addEventListener("mousedown", function () { box.draggable = true; });
      box.addEventListener("dragend", function () { box.draggable = false; });
      box.addEventListener("dragstart", function (e) {
        e.dataTransfer.effectAllowed = "move"; e.dataTransfer.setData("text/plain", String(idx));
        box.classList.add("dragging");
      });
      box.addEventListener("dragover", function (e) { e.preventDefault(); box.classList.add("drag-over"); });
      box.addEventListener("dragleave", function () { box.classList.remove("drag-over"); });
      box.addEventListener("drop", function (e) {
        e.preventDefault(); box.classList.remove("drag-over");
        var from = parseInt(e.dataTransfer.getData("text/plain"), 10);
        if (isNaN(from) || from === idx) return;
        var a = getAt(path); var moved = a.splice(from, 1)[0]; a.splice(idx, 0, moved); refresh();
      });
      list.appendChild(box);
    });
    wrap.appendChild(list);
    var act = el("div", "arr-actions");
    var add = el("button", "btn-studio btn-mini"); add.type = "button"; add.textContent = "+ Add " + singular(label).toLowerCase();
    add.addEventListener("click", function () {
      var a = getAt(path);
      a.push(a.length ? JSON.parse(JSON.stringify(a[a.length - 1])) : {});
      refresh();
      var items = list.parentNode.querySelectorAll(".arr-item"); var lastBox = items[items.length - 1];
      if (lastBox) lastBox.classList.remove("collapsed");
    });
    act.appendChild(add); wrap.appendChild(act); return wrap;
  }

  function buildNode(obj, path, parent) {
    Object.keys(obj).forEach(function (key) {
      var val = obj[key], p = path.concat(key);
      if (isPair(val)) parent.appendChild(pairField(labelFor(key), p));
      else if (Array.isArray(val)) parent.appendChild(arrayField(labelFor(key), val, p));
      else if (val && typeof val === "object") {
        var sub = el("div", "field group"); var lb = el("div", "field-label group-label"); lb.textContent = labelFor(key);
        sub.appendChild(lb); buildNode(val, p, sub); parent.appendChild(sub);
      }
      else if (IMG_KEYS.indexOf(key) !== -1 && (typeof val === "string" || val == null)) parent.appendChild(imageField(labelFor(key), p, val));
      else parent.appendChild(singleField(labelFor(key), p, val));
    });
  }

  /* ---------- media picker ---------- */
  var pickerCb = null, pickerEl = null;
  function openMediaPicker(cb) {
    pickerCb = cb;
    if (pickerEl) { pickerEl.classList.add("open"); return; }
    pickerEl = el("div", "mp-overlay open");
    var card = el("div", "mp-card");
    var head = el("div", "mp-head");
    var h = el("strong"); h.textContent = "Choose an image";
    var close = el("button", "mp-close"); close.type = "button"; close.innerHTML = "&times;";
    close.addEventListener("click", function () { pickerEl.classList.remove("open"); });
    head.appendChild(h); head.appendChild(close); card.appendChild(head);
    var body = el("div", "mp-body"); body.textContent = "Loading…"; card.appendChild(body);
    pickerEl.appendChild(card);
    pickerEl.addEventListener("click", function (e) { if (e.target === pickerEl) pickerEl.classList.remove("open"); });
    document.body.appendChild(pickerEl);
    fetch("media-list.php").then(function (r) { return r.json(); }).then(function (d) {
      body.innerHTML = "";
      Object.keys(d).forEach(function (folder) {
        if (!d[folder].files.length) return;
        var fh = el("div", "mp-folder"); fh.textContent = d[folder].label; body.appendChild(fh);
        var grid = el("div", "mp-grid");
        d[folder].files.forEach(function (f) {
          var cell = el("button", "mp-cell"); cell.type = "button"; cell.title = f;
          var img = document.createElement("img"); img.loading = "lazy"; img.src = "/assets/img/" + folder + "/" + f;
          cell.appendChild(img);
          var nm = el("span"); nm.textContent = f; cell.appendChild(nm);
          cell.addEventListener("click", function () {
            if (pickerCb) pickerCb(folder + "/" + f);
            pickerEl.classList.remove("open");
          });
          grid.appendChild(cell);
        });
        body.appendChild(grid);
      });
    }).catch(function () { body.textContent = "Could not load the media library."; });
  }

  /* ---------- chrome ---------- */
  var current = GROUPS[0] ? GROUPS[0].id : "home";
  function buildSidebar() {
    var nav = document.getElementById("editNav"); if (!nav) return; nav.innerHTML = "";
    if (GROUPS.length < 2) { nav.style.display = "none"; return; }
    var last = null;
    GROUPS.forEach(function (gp) {
      if (gp.group !== last) { var h = el("div", "sb-sub"); h.textContent = gp.group; nav.appendChild(h); last = gp.group; }
      var a = el("button", "seg" + (gp.id === current ? " on" : "")); a.type = "button"; a.textContent = gp.label;
      a.addEventListener("click", function () { current = gp.id; refresh(); window.scrollTo(0, 0); });
      nav.appendChild(a);
    });
  }
  function buildLangBar() {
    var bar = document.getElementById("langBar"); if (!bar) return; bar.innerHTML = "";
    var lab = el("span", "lang-lab"); lab.textContent = "Editing language:"; bar.appendChild(lab);
    LANGS.forEach(function (l) {
      var b = el("button", "lang-pill" + (l[0] === LANG ? " on" : "")); b.type = "button"; b.textContent = l[1];
      b.addEventListener("click", function () { LANG = l[0]; refresh(); });
      bar.appendChild(b);
    });
  }
  function renderPanels() {
    var host = document.getElementById("panels"); host.innerHTML = "";
    var gp = GROUPS.filter(function (x) { return x.id === current; })[0] || GROUPS[0];
    gp.sections.forEach(function (key) {
      if (!state[key]) return;
      var card = el("div", "section-card");
      var h = el("h2"); h.textContent = SEC[key] || key; card.appendChild(h);
      var body = el("div", "card-body"); buildNode(state[key], [key], body); card.appendChild(body);
      host.appendChild(card);
    });
  }
  function refresh() { buildSidebar(); buildLangBar(); renderPanels(); }

  function toast(msg, isErr) {
    var t = document.getElementById("toast"); t.textContent = msg;
    t.className = "toast show" + (isErr ? " err" : ""); setTimeout(function () { t.className = "toast" + (isErr ? " err" : ""); }, 2400);
  }
  var bS = document.getElementById("btnSave");
  if (bS) bS.addEventListener("click", function () {
    var btn = this; btn.disabled = true; var old = btn.textContent; btn.textContent = "Saving…";
    fetch("save.php", { method: "POST", headers: { "Content-Type": "application/json", "X-CSRF": CSRF }, body: JSON.stringify(state) })
      .then(function (r) { return r.json(); })
      .then(function (d) { d.ok ? toast("Saved — your site is updated") : toast(d.error || "Save failed", true); })
      .catch(function () { toast("Network error — not saved", true); })
      .finally(function () { btn.disabled = false; btn.textContent = old; });
  });
  var bD = document.getElementById("btnDiscard");
  if (bD) bD.addEventListener("click", function () {
    if (confirm("Discard unsaved changes and reload the last saved version?")) location.reload();
  });
  var bB = document.getElementById("btnBackup");
  if (bB) bB.addEventListener("click", function () {
    var blob = new Blob([JSON.stringify(state, null, 2)], { type: "application/json" });
    var url = URL.createObjectURL(blob); var a = el("a"); a.href = url; a.download = "lt-content-backup.json";
    document.body.appendChild(a); a.click(); document.body.removeChild(a); URL.revokeObjectURL(url); toast("Backup downloaded");
  });

  /* inject language bar + section nav above the panels */
  var host = document.getElementById("panels");
  var langBar = el("div", "lang-bar"); langBar.id = "langBar";
  var editNav = el("div", "edit-nav"); editNav.id = "editNav";
  host.parentNode.insertBefore(langBar, host);
  host.parentNode.insertBefore(editNav, host);

  refresh();
})();
