(function () {
  let cards = [];

  function buildIndex() {
    cards = Array.from(document.querySelectorAll(".course-card")).map(el => ({
      el,
      text: (el.querySelector("h3")?.textContent + " " + el.querySelector("p")?.textContent).toLowerCase(),
      originalH3: el.querySelector("h3")?.innerHTML,
      originalP:  el.querySelector("p")?.innerHTML,
    }));
  }

  function fuzzyMatch(text, query) {
    let i = 0;
    for (const ch of query.toLowerCase()) {
      i = text.indexOf(ch, i) + 1;
      if (!i) return false;
    }
    return true;
  }

  function highlight(html, query) {
    if (!query || query.length < 2) return html;
    const re = new RegExp(`(${query.replace(/[.*+?^${}()|[\]\\]/g, "\\$&")})`, "gi");
    return html.replace(re, `<mark style="background:rgba(200,146,42,0.3);border-radius:2px;padding:0 1px;">$1</mark>`);
  }

  function injectSearchBar() {
    if (!document.querySelector(".courses-grid")) return;
    const bar = document.createElement("div");
    bar.style.cssText = "max-width:1200px;margin:0 auto;padding:2rem 3rem 0;";
    bar.innerHTML = `<input id="luminary-search" type="text" placeholder="🔍  Search courses…" style="width:100%;max-width:440px;padding:0.8rem 1rem;border:1.5px solid var(--border);border-radius:8px;background:var(--paper);font-family:'DM Sans',sans-serif;font-size:0.95rem;outline:none;transition:border-color 0.2s;" />`;
    const ref = document.querySelector(".filters-bar") || document.querySelector(".courses-grid")?.parentNode;
    ref?.parentNode?.insertBefore(bar, ref);
  }

  function onSearch(query) {
    query = query.trim().toLowerCase();
    let visible = 0;
    cards.forEach(({ el, text, originalH3, originalP }) => {
      const match = !query || fuzzyMatch(text, query);
      el.style.display = match ? "" : "none";
      if (match) {
        visible++;
        const h3 = el.querySelector("h3");
        const p  = el.querySelector("p");
        if (h3) h3.innerHTML = query ? highlight(originalH3, query) : originalH3;
        if (p)  p.innerHTML  = query ? highlight(originalP,  query) : originalP;
      }
    });
    let noRes = document.getElementById("no-results");
    if (!noRes) {
      noRes = Object.assign(document.createElement("p"), { id: "no-results" });
      noRes.style.cssText = "grid-column:1/-1;text-align:center;padding:3rem;color:var(--muted);font-size:0.95rem;";
      document.querySelector(".courses-grid")?.appendChild(noRes);
    }
    noRes.textContent = visible === 0 ? `No results for "${query}" — try a different term.` : "";
  }

  function init() {
    injectSearchBar();
    buildIndex();
    const input = document.getElementById("luminary-search");
    if (!input) return;
    let t;
    input.addEventListener("input", e => { clearTimeout(t); t = setTimeout(() => onSearch(e.target.value), 150); });
    input.addEventListener("focus",  () => { input.style.borderColor = "var(--gold)"; });
    input.addEventListener("blur",   () => { input.style.borderColor = "var(--border)"; });
    document.addEventListener("keydown", e => { if (e.key === "/" && document.activeElement !== input) { e.preventDefault(); input.focus(); } });
  }

  document.readyState === "loading" ? document.addEventListener("DOMContentLoaded", init) : init();
})();