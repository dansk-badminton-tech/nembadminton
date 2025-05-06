import {defineConfig} from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue2';
import path from "path";
import vitePluginGraphqlLoader from "vite-plugin-graphql-loader";


export default defineConfig(
    {
        server: {
            watch: {
                ignored: ['**/storage/**/*'],
            },
        },
        plugins: [
            laravel([
                        'resources/js/admin-v2/main.js'
                    ]),
            vue({
                    template: {
                        transformAssetUrls: {
                            base: null,
                            includeAbsolute: false,
                        },
                    },
                }),
            vitePluginGraphqlLoader()
        ],
        resolve: {
            alias: {
                '@': path.join(__dirname, 'resources/js/admin-v2')
            }
        }
    });
