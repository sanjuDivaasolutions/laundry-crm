<template>
  <!--begin::Wrapper-->
  <div class="w-lg-600px p-10">
    <!--begin::Form-->
    <VForm
      class="form w-100"
      novalidate
      @submit="onSubmitRegister"
      id="kt_tenant_signup_form"
      :validation-schema="registrationSchema"
    >
      <!--begin::Heading-->
      <div class="mb-10 text-center">
        <h1 class="text-dark mb-3">Start Your Free Trial</h1>
        <div class="text-gray-500 fw-semibold fs-5">
          14 days free, no credit card required
        </div>
        <div class="text-gray-400 fw-semibold fs-6 mt-3">
          Already have an account?
          <router-link to="/sign-in" class="link-primary fw-bold">
            Sign in here
          </router-link>
        </div>
      </div>
      <!--end::Heading-->

      <!--begin::Alert for errors-->
      <div v-if="errorMessage" class="alert alert-danger d-flex align-items-center mb-10">
        <i class="ki-duotone ki-shield-cross fs-2hx text-danger me-4">
          <span class="path1"></span>
          <span class="path2"></span>
        </i>
        <div class="d-flex flex-column">
          <span>{{ errorMessage }}</span>
        </div>
      </div>
      <!--end::Alert-->

      <!--begin::Step indicator-->
      <div class="d-flex justify-content-between mb-10">
        <div
          v-for="(step, index) in steps"
          :key="index"
          class="d-flex align-items-center"
          :class="{'opacity-50': currentStep < index + 1}"
        >
          <div
            class="rounded-circle d-flex justify-content-center align-items-center"
            :class="currentStep >= index + 1 ? 'bg-primary text-white' : 'bg-light-primary text-primary'"
            style="width: 40px; height: 40px;"
          >
            <span class="fw-bold">{{ index + 1 }}</span>
          </div>
          <span class="ms-2 fw-semibold text-gray-700 d-none d-md-inline">{{ step }}</span>
          <span v-if="index < steps.length - 1" class="mx-4 border-bottom border-gray-300 w-20px d-none d-md-block"></span>
        </div>
      </div>
      <!--end::Step indicator-->

      <!--begin::Step 1: Company Info-->
      <div v-show="currentStep === 1">
        <!--begin::Input group-->
        <div class="fv-row mb-7">
          <label class="form-label fw-bold text-dark fs-6 required">Company Name</label>
          <Field
            class="form-control form-control-lg form-control-solid"
            type="text"
            placeholder="e.g., Acme Laundry Services"
            name="company_name"
            v-model="formData.company_name"
            @input="onCompanyNameChange"
          />
          <div class="fv-plugins-message-container">
            <div class="fv-help-block">
              <ErrorMessage name="company_name" />
            </div>
          </div>
        </div>
        <!--end::Input group-->

        <!--begin::Input group-->
        <div class="fv-row mb-7">
          <label class="form-label fw-bold text-dark fs-6 required">
            Your Website Address
            <span class="text-muted fw-normal ms-1">(subdomain)</span>
          </label>
          <div class="input-group input-group-solid">
            <Field
              class="form-control form-control-lg form-control-solid"
              type="text"
              placeholder="your-company"
              name="subdomain"
              v-model="formData.subdomain"
              @input="checkSubdomainDebounced"
            />
            <span class="input-group-text">.{{ baseDomain }}</span>
          </div>
          <div class="fv-plugins-message-container">
            <div class="fv-help-block">
              <ErrorMessage name="subdomain" />
            </div>
          </div>
          <div v-if="subdomainStatus" class="mt-2">
            <span
              class="badge"
              :class="subdomainStatus.available ? 'badge-light-success' : 'badge-light-danger'"
            >
              <i
                class="ki-duotone fs-6 me-1"
                :class="subdomainStatus.available ? 'ki-check-circle' : 'ki-cross-circle'"
              ></i>
              {{ subdomainStatus.message }}
            </span>
            <button
              v-if="!subdomainStatus.available && subdomainStatus.suggestion"
              type="button"
              class="btn btn-sm btn-link text-primary p-0 ms-2"
              @click="useSuggestion"
            >
              Use "{{ subdomainStatus.suggestion }}" instead?
            </button>
          </div>
        </div>
        <!--end::Input group-->

        <!--begin::Row-->
        <div class="row mb-7">
          <div class="col-md-6">
            <label class="form-label fw-bold text-dark fs-6 required">Timezone</label>
            <Field
              as="select"
              class="form-select form-select-lg form-select-solid"
              name="timezone"
              v-model="formData.timezone"
            >
              <option value="">Select timezone</option>
              <option
                v-for="(label, value) in timezones"
                :key="value"
                :value="value"
              >
                {{ label }}
              </option>
            </Field>
            <div class="fv-plugins-message-container">
              <div class="fv-help-block">
                <ErrorMessage name="timezone" />
              </div>
            </div>
          </div>
          <div class="col-md-6">
            <label class="form-label fw-bold text-dark fs-6 required">Currency</label>
            <Field
              as="select"
              class="form-select form-select-lg form-select-solid"
              name="currency"
              v-model="formData.currency"
            >
              <option value="">Select currency</option>
              <option
                v-for="(label, value) in currencies"
                :key="value"
                :value="value"
              >
                {{ label }}
              </option>
            </Field>
            <div class="fv-plugins-message-container">
              <div class="fv-help-block">
                <ErrorMessage name="currency" />
              </div>
            </div>
          </div>
        </div>
        <!--end::Row-->

        <div class="d-flex justify-content-end">
          <button
            type="button"
            class="btn btn-lg btn-primary"
            @click="nextStep"
            :disabled="!isStep1Valid"
          >
            Continue
            <i class="ki-duotone ki-arrow-right fs-4 ms-1">
              <span class="path1"></span>
              <span class="path2"></span>
            </i>
          </button>
        </div>
      </div>
      <!--end::Step 1-->

      <!--begin::Step 2: Account Info-->
      <div v-show="currentStep === 2">
        <!--begin::Input group-->
        <div class="fv-row mb-7">
          <label class="form-label fw-bold text-dark fs-6">Your Name</label>
          <Field
            class="form-control form-control-lg form-control-solid"
            type="text"
            placeholder="John Doe"
            name="name"
            v-model="formData.name"
          />
          <div class="fv-plugins-message-container">
            <div class="fv-help-block">
              <ErrorMessage name="name" />
            </div>
          </div>
        </div>
        <!--end::Input group-->

        <!--begin::Input group-->
        <div class="fv-row mb-7">
          <label class="form-label fw-bold text-dark fs-6 required">Email Address</label>
          <Field
            class="form-control form-control-lg form-control-solid"
            type="email"
            placeholder="you@company.com"
            name="email"
            v-model="formData.email"
          />
          <div class="fv-plugins-message-container">
            <div class="fv-help-block">
              <ErrorMessage name="email" />
            </div>
          </div>
        </div>
        <!--end::Input group-->

        <!--begin::Input group-->
        <div class="fv-row mb-7">
          <label class="form-label fw-bold text-dark fs-6">Phone Number</label>
          <Field
            class="form-control form-control-lg form-control-solid"
            type="tel"
            placeholder="+1 234 567 8900"
            name="phone"
            v-model="formData.phone"
          />
          <div class="fv-plugins-message-container">
            <div class="fv-help-block">
              <ErrorMessage name="phone" />
            </div>
          </div>
        </div>
        <!--end::Input group-->

        <!--begin::Input group-->
        <div class="mb-7 fv-row">
          <label class="form-label fw-bold text-dark fs-6 required">Password</label>
          <div class="position-relative mb-3">
            <Field
              class="form-control form-control-lg form-control-solid"
              :type="showPassword ? 'text' : 'password'"
              placeholder="Min 8 characters"
              name="password"
              v-model="formData.password"
            />
            <span
              class="btn btn-sm btn-icon position-absolute translate-middle top-50 end-0 me-n2"
              @click="showPassword = !showPassword"
            >
              <i class="ki-duotone fs-2" :class="showPassword ? 'ki-eye-slash' : 'ki-eye'">
                <span class="path1"></span>
                <span class="path2"></span>
                <span class="path3"></span>
              </i>
            </span>
          </div>
          <div class="fv-plugins-message-container">
            <div class="fv-help-block">
              <ErrorMessage name="password" />
            </div>
          </div>
          <div class="text-muted fs-7">
            Use 8 or more characters with a mix of letters and numbers.
          </div>
        </div>
        <!--end::Input group-->

        <!--begin::Input group-->
        <div class="fv-row mb-7">
          <label class="form-label fw-bold text-dark fs-6 required">Confirm Password</label>
          <Field
            class="form-control form-control-lg form-control-solid"
            :type="showPassword ? 'text' : 'password'"
            placeholder="Re-enter password"
            name="password_confirmation"
            v-model="formData.password_confirmation"
          />
          <div class="fv-plugins-message-container">
            <div class="fv-help-block">
              <ErrorMessage name="password_confirmation" />
            </div>
          </div>
        </div>
        <!--end::Input group-->

        <!--begin::Input group-->
        <div class="fv-row mb-10">
          <label class="form-check form-check-custom form-check-solid">
            <Field
              class="form-check-input"
              type="checkbox"
              name="terms"
              :value="true"
              v-model="formData.terms"
            />
            <span class="form-check-label fw-semibold text-gray-700 fs-6">
              I agree to the
              <a href="/terms" target="_blank" class="ms-1 link-primary">Terms of Service</a>
              and
              <a href="/privacy" target="_blank" class="link-primary">Privacy Policy</a>
            </span>
          </label>
          <div class="fv-plugins-message-container">
            <div class="fv-help-block">
              <ErrorMessage name="terms" />
            </div>
          </div>
        </div>
        <!--end::Input group-->

        <div class="d-flex justify-content-between">
          <button
            type="button"
            class="btn btn-lg btn-light"
            @click="prevStep"
          >
            <i class="ki-duotone ki-arrow-left fs-4 me-1">
              <span class="path1"></span>
              <span class="path2"></span>
            </i>
            Back
          </button>
          <button
            ref="submitButton"
            type="submit"
            class="btn btn-lg btn-primary"
            :disabled="isSubmitting || !subdomainStatus?.available"
          >
            <span v-if="!isSubmitting" class="indicator-label">
              Start Free Trial
              <i class="ki-duotone ki-rocket fs-4 ms-1">
                <span class="path1"></span>
                <span class="path2"></span>
              </i>
            </span>
            <span v-else class="indicator-progress d-block">
              Creating your account...
              <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
            </span>
          </button>
        </div>
      </div>
      <!--end::Step 2-->
    </VForm>
    <!--end::Form-->
  </div>
  <!--end::Wrapper-->
