<template>
    <div>
        <div v-for="item in row[field]" :key="item.id" class="mb-1">
            <a
                :href="item.url"
                :title="item.name"
                target="_blank"
                style="color: var(--dark-dark-text)"
                class="is-weight-700"
            >
                {{ item.file_name }}
            </a>
        </div>
    </div>
</template>

<script>
export default {
    props: ["field", "row"],
};
</script>

<style scoped>
a {
    font-size: 13px;
    font-weight: 500;
    color: #202124;
}
a:hover {
    color: #9c27b0;
    text-decoration: underline;
}
</style>
