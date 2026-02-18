<template>
    <!--begin::Card-->
    <div class="card mb-5 mb-xl-10">
        <!--begin::Card header-->
        <div class="card-header align-items-center border-0 mt-4">
            <h3 class="card-title align-items-start flex-column">
                <span class="fw-bold mb-2 text-dark">{{ $t('order.fields.history_timeline_title') }}</span>
                <span class="text-muted fw-semobold fs-7">{{ $t('order.fields.history_timeline_subtitle') }}</span>
            </h3>
            <div class="card-toolbar">
                <span class="badge badge-light-primary fs-7">
                    <i class="fas fa-clock me-1"></i>
                    {{ history.length }} {{ $t('general.fields.events') }}
                </span>
            </div>
        </div>
        <!--end::Card header-->

        <!--begin::Card body-->
        <div class="card-body pt-5">
            <!--begin::Timeline-->
            <div class="timeline-label">
                <!--begin::Item-->
                <div v-for="(log, index) in history" :key="log.id" class="timeline-item">
                    <!--begin::Label-->
                    <div class="timeline-label fw-bold text-gray-800 fs-6 text-end" style="width: 140px;">
                        <div class="mb-1">{{ formatDate(log.changed_at) }}</div>
                        <div class="text-muted fs-7">
                            <i class="fas fa-clock me-1 fs-8"></i>
                            {{ formatTime(log.changed_at) }}
                        </div>
                        <div class="text-muted fs-8 mt-1 fst-italic">{{ getRelativeTime(log.changed_at) }}</div>
                    </div>
                    <!--end::Label-->

                    <!--begin::Badge-->
                    <div class="timeline-badge">
                        <i :class="getBadgeIconClass(log.status_type)" class="fs-1"></i>
                    </div>
                    <!--end::Badge-->

                    <!--begin::Content-->
                    <div class="timeline-content ps-3">
                        <!--begin::Status type badge-->
                        <div class="mb-2">
                            <span class="badge badge-light fs-8 fw-bold text-gray-600 mb-1">
                                <i :class="getTypeIcon(log.status_type)" class="me-1 text-gray-500"></i>
                                {{ log.status_type_label }}
                            </span>
                        </div>
                        <!--end::Status type badge-->

                        <!--begin::Status transition-->
                        <div class="d-flex align-items-center flex-wrap mb-2">
                            <span v-if="log.old_status_name" class="me-2">
                                <span class="badge badge-light-secondary text-muted text-decoration-line-through">
                                    {{ log.old_status_name }}
                                </span>
                            </span>
                            <i v-if="log.old_status_name" class="fas fa-long-arrow-alt-right text-gray-400 fs-4 me-2"></i>
                            <span>
                                <span :class="getStatusBadgeClass(log.status_type, log.new_status_name)" class="fs-6 fw-bold px-3 py-2">
                                    {{ log.new_status_name || $t('general.fields.updated') }}
                                </span>
                            </span>
                        </div>
                        <!--end::Status transition-->

                        <!--begin::Remarks-->
                        <div v-if="log.remarks" class="bg-light rounded px-4 py-3 mt-3 d-flex align-items-center">
                            <i class="fas fa-comment-alt text-gray-400 fs-5 me-3"></i>
                            <div class="text-gray-600 fs-7 fst-italic">{{ log.remarks }}</div>
                        </div>
                        <!--end::Remarks-->
                    </div>
                    <!--end::Content-->
                </div>
                <!--end::Item-->

                <!--begin::Empty state-->
                <div v-if="history.length === 0" class="text-center py-15">
                    <div class="mb-5">
                        <i class="fas fa-history fs-3x text-gray-300"></i>
                    </div>
                    <h4 class="text-gray-700 fw-semobold mb-2">{{ $t('order.fields.history_empty_title') }}</h4>
                    <p class="text-muted fs-6 mb-0">{{ $t('order.fields.history_empty_description') }}</p>
                </div>
                <!--end::Empty state-->
            </div>
            <!--end::Timeline-->
        </div>
        <!--end::Card body-->
    </div>
    <!--end::Card-->
</template>

<script setup>
const props = defineProps({
    history: {
        type: Array,
        required: true,
    },
});

const formatDate = (dateStr) => {
    if (!dateStr) return '';
    const date = new Date(dateStr);
    return date.toLocaleDateString('en-US', { 
        month: 'short', 
        day: 'numeric',
        year: 'numeric'
    });
};

const formatTime = (dateStr) => {
    if (!dateStr) return '';
    const date = new Date(dateStr);
    return date.toLocaleTimeString('en-US', { 
        hour: '2-digit', 
        minute: '2-digit',
        hour12: true
    });
};

const getRelativeTime = (dateStr) => {
    if (!dateStr) return '';
    const date = new Date(dateStr);
    const now = new Date();
    const diffMs = now - date;
    const diffMins = Math.floor(diffMs / 60000);
    const diffHours = Math.floor(diffMs / 3600000);
    const diffDays = Math.floor(diffMs / 86400000);

    if (diffMins < 1) return 'Just now';
    if (diffMins < 60) return `${diffMins}m ago`;
    if (diffHours < 24) return `${diffHours}h ago`;
    if (diffDays < 7) return `${diffDays}d ago`;
    return '';
};

const getBadgeIconClass = (statusType) => {
    const classes = {
        'processing': 'fa fa-genderless text-primary',
        'order': 'fa fa-genderless text-success',
        'payment': 'fa fa-genderless text-warning',
    };
    return classes[statusType] || 'fa fa-genderless text-gray-400';
};

const getTypeIcon = (statusType) => {
    const icons = {
        'processing': 'fas fa-tasks',
        'order': 'fas fa-file-invoice',
        'payment': 'fas fa-money-bill-wave',
    };
    return icons[statusType] || 'fas fa-info-circle';
};

const getStatusBadgeClass = (statusType, statusName) => {
    if (statusType === 'processing') {
        const colors = {
            'Pending': 'badge badge-light-warning text-warning',
            'Washing': 'badge badge-light-info text-info',
            'Drying': 'badge badge-light-info text-info',
            'Ready Area': 'badge badge-light-success text-success',
            'Delivered': 'badge badge-light-success text-success',
        };
        return colors[statusName] || 'badge badge-light-primary text-primary';
    }
    
    if (statusType === 'order') {
        const colors = {
            'Open': 'badge badge-light-primary text-primary',
            'Closed': 'badge badge-light-dark text-dark',
        };
        return colors[statusName] || 'badge badge-light-secondary text-muted';
    }
    
    if (statusType === 'payment') {
        return 'badge badge-light-success text-success';
    }
    
    return 'badge badge-light-secondary text-muted';
};
</script>
