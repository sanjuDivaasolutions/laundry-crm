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
 *  *  Last modified: 17/10/24, 9:33 am
 *  *  Written by Chintan Bagdawala, 2024.
 *
 */

import "./bootstrap";
import { createApp } from "vue";
import { createPinia } from "pinia";
import { abilitiesPlugin } from "@casl/vue";
import { Tooltip } from "bootstrap";
import App from "@/App.vue";

import "@vueform/multiselect/themes/default.css";

import ElementPlus from "element-plus";
import router from "@/router";
import i18n from "@/core/plugins/i18n";
import mitt from "@/core/plugins/mitt";
import ToastPlugin from "vue-toast-notification"; //imports for app initialization
import ApiService from "@/core/services/ApiService";
import { useI18nStore } from "@/stores/i18n";
import { useAbilityStore } from "@/stores/ability";
import { initApexCharts } from "@/core/plugins/apexcharts";
import { initInlineSvg } from "@/core/plugins/inline-svg";
import { initVeeValidate } from "@/core/plugins/vee-validate";
import { useOptionStore } from "@common@/components/optionStore";
import registerFormGlobalComponents from "@common@/components/globalComponents";

import "@/core/plugins/prismjs";
import { useAuthStore } from "@/stores/auth";
import { $getUrl } from "@/core/helpers/utility";

const app = createApp(App);

app.use(createPinia());

ApiService.init(app);

const i18nStore = useI18nStore();
const abilityStore = useAbilityStore();

const optionStore = useOptionStore();
const auth = useAuthStore();
window.axios.interceptors.response.use(
    function (response) {
        return response;
    },
    function (error) {
        if (error.response.status === 401 || error.response.status === 419) {
            auth.logout();
            router.push({ name: "sign-in" });
        }

        return Promise.reject(error);
    }
);

i18nStore.fetchLocaleMessages().then(async () => {
    const messages = i18nStore.messages;
    const locale = i18nStore.currentLocale;
    //todo when change language just use the code below
    i18n.global.setLocaleMessage(locale, messages[locale]);
    i18n.global.locale.value = locale;
    app.use(abilitiesPlugin, abilityStore.abilities, {
        useGlobalProperties: true,
    });

    app.use(i18n);

    app.use(router);
    app.use(ElementPlus);
    app.use(ToastPlugin);
    const $trans = (key) => {
        return typeof messages[locale][key] !== undefined
            ? i18n.global.t(key)
            : key;
    };

    app.config.globalProperties.$emitter = mitt;
    app.config.globalProperties.$t = $trans;
    app.config.globalProperties.$te = i18n.global.te;
    app.config.globalProperties.$i18nForDatatable = (v) => {
        return i18n.global.t(v);
    };
    app.config.globalProperties.$url = $getUrl;
    app.config.globalProperties.appVersion = import.meta.env.PACKAGE_VERSION;

    registerFormGlobalComponents(app);

    initApexCharts(app);
    initInlineSvg(app);
    initVeeValidate();

    await optionStore.setupKeys();

    app.directive("tooltip", (el) => {
        new Tooltip(el);
    });
    app.mount("#app");
});
