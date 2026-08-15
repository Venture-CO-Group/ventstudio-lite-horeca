/* VentStudio — site chrome: mobile nav, cookie banner, smooth anchor. */
(function () {
  "use strict";
  var nav = document.getElementById("nav");
  var burger = document.getElementById("navBurger");
  if (burger && nav) burger.addEventListener("click", function () { nav.classList.toggle("open"); });

  // Cookie banner
  var KEY = "lt_cookie_choice";
  var banner = document.getElementById("cookieBanner");
  function show() { if (banner) banner.hidden = false; }
  function hide() { if (banner) banner.hidden = true; }
  try { if (!localStorage.getItem(KEY)) show(); } catch (e) {}
  function choose(v) { try { localStorage.setItem(KEY, v); } catch (e) {} hide(); }
  var all = document.getElementById("cookieAll"), ess = document.getElementById("cookieEssential"), prefs = document.getElementById("openCookiePrefs");
  if (all) all.addEventListener("click", function () { choose("all"); });
  if (ess) ess.addEventListener("click", function () { choose("essential"); });
  if (prefs) prefs.addEventListener("click", function (e) { e.preventDefault(); show(); });

  // in-page menu anchor active state
  document.querySelectorAll('a[href^="#"]').forEach(function (a) {
    a.addEventListener("click", function () { if (nav) nav.classList.remove("open"); });
  });
})();
