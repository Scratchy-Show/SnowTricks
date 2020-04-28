$(document).ready(function () {
    $(".burger").click(function () {
        $(this).toggleClass('active');
        $("ul.menu li").slideToggle('fast');
    });

    $(window).resize(function () {
        if ($(window).width() > 1050) {
            $('ul.menu li').removeAttr('style');
            $("a").removeClass("active");
        }
    })
});