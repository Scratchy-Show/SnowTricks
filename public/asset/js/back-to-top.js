// Vérifie la distance et affiche la flèche "back-to-top".
$(window).scroll(function () {
    if ( $(this).scrollTop() > 800 ) {
        $('.back-to-top').addClass('show-back-to-top');
    } else {
        $('.back-to-top').removeClass('show-back-to-top');
    }
});