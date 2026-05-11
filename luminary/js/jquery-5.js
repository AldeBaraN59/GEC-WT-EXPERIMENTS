/**
 * jquery-5.js
 * 
 * This script manages UI enhancements for the sticky navigation and banner:
 * 1. It adds a drop shadow to the sticky navigation bar when the user scrolls down.
 * 2. It injects a close button into the promotional banner and allows the user
 *    to dismiss it using a sliding animation.
 */
$(document).ready(function() {
    console.log("jQuery script 5 loaded: Sticky Nav and Banner Close");
    
    // 1. Sticky Nav Shadow on scroll
    $(window).on('scroll', function() {
        if ($(window).scrollTop() > 10) {
            $('nav').css({
                'box-shadow': '0 10px 30px rgba(0,0,0,0.6)',
                'transition': 'box-shadow 0.3s ease'
            });
        } else {
            $('nav').css('box-shadow', 'none');
        }
    });
    
    // 2. Dismissible Promo Banner
    $('.banner').css('position', 'relative');
    $('.banner').append('<span class="close-banner" style="position:absolute; right:15px; top:50%; transform:translateY(-50%); cursor:pointer; font-weight:bold; font-size:1.1rem; color:#000;">✕</span>');
    
    $('.close-banner').on('click', function() {
        $(this).parent().slideUp(300);
    });
});
