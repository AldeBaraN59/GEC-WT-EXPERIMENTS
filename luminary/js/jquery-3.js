$(document).ready(function() {
    console.log("jQuery script 3 loaded: Feature item hover");
    $('.feature-item').hover(
        function() { $(this).css('transform', 'scale(1.05)'); $(this).css('transition', 'transform 0.3s'); },
        function() { $(this).css('transform', 'scale(1)'); }
    );
});
