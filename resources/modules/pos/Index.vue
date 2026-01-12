<!--
  - /*
  -  *  Copyright (c) 2025 Divaa Solutions. All rights reserved.
  -  *
  -  *  This software is the confidential and proprietary information of Divaa Solutions
  -  *  ("Confidential Information"). You shall not disclose such Confidential Information and
  -  *  shall use it only in accordance with the terms of the license agreement you entered into
  -  *  with Divaa Solutions.
  -  *
  -  *  Unauthorized copying of this file, via any medium is strictly prohibited.
  -  *  Proprietary and confidential.
  -  *
  -  *  Last modified: 30/11/25, 12:30 pm
  -  *  Written by Chintan Bagdawala, 2025.
  -  */
  -->

<template>
    <div class="pos-wrapper">
        <!-- Option to switch to fullscreen -->
        <div class="pos-header-bar">
            <div class="header-title">
                <h3 class="mb-0">
                    <i class="fas fa-cash-register text-primary me-2"></i>
                    Point of Sale
                </h3>
                <small class="text-muted">Manage sales transactions quickly and efficiently</small>
            </div>
            <div class="header-actions">
                <router-link to="/pos-fullscreen" class="btn btn-primary btn-fullscreen">
                    <i class="fas fa-expand me-2"></i>
                    <span class="btn-text">Fullscreen Mode</span>
                    <span class="badge bg-white text-primary ms-2">F11</span>
                </router-link>
            </div>
        </div>

        <!-- Regular POS with layout -->
        <POS />
    </div>
</template>

<script setup>
import { onMounted, onUnmounted } from 'vue';
import { useRouter } from 'vue-router';
import POS from './POS.vue';

const router = useRouter();

// F11 shortcut to toggle fullscreen
const handleKeydown = (e) => {
    if (e.key === 'F11') {
        e.preventDefault();
        router.push('/pos-fullscreen');
    }
};

onMounted(() => {
    window.addEventListener('keydown', handleKeydown);
});

onUnmounted(() => {
    window.removeEventListener('keydown', handleKeydown);
});
</script>

<style lang="scss" scoped>
.pos-wrapper {
    height: calc(100vh - 180px);
    overflow: hidden;
    display: flex;
    flex-direction: column;
}

.pos-header-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem 0;
    margin-bottom: 0.75rem;
    border-bottom: 1px solid #e4e6ef;

    .header-title {
        h3 {
            font-weight: 600;
            color: #333;
        }
    }

    .header-actions {
        .btn-fullscreen {
            display: flex;
            align-items: center;
            padding: 0.5rem 1rem;
            border-radius: 6px;
            font-weight: 500;
            transition: all 0.2s ease;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);

            &:hover {
                transform: none;
                box-shadow: 0 2px 6px rgba(0, 0, 0, 0.15);
            }

            .badge {
                font-size: 0.7rem;
                padding: 0.25rem 0.4rem;
            }
        }
    }
}

@media (max-width: 768px) {
    .pos-header-bar {
        flex-direction: column;
        gap: 1rem;
        text-align: center;

        .btn-fullscreen {
            .btn-text {
                display: none;
            }

            .badge {
                display: none;
            }
        }
    }
}
</style>