<?php
if (apply_filters('project_theme_skip_preloader', false)) {
    return;
}
?>
<div class="preloader" id="preloader" aria-hidden="true">
    <div class="fon"></div>
    <div class="wrap">
        <div class="preloader-content">
            <div class="preloader-star">
                <span class="coordinates-1">+46°</span>
                <span class="coordinates-2">+77°</span>
                <svg width="561" height="341" viewBox="0 0 561 341" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M561 170.466C284.774 171.899 282.867 173.055 280.5 340.932C278.146 173.071 276.226 171.899 0 170.466C276.226 169.033 278.133 167.877 280.5 0C282.854 167.861 284.774 169.033 561 170.466Z" fill="#3a3a3a"/>
                </svg>
                <div class="star-fill">
                    <svg width="561" height="341" viewBox="0 0 561 341" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M561 170.466C284.774 171.899 282.867 173.055 280.5 340.932C278.146 173.071 276.226 171.899 0 170.466C276.226 169.033 278.133 167.877 280.5 0C282.854 167.861 284.774 169.033 561 170.466Z" fill="#BDFF7B"/>
                    </svg>
                </div>
            </div>
            <div class="preloader-counter">
                <span class="preloader-bracket preloader-bracket-1"></span>
                <span class="preloader-num" data-target="46">0</span>
                <span class="preloader-dash">-</span>
                <span class="preloader-num" data-target="77">0</span>
                <span class="preloader-bracket preloader-bracket-2"></span>
            </div>
        </div>
    </div>
</div>
