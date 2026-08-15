/* VentStudio — lightweight WYSIWYG editor.
   Enhances any <textarea data-wysiwyg> (or .wysiwyg) into a rich-text editor.
   Stores HTML back into the textarea so existing save handlers keep working. */
(function () {
  "use strict";
  if (window.__ltWys) return; window.__ltWys = true;

  var css = ""
    + ".lt-wys{border:1.5px solid var(--line-2,#E0D2B8);border-radius:12px;overflow:hidden;background:#fff}"
    + ".lt-wys-bar{display:flex;gap:2px;flex-wrap:wrap;padding:6px;background:#fbf6ec;border-bottom:1px solid var(--line,#EBDFCB)}"
    + ".lt-wys-bar button{border:none;background:none;width:32px;height:30px;border-radius:7px;cursor:pointer;font-weight:700;font-size:14px;color:#1B1512}"
    + ".lt-wys-bar button:hover{background:#efe2cc}"
    + ".lt-wys-ed{min-height:150px;padding:12px 14px;font-family:'Hanken',system-ui,sans-serif;font-size:14px;line-height:1.6;outline:none}"
    + ".lt-wys-ed:focus{box-shadow:inset 0 0 0 2px rgba(232,67,31,.15)}"
    + ".lt-wys-ed h2{font-family:'Bricolage',sans-serif;font-size:1.3rem;margin:.4em 0}"
    + ".lt-wys-ed a{color:#E8431F}";
  var st = document.createElement("style"); st.textContent = css; document.head.appendChild(st);

  function cmd(c, v) { document.execCommand(c, false, v || null); }
  var TOOLS = [
    ["H2", function () { cmd("formatBlock", "<h2>"); }, "Heading"],
    ["<b>B</b>", function () { cmd("bold"); }, "Bold"],
    ["<i>I</i>", function () { cmd("italic"); }, "Italic"],
    ["• List", function () { cmd("insertUnorderedList"); }, "Bulleted list"],
    ["1. List", function () { cmd("insertOrderedList"); }, "Numbered list"],
    ["Link", function () { var u = prompt("Link URL:", "https://"); if (u) cmd("createLink", u); }, "Insert link"],
    ["¶", function () { cmd("formatBlock", "<p>"); }, "Paragraph"],
    ["✕", function () { cmd("removeFormat"); }, "Clear formatting"]
  ];

  function enhance(ta) {
    if (ta.__wys) return; ta.__wys = true;
    var wrap = document.createElement("div"); wrap.className = "lt-wys";
    var bar = document.createElement("div"); bar.className = "lt-wys-bar";
    TOOLS.forEach(function (t) {
      var b = document.createElement("button");
      b.type = "button"; b.innerHTML = t[0]; b.title = t[2];
      b.addEventListener("mousedown", function (e) { e.preventDefault(); });
      b.addEventListener("click", function () { ed.focus(); t[1](); sync(); });
      bar.appendChild(b);
    });
    var ed = document.createElement("div");
    ed.className = "lt-wys-ed"; ed.contentEditable = "true";
    ed.innerHTML = ta.value || "";
    function sync() { ta.value = ed.innerHTML; ta.dispatchEvent(new Event("input", { bubbles: true })); }
    ed.addEventListener("input", sync);
    ta.style.display = "none";
    wrap.appendChild(bar); wrap.appendChild(ed);
    ta.parentNode.insertBefore(wrap, ta.nextSibling);
    // keep in sync if some external code updates the textarea before submit
    var form = ta.closest("form");
    if (form) form.addEventListener("submit", sync);
  }

  function scan(root) {
    (root || document).querySelectorAll("textarea[data-wysiwyg], textarea.wysiwyg").forEach(enhance);
  }
  if (document.readyState !== "loading") scan(); else document.addEventListener("DOMContentLoaded", function () { scan(); });
  window.LTWysiwyg = { scan: scan, enhance: enhance };
})();
