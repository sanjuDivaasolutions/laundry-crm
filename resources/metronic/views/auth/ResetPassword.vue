<template>
    <!--begin::Wrapper-->
    <div class="w-lg-500px p-10">
        <!--begin::Form-->
        <VForm
            class="form w-100 fv-plugins-bootstrap5 fv-plugins-framework"
            @submit="onSubmitReset"
            id="kt_new_password_form"
            :validation-schema="resetPassword"
        >
            <!--begin::Heading-->
            <div class="text-center mb-10">
                <!--begin::Title-->
                <h1 class="text-dark mb-3">Setup New Password</h1>
                <!--end::Title-->

                <!--begin::Link-->
                <div class="text-gray-400 fw-semobold fs-4">
                    Have you already reset the password?
                    <router-link to="/sign-in" class="link-primary fw-bold">
                        Sign in
                    </router-link>
                </div>
                <!--end::Link-->
            </div>
            <!--begin::Heading-->

            <!--begin::Input group-->
            <div class="fv-row mb-8">
                <label class="form-label fw-bold text-gray-900 fs-6">Email</label>
                <Field
                    class="form-control form-control-solid"
                    type="email"
                    placeholder=""
                    name="email"
                    autocomplete="off"
                    v-model="formData.email"
                    readonly
                />
                <div class="fv-plugins-message-container">
                    <div class="fv-help-block">
                        <ErrorMessage name="email" />
                    </div>
                </div>
            </div>
            <!--end::Input group-->

            <!--begin::Input group-->
            <div class="fv-row mb-8" data-kt-password-meter="true">
                <div class="mb-1">
                    <label class="form-label fw-bold text-gray-900 fs-6">
                        Password
                    </label>
                    <div class="position-relative mb-3">
                        <Field
                            class="form-control form-control-solid"
                            type="password"
                            placeholder=""
                            name="password"
                            autocomplete="off"
                        />
                        <span
                            class="btn btn-sm btn-icon position-absolute translate-middle top-50 end-0 me-n2"
                            data-kt-password-meter-control="visibility"
                        >
                            <i class="bi bi-eye-slash fs-2"></i>
                            <i class="bi bi-eye fs-2 d-none"></i>
                        </span>
                    </div>
                    <div class="fv-plugins-message-container">
                        <div class="fv-help-block">
                            <ErrorMessage name="password" />
                        </div>
                    </div>
                </div>
            </div>
            <!--end::Input group--->

            <!--begin::Input group-->
            <div class="fv-row mb-8">
                <label class="form-label fw-bold text-gray-900 fs-6">
                    Confirm Password
                </label>
                <Field
                    class="form-control form-control-solid"
                    type="password"
                    placeholder=""
                    name="password_confirmation"
                    autocomplete="off"
                />
                <div class="fv-plugins-message-container">
                    <div class="fv-help-block">
                        <ErrorMessage name="password_confirmation" />
                    </div>
                </div>
            </div>
            <!--end::Input group-->

            <!--begin::Actions-->
            <div class="d-grid mb-10">
                <button
                    type="submit"
                    ref="submitButton"
                    id="kt_new_password_submit"
                    class="btn btn-primary"
                >
                    <span class="indicator-label"> Submit </span>
                    <span class="indicator-progress">
                        Please wait...
                        <span
                            class="spinner-border spinner-border-sm align-middle ms-2"
                        ></span>
                    </span>
                </button>
            </div>
            <!--end::Actions-->
        </VForm>
        <!--end::Form-->
    </div>
    <!--end::Wrapper-->
</template>

<script lang="ts">
import { defineComponent, ref, onMounted } from "vue";
import { ErrorMessage, Field, Form as VForm } from "vee-validate";
import { useAuthStore } from "@/stores/auth";
import { useRoute, useRouter } from "vue-router";
import * as Yup from "yup";
import Swal from "sweetalert2";

export default defineComponent({
    name: "reset-password",
    components: {
        Field,
        VForm,
        ErrorMessage,
    },
    setup() {
        const store = useAuthStore();
        const route = useRoute();
        const router = useRouter();

        const submitButton = ref<HTMLButtonElement | null>(null);
        const formData = ref({
            email: "",
            token: "",
        });

        onMounted(() => {
            // Get email and token from URL parameters
            formData.value.email = route.query.email as string || "";
            formData.value.token = route.query.token as string || "";
            
            if (!formData.value.token || !formData.value.email) {
                Swal.fire({
                    text: "Invalid reset link. Please request a new password reset.",
                    icon: "error",
                    buttonsStyling: false,
                    confirmButtonText: "Ok, got it!",
                    heightAuto: false,
                    customClass: {
                        confirmButton: "btn fw-semobold btn-light-danger",
                    },
                }).then(() => {
                    router.push("/password-reset");
                });
            }
        });

        //Create form validation object
        const resetPassword = Yup.object().shape({
            email: Yup.string().email().required().label("Email"),
            password: Yup.string()
                .min(8)
                .required()
                .matches(
                    /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/,
                    "Password must contain at least one uppercase letter, one lowercase letter, and one number"
                )
                .label("Password"),
            password_confirmation: Yup.string()
                .required()
                .oneOf([Yup.ref("password")], "Passwords must match")
                .label("Password Confirmation"),
        });

        //Form submit function
        const onSubmitReset = async (values: any) => {
            // eslint-disable-next-line
            submitButton.value!.disabled = true;
            // Activate loading indicator
            submitButton.value?.setAttribute("data-kt-indicator", "on");

            // Add token to values
            const resetData = {
                ...values,
                token: formData.value.token,
            };

            // Send reset request
            await store.resetPassword(resetData);

            const error = Object.values(store.errors);

            if (!error || error.length === 0) {
                Swal.fire({
                    text: "Your password has been reset successfully!",
                    icon: "success",
                    buttonsStyling: false,
                    confirmButtonText: "Ok, got it!",
                    heightAuto: false,
                    customClass: {
                        confirmButton: "btn fw-semobold btn-light-primary",
                    },
                }).then(() => {
                    router.push("/sign-in");
                });
            } else {
                Swal.fire({
                    text: error[0] as string,
                    icon: "error",
                    buttonsStyling: false,
                    confirmButtonText: "Try again!",
                    heightAuto: false,
                    customClass: {
                        confirmButton: "btn fw-semobold btn-light-danger",
                    },
                });
            }

            submitButton.value?.removeAttribute("data-kt-indicator");
            // eslint-disable-next-line
            submitButton.value!.disabled = false;
        };

        return {
            onSubmitReset,
            resetPassword,
            submitButton,
            formData,
        };
    },
});
</script>