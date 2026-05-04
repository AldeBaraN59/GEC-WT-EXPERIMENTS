// progress-tracker.js
// Feature: Course View Tracker with confirm() reset
// Concepts: createElement, appendChild, removeChild, onclick, innerHTML, confirm(), for loop, if/else

let viewedCourses = [];

function markAsViewed(card) {
  const title = card.getElementsByTagName("h3")[0];
  if (!title) return;

  const name = title.innerText;

  if (viewedCourses.indexOf(name) === -1) {
    viewedCourses.push(name);
  }

  if (!card.querySelector(".viewed-badge")) {
    const badge = document.createElement("span");
    badge.className = "viewed-badge";
    badge.innerHTML = "✓ Viewed";
    badge.style.cssText = "position:absolute;top:10px;left:10px;background:rgba(74,103,65,0.9);color:#fff;font-size:0.68rem;font-family:'DM Mono',monospace;text-transform:uppercase;padding:3px 8px;border-radius:3px;pointer-events:none;z-index:10;";
    card.style.position = "relative";
    card.appendChild(badge);
  }

  document.getElementById("hud-count").innerHTML = viewedCourses.length;

  const list = document.getElementById("hud-list");
  list.innerHTML = "";
  for (let i = 0; i < viewedCourses.length; i++) {
    const li = document.createElement("li");
    li.innerHTML = viewedCourses[i];
    li.style.cssText = "font-size:0.75rem;color:#ccc;padding:0.3rem 0;border-bottom:1px solid #1e1e1e;list-style:none;";
    list.appendChild(li);
  }
}

function resetProgress() {
  const ok = window.confirm("Reset all viewed course history?");
  if (ok) {
    viewedCourses = [];
    const badges = document.getElementsByClassName("viewed-badge");
    while (badges.length > 0) {
      badges[0].parentNode.removeChild(badges[0]);
    }
    document.getElementById("hud-count").innerHTML = 0;
    document.getElementById("hud-list").innerHTML = "<li style='color:#555;font-size:0.78rem;list-style:none;'>No courses viewed yet.</li>";
  }
}

window.onload = function () {
  const cards = document.querySelectorAll(".course-card");
  for (let i = 0; i < cards.length; i++) {
    cards[i].onclick = function () { markAsViewed(this); };
  }

  const hud = document.createElement("div");
  hud.style.cssText = "position:fixed;bottom:1.5rem;right:1.5rem;width:230px;background:#0a0a0a;border:1px solid #2a2a2a;border-radius:10px;box-shadow:0 8px 30px rgba(0,0,0,0.5);z-index:9999;font-family:'DM Sans',sans-serif;overflow:hidden;";
  hud.innerHTML = `
    <div style="padding:0.75rem 1rem;background:#111;border-bottom:1px solid #1e1e1e;">
      <span style="font-family:'DM Mono',monospace;font-size:0.72rem;letter-spacing:0.08em;text-transform:uppercase;color:var(--gold);">
        Viewed: <span id="hud-count">0</span>
      </span>
    </div>
    <ul id="hud-list" style="margin:0;padding:0.75rem 1rem;">
      <li style="color:#555;font-size:0.78rem;list-style:none;">No courses viewed yet.</li>
    </ul>
    <div style="padding:0.5rem 1rem;border-top:1px solid #1e1e1e;text-align:center;">
      <button onclick="resetProgress()" style="background:none;border:1px solid #333;color:#666;font-family:'DM Mono',monospace;font-size:0.68rem;text-transform:uppercase;padding:0.35rem 0.9rem;border-radius:4px;cursor:pointer;">Reset</button>
    </div>`;
  document.body.appendChild(hud);
};
