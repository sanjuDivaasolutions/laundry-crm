<!--
  - /*
  -  *  Copyright (c) 2024 Divaa Solutions. All rights reserved.
  -  *
  -  *  This software is the confidential and proprietary information of Divaa Solutions
  -  *  ("Confidential Information"). You shall not disclose such Confidential Information and
  -  *  shall use it only in accordance with the terms of the license agreement you entered into
  -  *  with Divaa Solutions.
  -  *
  -  *  Unauthorized copying of this file, via any medium is strictly prohibited.
  -  *  Proprietary and confidential.
  -  *
  -  *  Last modified: 12/12/24, 9:24 am
  -  *  Written by Chintan Bagdawala, 2024.
  -  */
  -->

<template>
    <div class="card">
        <div
            v-if="!hideHeader"
            class="card-header"
            :class="{
                'card-header-stretch': headerStretch,
                'no-padding': headerPadding === 'no_padding',
            }"
        >
            <slot name="header"></slot>
        </div>
        <div
            class="card-body"
            :class="bodyPadding === 'no_padding' ? 'p-0' : ''"
        >
            <slot></slot>
            <slot name="body"></slot>
        </div>
        <div
            v-if="!hideFooter"
            class="card-footer"
            :class="{ 'no-padding': footerPadding === 'no_padding' }"
        >
            <slot name="footer"></slot>
        </div>
    </div>
</template>

<script>
export default {
    name: "CardContainer",
    props: {
        headerStretch: {
            type: Boolean,
            default: false,
        },
        bodyPadding: {
            type: String,
            default: "normal",
            validator: (value) => {
                const match = ["normal", "no_padding"];
                if (match.indexOf(value) === -1) {
                    console.warn(
                        `Body Padding: invalid "${value}" not allowed. Allowed values are: ${match.join(
                            ", "
                        )}`
                    );
                }

                return true;
            },
        },
        headerPadding: {
            type: String,
            default: "normal",
            validator: (value) => {
                const match = ["normal", "no_padding"];
                if (match.indexOf(value) === -1) {
                    console.warn(
                        `Header Padding: invalid "${value}" not allowed. Allowed values are: ${match.join(
                            ", "
                        )}`
                    );
                }

                return true;
            },
        },
        footerPadding: {
            type: String,
            default: "normal",
            validator: (value) => {
                const match = ["normal", "no_padding"];
                if (match.indexOf(value) === -1) {
                    console.warn(
                        `Footer Padding: invalid "${value}" not allowed. Allowed values are: ${match.join(
                            ", "
                        )}`
                    );
                }

                return true;
            },
        },
        hideHeader: {
            type: Boolean,
            default: false,
        },
        hideFooter: {
            type: Boolean,
            default: false,
        },
    },
};
</script>

<style scoped>
.card-header.no-padding {
    min-height: 60px;
}
.card-footer.no-padding {
    padding: 1rem 1.25rem;
}
</style>
