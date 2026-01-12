<template>
    <div>
        <div v-if="showControl" class="d-flex align-items-center">
            <span class="small-font me-2"
                >{{ sourceValue }} {{ sourceCode }} = {{ targetValue }}
                {{ targetCode }}</span
            ><a
                href="#"
                @click.prevent="initModal"
                title="Edit Currency Rate"
                class="text-danger"
                ><FormIcon icon="feather:edit-2"
            /></a>
        </div>
        <ModalContainer :id="modalId" :title="modalTitle">
            <template #body>
                <form @submit.prevent="handleConfirm">
                    <div class="row">
                        <div class="col-sm mb-4">
                            <FormInput
                                type="number"
                                step="0.0001"
                                name="exchange-rate"
                                label="Exchange Rate"
                                v-model="rate"
                                required
                            />
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm mb-4">
                            <label
                                class="form-check form-switch form-check-custom form-check-solid"
                            >
                                <input
                                    class="form-check-input"
                                    name="auto-update-currency-rate"
                                    type="checkbox"
                                    value="1"
                                    id="auto-update-currency-rate"
                                    v-model="autoUpdateRate"
                                />
                                <span
                                    class="form-check-label fw-semobold"
                                    for="auto-update-currency-rate"
                                >
                                    Auto-update Rate
                                </span>
                            </label>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm">
                            <button type="submit" class="btn btn-primary">
                                Save
                            </button>
                        </div>
                    </div>
                </form>
            </template>
        </ModalContainer>
    </div>
</template>

<script>
import ModalContainer from "@common@/modals/ModalContainer.vue";
import emitter from "@/core/plugins/mitt";
import FormInput from "@common@/components/form/FormInput.vue";

export default {
    name: "FormCurrencyControl",
    components: { FormInput, ModalContainer },
    props: {
        sourceValue: {
            type: Number,
            required: true,
        },
        sourceCode: {
            type: String,
            required: true,
        },
        targetValue: {
            type: Number,
            required: true,
        },
        targetCode: {
            type: String,
            required: true,
        },
    },
    data() {
        return {
            modalId: "modal-currency-control" + new Date().getTime(),
            rate: null,
            autoUpdateRate: true,
        };
    },
    computed: {
        modalTitle() {
            return "Update Currency: " + this.targetCode;
        },
        showControl() {
            if (!this.sourceCode || !this.targetCode) {
                return false;
            }
            return this.sourceCode.toString() !== this.targetCode.toString();
        },
    },
    methods: {
        initModal() {
            this.rate = this.targetValue;
            emitter.emit("show-modal-container", { id: this.modalId });
        },
        handleUpdateRate() {
            const obj = {
                code: this.targetCode,
                rate: this.rate,
            };
            axios.post("currencies-update-rate", obj);
        },
        handleConfirm() {
            if (this.autoUpdateRate) {
                this.handleUpdateRate();
            }
            this.$emit("saved", this.rate);
            emitter.emit("hide-modal-container", { id: this.modalId });
        },
    },
};
</script>

<style scoped>
.currency-container {
    font-size: 12px;
    float: right;
}
.small-font {
    font-size: 12px;
    margin-left: 5px;
}
</style>
