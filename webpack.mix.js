const mix = require('laravel-mix');


// mix.js([
//     'resources/js/frontend/default/app.js',
//     'public/backend/js/loadah.min.js',
//     'public/frontend/default/js/jquery.simpleLoadMore.js',
//     'public/backend/vendors/select2/js/select2.min.js',
//     'public/backend/vendors/js/nice-select.min.js',
//     'public/frontend/default/vendors/owl_carousel/js/owl.carousel.min.js',
//     'public/frontend/default/js/jquery.nicescroll.min.js',
//     'public/frontend/default/js/rangeslider.js',
//     'public/frontend/default/js/jquery.scrollbar.min.js',
//     'public/frontend/default/vendors/gijgo/gijgo.min.js',
//     'public/frontend/default/vendors/responsive_table/js/tablesaw.stackonly.js',
//     'public/frontend/default/js/custom.js',
//     'public/frontend/default/js/highlight.js',
//     'public/backend/vendors/lazyload/lazyload.js'
// ], 'public/frontend/default/compile_js/app.js')

// .sass('resources/sass/frontend/default/app.scss', 'public/frontend/default/compile_css/app.css').options({
//     processCssUrls: false
// })
// .sass('resources/sass/frontend/default/rtl_app.scss', 'public/frontend/default/compile_css/rtl_app.css').options({
//     processCssUrls: false
// });

mix.js([
    'resources/js/custom3d.js', 'public/js',
    'resources/js/frontend/amazy/app.js',
    'public/backend/js/loadah.min.js',
    'public/frontend/amazy/js/owl.carousel.min.js',
    'public/frontend/amazy/js/isotope.pkgd.min.js',
    'public/frontend/amazy/js/waypoints.min.js',
    'public/frontend/amazy/js/jquery.counterup.min.js',
    'public/frontend/amazy/js/imagesloaded.pkgd.min.js',
    'public/frontend/amazy/js/nice-select.min.js',
    'public/frontend/amazy/js/barfiller.js',
    'public/frontend/amazy/js/jquery.slicknav.js',
    'public/frontend/amazy/js/jquery.magnific-popup.min.js',
    'public/frontend/amazy/js/jquery.ajaxchimp.min.js',
    'public/frontend/amazy/js/parallax.js',
    'public/frontend/amazy/js/gijgo.min.js',
    'public/frontend/amazy/js/slick.min.js',
    'public/frontend/amazy/js/perfect-scrollbar.js',
    'public/frontend/amazy/js/jquery.nav.js',
    'public/frontend/amazy/js/summernote-lite.min.js',
    'public/frontend/amazy/js/query-ui.js',
    'public/frontend/amazy/js/jquery.countdown.min.js',
    'public/frontend/amazy/js/mail-script.js',
    'public/frontend/amazy/js/main.js',
    'public/frontend/default/js/highlight.js',
    'public/backend/vendors/lazyload/lazyload.js'
], 'public/frontend/amazy/compile_js/app.js')

.sass('resources/sass/frontend/amazy/app.scss', 'public/frontend/amazy/compile_css/app.css').options({
        processCssUrls: false
    })
    .sass('resources/sass/frontend/amazy/rtl_app.scss', 'public/frontend/amazy/compile_css/rtl_app.css').options({
        processCssUrls: false
    })
    .copy('node_modules/bootstrap/dist/js/bootstrap.bundle.min.js', 'public/js/bootstrap.bundle.min.js');
    // .sass('Modules/SEOOverAll/Resources/assets/sass/app.scss', 'Modules/SEOOverAll/Resources/assets/sass/style.css').options({
    //     processCssUrls: false
    // });
