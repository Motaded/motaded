
// Steps JS functionality.
// Data from drupalSettings via preprocess function in motaded_theme.theme.
const data = drupalSettings.motaded_theme?.data;
function newsCarousel() {

  return {
    current: 0,
    autoplayMs: 5000,
    timer: null,
    slides: [
      {
        title:
          "Chaired by HRH the Crown Prince, the Cabinet Approves the State's General Budget for the Fiscal Year 2025",
        date: "2025-02-12",
        img: "https://placehold.co/800x500",
      },
      {
        title:
          "Saudi Intellectual Property Hits a Milestone with 20,000th Patent Issued",
        date: "2025-07-02",
        img: "https://placehold.co/800x500",
      },
      {
        title: "stc Powers Up Esports World Cup with Cutting-Edge 5G Network",
        date: "2025-07-01",
        img: "https://placehold.co/800x500",
      },
      {
        title:
          "SDAIA Honors Tuwaiq Academy Students with Specialized Training Scholarships",
        date: "2025-06-30",
        img: "https://placehold.co/800x500",
      },
    ],
    next() {
      this.current = (this.current + 1) % this.slides.length;
    },
    prev() {
      this.current =
        (this.current - 1 + this.slides.length) % this.slides.length;
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

function investorTimeline() {

  return {
    current: 0,
    steps: drupalSettings.motaded_theme.steps,
    // steps: [
    //   {
    //     title: "Ministry of investment",
    //     desc: "Obtain an investment license from the Ministry of Investment in Saudi Arabia (MISA)",
    //     img: "https://www.motaded.com.sa/sites/default/files/2024-11/saudi-ministry-of-investment-logo-4DF28900FD-seeklogo.com%201.webp",
    //   },
    //   {
    //     title: "Ministry of commerce",
    //     desc: "issuing the Memorandum of association and commercial registration from the ministry of commerce",
    //     img: "https://www.motaded.com.sa/sites/default/files/2024-11/saudi-ministry-of-investment-logo-4DF28900FD-seeklogo.com%201-1.webp",
    //   },
    //   {
    //     title: "Ministry of Human resource",
    //     desc: "Issuing the visa of the general manager from the ministry of human resource",
    //     img: "https://www.motaded.com.sa/sites/default/files/2024-11/saudi-ministry-of-investment-logo-4DF28900FD-seeklogo.com%201-2.webp",
    //   },
    //   {
    //     title: "General Organization of social Insurance “ GOSI”",
    //     desc: "Open your account and activate Nitaqat system from the General organization of social insurance “ GOSI”",
    //     img: "https://www.motaded.com.sa/sites/default/files/2024-11/saudi-ministry-of-investment-logo-4DF28900FD-seeklogo.com%201-3.webp",
    //   },
    //   {
    //     title: "Zakat, Tax and customs authority",
    //     desc: "Issuing all the tax certificate from Zakat , tax and customs authority",
    //     img: "https://www.motaded.com.sa/sites/default/files/2024-11/saudi-ministry-of-investment-logo-4DF28900FD-seeklogo.com%201-4.webp",
    //   },
    //   {
    //     title: "Organizational Platforms",
    //     desc: "Register in all the Organizational Platforms like “ Mudad , Muqeem and Qiwa”",
    //     img: "https://www.motaded.com.sa/sites/default/files/2024-11/saudi-ministry-of-investment-logo-4DF28900FD-seeklogo.com%201-5.webp",
    //   },
    //   {
    //     title: "Saudi Post Local “ SPL”",
    //     desc: "Register the address from saudi post local “SPL”",
    //     img: "https://www.motaded.com.sa/sites/default/files/2024-11/saudi-ministry-of-investment-logo-4DF28900FD-seeklogo.com%201-6.webp",
    //   },
    //   {
    //     title: "Ministry of interior",
    //     desc: "Issuing the residency visa (Iqama) from Ministry of interior",
    //     img: "https://www.motaded.com.sa/sites/default/files/2024-11/saudi-ministry-of-investment-logo-4DF28900FD-seeklogo.com%201-7.webp",
    //   },
    //   {
    //     title: "Bank account",
    //     desc: "Opening the company’s bank account",
    //     img: "https://www.motaded.com.sa/sites/default/files/2024-11/image%202.webp",
    //   },
    // ],
    init() {
      // optional: read index from hash (?step=) or sessionStorage
      const url = new URL(window.location.href);
      const idx = parseInt(url.searchParams.get("step"), 10);
      if (!Number.isNaN(idx) && idx >= 0 && idx < this.steps.length) {
        this.current = idx;
      }
    },
    go(i) {
      if (i >= 0 && i < this.steps.length) this.current = i;
    },
    next() {
      if (this.current < this.steps.length - 1) this.current++;
    },
    prev() {
      if (this.current > 0) this.current--;
    },
    // This function is commented out in favor of using 'step_title' from Drupal.
    // shortLabel(i) {
    //   // compact top labels to keep the rail clean
    //   switch (i) {
    //     case 0:
    //       return `${data}`;
    //     case 1:
    //       return "Commerce";
    //     case 2:
    //       return "HR";
    //     case 3:
    //       return "GOSI";
    //     case 4:
    //       return "ZATCA";
    //     case 5:
    //       return "Platforms";
    //     case 6:
    //       return "SPL";
    //     case 7:
    //       return "MOI";
    //     case 8:
    //       return "Bank";
    //     default:
    //       return `Step ${i + 1}`;
    //   }
    // },
  };
}
/* eslint-enable */
