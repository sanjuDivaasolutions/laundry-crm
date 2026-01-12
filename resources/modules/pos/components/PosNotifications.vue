<template>
    <div class="pos-notifications">
        <!-- Success Toast -->
        <Transition name="toast">
            <div v-if="posStore.success" class="toast-notification success">
                <i class="fas fa-check-circle me-2"></i>
                {{ posStore.success }}
                <button @click="posStore.clearMessages" class="btn-close ms-auto"></button>
            </div>
        </Transition>
        
        <!-- Error Toast -->
        <Transition name="toast">
            <div v-if="posStore.error" class="toast-notification error">
                <i class="fas fa-exclamation-circle me-2"></i>
                {{ posStore.error }}
                <button @click="posStore.clearMessages" class="btn-close ms-auto"></button>
            </div>
        </Transition>
    </div>
</template>

<script setup>
import { watch } from 'vue';
import { usePosStore } from '../posStore';

const posStore = usePosStore();

// Auto-clear messages after 5 seconds
watch(() => posStore.success, (newVal) => {
    if (newVal) {
        setTimeout(() => {
            posStore.success = null;
        }, 5000);
    }
});

watch(() => posStore.error, (newVal) => {
    if (newVal) {
        setTimeout(() => {
            posStore.error = null;
        }, 5000);
    }
});
</script>

<style lang="scss" scoped>
.pos-notifications {
    position: fixed;
    top: 80px;
    right: 20px;
    z-index: 9999;
    
    .toast-notification {
        min-width: 300px;
        padding: 1rem;
        margin-bottom: 0.5rem;
        border-radius: 0.5rem;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        display: flex;
        align-items: center;
        color: white;
        font-weight: 500;
        
        &.success {
            background: linear-gradient(135deg, #28a745, #20c997);
        }
        
        &.error {
            background: linear-gradient(135deg, #dc3545, #f012be);
        }
        
        .btn-close {
            filter: brightness(0) invert(1);
            opacity: 0.8;
        }
    }
}

// Transitions
.toast-enter-active,
.toast-leave-active {
    transition: all 0.3s ease;
}

.toast-enter-from {
    transform: translateX(100%);
    opacity: 0;
}

.toast-leave-to {
    transform: translateX(100%);
    opacity: 0;
}
</style>