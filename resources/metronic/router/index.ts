import {
    createRouter,
    createWebHashHistory,
    type RouteRecordRaw,
} from "vue-router";
// @ts-ignore
import { useAuthStore } from "@/stores/auth";
// @ts-ignore
import { useConfigStore } from "@/stores/config";
// @ts-ignore
import { usePageStore } from "@/stores/page";
// @ts-ignore
import { systemRoutes } from "@common@/data/routes";
// @ts-ignore
import i18n from "@/core/plugins/i18n";
// @ts-ignore
import { useAbilityStore } from "@/stores/ability";
const routes: Array<RouteRecordRaw> = systemRoutes;

const router = createRouter({
    history: createWebHashHistory(),
    routes,
});

const setDocumentTitle = (title: string) => {
    // @ts-ignore
    document.title = `${title} - ${import.meta.env.VITE_APP_NAME}`;
};

router.beforeEach((to, from, next) => {
    const authStore = useAuthStore();
    const configStore = useConfigStore();
    const pageStore = usePageStore();
    //const abilityStore = useAbilityStore();

    pageStore.resetConfigs();

    // reset config to initial state
    configStore.resetLayoutConfig();

    // before page access check if page requires authentication
    if (to.meta.middleware == "auth") {
        // verify auth token before each page change for protected routes only
        const verification = authStore.verifyAuth();
        verification.catch(() => {
            next({ name: "sign-in" });
        });

        if (authStore.isAuthenticated) {
            if (authStore.user.redirect && to.name != authStore.user.redirect) {
                console.log("redirect to", authStore.user.redirect);
                const name = authStore.user.redirect;
                next({ name: name });
            } else {
                // current page view title
                setDocumentTitle(i18n.global.t(to.meta.pageTitle));
                next();
            }
        } else {
            // current page view title
            setDocumentTitle(i18n.global.t(to.meta.pageTitle));
            next({ name: "sign-in" });
        }
    } else {
        // For public routes (auth pages), don't verify auth
        // current page view title
        setDocumentTitle(i18n.global.t(to.meta.pageTitle));
        next();
    }

    // Scroll page to top on every route change
    window.scrollTo({
        top: 0,
        left: 0,
        behavior: "smooth",
    });
});

export default router;
