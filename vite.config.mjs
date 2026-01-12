/*
 *
 *  *  Copyright (c) 2024 Divaa Solutions. All rights reserved.
 *  *
 *  *  This software is the confidential and proprietary information of Divaa Solutions
 *  *  ("Confidential Information"). You shall not disclose such Confidential Information and
 *  *  shall use it only in accordance with the terms of the license agreement you entered into
 *  *  with Divaa Solutions.
 *  *
 *  *  Unauthorized copying of this file, via any medium is strictly prohibited.
 *  *  Proprietary and confidential.
 *  *
 *  *  Last modified: 17/10/24, 9:17 am
 *  *  Written by Chintan Bagdawala, 2024.
 *
 */

import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";
import vue from "@vitejs/plugin-vue";
import packageJson from "./package.json";

export default defineConfig({
    define: {
        "import.meta.env.PACKAGE_VERSION": JSON.stringify(packageJson.version),
    },
    server: {
        hmr: {
            overlay: false,
        },
    },
    plugins: [
        laravel({
            input: ["resources/sass/app.scss", "resources/js/app.js"],
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
    ],
    resolve: {
        alias: {
            "@": "/resources/metronic",
            "@modules@": "/resources/modules",
            "@common@": "/resources/modules/common",
            "@utility@": "/resources/utility",
            "~": "/node_modules",
            vue: "vue/dist/vue.esm-bundler.js",
        },
    },
    optimizeDeps: {
        include: ["@popperjs/core"],
    },
});
