document.addEventListener('DOMContentLoaded', () => {
    if (typeof Swup === 'undefined') {
        return;
    }

    new Swup({
        containers: ['#swup']
    });
});


jQuery(document).ready(function ($) {
    setTimeout(function () {
        $('[aria-current="page"]').addClass('js-active');
    }, 500);

    $('body').on('click', '.menu a', function (e) {
        // e.preventDefault();
        $('.js-active').removeClass('js-active')
        $(this).addClass('js-active');

        if ( $(this).parents('.menu').hasClass('menu-2') ) {
            setTimeout(function () {
                $('body').removeClass('ovh');
                $('.js-burger').removeClass('is-active');
                $('.js-mobile-menu').removeClass('is-active');
            }, 500);
        }
    });

    $(window).scroll(function () {
        if ($(window).scrollTop() > 0 ) {
            $('.header').addClass('scroll');
        } else {
            $('.header').removeClass('scroll');
        }
    });

    $('body').on('click', '.js-burger', function (e) {
        e.preventDefault();
        $('body').toggleClass('ovh');
        $('.js-burger').toggleClass('is-active');
        $('.js-mobile-menu').toggleClass('is-active');
    });

});