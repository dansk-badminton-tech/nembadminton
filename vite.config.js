import {defineConfig} from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import path from "path";
import vitePluginGraphqlLoader from "vite-plugin-graphql-loader";
import {sentryVitePlugin} from "@sentry/vite-plugin";


export default defineConfig(
    {
        server: {
            watch: {
                ignored: ['**/storage/**/*'],
            },
        },
        css: {
            devSourcemap: true,
            preprocessorOptions: {
                scss: {
                    silenceDeprecations: ["color-functions", "global-builtin", "import"]
                }
            }
        },
        build: {
            sourcemap: "hidden",
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
            vitePluginGraphqlLoader(),
            sentryVitePlugin({
                    org: process.env.SENTRY_ORG,
                    project: process.env.SENTRY_PROJECT,
                    authToken: process.env.SENTRY_AUTH_TOKEN,
                    release: {
                        name: process.env.VITE_SENTRY_RELEASE,
                    },
                    sourcemaps: {
                        assets: ["public/build/assets/**"],
                        filesToDeleteAfterUpload: ["public/build/assets/**/*.map"],
                    },
                    telemetry: false,
                }),
        ],
        resolve: {
            alias: {
                '@': path.join(__dirname, 'resources/js/admin-v2')
            }
        }
    });
