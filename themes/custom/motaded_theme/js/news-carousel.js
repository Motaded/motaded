function newsCarousel(length) {

  return {
    current: 0,
    autoplayMs: 5000,
    length: length,
    timer: null,

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
