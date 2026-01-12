<template>
    <button
        class="btn d-flex align-items-center justify-content-center"
        :class="buttonClass"
        type="button"
        :disabled="disabled || loading"
    >
        <FormIcon v-if="icon" :icon="icon"></FormIcon>
        <span v-if="loading">Please wait...</span>
        <slot v-if="!loading"></slot>
    </button>
</template>

<script>
export default {
    name: "FormButton",
    props: {
        to: {
            type: [Object, String],
            default: undefined,
        },
        href: {
            type: String,
            default: undefined,
        },
        icon: {
            type: String,
            default: undefined,
        },
        iconCaret: {
            type: String,
            default: undefined,
        },
        placeload: {
            type: String,
            default: undefined,
            validator: (value) => {
                if (
                    value.match(
                        "/(\\d*\\.?\\d+)\\s?(cm|mm|in|px|pt|pc|em|ex|ch|rem|vw|vh|vmin|vmax|%)/"
                    ) === null
                ) {
                    console.warn(
                        `VButton: invalid "${value}" placeload. Should be a valid css unit value.`
                    );
                }

                return true;
            },
        },
        color: {
            type: String,
            default: undefined,
            validator: (value) => {
                if (
                    value.match(
                        /(primary|secondary|success|danger|warning|info|light|dark|link)/
                    ) === null
                ) {
                    console.warn(
                        `VButton: invalid "${value}" color. Should be one of the following: primary, secondary, success, danger, warning, info, light, dark, link.`
                    );
                }

                return true;
            },
        },
        size: {
            type: String,
            default: undefined,
            validator: (value) => {
                if (value === undefined) {
                    return true;
                }
                if (value.match(/(sm|lg)/) === null) {
                    console.warn(
                        `VButton: invalid "${value}" size. Should be one of the following: small, medium, large, big, xl.`
                    );
                }

                return true;
            },
        },
        dark: {
            type: String,
            default: undefined,
            validator: (value) => {
                if (value === undefined) {
                    return true;
                }
                if (value.match(/(1|2|3|4|5|6)/) === null) {
                    console.warn(
                        `VButton: invalid "${value}" dark. Should be one of the following: 1, 2, 3, 4, 5, 6.`
                    );
                }

                return true;
            },
        },
        active: {
            type: Boolean,
            default: false,
        },
        rounded: {
            type: Boolean,
            default: false,
        },
        bold: {
            type: Boolean,
            default: false,
        },
        fullwidth: {
            type: Boolean,
            default: false,
        },
        light: {
            type: Boolean,
            default: false,
        },
        raised: {
            type: Boolean,
            default: false,
        },
        elevated: {
            type: Boolean,
            default: false,
        },
        outlined: {
            type: Boolean,
            default: false,
        },
        darkOutlined: {
            type: Boolean,
            default: false,
        },
        loading: {
            type: Boolean,
            default: false,
        },
        lower: {
            type: Boolean,
            default: false,
        },
        disabled: {
            type: Boolean,
            default: false,
        },
    },
    computed: {
        buttonClass() {
            return {
                "btn-sm": this.size === "sm",
                "btn-lg": this.size === "lg",
                "btn-primary": this.color === "primary",
                "btn-secondary": this.color === "secondary",
                "btn-success": this.color === "success",
                "btn-danger": this.color === "danger",
                "btn-warning": this.color === "warning",
                "btn-info": this.color === "info",
                "btn-light": this.color === "light",
                "btn-dark": this.color === "dark",
                "btn-link": this.color === "link",
            };
        },
    },
};
</script>

<style scoped></style>
