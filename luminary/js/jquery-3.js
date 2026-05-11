/**
 * jquery-3.js
 * 
 * This script adds dynamic hover effects to feature items on the page.
 * It uses jQuery to scale elements up slightly when hovered and 
 * return them to their original size when the mouse leaves.
 */
$(document).ready(function() {
    console.log("jQuery script 3 loaded: Feature item hover");
    $('.feature-item').hover(
        function() { $(this).css('transform', 'scale(1.05)'); $(this).css('transition', 'transform 0.3s'); },
        function() { $(this).css('transform', 'scale(1)'); }
    );
});
