<meta charset="<?php bloginfo('charset'); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">

<script>
    // document.documentElement.classList.add('preloader-lock');
</script>

<style>
    html {
        scrollbar-gutter: stable;
    }

    /*html.preloader-lock,*/
    /*html.preloader-lock body {*/
    /*    overflow: hidden;*/
    /*}*/

    /*html.preloader-lock {*/
    /*    background: #1E1E1E;*/
    /*}*/

    #preloader {
        position: fixed;
        inset: 0;
        z-index: 9999;
        background: #1E1E1E;
    }

    #preloader .fon {
        position: absolute;
        inset: 0;
        background: #1E1E1E;
        &:before {
            content: "";
            position: absolute;
            width: 100%;
            top: 0;
            bottom: 0;
            left: 0;
            right: 0;
            @media (min-width: 576px) {
                background-image: url(/wp-content/themes/project-theme/assets/img/svg/bg1.svg);
            }
            @media (max-width: 575px) {
                background-image: url(/wp-content/themes/project-theme/assets/img/bg1-mobile.png);
            }
            background-position: top center;
            opacity: .2;
            z-index: -1;
        }
    }
    .preloader.preloader-completed {
        pointer-events: none;
    }

    .preloader.preloader-completed {
        pointer-events: none;
        opacity: 0;
        transform: translate3d(0, -105%, 0);
    }
</style>

