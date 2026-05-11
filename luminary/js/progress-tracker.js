/**
 * progress-tracker.js
 * 
 * A vanilla JavaScript module that tracks user engagement using localStorage.
 * It monitors page visits, scroll depth, and course clicks, and renders 
 * a floating Heads-Up Display (HUD) to show the user their progress.
 */
(function () {
  const KEY  = "luminary_progress";
  const page = location.pathname.split("/").pop() || "index.html";
  let data   = JSON.parse(localStorage.getItem(KEY) || '{"pages":{},"courses":[]}');

  // Track this page visit + scroll depth
  if (!data.pages[page]) data.pages[page] = { visits: 0, scroll: 0 };
  data.pages[page].visits++;
  localStorage.setItem(KEY, JSON.stringify(data));

  window.addEventListener("scroll", () => {
    const el  = document.documentElement;
    const pct = Math.round((el.scrollTop / (el.scrollHeight - el.clientHeight)) * 100) || 0;
    if (pct > data.pages[page].scroll) {
      data.pages[page].scroll = pct;
      localStorage.setItem(KEY, JSON.stringify(data));
      document.getElementById("hud-scroll-fill")?.style && (document.getElementById("hud-scroll-fill").style.width = pct + "%");
    }
  }, { passive: true });

  // Track course card clicks
  function attachCardTracking() {
    document.querySelectorAll(".course-card").forEach(card => {
      const title = card.querySelector("h3")?.textContent?.trim();
      if (!title) return;
      if (data.courses.includes(title)) markSeen(card);
      card.addEventListener("click", () => {
        if (!data.courses.includes(title)) { data.courses.push(title); localStorage.setItem(KEY, JSON.stringify(data)); }
        markSeen(card);
        updateHUD();
      });
    });
  }

  function markSeen(card) {
    if (card.querySelector(".seen-badge")) return;
    card.style.position = "relative";
    const b = document.createElement("div");
    b.className = "seen-badge";
    b.textContent = "✓ Viewed";
    b.style.cssText = "position:absolute;top:10px;left:10px;background:rgba(74,103,65,0.9);color:#fff;font-size:0.65rem;font-family:'DM Mono',monospace;letter-spacing:0.08em;text-transform:uppercase;padding:3px 8px;border-radius:3px;pointer-events:none;z-index:10;";
    card.appendChild(b);
  }

  // HUD
  function buildHUD() {
    const totalPages   = Object.keys(data.pages).length;
    const scrollNow    = data.pages[page]?.scroll || 0;
    const courseCount  = data.courses.length;
    const recentCourses = data.courses.slice(-3).reverse().map(t => `<div style="font-size:0.75rem;color:#aaa;padding:0.3rem 0;border-bottom:1px solid #1e1e1e;">${t}</div>`).join("") || `<div style="font-size:0.75rem;color:#555;">None yet</div>`;

    return `
      <div style="padding:0.75rem 1rem;background:#111;border-bottom:1px solid #1e1e1e;font-family:'DM Mono',monospace;font-size:0.72rem;letter-spacing:0.08em;text-transform:uppercase;color:var(--gold);">📊 Progress</div>
      <div style="display:grid;grid-template-columns:repeat(3,1fr);border-bottom:1px solid #1e1e1e;text-align:center;">
        ${[["Pages",totalPages],["Courses",courseCount],["Scroll",scrollNow+"%"]].map(([l,v])=>`<div style="padding:0.6rem 0;border-right:1px solid #1e1e1e;"><div style="font-family:'Playfair Display',serif;font-size:1.1rem;color:var(--paper);">${v}</div><div style="font-size:0.6rem;color:#555;font-family:'DM Mono',monospace;text-transform:uppercase;">${l}</div></div>`).join("")}
      </div>
      <div style="padding:0.75rem 1rem;border-bottom:1px solid #1e1e1e;">
        <div style="font-size:0.65rem;color:#555;font-family:'DM Mono',monospace;text-transform:uppercase;margin-bottom:4px;">Page Read</div>
        <div style="height:4px;background:#1e1e1e;border-radius:2px;"><div id="hud-scroll-fill" style="height:100%;width:${scrollNow}%;background:var(--gold);border-radius:2px;transition:width 0.3s;"></div></div>
      </div>
      <div style="padding:0.75rem 1rem;">
        <div style="font-size:0.65rem;color:#555;font-family:'DM Mono',monospace;text-transform:uppercase;margin-bottom:6px;">Recently Viewed</div>
        ${recentCourses}
      </div>`;
  }

  function updateHUD() {
    const hud = document.getElementById("progress-hud");
    if (hud) hud.innerHTML = buildHUD();
  }

  function init() {
    attachCardTracking();
    const hud = document.createElement("div");
    hud.id = "progress-hud";
    hud.style.cssText = "position:fixed;bottom:1.5rem;right:1.5rem;width:260px;background:#0a0a0a;border:1px solid #2a2a2a;border-radius:10px;box-shadow:0 8px 30px rgba(0,0,0,0.5);z-index:9999;overflow:hidden;";
    hud.innerHTML = buildHUD();
    document.body.appendChild(hud);
  }

  document.readyState === "loading" ? document.addEventListener("DOMContentLoaded", init) : init();
})();