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
const routes: Array<RouteRecordRaw> = [
    {
        path: "/",
        redirect: "/dashboard",
        component: () => import("@/layouts/main-layout/MainLayout.vue"),
        meta: {
            middleware: "auth",
        },
        children: systemRoutes[0].children,
    },
    {
        path: "/",
        component: () => import("@/layouts/AuthLayout.vue"),
        children: [
            {
                path: "/sign-in",
                name: "sign-in",
                component: () =>
                    import("@/views/auth/SignIn.vue"),
                meta: {
                    pageTitle: "general.fields.signIn",
                },
            },
            {
                path: "/sign-up",
                name: "sign-up",
                component: () =>
                    import("@/views/auth/SignUp.vue"),
                meta: {
                    pageTitle: "Sign Up",
                },
            },
            {
                path: "/password-reset",
                name: "password-reset",
                component: () =>
                    import("@/views/auth/PasswordReset.vue"),
                meta: {
                    pageTitle: "Password Reset",
                },
            },
            {
                path: "/reset-password",
                name: "reset-password",
                component: () =>
                    import("@/views/auth/ResetPassword.vue"),
                meta: {
                    pageTitle: "Reset Password",
                },
            },
            {
                path: "/404",
                name: "404",
                component: () =>
                    import("@/views/auth/Error404.vue"),
                meta: {
                    pageTitle: "Error 404",
                },
            },
            {
                path: "/500",
                name: "500",
                component: () =>
                    import("@/views/auth/Error500.vue"),
                meta: {
                    pageTitle: "Error 500",
                },
            },
        ],
    },
    {
        path: "/:pathMatch(.*)*",
        redirect: "/404",
    },
];

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

    // verify auth token before each page change
    authStore.verifyAuth();

    // before page access check if page requires authentication
    if (to.meta.middleware == "auth") {
        if (authStore.isAuthenticated) {
            // current page view title
            if (to.meta.pageTitle) {
                setDocumentTitle(i18n.global.t(to.meta.pageTitle));
            }
            next();
        } else {
            // current page view title
            if (to.meta.pageTitle) {
                setDocumentTitle(i18n.global.t(to.meta.pageTitle));
            }
            next({ name: "sign-in" });
        }
    } else {
        // For public routes (auth pages), don't verify auth
        // current page view title
        if (to.meta.pageTitle) {
            setDocumentTitle(i18n.global.t(to.meta.pageTitle));
        }
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
