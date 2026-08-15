(function () {
  "use strict";
  var P = window.LT_PAGE || {};
  P.title = P.title || { en: "", hu: "", es: "" };
  P.metaDesc = P.metaDesc || { en: "", hu: "", es: "" };
  P.blocks = Array.isArray(P.blocks) ? P.blocks : [];
  var CSRF = window.LT_CSRF || "";
  var LANGS = [["en", "English"], ["hu", "Magyar"], ["es", "Español"]];
  var LANG = "en";
  var sel = -1; // selected block index

  var $ = function (s, c) { return (c || document).querySelector(s); };
  function el(t, c) { var n = document.createElement(t); if (c) n.className = c; return n; }
  function lv(o) { return (o && typeof o === "object") ? (o[LANG] || "") : (o || ""); }
  function setLv(o, v) { o[LANG] = v; }
  function esc(s) { return String(s == null ? "" : s).replace(/[&<>"]/g, function (c) { return ({ "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;" })[c]; }); }
  function ytId(u) { u = String(u || "").trim(); var m = u.match(/(?:youtu\.be\/|v=|embed\/|shorts\/)([A-Za-z0-9_-]{6,})/); if (m) return m[1]; if (/^[A-Za-z0-9_-]{6,}$/.test(u)) return u; return ""; }

  /* ---------- block definitions ---------- */
  var DEFS = {
    heading: { label: "Heading", icon: "H", make: function () { return { type: "heading", text: { en: "New heading", hu: "", es: "" }, level: "h2", align: "left" }; } },
    text:    { label: "Text",    icon: "¶", make: function () { return { type: "text", html: { en: "<p>Write something…</p>", hu: "", es: "" } }; } },
    image:   { label: "Image",   icon: "🖼", make: function () { return { type: "image", src: "", alt: { en: "", hu: "", es: "" }, rounded: true }; } },
    youtube: { label: "YouTube", icon: "▶", make: function () { return { type: "youtube", url: "" }; } },
    button:  { label: "Button",  icon: "◉", make: function () { return { type: "button", label: { en: "Click here", hu: "", es: "" }, url: "", style: "magenta", align: "left" }; } },
    spacer:  { label: "Spacer",  icon: "↕", make: function () { return { type: "spacer", size: 40 }; } }
  };

  /* ---------- block preview (inside canvas) ---------- */
  function previewHTML(b) {
    switch (b.type) {
      case "heading":
        var hl = { h1: 40, h2: 32, h3: 25, h4: 20 }[b.level || "h2"] || 32;
        return '<div style="text-align:' + esc(b.align || "left") + ';font-family:Anton,sans-serif;color:#1B1F3C;font-size:' + hl + 'px;line-height:1.1">' + (esc(lv(b.text)) || '<span style="color:#b6bacd">Heading…</span>') + '</div>';
      case "text":
        return '<div style="font-size:15px;line-height:1.7;color:#1B1F3C">' + (lv(b.html) || '<span style="color:#b6bacd">Empty text block…</span>') + '</div>';
      case "image":
        var src = b.src ? (/^https?:|^\//.test(b.src) ? b.src : "/assets/img/" + b.src) : "";
        return src ? '<img src="' + esc(src) + '" style="width:100%;border-radius:' + (b.rounded ? "14px" : "0") + '">' : '<div style="height:150px;border:2px dashed #d8dbe8;border-radius:12px;display:flex;align-items:center;justify-content:center;color:#b6bacd">No image — pick one on the right</div>';
      case "youtube":
        var id = ytId(b.url);
        return id ? '<div style="position:relative;aspect-ratio:16/9;border-radius:14px;overflow:hidden;background:#000"><img src="https://img.youtube.com/vi/' + esc(id) + '/hqdefault.jpg" style="width:100%;height:100%;object-fit:cover"><span style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;font-size:44px;color:#fff">▶</span></div>' : '<div style="height:120px;border:2px dashed #d8dbe8;border-radius:12px;display:flex;align-items:center;justify-content:center;color:#b6bacd">Paste a YouTube link on the right</div>';
      case "button":
        var bg = b.style === "navy" ? "#1B1F3C" : (b.style === "ghost" ? "transparent" : "#B82786");
        var col = b.style === "ghost" ? "#1B1F3C" : "#fff";
        var bd = b.style === "ghost" ? "border:1.5px solid #1B1F3C;" : "";
        return '<div style="text-align:' + esc(b.align || "left") + '"><span style="display:inline-block;background:' + bg + ';color:' + col + ';' + bd + 'padding:12px 28px;border-radius:999px;font-weight:600">' + (esc(lv(b.label)) || "Button") + '</span></div>';
      case "spacer":
        return '<div style="height:' + (parseInt(b.size, 10) || 0) + 'px;border:1px dashed #d8dbe8;border-radius:6px;display:flex;align-items:center;justify-content:center;color:#b6bacd;font-size:11px">Spacer ' + (parseInt(b.size, 10) || 0) + 'px</div>';
    }
    return "";
  }

  /* ---------- palette ---------- */
  function buildPalette() {
    var pal = $("#bxPalette");
    Object.keys(DEFS).forEach(function (k) {
      var b = el("button", "bx-p-item"); b.type = "button";
      b.innerHTML = '<span class="bx-p-ic">' + DEFS[k].icon + '</span>' + DEFS[k].label;
      b.addEventListener("click", function () { addBlock(k); });
      pal.appendChild(b);
    });
  }
  function addBlock(type) {
    var b = DEFS[type].make();
    var at = sel >= 0 ? sel + 1 : P.blocks.length;
    P.blocks.splice(at, 0, b); sel = at; render();
  }

  /* ---------- canvas ---------- */
  function render() {
    renderLangBar(); renderMeta(); renderCanvas(); renderInspector();
  }
  function renderCanvas() {
    var c = $("#bxCanvas"); c.innerHTML = "";
    if (!P.blocks.length) { c.innerHTML = '<div class="bx-empty">Empty page. Add a block from the left.</div>'; return; }
    P.blocks.forEach(function (b, i) {
      var box = el("div", "bx-block" + (i === sel ? " sel" : "")); box.setAttribute("data-i", i);
      var bar = el("div", "bx-b-bar");
      bar.innerHTML = '<span class="bx-b-type">' + (DEFS[b.type] ? DEFS[b.type].label : b.type) + '</span>';
      var tools = el("span", "bx-b-tools");
      tools.innerHTML = '<button type="button" title="Move up" data-act="up">↑</button><button type="button" title="Move down" data-act="down">↓</button><button type="button" title="Duplicate" data-act="dup">⧉</button><button type="button" class="danger" title="Delete" data-act="del">✕</button>';
      bar.appendChild(tools);
      var body = el("div", "bx-b-body"); body.innerHTML = previewHTML(b);
      box.appendChild(bar); box.appendChild(body);
      box.setAttribute("draggable", "true");
      box.addEventListener("click", function (e) { if (e.target.closest("[data-act]")) return; sel = i; render(); });
      tools.addEventListener("click", function (e) {
        var a = e.target.closest("[data-act]"); if (!a) return; e.stopPropagation();
        var act = a.getAttribute("data-act");
        if (act === "up" && i > 0) { var t = P.blocks[i]; P.blocks[i] = P.blocks[i - 1]; P.blocks[i - 1] = t; sel = i - 1; }
        else if (act === "down" && i < P.blocks.length - 1) { var t2 = P.blocks[i]; P.blocks[i] = P.blocks[i + 1]; P.blocks[i + 1] = t2; sel = i + 1; }
        else if (act === "dup") { P.blocks.splice(i + 1, 0, JSON.parse(JSON.stringify(b))); sel = i + 1; }
        else if (act === "del") { if (confirm("Delete this block?")) { P.blocks.splice(i, 1); sel = -1; } }
        render();
      });
      /* drag reorder */
      box.addEventListener("dragstart", function (e) { e.dataTransfer.setData("text/plain", String(i)); box.classList.add("dragging"); });
      box.addEventListener("dragend", function () { box.classList.remove("dragging"); });
      box.addEventListener("dragover", function (e) { e.preventDefault(); box.classList.add("drop"); });
      box.addEventListener("dragleave", function () { box.classList.remove("drop"); });
      box.addEventListener("drop", function (e) {
        e.preventDefault(); box.classList.remove("drop");
        var from = parseInt(e.dataTransfer.getData("text/plain"), 10);
        if (isNaN(from) || from === i) return;
        var moved = P.blocks.splice(from, 1)[0]; P.blocks.splice(i, 0, moved); sel = i; render();
      });
      c.appendChild(box);
    });
  }

  /* ---------- inspector ---------- */
  function field(label, node) { var w = el("div", "field"); var l = el("div", "field-label"); l.textContent = label; w.appendChild(l); w.appendChild(node); return w; }
  function textInput(val, on) { var i = el("input", "txt"); i.type = "text"; i.value = val || ""; i.addEventListener("input", function () { on(i.value); }); return i; }
  function selectInput(opts, val, on) { var s = el("select", "txt"); opts.forEach(function (o) { var op = el("option"); op.value = o[0]; op.textContent = o[1]; if (o[0] === val) op.selected = true; s.appendChild(op); }); s.addEventListener("change", function () { on(s.value); }); return s; }

  function renderInspector() {
    var ins = $("#bxInspector"); ins.innerHTML = "";
    if (sel < 0 || !P.blocks[sel]) { ins.innerHTML = '<div class="bx-i-empty">Select a block to edit its settings, or add one from the left.</div>'; return; }
    var b = P.blocks[sel];
    var h = el("div", "bx-i-head"); h.textContent = (DEFS[b.type] ? DEFS[b.type].label : b.type) + " settings"; ins.appendChild(h);

    if (b.type === "heading") {
      ins.appendChild(field("Text (" + LANG + ")", textInput(lv(b.text), function (v) { setLv(b.text, v); renderCanvas(); })));
      ins.appendChild(field("Level", selectInput([["h1", "H1"], ["h2", "H2"], ["h3", "H3"], ["h4", "H4"]], b.level, function (v) { b.level = v; renderCanvas(); })));
      ins.appendChild(field("Align", selectInput([["left", "Left"], ["center", "Center"], ["right", "Right"]], b.align, function (v) { b.align = v; renderCanvas(); })));
    } else if (b.type === "text") {
      var ta = el("textarea"); ta.style.minHeight = "220px"; ta.value = lv(b.html);
      ta.addEventListener("input", function () { setLv(b.html, ta.value); renderCanvas(); });
      ins.appendChild(field("HTML (" + LANG + ")", ta));
      var hint = el("div", "bx-hint"); hint.innerHTML = "Allowed tags: p, br, strong, em, a, ul/ol/li, h2–h4, blockquote."; ins.appendChild(hint);
    } else if (b.type === "image") {
      var prev = el("div", "cover-prev" + (b.src ? "" : " empty")); prev.id = "bxImgPrev";
      if (b.src) { var im = el("img"); im.src = /^https?:|^\//.test(b.src) ? b.src : "/assets/img/" + b.src; prev.appendChild(im); }
      ins.appendChild(prev);
      var srcIn = textInput(b.src ? (/^https?:|^\//.test(b.src) ? b.src : "/assets/img/" + b.src) : "", function (v) { b.src = v.replace(/^\/?assets\/img\//, ""); renderCanvas(); });
      srcIn.id = "bxImgInput";
      ins.appendChild(field("Image path / URL", srcIn));
      var row = el("div", "form-row");
      var pick = el("button", "btn-studio btn-mini"); pick.type = "button"; pick.textContent = "Browse…";
      pick.addEventListener("click", function () { window.LtMedia.setTarget("#bxImgInput", "#bxImgPrev"); window.LtMedia.open(); });
      var up = el("button", "btn-studio btn-mini"); up.type = "button"; up.textContent = "Upload"; up.setAttribute("data-mediaupload", "#bxImgInput"); up.setAttribute("data-preview", "#bxImgPrev");
      row.appendChild(pick); row.appendChild(up); ins.appendChild(row);
      // keep model in sync when the picker fills the input
      srcIn.addEventListener("input", function () { b.src = srcIn.value.replace(/^\/?assets\/img\//, ""); renderCanvas(); });
      ins.appendChild(field("Alt text (" + LANG + ")", textInput(lv(b.alt), function (v) { setLv(b.alt, v); })));
      var rw = el("label", "switch-row"); rw.innerHTML = "<span>Rounded corners</span>"; var sw = el("label", "switch"); var cb = el("input"); cb.type = "checkbox"; cb.checked = !!b.rounded; cb.addEventListener("change", function () { b.rounded = cb.checked; renderCanvas(); }); var sl = el("span", "switch-slider"); sw.appendChild(cb); sw.appendChild(sl); rw.appendChild(sw); ins.appendChild(rw);
    } else if (b.type === "youtube") {
      ins.appendChild(field("YouTube link or ID", textInput(b.url, function (v) { b.url = v; renderCanvas(); })));
    } else if (b.type === "button") {
      ins.appendChild(field("Label (" + LANG + ")", textInput(lv(b.label), function (v) { setLv(b.label, v); renderCanvas(); })));
      ins.appendChild(field("Link URL", textInput(b.url, function (v) { b.url = v; })));
      ins.appendChild(field("Style", selectInput([["magenta", "Magenta"], ["navy", "Navy"], ["ghost", "Outline"]], b.style, function (v) { b.style = v; renderCanvas(); })));
      ins.appendChild(field("Align", selectInput([["left", "Left"], ["center", "Center"], ["right", "Right"]], b.align, function (v) { b.align = v; renderCanvas(); })));
    } else if (b.type === "spacer") {
      var si = el("input", "txt"); si.type = "number"; si.min = "0"; si.value = b.size || 0; si.addEventListener("input", function () { b.size = parseInt(si.value, 10) || 0; renderCanvas(); });
      ins.appendChild(field("Height (px)", si));
    }
  }

  /* ---------- language bar + meta ---------- */
  function renderLangBar() {
    var bar = $("#bLang"); bar.innerHTML = "";
    LANGS.forEach(function (l) {
      var b = el("button", "lang-pill" + (l[0] === LANG ? " on" : "")); b.type = "button"; b.textContent = l[1];
      b.addEventListener("click", function () { LANG = l[0]; render(); });
      bar.appendChild(b);
    });
    var tag = $("#bLangTag"); if (tag) tag.textContent = LANG;
  }
  function renderMeta() {
    var t = $("#bTitle"); if (t && document.activeElement !== t) t.value = lv(P.title);
    t.oninput = function () { setLv(P.title, t.value); };
  }

  /* ---------- save ---------- */
  function toast(m, err) { var t = $("#toast"); t.textContent = m; t.className = "toast show" + (err ? " err" : ""); setTimeout(function () { t.className = "toast" + (err ? " err" : ""); }, 2600); }
  function collect() {
    P.slug = $("#bSlug").value; P.published = $("#bPub").checked;
    P.origSlug = window.LT_PAGE.slug || "";
    return P;
  }
  function save(cb) {
    var data = collect();
    if (!data.slug) { toast("Add a URL slug first", true); return; }
    fetch("builder-save.php", { method: "POST", headers: { "Content-Type": "application/json", "X-CSRF": CSRF }, body: JSON.stringify(data) })
      .then(function (r) { return r.json(); })
      .then(function (j) { if (j.ok) { window.LT_PAGE.slug = j.slug; toast("Saved — page is live at /<lang>/" + j.slug); if (cb) cb(j.slug); } else toast(j.error || "Save failed", true); })
      .catch(function () { toast("Network error — not saved", true); });
  }
  $("#bSave").addEventListener("click", function () { save(); });
  $("#bPreview").addEventListener("click", function () { save(function (slug) { window.open("/en/" + slug, "_blank"); }); });

  buildPalette();
  render();
})();
