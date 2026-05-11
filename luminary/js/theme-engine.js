/**
 * theme-engine.js
 * 
 * A vanilla JavaScript theme switcher that supports multiple color palettes 
 * (Classic, Midnight, Sepia, Frost). It injects a floating UI to swap themes,
 * modifies CSS variables dynamically, and saves the preference to localStorage.
 */
(function () {
  const KEY = "luminary_theme";
  const THEMES = {
    classic: { icon:"☀️", label:"Classic", "--ink":"#0d0d0d","--paper":"#f5f0e8","--cream":"#faf7f2","--gold":"#c8922a","--gold-light":"#e8b84b","--muted":"#8a8278","--border":"#d8cfc0" },
    midnight:{ icon:"🌙", label:"Midnight","--ink":"#e8e4dc","--paper":"#1a1a1a","--cream":"#141414","--gold":"#e8b84b","--gold-light":"#f5d07a","--muted":"#6a6460","--border":"#2a2a2a" },
    sepia:   { icon:"📜", label:"Sepia",   "--ink":"#2c1a0e","--paper":"#f2e8d5","--cream":"#faf4e8","--gold":"#a0621a","--gold-light":"#c8922a","--muted":"#7a6a58","--border":"#c8b898" },
    frost:   { icon:"❄️", label:"Frost",   "--ink":"#1a2a3a","--paper":"#eef4fb","--cream":"#f5f9ff","--gold":"#2a7ab8","--gold-light":"#4a9ad8","--muted":"#6a8298","--border":"#c8d8e8" },
  };

  let current = localStorage.getItem(KEY) || (matchMedia("(prefers-color-scheme: dark)").matches ? "midnight" : "classic");

  function applyTheme(key) {
    const t = THEMES[key]; if (!t) return;
    const root = document.documentElement;
    Object.keys(t).filter(k => k.startsWith("--")).forEach(k => root.style.setProperty(k, t[k]));
    current = key;
    localStorage.setItem(KEY, key);
    document.cookie = KEY + "=" + key + "; path=/; max-age=31536000";
    document.querySelectorAll(".theme-btn").forEach(b => {
      b.style.border = b.dataset.theme === key ? "2px solid var(--gold)" : "2px solid transparent";
    });
  }

  // Apply immediately to prevent flash
  applyTheme(current);

  function buildSwitcher() {
    const wrap = document.createElement("div");
    wrap.style.cssText = "position:fixed;bottom:1.5rem;left:1.5rem;z-index:9998;display:flex;flex-direction:column;align-items:flex-start;gap:0.4rem;";
    const btn = document.createElement("button");
    btn.textContent = "🎨";
    btn.style.cssText = "width:40px;height:40px;border-radius:50%;background:var(--ink);border:1px solid #333;color:var(--gold);font-size:1rem;cursor:pointer;box-shadow:0 4px 16px rgba(0,0,0,0.3);transition:transform 0.2s;";
    btn.title = "Change theme (or press T)";

    const panel = document.createElement("div");
    panel.style.cssText = "display:none;background:#0f0f0f;border:1px solid #2a2a2a;border-radius:10px;padding:0.75rem;box-shadow:0 8px 30px rgba(0,0,0,0.5);";
    panel.innerHTML = Object.entries(THEMES).map(([key, t]) =>
      `<button class="theme-btn" data-theme="${key}" style="display:flex;align-items:center;gap:0.5rem;width:100%;background:none;border:2px solid transparent;border-radius:6px;padding:0.5rem 0.75rem;cursor:pointer;margin-bottom:0.25rem;color:#ccc;font-family:'DM Sans',sans-serif;font-size:0.82rem;transition:background 0.15s;">
        <span>${t.icon}</span><span>${t.label}</span>
      </button>`
    ).join("");

    btn.addEventListener("click", e => { e.stopPropagation(); panel.style.display = panel.style.display === "none" ? "block" : "none"; });
    panel.querySelectorAll(".theme-btn").forEach(b => b.addEventListener("click", () => { applyTheme(b.dataset.theme); panel.style.display = "none"; }));
    document.addEventListener("click", () => { panel.style.display = "none"; });

    wrap.appendChild(panel);
    wrap.appendChild(btn);
    document.body.appendChild(wrap);
    applyTheme(current); // re-run to set border on buttons
  }

  // Press T to cycle themes
  const keys = Object.keys(THEMES);
  document.addEventListener("keydown", e => {
    if (["INPUT","TEXTAREA","SELECT"].includes(document.activeElement.tagName)) return;
    if (e.key === "t" || e.key === "T") applyTheme(keys[(keys.indexOf(current) + 1) % keys.length]);
  });

  // Listen for OS theme changes
  matchMedia("(prefers-color-scheme: dark)").addEventListener("change", e => {
    if (!localStorage.getItem(KEY)) applyTheme(e.matches ? "midnight" : "classic");
  });

  document.readyState === "loading" ? document.addEventListener("DOMContentLoaded", buildSwitcher) : buildSwitcher();
})();