</template>

<script lang="ts">
import { defineComponent, ref, reactive, onMounted, computed } from "vue";
import { ErrorMessage, Field, Form as VForm } from "vee-validate";
import * as Yup from "yup";
import { useRouter } from "vue-router";
import Swal from "sweetalert2";
import ApiService from "@/core/services/ApiService";

interface SubdomainStatus {
  available: boolean;
  message: string;
  suggestion?: string;
}

export default defineComponent({
  name: "tenant-sign-up",
  components: {
    Field,
    VForm,
    ErrorMessage,
  },
  setup() {
    const router = useRouter();
    const submitButton = ref<HTMLButtonElement | null>(null);
    const currentStep = ref(1);
    const steps = ["Company", "Account"];
    const isSubmitting = ref(false);
    const errorMessage = ref("");
    const showPassword = ref(false);
    const baseDomain = ref("laundry-crm.com");

    // Form data
    const formData = reactive({
      company_name: "",
      subdomain: "",
      timezone: "",
      currency: "USD",
      name: "",
      email: "",
      phone: "",
      password: "",
      password_confirmation: "",
      terms: false,
    });

    // Subdomain validation
    const subdomainStatus = ref<SubdomainStatus | null>(null);
    const checkSubdomainTimeout = ref<number | null>(null);

    // Options
    const timezones = ref<Record<string, string>>({});
    const currencies = ref<Record<string, string>>({});

    // Validation schema
    const registrationSchema = Yup.object().shape({
      company_name: Yup.string().required("Company name is required").min(2).max(100),
      subdomain: Yup.string()
        .required("Subdomain is required")
        .min(3, "Subdomain must be at least 3 characters")
        .max(63)
        .matches(/^[a-z0-9][a-z0-9-]*[a-z0-9]$/, "Invalid subdomain format"),
      timezone: Yup.string().required("Timezone is required"),
      currency: Yup.string().required("Currency is required").length(3),
      name: Yup.string().max(100),
      email: Yup.string().required("Email is required").email("Invalid email format"),
      phone: Yup.string().max(20),
      password: Yup.string()
        .required("Password is required")
        .min(8, "Password must be at least 8 characters")
        .matches(/[a-zA-Z]/, "Password must contain at least one letter")
        .matches(/[0-9]/, "Password must contain at least one number"),
      password_confirmation: Yup.string()
        .required("Please confirm your password")
        .oneOf([Yup.ref("password")], "Passwords must match"),
      terms: Yup.boolean().oneOf([true], "You must accept the terms"),
    });

    // Computed
    const isStep1Valid = computed(() => {
      return (
        formData.company_name.length >= 2 &&
        formData.subdomain.length >= 3 &&
        formData.timezone &&
        formData.currency &&
        subdomainStatus.value?.available
      );
    });

    // Methods
    const loadOptions = async () => {
      try {
        const [tzResponse, currResponse] = await Promise.all([
          ApiService.get("register/timezones"),
          ApiService.get("register/currencies"),
        ]);
        timezones.value = tzResponse.data.timezones || {};
        currencies.value = currResponse.data.currencies || {};

        // Auto-detect timezone
        const userTimezone = Intl.DateTimeFormat().resolvedOptions().timeZone;
        if (userTimezone && timezones.value[userTimezone]) {
          formData.timezone = userTimezone;
        }
      } catch (error) {
        console.error("Failed to load options:", error);
      }
    };

    const onCompanyNameChange = () => {
      if (!formData.subdomain || formData.subdomain === suggestSubdomain(formData.company_name.slice(0, -1))) {
        formData.subdomain = suggestSubdomain(formData.company_name);
        checkSubdomainDebounced();
      }
    };

    const suggestSubdomain = (name: string): string => {
      return name
        .toLowerCase()
        .replace(/[^a-z0-9]+/g, "-")
        .replace(/^-|-$/g, "")
        .replace(/-+/g, "-")
        .substring(0, 63);
    };

    const checkSubdomainDebounced = () => {
      if (checkSubdomainTimeout.value) {
        clearTimeout(checkSubdomainTimeout.value);
      }

      if (formData.subdomain.length < 3) {
        subdomainStatus.value = null;
        return;
      }

      checkSubdomainTimeout.value = window.setTimeout(async () => {
        try {
          const response = await ApiService.get("register/check-subdomain", {
            params: { subdomain: formData.subdomain },
          });
          subdomainStatus.value = response.data;
        } catch (error) {
          subdomainStatus.value = {
            available: false,
            message: "Unable to check availability",
          };
        }
      }, 500);
    };

    const useSuggestion = () => {
      if (subdomainStatus.value?.suggestion) {
        formData.subdomain = subdomainStatus.value.suggestion;
        checkSubdomainDebounced();
      }
    };

    const nextStep = () => {
      if (currentStep.value < steps.length) {
        currentStep.value++;
      }
    };

    const prevStep = () => {
      if (currentStep.value > 1) {
        currentStep.value--;
      }
    };

    const onSubmitRegister = async () => {
      if (!subdomainStatus.value?.available) {
        errorMessage.value = "Please choose an available subdomain";
        return;
      }

      isSubmitting.value = true;
      errorMessage.value = "";

      try {
        const response = await ApiService.post("register", formData);

        if (response.data.success) {
          await Swal.fire({
            title: "Account Created!",
            html: `
              <p>Your 14-day free trial has started.</p>
              <p class="text-muted">Please check your email to verify your account.</p>
              <p class="mt-3">
                <strong>Your URL:</strong><br>
                <a href="${response.data.data.tenant.url}" target="_blank">${response.data.data.tenant.url}</a>
              </p>
            `,
            icon: "success",
            confirmButtonText: "Go to Login",
            customClass: {
              confirmButton: "btn btn-primary",
            },
            buttonsStyling: false,
          });

          // Redirect to their tenant login
          window.location.href = response.data.data.tenant.url;
        }
      } catch (error: any) {
        const message = error.response?.data?.message || "Registration failed. Please try again.";
        errorMessage.value = message;

        Swal.fire({
          text: message,
          icon: "error",
          confirmButtonText: "Try again",
          customClass: {
            confirmButton: "btn btn-light-danger",
          },
          buttonsStyling: false,
        });
      } finally {
        isSubmitting.value = false;
      }
    };

    onMounted(() => {
      loadOptions();
    });

    return {
      formData,
      registrationSchema,
      currentStep,
      steps,
      isSubmitting,
      errorMessage,
      showPassword,
      baseDomain,
      subdomainStatus,
      timezones,
      currencies,
      isStep1Valid,
      submitButton,
      onCompanyNameChange,
      checkSubdomainDebounced,
      useSuggestion,
      nextStep,
      prevStep,
      onSubmitRegister,
    };
  },
});
</script>

<style scoped>
.w-lg-600px {
  width: 100%;
}

@media (min-width: 992px) {
  .w-lg-600px {
    width: 600px;
  }
}

.badge {
  font-size: 0.85rem;
  padding: 0.5rem 0.75rem;
}
</style>
