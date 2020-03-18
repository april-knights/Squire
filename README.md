# Squire

## Dev Setup
1. Install `git`, `php`, `composer` and `npm`.
2. Clone the repository using `git clone https://github.com/april-knights/Squire.git`.
3. Enter the `Squire` directory and run `composer install`.
4. Copy `.env.example` to `.env` and modify whatever you need.
5. Start the development server using `php artisan serve`

### Compiling CSS/JS changes
1. Install the required Javascript dependencies using `npm install`.
2. Run `npm run dev` to recompile css and js files.

### Automatically recompile CSS/JS and refresh browser
1. Change the redirect_uri in `.env` and on reddit to `127.0.0.1:3000`.
2. Run `npm run watch`. Whenever you make a change to the application, your browser is automatically reloaded.


## Deployment
0. Properly set up your webserver.
1. Install `git`, `php` and `composer`.
2. Clone the repository using `git clone https://github.com/april-knights/Squire.git`.
3. Enter the `Squire` directory and run `composer install --optimize-autoloader --no-dev`.
4. Install the required Javascript dependencies using `npm install`.
5. Run `npm run prod` to generate the minified static files.
6. Copy `.env.prod` to `.env` and set up the DB connection and Reddit API.
7. Run `php artisan key:generate` to generate an app key for secure session storage.
8. Run `php artisan config:cache` to combine all config files into a single one. Rerun this whenever you make changes to `.env`!
9. Point your webserver to `squire/public`.
