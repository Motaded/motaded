function partnersCarousel(length) {
  console.log('test');

  return {
    // Public state
    // items: [
    //   {
    //     id: 1,
    //     img: "https://www.motaded.com.sa/sites/default/files/2024-10/motaded-partner-1.webp",
    //     alt: "Motaded Partner 1",
    //   },
    //   {
    //     id: 2,
    //     img: "https://www.motaded.com.sa/sites/default/files/2024-10/motaded-partner-2.webp",
    //     alt: "Motaded Partner 2",
    //   },
    //   {
    //     id: 3,
    //     img: "https://www.motaded.com.sa/sites/default/files/2024-10/motaded-partner-3.webp",
    //     alt: "Motaded Partner 3",
    //   },
    //   {
    //     id: 4,
    //     img: "https://www.motaded.com.sa/sites/default/files/2024-10/motaded-partner-4.webp",
    //     alt: "Motaded Partner 4",
    //   },
    //   {
    //     id: 5,
    //     img: "https://www.motaded.com.sa/sites/default/files/2024-10/motaded-partner-5.webp",
    //     alt: "Motaded Partner 5",
    //   },
    //   {
    //     id: 6,
    //     img: "https://www.motaded.com.sa/sites/default/files/2024-10/motaded-partner-6.webp",
    //     alt: "Motaded Partner 6",
    //   },
    //   {
    //     id: 7,
    //     img: "https://www.motaded.com.sa/sites/default/files/2024-10/motaded-partner-7.webp",
    //     alt: "Motaded Partner 7",
    //   },
    //   {
    //     id: 8,
    //     img: "https://www.motaded.com.sa/sites/default/files/2024-10/motaded-partner-8.webp",
    //     alt: "Motaded Partner 8",
    //   },
    //   {
    //     id: 9,
    //     img: "https://www.motaded.com.sa/sites/default/files/2024-10/motaded-partner-9.webp",
    //     alt: "Motaded Partner 9",
    //   },
    //   {
    //     id: 10,
    //     img: "https://www.motaded.com.sa/sites/default/files/2024-10/motaded-partner-10.webp",
    //     alt: "Motaded Partner 10",
    //   },
    //   {
    //     id: 11,
    //     img: "https://www.motaded.com.sa/sites/default/files/2024-10/sabb-partner.webp",
    //     alt: "SABB Bank Partner",
    //   },
    //   {
    //     id: 12,
    //     img: "https://www.motaded.com.sa/sites/default/files/2024-10/motaded-partner-12.webp",
    //     alt: "Motaded Partner 12",
    //   },
    //   {
    //     id: 13,
    //     img: "https://www.motaded.com.sa/sites/default/files/2024-10/motaded-partner-14.webp",
    //     alt: "Motaded Partner 14",
    //   },
    //   {
    //     id: 14,
    //     img: "https://www.motaded.com.sa/sites/default/files/2024-10/motaded-partner-snb.webp",
    //     alt: "SNB Bank Partner",
    //   },
    //   {
    //     id: 15,
    //     img: "https://www.motaded.com.sa/sites/default/files/2024-10/al-rahji.webp",
    //     alt: "Al Rajhi Bank Partner",
    //   },
    //   {
    //     id: 16,
    //     img: "https://www.motaded.com.sa/sites/default/files/2024-10/motaded-partner-stcpay.webp",
    //     alt: "STC Pay Partner",
    //   },
    //   {
    //     id: 17,
    //     img: "https://www.motaded.com.sa/sites/default/files/2024-10/motaded-partner-18.webp",
    //     alt: "Motaded Partner 18",
    //   },
    // ],
    length: length,
    ariaLabel: "Partner logos carousel",
    currentPage: 0,
    autoplayMs: 3500,
    _timer: null,
    _cardWidth: 0,
    isRtl:
      typeof document !== "undefined" &&
      document.documentElement.getAttribute("dir") === "rtl",

    init() {
      // prepare snapping widths
      this.$nextTick(() => {
        this.measure();
        window.addEventListener("resize", this.measure.bind(this), {
          passive: true,
        });
        this.play();
      });
    },

    // Desktop buttons (just loop pages visually by updating active page index for dots)
    prev() {
      this.currentPage = Math.max(0, this.currentPage - 1);
    },
    next() {
      this.currentPage = Math.min(this.pageCount - 1, this.currentPage + 1);
    },

    // Mobile snapping helpers
    get pageCount() {
      // approximate: show ~2 logos per view on phones
      return Math.max(1, Math.ceil(this.length / 2));
    },

    measure() {
      const track = this.$refs.track;
      if (!track) return;
      // Grab first slide width to compute snapping
      const first = track.querySelector("div > div");
      this._cardWidth = first ? first.getBoundingClientRect().width + 12 : 240; // + gap
    },

    onScroll() {
      // update dot from scroll position
      const pos = this._getScrollPosition();
      const idx = Math.round(pos / (this._cardWidth * 2));
      this.currentPage = Math.min(this.pageCount - 1, Math.max(0, idx));
    },

    goTo(page) {
      const left = page * this._cardWidth * 2; // ~2 cards per "page"
      this._setScrollPosition(left);
      this.currentPage = page;
    },

    prevSnap() {
      this.goTo(Math.max(0, this.currentPage - 1));
    },
    nextSnap() {
      this.goTo(Math.min(this.pageCount - 1, this.currentPage + 1));
    },

    play() {
      if (this._timer) return;
      this._timer = setInterval(() => {
        const next = (this.currentPage + 1) % this.pageCount;
        this.goTo(next);
      }, this.autoplayMs);
    },

    pause() {
      clearInterval(this._timer);
      this._timer = null;
    },

    _getScrollPosition() {
      const track = this.$refs.track;
      if (!track) return 0;
      if (!this.isRtl) {
        return track.scrollLeft;
      }

      return track.scrollWidth - track.clientWidth - track.scrollLeft;
    },

    _setScrollPosition(value) {
      const track = this.$refs.track;
      if (!track) return;
      const left = this.isRtl
        ? track.scrollWidth - track.clientWidth - value
        : value;

      track.scrollTo({ left, behavior: "smooth" });
    },
  };
}
