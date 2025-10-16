(function ($, Drupal) {

	Drupal.behaviors.autoCloseMessages = {
    attach: function (context, settings) {
      // Select the messages container and set a timeout to hide it after 3 seconds
      setTimeout(function () {
        $('.messages__wrapper').fadeOut('slow');
      }, 2000); // 2000 milliseconds = 3 seconds
    }
  };


  $(document).ready(function () {
    // const toggleButton = $('#toggleButton');
    // const commentSection = $('form#comment-form');
    // commentSection.hide();
    // toggleButton.on('click', function (event) {
    // event.preventDefault();
    // commentSection.toggle();
    // if (commentSection.is(':visible')) {
    // toggleButton.text('Hide Comment');
    // } else {
    // toggleButton.text('Add Comment');
    // }
    // });

    const toggleButton = $("#toggleButton");
    const commentSection = $("form#comment-form");
    const isArabic = $("html").attr("lang") === "ar"; // Check if the language is Arabic

    // Define the button text for English and Arabic
    const buttonText = {
      show: isArabic ? "أضف تعليق" : "Add Comment",
      hide: isArabic ? "إخفاء التعليق" : "Hide Comment",
    };

    // Initial setup
    commentSection.hide();
    toggleButton.text(buttonText.show);

    // Toggle the comment section on button click
    toggleButton.on("click", function (event) {
      event.preventDefault();
      commentSection.toggle();

      if (commentSection.is(":visible")) {
        toggleButton.text(buttonText.hide);
      } else {
        toggleButton.text(buttonText.show);
      }
    });

    $(function () {
      var $selectedDivs = $("dt");
      console.log($selectedDivs.length);

      // Check if there are 4 or fewer dt elements and hide the loadMore button if true
      if ($selectedDivs.length <= 4) {
        $("#loadMore").hide();
      } else {
        $("#loadMore").show();
      }

      // Show the first 4 dt elements initially
      $selectedDivs.slice(0, 4).show();

      // Load more functionality
      $("#loadMore").on("click", function (e) {
        e.preventDefault();
        $("dt:hidden").slice(0, 4).slideDown();

        // If there are no more hidden dt elements, fade out the loadMore button
        if ($("dt:hidden").length == 0) {
          $("#loadMore").fadeOut("slow");
        }
      });
    });


    // Function to open search panel
    $(".search-icon").on("click", function () {
      $(".floating-search").slideToggle(280);
      $(".floating-search").toggleClass("search-expanded");
    });
    // Function to close search panel
    $(".close-search").on("click", function () {
      $(".floating-search").slideToggle(280);
      $(".floating-search").toggleClass("search-expanded");
    });
    // Function to toggle language dropdown
    $("#language-switcher-toggle").on("click", function () {
      $(".language-dropdown").slideToggle(280);
      $(".language-switcher").toggleClass("language-box-expanded");
    });
    // Function to toggle the navabr
    $(".navbar-toggle").on("click", function () {
      $(".menu-bar").toggleClass("navbar-expanded");
      $(this).toggleClass("active");
    });

    $(".iti__country").on("click", function () {
      setTimeout(() => {
        var activeCountry = $(".iti__active");

        if (activeCountry.length) {
          const { countryCode, dialCode } = activeCountry[0]?.dataset;
          const countryValueToSet = countryCode.toUpperCase();
          console.log(countryValueToSet);
          $("#edit-1parent-company-s-country").val(countryValueToSet).change();
        }
      }, 500);
    });

    var swiper = new Swiper(".mySwiper", {
      slidesPerView: 1,
      spaceBetween: 14,
      navigation: {
        nextEl: ".swiper-button-next",
        prevEl: ".swiper-button-prev",
      },
      breakpoints: {
        576: {
          slidesPerView: 2,
          spaceBetween: 16,
        },
        767: {
          slidesPerView: 3,
          spaceBetween: 16,
        },
        1024: {
          slidesPerView: 4,
          spaceBetween: 16,
        },
        1179: {
          slidesPerView: 5,
          spaceBetween: 20,
        },
        1536: {
          slidesPerView: 5,
          spaceBetween: 30,
        },
      },
    });

    var swiper = new Swiper(".testimonial", {
      slidesPerView: 1,
      spaceBetween: 14,
      loop: false,
      pagination: {
        el: ".swiper-pagination",
        clickable: true,
        dynamicBullets: true,
      },
      breakpoints: {
        576: {
          slidesPerView: 2,
          spaceBetween: 16,
        },
        991: {
          slidesPerView: 3,
          spaceBetween: 16,
        },
        1536: {
          slidesPerView: 3,
          spaceBetween: 30,
        },
      },
    });

    var swiper = new Swiper(".swipers", {
      slidesPerView: 1,
      spaceBetween: 14,
      loop: false,
      pagination: {
        el: ".swiper-pagination",
        clickable: true,
        dynamicBullets: true,
      },
      });


    var swiper = new Swiper(".aroundPartnerSlide", {
      slidesPerView: 2,
      spaceBetween: 14,
      loop: false,
      pagination: {
        el: ".swiper-pagination",
        clickable: true,
        dynamicBullets: true,
      },
      breakpoints: {
        576: {
          slidesPerView: 3,
          spaceBetween: 16,
        },
        767: {
          slidesPerView: 4,
          spaceBetween: 30,
        },
        1024: {
          slidesPerView: 5,
          spaceBetween: 30,
        },
        1280: {
          slidesPerView: 8,
          spaceBetween: 30,
        },
      },
    });

    var swiper = new Swiper(".mediaCenterSlide", {
      slidesPerView: 1,
      spaceBetween: 14,
      pagination: {
        el: ".swiper-pagination",
        clickable: true,
        dynamicBullets: true,
      },
      breakpoints: {
        576: {
          slidesPerView: 2,
          spaceBetween: 16,
        },
        767: {
          slidesPerView: 3,
          spaceBetween: 20,
        },
        1024: {
          slidesPerView: 4,
          spaceBetween: 30,
        },
      },
    });

    var swiper = new Swiper(".teamSlide", {
      slidesPerView: 1,
      spaceBetween: 14,
      pagination: {
        el: ".swiper-pagination",
        clickable: true,
        dynamicBullets: true,
      },
      breakpoints: {
        576: {
          slidesPerView: 2,
          spaceBetween: 14,
        },
        767: {
          slidesPerView: 3,
          spaceBetween: 14,
        },
        991: {
          slidesPerView: 4,
          spaceBetween: 14,
        },
        1536: {
          slidesPerView: 4,
          spaceBetween: 30,
        },
      },
    });

    //  Steps Slider vanillaJS

    var swiper = new Swiper(".steps-slider", {
      slidesPerView: 1,
      spaceBetween: 30,

      pagination: {
        el: ".swiper-pagination",
        clickable: true,
      },
      breakpoints: {
        576: {
          slidesPerView: 2,
          spaceBetween: 30,
        },
        640: {
          slidesPerView: 3,
          spaceBetween: 30,
        },
        767: {
          slidesPerView: 2,
          spaceBetween: 30,
        },
        880: {
          slidesPerView: 3,
          spaceBetween: 30,
        },
      },
    });

    $(".estrow .whiteBtn").click(function () {
      $(".slidePopup").toggleClass("showSlide");
      $("body").toggleClass("disable-scroll");
    });
    $(".close").click(function () {
      $(".slidePopup").removeClass("showSlide");
      $("body").toggleClass("disable-scroll");
    });

    $(".consultation-button").click(function () {
      $(".consultation-slide").toggleClass("is-expanded");
    });
    $(".close-consultation").click(function () {
      $(".consultation-slide").removeClass("is-expanded");
    });

    $(".mobile-toggle").click(function () {
      $(".left-filters").slideToggle(280);
      $(".leftPartFilter").toggleClass("filter-expanded");
    });

    // Tabing Content JS
    $(".tab-nav-item").click(function () {
      var tabId = $(this).data("tab");
      // Remove active class from all tabs and tab contents
      $(".tab-nav-item, .tab-content-inner").removeClass("active");
      // Add active class to clicked tab and corresponding content
      $(this).addClass("active");
      $("#" + tabId).addClass("active");
    });
  });

  // Scrollspy function
  $(document).ready(function () {
    function activeLink(li) {
      $(".tab-links li").removeClass("active");
      $(li).addClass("active");
    }

    // Scroll event handler
    $(window).on("scroll", function () {
      let currentScroll = $(window).scrollTop();

      $(".service-content-section").each(function () {
        let section = $(this);
        let sectionTop = section.offset().top - 150;
        let sectionHeight = section.outerHeight();
        let sectionId = section.attr("id");

        if (
          currentScroll >= sectionTop &&
          currentScroll < sectionTop + sectionHeight
        ) {
          const targetLink = $(`a[href="#${sectionId}"]`).parent();
          activeLink(targetLink);
        }
      });
    });

    // Click event handler
    $(".tab-link").on("click", function (e) {
      e.preventDefault();
      let targetId = $(this).attr("href");
      window.location.hash = targetId;

      // Scroll to section
      window.scrollTo(0, $(targetId).offset().top - 100);

      // Update active state
      activeLink($(this).parent());
    });

    // Handle initial hash in URL
    if (window.location.hash) {
      if ($(window.location.hash).length) {
        $(window).scrollTop($(window.location.hash).offset().top - 100);
        activeLink($(`a[href="${window.location.hash}"]`).parent());
      }
    }

    // Set initial active state on page load
    $(window).trigger("scroll");
  });

  // Custom scrollbar for the steps section
  $(document).ready(function () {
    const ps = new PerfectScrollbar(".scroll-container", {
      suppressScrollY: true,
      wheelPropagation: false,
    });
  });
})(jQuery, Drupal);
