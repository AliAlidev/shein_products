$(document).ready(function() {
    'use strict';
    $("#mobileCartBtn").on('click',function() {
        const mediaQuery = window.matchMedia('(max-width: 991px)')
        if (mediaQuery.matches) {
            $(".cart-modal").toggle();
        }
    });
    $("#desktopmobilecart").on('click',function() {
        const mediaQuery = window.matchMedia('(max-width: 991px)')
        if (mediaQuery.matches) {
            $(".cart-modal").toggle();
        }
    });
    $(".cartClose").on('click',function() {
        const mediaQuery = window.matchMedia('(max-width: 991px)')
        if (mediaQuery.matches) {
            $(".cart-modal").toggle();
        }
    });
});
