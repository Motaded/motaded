/*
  Theme: Muin (Custom Licensed Version)
  © Banafsijy.com – Single Project License – Do Not Redistribute
*/

function articlePageEN() {
  const article = document.getElementById("toc-root");
  const inlineCtaCopy = {
    eyebrow: article?.dataset.inlineCtaEyebrow || "Next step",
    title: article?.dataset.inlineCtaTitle || "Talk to Motaded experts today",
    text:
      article?.dataset.inlineCtaText ||
      "Book a quick consultation and receive clear, actionable guidance tailored to your case.",
  };
  const injectInlineCta = () => {
    if (!article || !contentRoot) return;
    if (contentRoot.querySelector(".article-inline-cta")) return;

    const ctaSource =
      article.querySelector(".article-inline-cta-source") ||
      article.querySelector(".article-rail-cta-source");
    if (!ctaSource) return;

    const ctaMarkup = (ctaSource.innerHTML || "").trim();
    if (!ctaMarkup) return;

    const bodyItem =
      contentRoot.querySelector(".field--name-body .field__item") || contentRoot;
    const paragraphs = Array.from(bodyItem.querySelectorAll("p")).filter((p) => {
      const textLength = (p.textContent || "").trim().length;
      if (textLength < 40) return false;
      if (p.closest(".article-inline-cta")) return false;
      return true;
    });
    if (paragraphs.length < 4) return;

    // Prefer section boundaries (before headings), not list-intro paragraphs.
    const preferredBoundaryTags = new Set(["H2", "H3"]);
    const secondaryBoundaryTags = new Set(["TABLE", "BLOCKQUOTE", "PRE", "FIGURE"]);
    const withPreferredBoundaryAfter = paragraphs.filter((p) => {
      const next = p.nextElementSibling;
      return next && preferredBoundaryTags.has(next.tagName);
    });
    const withSecondaryBoundaryAfter = paragraphs.filter((p) => {
      const next = p.nextElementSibling;
      return next && secondaryBoundaryTags.has(next.tagName);
    });
    const candidatePool = withPreferredBoundaryAfter.length
      ? withPreferredBoundaryAfter
      : withSecondaryBoundaryAfter.length
        ? withSecondaryBoundaryAfter
        : paragraphs;
    const targetParagraph = candidatePool[Math.floor(candidatePool.length / 2)];
    if (!targetParagraph || !targetParagraph.parentNode) return;

    const inlineCta = document.createElement("div");
    inlineCta.className = "article-inline-cta";
    inlineCta.innerHTML = `
      <div class="article-inline-cta__eyebrow">${inlineCtaCopy.eyebrow}</div>
      <h3 class="article-inline-cta__title">${inlineCtaCopy.title}</h3>
      <p class="article-inline-cta__text">
        ${inlineCtaCopy.text}
      </p>
      <div class="article-inline-cta__content">${ctaMarkup}</div>
    `;

    // Avoid duplicate IDs in DOM when we clone CTA markup from sidebar.
    inlineCta.querySelectorAll("[id]").forEach((el) => el.removeAttribute("id"));

    targetParagraph.parentNode.insertBefore(inlineCta, targetParagraph.nextSibling);
  };

  const isLikelyTocParagraphHeading = (text) => {
    const normalized = (text || "").replace(/\s+/g, " ").trim();
    if (!normalized) return false;
    if (normalized.length > 140) return false;
    if (/[:؛：]\s*$/.test(normalized)) return false;
    if (/^(read|learn)\s+more\s+about\b/i.test(normalized)) return false;
    if (/^(these|this)\b/i.test(normalized)) return false;
    return true;
  };
  const contentRoot = article?.querySelector("#article-root");
  const contentHeadings = contentRoot
    ? Array.from(contentRoot.querySelectorAll("h2, h3"))
    : [];
  const strongParagraphHeadings = contentRoot
    ? Array.from(contentRoot.querySelectorAll("p > strong:first-child"))
    : [];
  const headings = [...contentHeadings];

  strongParagraphHeadings.forEach((strongEl) => {
    const parentParagraph = strongEl.parentElement;
    if (!parentParagraph) return;

    const strongText = strongEl.textContent?.trim() || "";
    const paragraphText = parentParagraph.textContent?.trim() || "";

    // Only treat as heading-like blocks when the paragraph is effectively a title.
    if (!strongText || paragraphText !== strongText) return;
    if (!isLikelyTocParagraphHeading(paragraphText)) return;
    headings.push(parentParagraph);
  });

  headings.sort((a, b) => {
    if (a === b) return 0;
    const pos = a.compareDocumentPosition(b);
    return pos & Node.DOCUMENT_POSITION_FOLLOWING ? -1 : 1;
  });
  const hasStructuralH2 = headings.some(
    (el) => el.tagName.toLowerCase() === "h2"
  );
  const hasStructuralH3 = headings.some(
    (el) => el.tagName.toLowerCase() === "h3"
  );
  const useH3AsPrimary = !hasStructuralH2 && hasStructuralH3;
  const result = [];
  let sectionCount = 0;
  let subsectionCount = 0;
  let currentSection = null;

  headings.forEach((el) => {
    
  const tag = el.tagName.toLowerCase();

    const isSectionHeading =
      tag === "h2" ||
      (useH3AsPrimary && tag === "h3") ||
      (!useH3AsPrimary && tag === "p");
    const isSubsectionHeading =
      (hasStructuralH2 && tag === "h3") || (useH3AsPrimary && tag === "p");

    if (el.textContent.trim() && isSectionHeading) {
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
    else if (el.textContent.trim() && isSubsectionHeading && currentSection) {
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
      injectInlineCta();
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