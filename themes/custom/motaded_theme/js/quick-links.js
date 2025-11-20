function quickLinks() {
  const article = document.getElementById("block-motaded-theme-content");
  let headings = article.querySelectorAll('h2');
  if (headings.length === 0) {
    headings = article.querySelectorAll('h3');
  }
  
  const result = [];
  let sectionCount = 0;
  let currentSection = null;

  headings.forEach((el) => {
    
  const tag = el.tagName.toLowerCase();

    if (el.textContent.trim()) { // && tag === 'h3') {
      sectionCount++;

      const sectionId = `section-${sectionCount}`;
      el.id = sectionId; // ✅ assign id to DOM element

      currentSection = {
        id: sectionId,
        label: el.textContent.trim() || `Section ${sectionCount}`,
      };

      result.push(currentSection);
    }
  });
  return {
    activeId: null,
    links: result,
    _observer: null,

    init() {
      // Smooth hash scrolling if the page loads with a hash
      if (location.hash) {
        const id = decodeURIComponent(location.hash.slice(1));
        this.$nextTick(() => this.scrollTo(id, false));
      }
    },

    scrollTo(id, pushHash = true) {
      const el = document.getElementById(id);
      if (!el) return;
      const y = el.getBoundingClientRect().top + window.scrollY - 96; // header offset
      window.scrollTo({ top: y, behavior: "smooth" });
      this.activeId = id;
      if (pushHash) history.replaceState(null, "", `#${id}`);
    },
  };
}