<!--preloader start-->
<style>
    html.preloader-lock,
    html.preloader-lock body,
    body.ovh {
        overflow: hidden !important;
        height: 100%;
    }

    .preloader {
        position: fixed;
        inset: 0;
        z-index: 9999;
        opacity: 1;
        visibility: visible;
        overflow: hidden;
        pointer-events: auto;
        background: #1E1E1E;
        will-change: opacity;
        transform: none;
        backface-visibility: hidden;
        contain: paint;
    }

    .preloader .fon {
        position: absolute;
        inset: 0;
        z-index: 0;
        background: #1E1E1E;
        opacity: 0;
        transform: scale(1.04);
        animation: preloaderFonIn 0.8s cubic-bezier(0.22, 1, 0.36, 1) forwards;
    }

    .preloader .wrap {
        position: absolute;
        inset: 0;
        z-index: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        will-change: opacity;
        transform: none;
        backface-visibility: hidden;
    }

    .preloader-content {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 20px;
        opacity: 0;
        transform: translate3d(0, 24px, 0) scale(0.985);
        animation: preloaderContentIn 0.9s cubic-bezier(0.22, 1, 0.36, 1) 0.12s forwards;
    }

    .preloader-star {
        position: relative;
        width: 561px;
        height: 340px;
        will-change: opacity, transform;
    }

    .preloader-star > svg,
    .preloader-star .star-fill svg {
        display: block;
        width: 100%;
        height: 100%;
    }

    .preloader-star > svg {
        opacity: 0;
        transform: scale(0.92);
        animation: preloaderStarBaseIn 0.85s cubic-bezier(0.22, 1, 0.36, 1) 0.25s forwards;
    }

    .preloader-star .star-fill {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        clip-path: inset(0 100% 0 0);
        will-change: clip-path;
        animation: preloaderStarReveal 1.8s cubic-bezier(0.4, 0, 0.2, 1) 0.35s forwards;
    }

    .preloader-star .star-fill svg {
        filter: drop-shadow(0 0 18px rgba(189, 255, 123, 0.16));
    }

    .coordinates-1,
    .coordinates-2 {
        position: absolute;
        color: #BDFF7B;
        font-size: 12px;
        opacity: 0;
        transform: translate3d(0, 10px, 0);
        will-change: opacity, transform;
    }

    .coordinates-1 {
        top: 99px;
        left: 169px;
        animation: preloaderSmallTextIn 0.55s ease-out 0.65s forwards;
    }

    .coordinates-2 {
        top: 220px;
        left: 360px;
        animation: preloaderSmallTextIn 0.55s ease-out 0.82s forwards;
    }

    .preloader-counter {
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 2px;
        width: 170px;
        height: 60px;
        margin-top: 40px;
        color: #BDFF7B;
        font-size: 46px;
        font-variant-numeric: tabular-nums;
        opacity: 0;
        transform: translate3d(0, 18px, 0);
        animation: preloaderCounterIn 0.75s cubic-bezier(0.22, 1, 0.36, 1) 0.55s forwards;
    }

    .preloader-num,
    .preloader-dash {
        display: inline-block;
    }

    .preloader-dash {
        opacity: 0.7;
    }

    .preloader-bracket {
        position: absolute;
        inset: 0;
        z-index: 0;
        pointer-events: none;
    }

    .preloader-bracket::before,
    .preloader-bracket::after {
        content: "";
        position: absolute;
        width: 17px;
        height: 17px;
        border-left: 2px solid var(--green, #BDFF7B);
        border-top: 2px solid var(--green, #BDFF7B);
        filter:
                drop-shadow(0 4px 4px rgba(0, 0, 0, 0.25))
                drop-shadow(0 4px 4px rgba(0, 0, 0, 0.25));
    }

    .preloader-bracket-1::before {
        left: 0;
        top: 0;
    }

    .preloader-bracket-1::after {
        right: 0;
        top: 0;
        transform: rotate(90deg);
    }

    .preloader-bracket-2::before {
        left: 0;
        bottom: 0;
        transform: rotate(270deg);
    }

    .preloader-bracket-2::after {
        right: 0;
        bottom: 0;
        transform: rotate(180deg);
    }

    .preloader.preload-hidden {
        pointer-events: none;
        animation: preloaderFadeOut 0.65s ease forwards;
    }

    .preloader.preload-hidden .wrap {
        animation: none;
    }

    .preloader.preload-hidden .fon {
        opacity: 1;
        transform: none;
        animation: none;
    }

    .preloader.preloader-completed {
        pointer-events: none;
        opacity: 0;
        visibility: hidden;
        transform: none;
    }

    @media (max-width: 600px) {
        .preloader {
            will-change: opacity;
            transform: none;
            contain: paint;
        }

        .preloader .wrap {
            will-change: opacity;
            transform: none;
        }

        .preloader-content {
            gap: 16px;
            transform: none;
            animation: preloaderContentInMobile 0.7s ease-out 0.15s forwards;
        }

        .preloader-star {
            width: 82vw;
            max-width: 330px;
            height: auto;
            aspect-ratio: 561 / 340;
            will-change: opacity;
        }

        .preloader-star > svg {
            transform: none;
            animation: preloaderStarMobileIn 0.7s ease-out 0.25s forwards;
        }

        .preloader-star .star-fill {
            clip-path: inset(0 100% 0 0);
            animation: preloaderStarRevealMobile 2.2s ease-out 0.45s forwards;
            will-change: clip-path;
        }

        .preloader-star .star-fill svg {
            filter: none;
        }

        .preloader-bracket::before,
        .preloader-bracket::after {
            filter: none;
        }

        .coordinates-1,
        .coordinates-2 {
            display: none;
        }

        .preloader-counter {
            margin-top: 24px;
            font-size: 34px;
            transform: none;
            animation: preloaderCounterMobileIn 0.7s ease-out 0.35s forwards;
        }

        .preloader.preload-hidden {
            animation-duration: 0.9s;
        }
    }

    @keyframes preloaderStarRevealMobile {
        from {
            clip-path: inset(0 100% 0 0);
        }

        to {
            clip-path: inset(0 0 0 0);
        }
    }

    @keyframes preloaderFonIn {
        from {
            opacity: 0;
            transform: scale(1.04);
        }

        to {
            opacity: 1;
            transform: scale(1);
        }
    }

    @keyframes preloaderContentIn {
        from {
            opacity: 0;
            transform: translate3d(0, 24px, 0) scale(0.985);
        }

        to {
            opacity: 1;
            transform: translate3d(0, 0, 0) scale(1);
        }
    }

    @keyframes preloaderStarBaseIn {
        from {
            opacity: 0;
            transform: scale(0.92);
        }

        to {
            opacity: 1;
            transform: scale(1);
        }
    }

    @keyframes preloaderStarReveal {
        from {
            clip-path: inset(0 100% 0 0);
        }

        to {
            clip-path: inset(0 0 0 0);
        }
    }

    @keyframes preloaderSmallTextIn {
        from {
            opacity: 0;
            transform: translate3d(0, 10px, 0);
        }

        to {
            opacity: 1;
            transform: translate3d(0, 0, 0);
        }
    }

    @keyframes preloaderCounterIn {
        from {
            opacity: 0;
            transform: translate3d(0, 18px, 0);
        }

        to {
            opacity: 1;
            transform: translate3d(0, 0, 0);
        }
    }

    @keyframes preloaderFadeOut {
        from {
            opacity: 1;
            visibility: visible;
        }

        to {
            opacity: 0;
            visibility: hidden;
        }
    }

    @keyframes preloaderContentInMobile {
        from {
            opacity: 0;
        }

        to {
            opacity: 1;
        }
    }

    @keyframes preloaderStarMobileIn {
        from {
            opacity: 0;
        }

        to {
            opacity: 1;
        }
    }

    @keyframes preloaderCounterMobileIn {
        from {
            opacity: 0;
        }

        to {
            opacity: 1;
        }
    }
</style>
<!--preloader end-->

<link rel="icon" type="image/png" href="/wp-content/themes/project-theme/assets/favicon/favicon-96x96.png" sizes="96x96" />
<link rel="icon" type="image/svg+xml" href="/wp-content/themes/project-theme/assets/favicon/favicon.svg" />
<link rel="shortcut icon" href="/wp-content/themes/project-theme/assets/favicon/favicon.ico" />
<link rel="apple-touch-icon" sizes="180x180" href="/wp-content/themes/project-theme/assets/favicon/apple-touch-icon.png" />
<meta name="apple-mobile-web-app-title" content="RASTI" />
<link rel="manifest" href="/wp-content/themes/project-theme/assets/favicon/site.webmanifest" />
