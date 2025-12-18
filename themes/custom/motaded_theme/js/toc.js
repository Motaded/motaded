/*
  Theme: Muin (Custom Licensed Version)
  © Banafsijy.com – Single Project License – Do Not Redistribute
*/

function articlePageEN() {
  const article = document.getElementById("toc-root");
  const headings = article.querySelectorAll('h2, h3');
  const result = [];
  let sectionCount = 0;
  let subsectionCount = 0;
  let currentSection = null;

  headings.forEach((el) => {
    
  const tag = el.tagName.toLowerCase();

    if (el.textContent.trim() && tag === 'h2') {
      sectionCount++;
      subsectionCount = 0;

      const sectionId = `section-${sectionCount}`;
      el.id = sectionId; // ✅ assign id to DOM element

      currentSection = {
        id: sectionId,
        label: el.textContent.trim() || `Section ${sectionCount}`,
      };

      result.push(currentSection);
    } 
    else if (el.textContent.trim() && tag === 'h3' && currentSection) {
      subsectionCount++;

      const subId = `sub-${sectionCount}-${subsectionCount}`;
      el.id = subId; // ✅ assign id to DOM element

      currentSection.children = currentSection.children || [];
      currentSection.children.push({
        id: subId,
        label: el.textContent.trim() || `Sub Section ${subsectionCount}`,
      });
    }
  });
  return {
    tocOpen: false,
    activeId: null,
    toc: result,
    _observer: null,

    init() {
      // Smooth hash scrolling if the page loads with a hash
      if (location.hash) {
        const id = decodeURIComponent(location.hash.slice(1));
        this.$nextTick(() => this.scrollTo(id, false));
      }

      // Observe headings to update active state while scrolling
      const targets = Array.from(
        document.querySelectorAll("#article-root [id]")
      );
      const io = new IntersectionObserver(
        (entries) => {
          entries.forEach((entry) => {
            if (entry.isIntersecting) this.activeId = entry.target.id;
          });
        },
        { rootMargin: "-30% 0px -60% 0px", threshold: 0.01 }
      );
      targets.forEach((el) => io.observe(el));
      this._observer = io;

      // Keep ToC open on md+ screens
      const mq = window.matchMedia("(min-width: 768px)");
      const sync = () => {
        if (mq.matches) this.tocOpen = true;
      };
      mq.addEventListener?.("change", sync);
      sync();
    },

    scrollTo(id, pushHash = true) {
      const el = document.getElementById(id);
      if (!el) return;
      const y = el.getBoundingClientRect().top + window.scrollY - 96; // header offset
      window.scrollTo({ top: y, behavior: "smooth" });
      this.activeId = id;
      if (pushHash) history.replaceState(null, "", `#${id}`);
      if (window.innerWidth < 768) this.tocOpen = false; // collapse on mobile after nav
    },
  };
}