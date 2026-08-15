/* Vent Studio — first-run guided tour (highlights + tooltips).
   Auto-starts once on the Dashboard; re-launchable via LtTour.start(). */
(function () {
  "use strict";
  var STEPS = [
    { sel: ".sb-brand", title: "Welcome to your VentStudio admin 👋", body: "This is where you run the webshop — orders, menu, invoices and emails. Click the logo any time to return to the Dashboard. Here's a 60-second tour." },
    { sel: 'a.sb-item[href="index.php"]', title: "Dashboard", body: "Your webshop at a glance: orders, revenue, today's takings, average order value, order status breakdown and top sellers." },
    { sel: 'a.sb-item[href="menu.php"]', title: "Menu", body: "Add, edit and remove dishes — name, description, price, tags and allergens. Changes go live on the site instantly." },
    { sel: 'a.sb-item[href="orders.php"]', title: "Orders", body: "Every online order lands here. Move it through Approve → Preparing → Out for delivery → Delivered (each step emails the customer or courier). Download the invoice PDF, or Cancel to auto-refund via Stripe and issue a credit note." },
    { sel: 'a.sb-item[href="invoices.php"]', title: "Invoices", body: "All invoices in one place, filterable by date range, with VAT totals for your bookkeeping. Download any invoice or credit note as a PDF." },
    { sel: 'a.sb-item[href="emails.php"]', title: "Email templates", body: "Edit the branded emails customers and your kitchen/courier receive (order received, cooking, delivered) with a visual editor." },
    { sel: 'a.sb-item[href="media.php"]', title: "Media library", body: "Upload photos (e.g. dish images) and manage them here." },
    { sel: 'a.sb-item[href="settings.php"]', title: "Settings", body: "Business details, contact numbers, delivery fee / minimum / area, socials and integrations." },
    { sel: 'a.sb-item[href="users.php"]', title: "Users & access", body: "The owner and super-admins manage accounts, roles and password resets here." },
    { sel: ".me", title: "Your account", body: "Open this menu for My account (name, email, password) and Sign out." },
    { sel: 'a.sb-item[href="docs.php"]', title: "Help & docs", body: "The full manual lives here — and you can replay this tour any time from the Help page. Enjoy! 🎉" }
  ];

  var overlay, box, tip, tipTitle, tipBody, tipCount, curr = 0, active = false;

  function injectStyle() {
    if (document.getElementById("tourStyle")) return;
    var css =
      ".tour-overlay{position:fixed;inset:0;background:rgba(15,17,35,.55);z-index:100000;display:none}" +
      ".tour-overlay.on{display:block}" +
      ".tour-box{position:fixed;z-index:100001;border-radius:12px;border:2px solid #B82786;box-shadow:0 0 0 4px rgba(184,39,134,.35),0 0 0 9999px rgba(15,17,35,.55);pointer-events:none;display:none;transition:top .2s ease,left .2s ease,width .2s ease,height .2s ease}" +
      ".tour-box.on{display:block}" +
      ".tour-tip{position:fixed;z-index:100002;box-sizing:border-box;width:320px;max-width:calc(100vw - 24px);background:#fff;border-radius:14px;box-shadow:0 24px 60px -18px rgba(0,0,0,.5);padding:18px;display:none;font-family:Montserrat,system-ui,-apple-system,sans-serif}" +
      ".tour-tip.on{display:block}" +
      ".tour-tip-count{font-size:11px;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:#B82786;margin:0}" +
      ".tour-tip-title{margin:4px 0 6px;font-family:'Bricolage',system-ui,sans-serif;font-weight:800;font-size:20px;line-height:1.15;color:#1B1512}" +
      ".tour-tip-body{margin:0 0 14px;font-size:13.5px;line-height:1.55;color:#6A7085}" +
      ".tour-tip-actions{display:flex;justify-content:space-between;align-items:center;gap:10px}" +
      ".tour-tip-actions button{border:0;cursor:pointer;font-family:inherit;font-weight:600;font-size:13px;border-radius:999px;padding:8px 16px;line-height:1}" +
      ".tour-skip{background:none;color:#6A7085}.tour-skip:hover{color:#1B1F3C}" +
      ".tour-nav{display:flex;gap:8px}" +
      ".tour-back{background:#F3F4F8;color:#1B1F3C}" +
      ".tour-next{background:#1B1F3C;color:#fff}";
    var st = document.createElement("style"); st.id = "tourStyle"; st.textContent = css;
    document.head.appendChild(st);
  }

  function build() {
    injectStyle();
    overlay = document.createElement("div"); overlay.className = "tour-overlay";
    box = document.createElement("div"); box.className = "tour-box";
    tip = document.createElement("div"); tip.className = "tour-tip";
    tip.innerHTML =
      '<div class="tour-tip-count"></div>' +
      '<h3 class="tour-tip-title"></h3>' +
      '<p class="tour-tip-body"></p>' +
      '<div class="tour-tip-actions">' +
        '<button type="button" class="tour-skip">Skip</button>' +
        '<span class="tour-nav"><button type="button" class="tour-back">Back</button>' +
        '<button type="button" class="tour-next">Next</button></span>' +
      '</div>';
    document.body.appendChild(overlay); document.body.appendChild(box); document.body.appendChild(tip);
    tipTitle = tip.querySelector(".tour-tip-title");
    tipBody = tip.querySelector(".tour-tip-body");
    tipCount = tip.querySelector(".tour-tip-count");
    tip.querySelector(".tour-skip").addEventListener("click", end);
    tip.querySelector(".tour-back").addEventListener("click", function () { go(curr - 1); });
    tip.querySelector(".tour-next").addEventListener("click", function () { go(curr + 1); });
    overlay.addEventListener("click", end);
    window.addEventListener("resize", position);
    window.addEventListener("scroll", position, true);
  }

  function go(i) {
    // skip missing targets in the requested direction
    var dir = i >= curr ? 1 : -1;
    while (i >= 0 && i < STEPS.length && !document.querySelector(STEPS[i].sel)) i += dir;
    if (i < 0) { i = 0; }
    if (i >= STEPS.length) { end(); return; }
    curr = i;
    var s = STEPS[curr], el = document.querySelector(s.sel);
    if (!el) { end(); return; }
    el.scrollIntoView({ block: "center", behavior: "smooth" });
    tipTitle.textContent = s.title; tipBody.textContent = s.body;
    tipCount.textContent = "Step " + (curr + 1) + " of " + STEPS.length;
    tip.querySelector(".tour-back").style.visibility = curr === 0 ? "hidden" : "visible";
    tip.querySelector(".tour-next").textContent = curr === STEPS.length - 1 ? "Done" : "Next";
    setTimeout(position, 180);
  }

  function position() {
    if (!active) return;
    var s = STEPS[curr], el = document.querySelector(s.sel);
    if (!el) return;
    var r = el.getBoundingClientRect(), pad = 6;
    box.style.top = (r.top - pad) + "px"; box.style.left = (r.left - pad) + "px";
    box.style.width = (r.width + pad * 2) + "px"; box.style.height = (r.height + pad * 2) + "px";
    // place tooltip: to the right of the sidebar items, else below
    var tw = tip.offsetWidth || 320, th = tip.offsetHeight || 160, gap = 14;
    var left = r.right + gap, top = r.top;
    // not enough room on the right → drop below the target
    if (left + tw > window.innerWidth - 12) { left = r.left; top = r.bottom + gap; }
    // keep fully on screen
    left = Math.min(Math.max(12, left), window.innerWidth - tw - 12);
    top = Math.min(Math.max(12, top), window.innerHeight - th - 12);
    tip.style.left = left + "px"; tip.style.top = top + "px";
  }

  function start() {
    if (active) return;
    if (!overlay) build();
    active = true;
    overlay.classList.add("on"); box.classList.add("on"); tip.classList.add("on");
    go(0);
  }
  function end() {
    active = false;
    if (overlay) { overlay.classList.remove("on"); box.classList.remove("on"); tip.classList.remove("on"); }
    try { localStorage.setItem("lt_tour_done", "1"); } catch (e) {}
  }

  window.LtTour = { start: start, reset: function () { try { localStorage.removeItem("lt_tour_done"); } catch (e) {} } };

  // auto-start once, on the Dashboard only
  document.addEventListener("DOMContentLoaded", function () {
    var onDash = !!document.querySelector('.sb-item.active[href="index.php"]');
    var done = false; try { done = localStorage.getItem("lt_tour_done") === "1"; } catch (e) {}
    if (onDash && !done) setTimeout(start, 700);
    // wire any [data-tour] trigger buttons
    document.querySelectorAll("[data-tour-start]").forEach(function (b) {
      b.addEventListener("click", function (e) { e.preventDefault(); start(); });
    });
  });
})();
