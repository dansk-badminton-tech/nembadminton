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

mix.js('resources/js/app.js', 'public/js').vue({ version: 2 });
mix.sass('resources/sass/app.scss', 'public/css');
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

//mix.alias({
//              '@': path.join(__dirname, 'resources/admin-v2')
//          });

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
