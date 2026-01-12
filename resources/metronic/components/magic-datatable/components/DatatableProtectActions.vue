<template>
    <VDropdown
        icon="feather:more-vertical"
        class="is-pushed-mobile"
        spaced
        right
    >
        <template #content="{ close }">
            <router-link
                v-if="$can(xprops.permission_prefix + 'show') && 1 === 2"
                class="dropdown-item is-media"
                :to="{ name: xprops.route + '.show', params: { id: row.id } }"
                title="Show Details"
            >
                <div class="icon">
                    <i aria-hidden="true" class="lnil lnil-eye"></i>
                </div>
                <div class="meta">
                    <span>View</span>
                    <span>View details</span>
                </div>
            </router-link>
            <router-link
                v-if="$can(xprops.permission_prefix + 'edit')"
                class="dropdown-item is-media"
                :to="{ name: xprops.route + '.edit', params: { id: row.id } }"
            >
                <div class="icon">
                    <VIconify icon="feather:edit" />
                </div>
                <div class="meta">
                    <span>Edit</span>
                    <span>Modify this item</span>
                </div>
            </router-link>
            <hr class="dropdown-divider" />
            <a
                v-if="$can(xprops.permission_prefix + 'delete')"
                class="dropdown-item is-media"
                @click.prevent="destroyData(row.id)"
            >
                <div class="icon">
                    <VIconify icon="feather:trash-2" />
                </div>
                <div class="meta">
                    <span>Remove</span>
                    <span>Remove this item</span>
                </div>
            </a>
        </template>
    </VDropdown>
</template>

<script>
export default {
    props: ["row", "xprops"],
    data() {
        return {
            // Code...
        };
    },
    created() {
        // Code...
    },
    methods: {
        destroyData(id) {
            this.$store
                .dispatch(this.xprops.module + "/destroyData", id)
                .then((result) => {
                    this.$eventHub.$emit("delete-success");
                });
        },
    },
};
</script>
<style scoped>
.btn {
    padding: 0 0.2rem;
}
.btn:last-child {
    padding-right: 0;
}
</style>
