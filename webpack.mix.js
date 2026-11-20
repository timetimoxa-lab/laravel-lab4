const mix = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Laravel Mix (Webpack)
 |--------------------------------------------------------------------------
 | Исходники:  resources/js, resources/scss
 | Результат:  public/js/app.js, public/css/app.css, public/webfonts
 | Команды:    npm run dev | npm run watch | npm run prod
 */
mix.options({ processCssUrls: false })        // пути к шрифтам FontAwesome оставляем как есть
   .js('resources/js/app.js', 'public/js')
   .sass('resources/scss/app.scss', 'public/css', {
       sassOptions: { quietDeps: true },
   })
   .copy('node_modules/@fortawesome/fontawesome-free/webfonts', 'public/webfonts')
   .disableNotifications();
