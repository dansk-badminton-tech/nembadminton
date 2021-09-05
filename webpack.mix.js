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

mix.js('resources/js/app.js', 'public/js')
   .sass('resources/sass/app.scss', 'public/css')
   .sourceMaps(false, 'cheap-module-eval-source-map')
   .webpackConfig({
                      module: {
                          rules: [
                              {
                                  test: /\.(gql|graphql)$/,
                                  loader: 'graphql-tag/loader',
                                  exclude: '/node_modules/'
                              }
                          ]
                      }
                  });

if (mix.inProduction()) {
    mix.webpackConfig(
        {
            output: {
                chunkFilename: "js/chunks/[name].[chunkhash].js"
            }
        }
    )
    //mix.version();
}
