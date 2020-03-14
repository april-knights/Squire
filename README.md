# Squire

## Dev Setup
1. Install `git`, `php`, `composer` and `npm`.
2. Clone the repository using `git clone https://github.com/april-knights/Squire.git`.
3. Enter the `Squire` directory and run `composer install`.
4. Start the development server using `php artisan serve`
5. Install the required Javascript dependencies using `npm install`.
6. Run `npm run watch` to automatically recompile css and js files when you update them.

## Deployment
0. Properly set up your webserver.
1. Install `git`, `php` and `composer`.
2. Clone the repository using `git clone https://github.com/april-knights/Squire.git`.
3. Enter the `Squire` directory and run `composer install --optimize-autoloader --no-dev`.
4. Install the required Javascript dependencies using `npm install`.
5. Run `npm run prod` to generate the minified static files.
6. Point your webserver to `squire/public`.
