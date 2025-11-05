function newsCarousel(length) {
  console.log(length);

  return {
    current: 0,
    autoplayMs: 5000,
    length: length,
    timer: null,
    // slides: [
    //   {
    //     title:
    //       "Chaired by HRH the Crown Prince, the Cabinet Approves the State's General Budget for the Fiscal Year 2025",
    //     date: "2025-02-12",
    //     img: "https://placehold.co/800x500",
    //   },
    //   {
    //     title:
    //       "Saudi Intellectual Property Hits a Milestone with 20,000th Patent Issued",
    //     date: "2025-07-02",
    //     img: "https://placehold.co/800x500",
    //   },
    //   {
    //     title: "stc Powers Up Esports World Cup with Cutting-Edge 5G Network",
    //     date: "2025-07-01",
    //     img: "https://placehold.co/800x500",
    //   },
    //   {
    //     title:
    //       "SDAIA Honors Tuwaiq Academy Students with Specialized Training Scholarships",
    //     date: "2025-06-30",
    //     img: "https://placehold.co/800x500",
    //   },
    // ],
    next() {
      this.current = (this.current + 1) % this.length;
    },
    prev() {
      this.current =
        (this.current - 1 + this.length) % this.length;
    },
    go(i) {
      this.current = i;
    },
    startAutoplay() {
      this.stopAutoplay();
      this.timer = setInterval(() => this.next(), this.autoplayMs);
    },
    stopAutoplay() {
      if (this.timer) clearInterval(this.timer);
      this.timer = null;
    },
  };
}
