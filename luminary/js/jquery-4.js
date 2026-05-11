/**
 * jquery-4.js
 * 
 * This script handles scroll-reveal animations. It hides specific elements 
 * (like feature items and course cards) initially, and then fades them in 
 * and slides them up dynamically as the user scrolls them into the viewport.
 */
$(document).ready(function() {
    console.log("jQuery script 4 loaded: Scroll Animations");
    
    // Hide elements initially
    var $animatable = $('.feature-item, .course-card, .testimonial-card');
    $animatable.css({
        'opacity': 0,
        'transform': 'translateY(20px)',
        'transition': 'all 0.6s ease-out'
    });
    
    // Fade in and slide up when scrolling into view
    $(window).on('scroll', function() {
        var scrollTop = $(window).scrollTop();
        var windowHeight = $(window).height();
        
        $animatable.each(function() {
            var $el = $(this);
            if ($el.css('opacity') == 0) {
                var offsetTop = $el.offset().top;
                if (scrollTop + windowHeight > offsetTop + 50) {
                    $el.css({
                        'opacity': 1,
                        'transform': 'translateY(0)'
                    });
                }
            }
        });
    });
    
    // Trigger immediately to show elements already in view
    $(window).trigger('scroll');
});
