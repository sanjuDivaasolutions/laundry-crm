<template>
    <div class="d-flex flex-column flex-root">
        <div class="d-flex flex-column flex-center flex-column-fluid p-10">
            <!-- Logo -->
            <a href="/" class="mb-12">
                <img
                    alt="Logo"
                    src="/images/logo-dark.png"
                    class="h-45px"
                />
            </a>

            <!-- Card -->
            <div class="w-lg-500px bg-body rounded shadow-sm p-10 p-lg-15 mx-auto">
                <!-- Processing -->
                <div v-if="loading" class="text-center">
                    <div class="spinner-border text-primary mb-5" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <h2 class="fw-bold mb-3">Verifying your email...</h2>
                    <p class="text-muted">Please wait while we verify your email address.</p>
                </div>

                <!-- Success -->
                <div v-else-if="success" class="text-center">
                    <div class="mb-10">
                        <i class="ki-duotone ki-check-circle text-success fs-5x">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>
                    </div>
                    <h2 class="fw-bold text-dark mb-3">Email Verified!</h2>
                    <p class="text-muted mb-10">
                        Your email has been verified successfully. You can now access your workspace.
                    </p>
                    <a :href="redirectUrl" class="btn btn-primary">
                        Go to Dashboard
                    </a>
                </div>

                <!-- Error -->
                <div v-else class="text-center">
                    <div class="mb-10">
                        <i class="ki-duotone ki-cross-circle text-danger fs-5x">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>
                    </div>
                    <h2 class="fw-bold text-dark mb-3">Verification Failed</h2>
                    <p class="text-muted mb-5">{{ errorMessage }}</p>

                    <!-- Resend Form -->
                    <div v-if="showResendForm" class="mt-10">
                        <p class="text-muted mb-5">Enter your email to resend the verification link:</p>
                        <form @submit.prevent="resendVerification">
                            <div class="mb-5">
                                <input
                                    type="email"
                                    class="form-control form-control-lg"
                                    placeholder="Email address"
                                    v-model="email"
                                    required
                                />
                            </div>
                            <button
                                type="submit"
                                class="btn btn-primary w-100"
                                :disabled="resending"
                            >
                                <span v-if="resending" class="spinner-border spinner-border-sm me-2"></span>
                                Resend Verification Email
                            </button>
                        </form>
                        <div v-if="resendSuccess" class="alert alert-success mt-5">
                            Verification email sent! Check your inbox.
                        </div>
                    </div>

                    <div class="mt-10">
                        <router-link to="/sign-in" class="btn btn-light">
                            Back to Sign In
                        </router-link>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import { useRoute } from "vue-router";
import axios from "axios";

const route = useRoute();

const loading = ref(true);
const success = ref(false);
const errorMessage = ref("");
const redirectUrl = ref("");
const showResendForm = ref(false);
const email = ref("");
const resending = ref(false);
const resendSuccess = ref(false);

const verifyEmail = async () => {
    const { id, hash } = route.query;

    if (!id || !hash) {
        loading.value = false;
        errorMessage.value = "Invalid verification link.";
        showResendForm.value = true;
        return;
    }

    try {
        const response = await axios.get(`/api/v1/register/verify-email/${id}/${hash}`);

        if (response.data.success) {
            success.value = true;
            redirectUrl.value = response.data.redirect_url || "/sign-in";
        } else {
            errorMessage.value = response.data.message || "Verification failed.";
            showResendForm.value = true;
        }
    } catch (error) {
        errorMessage.value = error.response?.data?.message || "Verification failed. The link may have expired.";
        showResendForm.value = true;
    } finally {
        loading.value = false;
    }
};

const resendVerification = async () => {
    resending.value = true;
    resendSuccess.value = false;

    try {
        await axios.post("/api/v1/register/resend-verification", {
            email: email.value
        });
        resendSuccess.value = true;
    } catch (error) {
        errorMessage.value = error.response?.data?.message || "Failed to resend. Please try again.";
    } finally {
        resending.value = false;
    }
};

onMounted(() => {
    verifyEmail();
});
</script>
