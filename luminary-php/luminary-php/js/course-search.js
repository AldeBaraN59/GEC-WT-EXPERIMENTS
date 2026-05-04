// course-search.js
// Feature: Live Course Search using DOM Manipulation
// Concepts: getElementById, getElementsByTagName, innerHTML, onkeyup, for loop, if/else

function searchCourses() {
  const query = document.getElementById("searchBox").value.toLowerCase();
  const cards = document.getElementsByClassName("course-card");
  let count = 0;

  for (let i = 0; i < cards.length; i++) {
    const title = cards[i].getElementsByTagName("h3")[0];
    const text  = title ? title.innerHTML.toLowerCase() : "";

    if (text.includes(query)) {
      cards[i].style.display = "";
      count++;
    } else {
      cards[i].style.display = "none";
    }
  }

  document.getElementById("searchResult").innerHTML =
    query === "" ? "" : count + " result(s) found for: <b>" + query + "</b>";
}

// Inject search bar into page on load
window.onload = function () {
  const grid = document.querySelector(".courses-grid");
  if (!grid) return;

  const wrapper = document.createElement("div");
  wrapper.style.cssText = "max-width:1200px;margin:0 auto;padding:2rem 3rem 0;";
  wrapper.innerHTML = `
    <input id="searchBox" type="text" placeholder="Search courses..."
      onkeyup="searchCourses()"
      onfocus="this.style.borderColor='var(--gold)'"
      onblur="this.style.borderColor='var(--border)'"
      style="width:100%;max-width:420px;padding:0.8rem 1rem;border:1.5px solid var(--border);
             border-radius:8px;background:var(--paper);font-family:'DM Sans',sans-serif;font-size:0.95rem;outline:none;" />
    <p id="searchResult" style="margin-top:0.5rem;font-size:0.85rem;color:var(--muted);"></p>`;

  grid.parentNode.insertBefore(wrapper, grid.parentNode.querySelector(".filters-bar") || grid);
};
