jQuery(document).ready(function($) {
	"use strict";
	//  TESTIMONIALS CAROUSEL HOOK
	jQuery('.client-testimonials').each(function( index ) {
	    var objectId = jQuery(this).attr('id');
        jQuery('#'+objectId).owlCarousel({
            loop: true,
            center: true,
            items: 1,
            margin: 0,
            autoplay: true,
            dots:false,
			dotsData: false,
            navigation:false,
            autoplayTimeout: 8500,
            smartSpeed: 450,
            responsive: {
              0: {
                items: 1
              },
              768: {
                items: 1
              },
              1170: {
                items: 1
              }
            }
        });
	});
});