const mix = require('laravel-mix');
const path = require("path");

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

mix.js('resources/js/app.js', 'public/js/v1').vue({ version: 2 });
mix.sass('resources/sass/app.scss', 'public/css/v1');
mix.js('resources/js/admin-v2/main.js', 'public/js/v2').vue({ version: 2 });
mix.js('resources/js/marketing/main.js', 'public/js/marketing').vue({ version: 2 });
//mix.sass('resources/sass/app.scss', 'public/css');
mix.sourceMaps(false, 'source-map')
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

mix.alias({
              '@': path.join(__dirname, 'resources/js/admin-v2')
          });

if (mix.inProduction()) {
//    mix.webpackConfig(
//        {
//            output: {
//                chunkFilename: "js/chunks/[name].[chunkhash].js"
//            }
//        }
//    )
    mix.version();
}
