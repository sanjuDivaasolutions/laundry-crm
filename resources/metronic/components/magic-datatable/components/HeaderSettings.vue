<template>
    <div id="header-settings" name="HeaderSettings">
        <VModal
            :id="modal.id"
            :open="modal.show"
            title="Select Columns"
            size="medium"
            actions="center"
            cancel-label="Cancel"
            @close="handleCloseModal"
        >
            <template #content>
                <div class="columns is-multiline">
                    <div
                        class="column is-4"
                        :key="c.title"
                        v-for="c in modal.columns"
                    >
                        <FormSwitchBlock
                            :label="$t(c.title)"
                            :value="c.visible"
                            @input="c.visible = $event"
                        />
                    </div>
                </div>
            </template>
            <template #action>
                <a
                    href="#"
                    @click.prevent="handleSaveColumns"
                    class="button v-button is-primary mr-3"
                    >Save</a
                >
            </template>
        </VModal>
    </div>
</template>

<script>
import { mapActions, mapGetters } from "vuex";
import { random } from "lodash/number";
import VModal from "../Vuero/Components/VModal.vue";
import { EventBus } from "../../EventBus";

export default {
    name: "HeaderSettings",
    components: { VModal },
    data() {
        return {
            modal: {
                id: "modal-columns",
                show: false,
                columns: [],
            },
        };
    },
    props: {
        columns: {
            default: [],
        },
        module: {
            default: null,
        },
    },
    computed: {
        columnSettingKey() {
            return this.module + "Columns";
        },
    },
    methods: {
        initColumnModal() {
            const columns = _.cloneDeep(this.columns);
            if (columns.length) {
                columns.forEach((c) => {
                    c.visible = this.isColVisible(c);
                });
            }
            this.modal.columns = columns;
            this.modal.show = true;
        },
        isColVisible(col) {
            return (
                typeof col.visible === "undefined" ||
                "" + col.visible === "true"
            );
        },
        handleChange(col, saveSettings) {
            saveSettings =
                typeof saveSettings !== "undefined" ? saveSettings : true;
            this.$set(col, "visible", !this.isColVisible(col));
            if (this.module !== null && saveSettings) {
                this.saveSettings();
            }
        },
        handleSaveColumns() {
            this.$emit("updated", this.modal.columns);
            this.modal.show = false;
            this.saveSettings(this.modal.columns);
        },
        handleCloseModal() {
            this.modal.show = false;
        },
        saveSettings(columns) {
            let visibleColumns = [];
            columns.forEach(function (c) {
                if (c.visible !== false) {
                    visibleColumns.push(c.title);
                }
            });
            this.setUserSetting({
                key: this.columnSettingKey,
                value: visibleColumns,
            });
        },
        loadSettings() {
            const self = this;
            const userColumns = this.userSetting()(this.columnSettingKey);
            if (userColumns !== null) {
                this.columns.forEach(function (c) {
                    console.log(c);
                    if (userColumns.indexOf(c.title) === -1) {
                        self.handleChange(c, false);
                    }
                });
            }
        },
        ...mapGetters("UserStore", ["userSetting"]),
        ...mapActions("UserStore", ["setUserSetting"]),
    },
    mounted() {
        this.loadSettings();
        /*if (this.setting !== null) {
            this.loadSettings();
        }*/
    },
    created() {
        this.modal.id = "modal-columns-" + random(111111, 999999);
        EventBus.$on("initColumnChooser", this.initColumnModal);
    },
};
</script>

<style>
#header-settings .form-check,
label {
    color: #222;
}
#header-settings .dropdown-menu .dropdown-item:hover,
#header-settings .dropdown-menu .dropdown-item:focus {
    box-shadow: none;
    background-color: #f4f4f4;
    color: #000;
    cursor: pointer !important;
}
#header-settings .dropdown-menu .dropdown-item,
.dropdown-menu li > a {
    padding-top: 0 !important;
    padding-bottom: 5px !important;
}
</style>
