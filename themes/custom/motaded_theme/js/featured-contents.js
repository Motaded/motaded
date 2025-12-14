(function () {
  window.articlesCarousel = function articlesCarousel(length) {
    return {
      length: length,
      current: 0,
      startX: 0,
      deltaX: 0,
      swiping: false,
      isRtl: (document.documentElement.lang || "").toLowerCase().startsWith('ar'),
      init() {
        this.current = 0;
      },
      go(i) {
        if (i >= 0 && i < length) this.current = i;
      },
      prev() {
        if (this.current > 0) this.current--;
      },
      next() {
        if (this.current < length - 1) this.current++;
      },
      // touch handlers (mobile)
      onTouchStart(e) {
        this.swiping = true;
        this.startX = e.touches[0].clientX;
        this.deltaX = 0;
      },
      onTouchMove(e) {
        if (!this.swiping) return;
        this.deltaX = e.touches[0].clientX - this.startX;
      },
      onTouchEnd() {
        if (!this.swiping) return;
        const threshold = 50; // px
        if (this.isRtl) {
          if (this.deltaX > threshold) this.next();
          if (this.deltaX < -threshold) this.prev();
        } else {
          if (this.deltaX > threshold) this.prev();
          if (this.deltaX < -threshold) this.next();
        }
        this.swiping = false;
        this.deltaX = 0;
      },
    };
  };
})();
