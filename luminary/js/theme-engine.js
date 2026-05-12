/**
 * theme-engine.js
 *
 * Vanilla JS theme switcher for Luminary.
 * Uses a cookie whose name can be configured from PHP via
 * window.LUMINARY_THEME_COOKIE. This lets you have per-user theme cookies.
 */
(function () {
  const COOKIE_NAME = window.LUMINARY_THEME_COOKIE || "luminary_theme";

  const THEMES = {
    classic: {
      icon: "☀️",
      label: "Classic",
      "--ink": "#0d0d0d",
      "--paper": "#f5f0e8",
      "--cream": "#faf7f2",
      "--gold": "#c8922a",
      "--gold-light": "#e8b84b",
      "--muted": "#8a8278",
      "--border": "#d8cfc0"
    },
    midnight: {
      icon: "🌙",
      label: "Midnight",
      "--ink": "#e8e4dc",
      "--paper": "#1a1a1a",
      "--cream": "#141414",
      "--gold": "#e8b84b",
      "--gold-light": "#f5d07a",
      "--muted": "#6a6460",
      "--border": "#2a2a2a"
    },
    sepia: {
      icon: "📜",
      label: "Sepia",
      "--ink": "#2c1a0e",
      "--paper": "#f2e8d5",
      "--cream": "#faf4e8",
      "--gold": "#a0621a",
      "--gold-light": "#c8922a",
      "--muted": "#7a6a58",
      "--border": "#c8b898"
    },
    frost: {
      icon: "❄️",
      label: "Frost",
      "--ink": "#1a2a3a",
      "--paper": "#eef4fb",
      "--cream": "#f5f9ff",
      "--gold": "#2a7ab8",
      "--gold-light": "#4a9ad8",
      "--muted": "#6a8298",
      "--border": "#c8d8e8"
    }
  };

  function getCookie(name) {
    const value = document.cookie.split("; ").find(row => row.startsWith(name + "="));
    return value ? decodeURIComponent(value.split("=")[1]) : null;
  }

  function setCookie(name, value) {
    const oneYear = 60 * 60 * 24 * 365;
    document.cookie = name + "=" + encodeURIComponent(value) + "; path=/; max-age=" + oneYear;
  }

  function applyTheme(key) {
    const t = THEMES[key];
    if (!t) return;

    const root = document.documentElement;
    Object.keys(t)
      .filter(k => k.startsWith("--"))
      .forEach(k => root.style.setProperty(k, t[k]));

    setCookie(COOKIE_NAME, key);

    const select = document.getElementById("theme");
    if (select) {
      select.value = key;
    }
  }

  window.applyTheme = applyTheme;

  document.addEventListener("DOMContentLoaded", function () {
    const select = document.getElementById("theme");
    if (select) {
      // Live preview when user changes dropdown
      select.addEventListener("change", function () {
        applyTheme(this.value);
      });
    }

    // Initial theme from cookie (if JS runs before PHP applies it)
    const cookieTheme = getCookie(COOKIE_NAME);
    const initial = cookieTheme && THEMES[cookieTheme] ? cookieTheme : select?.value || "classic";
    applyTheme(initial);
  });
})();