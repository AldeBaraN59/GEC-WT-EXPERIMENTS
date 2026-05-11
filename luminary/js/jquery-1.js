/**
 * jquery-1.js
 * 
 * This script uses jQuery to create a smooth fade-in animation
 * for the hero section text when the document is first loaded.
 */
$(document).ready(function () {
    console.log("jQuery script 1 loaded: Hero fade in");
    $('.hero-text').hide().fadeIn(1500);
});
