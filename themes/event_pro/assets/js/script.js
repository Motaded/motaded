


$(document).ready(function () {











$(window).on('load', function() { 
  $('#loading').fadeOut();  
  $('#page-loader').delay(350).fadeOut('slow'); 
  $('body').delay(350).css({'overflow':'visible'});
});


$(function () {
  var $grid1 = $('.portfolio-lists').imagesLoaded(function() {
    $grid1.isotope({
        itemSelector: '.item'
    });
  });
  var $containerr = $('.portfolio-lists').imagesLoaded( function() {
    $containerr.isotope({
          itemSelector : '.item', 
          layoutMode : 'masonry',
          percentPosition: true
    });
  });
});





    $(".navbar-toggler").click(function(){
      $(this).toggleClass("is-active");
    });



  $(".accordion-style-1 button").click(function(){
    $(this).toggleClass("border-style");
  });






  $("#header-1 .navbar-toggler").click(function(){
    $(".header-1-menu").toggleClass("header-1-block");
  });
  $(".header-1-menu .close .close-icon").click(function(){
    $(".header-1-menu").toggleClass("header-1-block");
  });


  $(".search-btn .btn").click(function(){
    $(".search-btn .search-overlay").toggleClass("search-block");
  });
  $(".hamburger-btn").click(function(){
    $(".hamburger-menu").toggleClass("hamburger-block");
  });




  $(function () {
    $(document).scroll(function () {
      var $nav = $("#header-1 .navigation-sticky");
      $nav.toggleClass("header-fixed", $(this).scrollTop() > 0);
    });
  });
  $(function () {
    $(document).scroll(function () {
      var $nav = $("#header-2 .navigation-sticky");
      $nav.toggleClass("header-fixed", $(this).scrollTop() > 0);
    });
  });
  $(function () {
    $(document).scroll(function () {
      var $nav = $("#header-3 .navigation-sticky");
      $nav.toggleClass("header-fixed", $(this).scrollTop() > 0);
    });
  });





  $(".dropdown-menu a.drop-toggle").on("click", function (e) {
    if (!$(this).next().hasClass("show")) {
      $(this)
        .parents(".dropdown-menu")
        .first()
        .find(".show")
        .removeClass("show");
    }
    var $subMenu = $(this).next(".dropdown-menu");
    $subMenu.toggleClass("show");
    $(this).parent("li").toggleClass("show");
    $(this)
      .parents("li.nav-item.dropdown.show")
      .on("hidden.bs.dropdown", function (e) {
        $(".dropdown-menu .show").removeClass("show");
      });
    return false;
  });



  








// Owl Carousel
$(function () {
  $(".style-1-slider").owlCarousel({
    autoplay: true,
    autoplayTimeout: 5000,
    autoplayHoverPause: false,
    loop: true,
    nav: false,
    margin: 0,
    dots: true,
    responsiveClass: true,
    responsive: {
      0: {
        items: 1,
      },
      600: {
        items: 1,
      },
      1000: {
        items: 1,
      },
    },
  });
  $(".style-1-slider .owl-dots").addClass("owl-dots-1");
  var owl = $(".style-1-slider");
  owl.owlCarousel();
  $(".slider-style-1 .arrows .next").click(function () {
    owl.trigger("next.owl.carousel");
  });
  $(".slider-style-1 .arrows .prev").click(function () {
    owl.trigger("prev.owl.carousel");
  });
});
$(function () {
  $(".car-style-2-slider").owlCarousel({
    autoplay: true,
    autoplayTimeout: 3000,
    autoplayHoverPause: false,
    loop: true,
    nav: false,
    margin: 20,
    dots: false,
    responsiveClass: true,
    responsive: {
      0: {
        items: 1,
      },
      600: {
        items: 2,
      },
      1000: {
        items: 3,
      },
    },
  });
});
$(function () {
  $(".style-2-slider").owlCarousel({
    autoplay: true,
    autoplayTimeout: 5000,
    autoplayHoverPause: false,
    loop: true,
    nav: false,
    margin: 0,
    dots: false,
    responsiveClass: true,
    responsive: {
      0: {
        items: 1,
      },
      600: {
        items: 1,
      },
      1000: {
        items: 1,
      },
    },
  });
  var owl = $(".style-2-slider");
  owl.owlCarousel();
  $(".slider-style-2 .arrows .next").click(function () {
    owl.trigger("next.owl.carousel");
  });
  $(".slider-style-2 .arrows .prev").click(function () {
    owl.trigger("prev.owl.carousel");
  });
});
$(function () {
  $(".style-3-slider").owlCarousel({
    autoplay: true,
    autoplayTimeout: 5000,
    autoplayHoverPause: false,
    loop: true,
    nav: false,
    margin: 0,
    dots: true,
    responsiveClass: true,
    responsive: {
      0: {
        items: 1,
      },
      600: {
        items: 1,
      },
      1000: {
        items: 1,
      },
    },
  });
  $(".style-3-slider .owl-dots").addClass("owl-dots-1");
  var owl = $(".style-3-slider");
  owl.owlCarousel();
  $(".slider-style-3 .arrows .next").click(function () {
    owl.trigger("next.owl.carousel");
  });
  $(".slider-style-3 .arrows .prev").click(function () {
    owl.trigger("prev.owl.carousel");
  });
});
$(function () {
  $(".style-4-slider").owlCarousel({
    autoplay: true,
    autoplayTimeout: 5000,
    autoplayHoverPause: false,
    loop: true,
    nav: false,
    margin: 70,
    dots: true,
    responsiveClass: true,
    responsive: {
      0: {
        items: 1,
      },
      600: {
        items: 1,
      },
      1000: {
        items: 1,
      },
    },
  });
  $(".style-4-slider .owl-dots").addClass("owl-dots-1");
  $(".style-4-slider .owl-dot").html('<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20"><g id="Group_39243" data-name="Group 39243" transform="translate(10682 16860)"><g id="outer_of_the_dot" data-name="Rectangle 17801" transform="translate(-10662 -16860) rotate(90)" fill="rgba(255,255,255,0)" stroke="#2055e4" stroke-width="2"><rect width="20" height="20" rx="10" stroke="none"/><rect x="1" y="1" width="18" height="18" rx="9" fill="none"/></g><rect id="center_of_the_dot" data-name="Rectangle 17802" width="8" height="8" rx="4" transform="translate(-10668 -16854) rotate(90)" fill="#2055e4"/></g></svg>');

});
  $(function () {
    $(".style-5-slider").owlCarousel({
      autoplay: true,
      autoplayTimeout: 5000,
      autoplayHoverPause: false,
      loop: true,
      nav: false,
      margin: 70,
      dots: false,
      responsiveClass: true,
      responsive: {
        0: {
          items: 1,
        },
        600: {
          items: 1,
        },
        1000: {
          items: 1,
        },
      },
    });
    var owl = $(".style-5-slider");
    owl.owlCarousel();
    $(".slider-style-5 .item-wrapper .arrows .next").click(function () {
      owl.trigger("next.owl.carousel");
    });
    $(".slider-style-5 .item-wrapper .arrows .prev").click(function () {
      owl.trigger("prev.owl.carousel");
    });
  });
$(function () {
  $(".style-6-slider").owlCarousel({
    autoplay: true,
    autoplayTimeout: 5000,
    autoplayHoverPause: false,
    loop: true,
    nav: false,
    margin: 50,
    dots: true,
    responsiveClass: true,
    responsive: {
      0: {
        items: 1,
      },
      600: {
        items: 1,
      },
      1000: {
        items: 2,
      },
    },
  });
  $(".style-6-slider .owl-dots").addClass("owl-dots-3");

});
$(function () {
  $(".list-of-doctors-slider").owlCarousel({
    autoplay: true,
    autoplayTimeout: 5000,
    autoplayHoverPause: false,
    loop: true,
    nav: false,
    margin: 30,
    dots: true,
    responsiveClass: true,
    responsive: {
      0: {
        items: 1,
      },
      600: {
        items: 2,
      },
      1000: {
        items: 3,
      },
    },
  });
  $(".list-of-doctors-slider .owl-dots").addClass("owl-dots-3");
});


  // $(".owl-dot").html('<i class="far fa-dot-circle"></i>');









  // Home Sliders
  // Home Page 1
  $(function () {

    $(".home-1-slider").owlCarousel({
      autoplay: true,
      autoplayTimeout: 5000,
      autoplayHoverPause: false,
      loop: true,
      nav: false,
      margin: 0,
      dots: false,
      responsiveClass: true,
      responsive: {
        0: {
          items: 1,
        },
        600: {
          items: 1,
        },
        1000: {
          items: 1,
        },
      },
    });
    // var owl = $(".home-slider-1");
    // owl.owlCarousel();
    // $(".home-banner-1 .arrows .next").click(function () {
    //   owl.trigger("next.owl.carousel");
    // });
    // $(".home-banner-1 .arrows .prev").click(function () {
    //   owl.trigger("prev.owl.carousel");
    // });
    // $(".home-banner-1 .owl-dots").addClass("home-banner-1-dots");
  });


  $(function () {
    $(".home-slider-3").owlCarousel({
      autoplay: true,
      autoplayTimeout: 5000,
      autoplayHoverPause: false,
      loop: true,
      nav: false,
      margin: 0,
      dots: true,
      responsiveClass: true,
      responsive: {
        0: {
          items: 1,
        },
        600: {
          items: 1,
        },
        1000: {
          items: 1,
        },
      },
    });
    $(".home-banner-3 .owl-dots").addClass("owl-dots-1");
    $(".home-banner-3 .owl-dot").html('<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20"><g id="Group_39243" data-name="Group 39243" transform="translate(10682 16860)"><g id="outer_of_the_dot" data-name="Rectangle 17801" transform="translate(-10662 -16860) rotate(90)" fill="rgba(255,255,255,0)" stroke="#2055e4" stroke-width="2"><rect width="20" height="20" rx="10" stroke="none"/><rect x="1" y="1" width="18" height="18" rx="9" fill="none"/></g><rect id="center_of_the_dot" data-name="Rectangle 17802" width="8" height="8" rx="4" transform="translate(-10668 -16854) rotate(90)" fill="#2055e4"/></g></svg>');

  });


  

  $(".portfolio-slider-1").owlCarousel({
    autoplay: true,
    autoplayTimeout: 5000,
    autoplayHoverPause: false,
    loop: true,
    nav: false,
    margin: 0,
    dots: true,
    responsiveClass: true,
    responsive: {
      0: {
        items: 1,
      },
      600: {
        items: 1,
      },
      1000: {
        items: 1,
      },
    },
  });
  $(".portfolio-slider-2").owlCarousel({
    autoplay: true,
    autoplayTimeout: 5000,
    autoplayHoverPause: false,
    loop: true,
    nav: true,
    margin: 0,
    dots: false,
    responsiveClass: true,
    responsive: {
      0: {
        items: 1,
      },
      600: {
        items: 1,
      },
      1000: {
        items: 1,
      },
    },
  });

      // Tabs Style 1
      $('.portfolio-3 .portfolio-lists').masonry({
        itemSelector: '.item',
        layoutMode: 'fitRows'
      });
      $(".portfolio-3 .portfolio-lists").isotope({
        itemSelector: ".item"
      });
      // $(".portfolio-tab .tabs-menu ul li").click(function () {
      //   $(".portfolio-tab .tabs-menu ul li").removeClass("active");
      //   $(this).addClass("active");
      //   var selector;
      //   selector = $(this).attr("data-filter");
      //   $(".portfolio-tab .tabs-item").isotope({
      //     filter: selector,
      //   });
      //   return false;
      // });


      $('.masonry .portfolio-lists').masonry({
        itemSelector: '.item',
        layoutMode: 'fitRows'
      });
      $(".masonry .portfolio-lists").isotope({
        itemSelector: ".item"
      });






  $(".circle_percent").each(function() {
    var $this = $(this),
		$dataV = $this.data("percent"),
		$dataDeg = $dataV * 3.6,
		$round = $this.find(".round_per");
	$round.css("transform", "rotate(" + parseInt($dataDeg + 180) + "deg)");	
	$this.append('<div class="circle_inbox"><span class="percent_text"></span></div>');
	$this.prop('Counter', 0).animate({Counter: $dataV},
	{
		duration: 2000, 
		easing: 'swing', 
		step: function (now) {
            $this.find(".percent_text").text(Math.ceil(now)+"%");
        }
    });
	if($dataV >= 51){
		$round.css("transform", "rotate(" + 360 + "deg)");
		setTimeout(function(){
			$this.addClass("percent_more");
		},1000);
		setTimeout(function(){
			$round.css("transform", "rotate(" + parseInt($dataDeg + 180) + "deg)");
		},1000);
	} 
});





      $('.departments-1 .row').masonry({
        itemSelector: '.col-lg-4',
        layoutMode: 'fitRows'
      });
      $('.doctor-lists .doctor-lists-masonry').masonry({
        itemSelector: '.item',
        layoutMode: 'fitRows'
      });
      $(".doctor-lists .doctor-lists-masonry").isotope({
        itemSelector: ".item"
      });
      $('.about-us-4 .lists').masonry({
        itemSelector: '.list',
        layoutMode: 'fitRows'
      });
      $(".about-us-4 .lists").isotope({
        itemSelector: ".list"
      });
      // $(".portfolio-tab .tabs-menu ul li").click(function () {
      //   $(".portfolio-tab .tabs-menu ul li").removeClass("active");
      //   $(this).addClass("active");
      //   var selector;
      //   selector = $(this).attr("data-filter");
      //   $(".portfolio-tab .portfolio-lists").isotope({
      //     filter: selector,
      //   });
      //   return false;
      // });

  
      $('.portfolio-tab .portfolio-lists').masonry({
        itemSelector: '.item'
      });
      $('.portfolio-tab .portfolio-lists').isotope({
        itemSelector: '.item',
        layoutMode: 'fitRows'
      });

      $(".portfolio-tab .tabs-menu ul li").click(function () {
        $(".portfolio-tab .tabs-menu ul li").removeClass("active");
        $(this).addClass("active");
        var selector;
        selector = $(this).attr("data-filter");
        $(".portfolio-tab .portfolio-lists").isotope({
          filter: selector,
        });
        return false;
      });
      // ------------------------------------
      $('.masonry-style-2.portfolio-tab .portfolio-lists').masonry({
        itemSelector: '.item',
        layoutMode: 'fitRows'
      });

      $(".masonry-style-2.portfolio-tab .tabs-menu ul li").click(function () {
        $(".masonry-style-2.portfolio-tab .tabs-menu ul li").removeClass("active");
        $(this).addClass("active");
        var selector;
        selector = $(this).attr("data-filter");
        $(".masonry-style-2.portfolio-tab .portfolio-lists").isotope({
          filter: selector,
        });
        return false;
      });






      $('.portfolio-lists').magnificPopup({
        delegate: 'a',
        type: 'image',
        tLoading: 'Loading image #%curr%...',
        mainClass: 'mfp-img-mobile',
        gallery: {
          enabled: true,
          navigateByImgClick: true,
          preload: [0,1]
        }
      });




      var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
      var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
      })


    // $('.form-select').select2({
    //   minimumResultsForSearch: -1
    // });


  // Clipboard
	var elementCopy = document.getElementsByClassName("language-markup");
	if(typeof(elementCopy) != 'undefined' && elementCopy != null){ 
	var clipboard = new ClipboardJS('.clipboard');   
	clipboard.on('success', function (e) {
	e.trigger.textContent = 'Copied';
	window.setTimeout(function() {
		e.trigger.textContent = 'Copy to Clipboard';
	}, 8000);
	console.log(e);
	});
	clipboard.on('error', function (e) {
	console.log(e);
	});
	}
      








  
  

});
