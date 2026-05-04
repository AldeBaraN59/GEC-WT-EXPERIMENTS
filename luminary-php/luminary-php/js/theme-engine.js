const themes = {
  classic:  { "--ink":"#0d0d0d", "--paper":"#f5f0e8", "--cream":"#faf7f2", "--gold":"#c8922a", "--gold-light":"#e8b84b", "--muted":"#8a8278", "--border":"#d8cfc0" },
  midnight: { "--ink":"#e8e4dc", "--paper":"#1a1a1a", "--cream":"#141414", "--gold":"#e8b84b", "--gold-light":"#f5d07a", "--muted":"#6a6460", "--border":"#2a2a2a" },
  sepia:    { "--ink":"#2c1a0e", "--paper":"#f2e8d5", "--cream":"#faf4e8", "--gold":"#a0621a", "--gold-light":"#c8922a", "--muted":"#7a6a58", "--border":"#c8b898" },
  frost:    { "--ink":"#1a2a3a", "--paper":"#eef4fb", "--cream":"#f5f9ff", "--gold":"#2a7ab8", "--gold-light":"#4a9ad8", "--muted":"#6a8298", "--border":"#c8d8e8" },
};

function getCookieName() {
  return window.LUMINARY_THEME_COOKIE || "luminary_theme_guest";
}

function setThemeCookie(theme) {
  const cookieName = getCookieName();
  document.cookie = `${cookieName}=${encodeURIComponent(theme)}; max-age=${30 * 24 * 60 * 60}; path=/`;
}

function getThemeCookie() {
  const cookieName = getCookieName() + "=";
  const cookies = document.cookie.split(";");

  for (let i = 0; i < cookies.length; i++) {
    let c = cookies[i].trim();
    if (c.indexOf(cookieName) === 0) {
      return decodeURIComponent(c.substring(cookieName.length));
    }
  }

  return null;
}

function updateThemeLabel(key) {
  let label = "";

  switch (key) {
    case "classic":
      label = "☀️ Classic";
      break;
    case "midnight":
      label = "🌙 Midnight";
      break;
    case "sepia":
      label = "📜 Sepia";
      break;
    case "frost":
      label = "❄️ Frost";
      break;
    default:
      label = "☀️ Classic";
  }

  const themeLabel = document.getElementById("theme-label");
  const themeSelect = document.getElementById("theme-select");

  if (themeLabel) themeLabel.innerHTML = label;
  if (themeSelect) themeSelect.value = key;
}

function renderTheme(key) {
  const t = themes[key];
  if (!t) return;

  const vars = Object.keys(t);
  for (let i = 0; i < vars.length; i++) {
    document.documentElement.style.setProperty(vars[i], t[vars[i]]);
  }

  updateThemeLabel(key);
}

function applyTheme(key) {
  if (!themes[key]) return;
  renderTheme(key);
  setThemeCookie(key);
}

function askTheme() {
  const answer = window.prompt("Enter theme name: classic, midnight, sepia, frost");
  if (answer === null) return;

  const selected = answer.toLowerCase().trim();

  if (themes[selected]) {
    applyTheme(selected);
  } else {
    window.alert("Invalid theme! Choose: classic, midnight, sepia, or frost");
  }
}

window.onload = function () {
  const wrap = document.createElement("div");
  wrap.style.cssText = "position:fixed;bottom:1.5rem;left:1.5rem;z-index:9998;background:#0f0f0f;border:1px solid #2a2a2a;border-radius:10px;padding:0.75rem 1rem;font-family:'DM Sans',sans-serif;";
  wrap.innerHTML = `
    <div style="font-family:'DM Mono',monospace;font-size:0.68rem;letter-spacing:0.1em;text-transform:uppercase;color:#555;margin-bottom:0.5rem;">Theme</div>
    <select id="theme-select" onchange="applyTheme(this.value)"
      style="width:100%;padding:0.4rem 0.6rem;background:#1a1a1a;border:1px solid #333;color:#ccc;border-radius:6px;font-family:'DM Sans',sans-serif;font-size:0.82rem;cursor:pointer;margin-bottom:0.5rem;">
      <option value="classic">☀️ Classic</option>
      <option value="midnight">🌙 Midnight</option>
      <option value="sepia">📜 Sepia</option>
      <option value="frost">❄️ Frost</option>
    </select>
    <div id="theme-label" style="font-size:0.75rem;color:var(--gold);font-family:'DM Mono',monospace;margin-bottom:0.5rem;">☀️ Classic</div>
    <button onclick="askTheme()" style="width:100%;background:none;border:1px solid #333;color:#666;font-family:'DM Mono',monospace;font-size:0.68rem;text-transform:uppercase;padding:0.4rem;border-radius:4px;cursor:pointer;">Type Theme</button>
  `;
  document.body.appendChild(wrap);

  const savedTheme = getThemeCookie();
  if (savedTheme && themes[savedTheme]) {
    renderTheme(savedTheme);
  } else {
    renderTheme("classic");
  }
};