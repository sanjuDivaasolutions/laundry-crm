//@ts-ignore
import type LayoutConfigTypes from "@/core/config/LayoutConfigTypes";

const config: LayoutConfigTypes = {
    general: {
        mode: "light",
        primaryColor: "#939598",
        pageWidth: "default",
        layout: "dark-sidebar",
    },
    header: {
        display: true,
        default: {
            container: "fluid",
            fixed: {
                desktop: true,
                mobile: false,
            },
            menu: {
                display: true,
                iconType: "font",
            },
        },
    },
    sidebar: {
        display: true,
        default: {
            minimize: {
                desktop: {
                    enabled: true,
                    default: false,
                    hoverable: true,
                },
            },
            menu: {
                iconType: "font",
            },
        },
    },
    toolbar: {
        display: true,
        container: "fluid",
        fixed: {
            desktop: false,
            mobile: false,
        },
    },
    pageTitle: {
        display: true,
        breadcrumb: true,
        direction: "column",
    },
    content: {
        container: "fluid",
    },
    footer: {
        display: false,
        container: "fluid",
        fixed: {
            desktop: false,
            mobile: false,
        },
    },
    illustrations: {
        set: "sketchy-1",
    },
    scrolltop: {
        display: true,
    },
};

export default config;
