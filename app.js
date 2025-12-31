document.addEventListener("DOMContentLoaded", () => {
  // Scroll buttons (down & up) via data-scroll
  document.querySelectorAll("[data-scroll]").forEach((btn) => {
    btn.addEventListener("click", () => {
      const target = document.querySelector(btn.dataset.scroll);
      if (target) target.scrollIntoView({ behavior: "smooth", block: "start" });
    });
  });

  // Disponibilité (optionnel)
  const availabilityEl = document.getElementById("availability");
  if (availabilityEl) availabilityEl.textContent = "❌";

  // Back to top visibility (robuste)
  const scrollUpBtn = document.querySelector(".scroll-up");
  const heroSection = document.querySelector(".hero");

  if (!scrollUpBtn || !heroSection) return;

  function toggleScrollUp() {
    // Afficher le bouton quand on a quitté le hero
    // (petite marge pour éviter les effets "clignotants")
    const showAfter = heroSection.offsetHeight - 120;
    const shouldShow = window.scrollY > showAfter;

    scrollUpBtn.classList.toggle("is-visible", shouldShow);
  }

  window.addEventListener("scroll", toggleScrollUp, { passive: true });
  window.addEventListener("resize", toggleScrollUp);
  toggleScrollUp();
})

  // Theme (system detection + user override)
  const themeToggle = document.querySelector(".theme-toggle");
  const themeIcon = document.querySelector(".theme-toggle__icon");
  const storageKey = "rc_theme"; // "light" | "dark" | null

  const mql = window.matchMedia("(prefers-color-scheme: dark)");

  function setTheme(theme) {
    // theme: "light" | "dark"
    document.documentElement.setAttribute("data-theme", theme);
    if (themeIcon) themeIcon.textContent = theme === "dark" ? "🌙" : "☀️";
  }

  function getSystemTheme() {
    return mql.matches ? "dark" : "light";
  }

  function getSavedTheme() {
    return localStorage.getItem(storageKey); // null | "light" | "dark"
  }

  function applyTheme() {
    const saved = getSavedTheme();
    const theme = saved === "light" || saved === "dark" ? saved : getSystemTheme();
    setTheme(theme);
  }

  // Init
  applyTheme();

  // Toggle click => set user override
  if (themeToggle) {
    themeToggle.addEventListener("click", () => {
      const current = document.documentElement.getAttribute("data-theme") || getSystemTheme();
      const next = current === "dark" ? "light" : "dark";
      localStorage.setItem(storageKey, next);
      setTheme(next);
    });
  }

  // If user hasn't overridden, follow system changes live
  mql.addEventListener("change", () => {
    const saved = getSavedTheme();
    if (saved !== "light" && saved !== "dark") {
      applyTheme();
    }
  });

// Typing animation for name (hero) — no dots
(function () {
  const el = document.getElementById("typed-name");
  const container = document.querySelector(".typing");
  if (!el || !container) return;

  const prefersReduced = window.matchMedia("(prefers-reduced-motion: reduce)").matches;
  const fullText = el.getAttribute("data-text") || el.textContent || "";

  if (prefersReduced) {
    el.textContent = fullText;
    container.classList.add("typing-done");
    return;
  }

  el.textContent = "";
  let i = 0;
  const speed = 55;

  const tick = () => {
    i++;
    el.textContent = fullText.slice(0, i);

    if (i < fullText.length) {
      setTimeout(tick, speed);
    } else {
      container.classList.add("typing-done"); // curseur disparaît
    }
  };

  setTimeout(tick, 250);
})();
