$(document).ready(function() {
    console.log("jQuery script 2 loaded: Smooth scroll");
    $('nav a').on('click', function(e) {
        if (this.hash !== "") {
            e.preventDefault();
            var hash = this.hash;
            $('html, body').animate({
                scrollTop: $(hash).offset() ? $(hash).offset().top : 0
            }, 800);
        }
    });
});
