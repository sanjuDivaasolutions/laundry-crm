<template>
    <div class="dt-action-container">
        <permission-select
            v-if="$can(xprops.permission_prefix + 'edit')"
            :parent="row.id"
            :title="row.title"
        ></permission-select>
        <router-link
            v-if="$can(xprops.permission_prefix + 'show') && 1 == 2"
            :to="{ name: xprops.route + '.show', params: { id: row.id } }"
            class="btn btn-just-icon btn-round btn-link text-azure small"
        >
            <i class="material-icons">remove_red_eye</i>
        </router-link>

        <router-link
            class="btn btn-just-icon btn-round btn-link text-success small"
            v-if="$can(xprops.permission_prefix + 'edit')"
            :to="{ name: xprops.route + '.edit', params: { id: row.id } }"
        >
            <i class="material-icons">edit</i>
        </router-link>

        <a
            href="#"
            class="btn btn-just-icon btn-round btn-link text-rose small"
            v-if="$can(xprops.permission_prefix + 'delete')"
            @click.prevent="destroyData(row.id)"
            type="button"
        >
            <i class="material-icons">delete</i>
        </a>
    </div>
</template>

<script>
import PermissionSelect from "../../cruds/Roles/PermissionSelect";

export default {
    components: {
        PermissionSelect
    },
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
            this.$swal({
                title: "Are you sure?",
                text: "You won't be able to revert this!",
                type: "warning",
                showCancelButton: true,
                confirmButtonText: "Delete",
                confirmButtonColor: "#dd4b39",
                focusCancel: true,
                reverseButtons: true
            }).then(result => {
                if (result.value) {
                    this.$store
                        .dispatch(this.xprops.module + "/destroyData", id)
                        .then(result => {
                            this.$eventHub.$emit("delete-success");
                        })
                        .catch(error => {
                            let message =
                                error.response.data.message || error.message;
                            let errors = error.response.data.errors;

                            this.$store.dispatch(
                                "Alert/setAlert",
                                {
                                    message: message,
                                    errors: errors,
                                    color: "danger"
                                },
                                { root: true }
                            );
                        });
                }
            });
        }
    }
};
</script>
<style scoped>
.btn {
    padding: 0;
}
</style>
