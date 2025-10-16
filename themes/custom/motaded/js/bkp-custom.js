(function ($, Drupal) {
  $(document).ready(function() {
    // Function to open search panel
    $('.search-icon').on('click', function() {
        $('.floating-search').slideToggle(280);
        $('.floating-search').toggleClass("search-expanded");
    });
    // Function to close search panel
    $('.close-search').on('click', function() {
        $('.floating-search').slideToggle(280);
        $('.floating-search').toggleClass("search-expanded");
    });
    // Function to toggle language dropdown
    $('#language-switcher-toggle').on('click', function() {
        $('.language-dropdown').slideToggle(280);
        $('.language-switcher').toggleClass("language-box-expanded");
    }); 
    // Function to toggle the navabr
    $('.navbar-toggle').on('click', function() {
        $('.menu-bar').toggleClass("navbar-expanded");
        $(this).toggleClass("active");
    });
    

    $('.iti__country').on('click', function () {
      setTimeout(()=>{
        var activeCountry = $('.iti__active');

        if(activeCountry.length){
          const { countryCode, dialCode } = activeCountry[0]?.dataset;
          const countryValueToSet = countryCode.toUpperCase();
          console.log(countryValueToSet);
          $('#edit-1parent-company-s-country').val(countryValueToSet).change();
        }
      }, 500);
    
  })
 
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
      slidesPerView:1,
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
      slidesPerView:1,
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
	
	
	
	$('.estrow .darkBtn').click(function(){
		$('.slidePopup').toggleClass("showSlide");
    $('body').toggleClass("disable-scroll");
	});
	$('.close').click(function(){
		$('.slidePopup').removeClass("showSlide");
    $('body').toggleClass("disable-scroll");
	});
  
	$('.consultation-button').click(function(){
		$('.consultation-slide').toggleClass("is-expanded");
	});
	$('.close-consultation').click(function(){
		$('.consultation-slide').removeClass("is-expanded");
	});
	
	$('.mobile-toggle').click(function(){
		$('.left-filters').slideToggle(280);
		$('.leftPartFilter').toggleClass("filter-expanded");
	});  
	
 }); 
})(jQuery, Drupal);
