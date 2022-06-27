const mix = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

mix.js('resources/js/app.js', 'public/static/js')
    .sass('resources/sass/app.scss', 'public/static/css');

// Auto-reload
mix.browserSync({
    proxy: process.env.APP_HOST ? process.env.APP_HOST : '127.0.0.1:8000',
    port: process.env.WEB_PORT ? process.env.WEB_PORT : 3000,
    open: false
});
