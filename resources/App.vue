<template>
  <div id="app">
    <router-view />
  </div>
</template>

<script setup>
import { onMounted } from 'vue'

onMounted(() => {
  // Initialize any global setup here
})
</script>

<style>
/* Import Metronic theme styles or any global styles */
@import '@metronic/styles/main.css';

#app {
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
}

.page-header {
  background: #fff;
  border-bottom: 1px solid #e6e6e6;
  padding: 1rem 0;
}

.page-title {
  font-size: 1.5rem;
  font-weight: 600;
  margin: 0;
  color: #1a1a1a;
}

.container-xl {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 1rem;
}

.row {
  display: flex;
  flex-wrap: wrap;
  margin-right: -0.75rem;
  margin-left: -0.75rem;
}

.col, .col-sm-6, .col-lg-3 {
  flex: 1 0 0%;
  padding-right: 0.75rem;
  padding-left: 0.75rem;
}

@media (min-width: 576px) {
  .col-sm-6 {
    flex: 0 0 50%;
    max-width: 50%;
  }
}

@media (min-width: 992px) {
  .col-lg-3 {
    flex: 0 0 25%;
    max-width: 25%;
  }
}

.card {
  position: relative;
  display: flex;
  flex-direction: column;
  min-width: 0;
  word-wrap: break-word;
  background-color: #fff;
  background-clip: border-box;
  border: 1px solid rgba(0, 0, 0, 0.125);
  border-radius: 0.375rem;
  margin-bottom: 1.5rem;
}

.card-body {
  flex: 1 1 auto;
  padding: 1.25rem;
}

.d-flex {
  display: flex;
}

.align-items-center {
  align-items: center;
}

.ms-auto {
  margin-left: auto;
}

.lh-1 {
  line-height: 1;
}

.mb-3 {
  margin-bottom: 1rem;
}

.mb-2 {
  margin-bottom: 0.5rem;
}

.mt-2 {
  margin-top: 0.5rem;
}

.text-muted {
  color: #6c757d;
}

.dropdown-toggle {
  cursor: pointer;
  text-decoration: none;
}

.dropdown-toggle:hover {
  text-decoration: underline;
}

.btn-action {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 0.25rem 0.5rem;
  font-size: 0.875rem;
  border-radius: 0.25rem;
  color: #6c757d;
  background: transparent;
  border: none;
  cursor: pointer;
  text-decoration: none;
}

.btn-action:hover {
  color: #495057;
  background-color: #f8f9fa;
}

.icon {
  width: 1.5rem;
  height: 1.5rem;
}

.dropdown-menu {
  position: absolute;
  top: 100%;
  right: 0;
  z-index: 1000;
  display: none;
  min-width: 10rem;
  padding: 0.5rem 0;
  margin: 0.125rem 0 0;
  font-size: 0.875rem;
  color: #212529;
  text-align: left;
  list-style: none;
  background-color: #fff;
  background-clip: padding-box;
  border: 1px solid rgba(0, 0, 0, 0.15);
  border-radius: 0.375rem;
  box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.175);
}

.dropdown-menu-end {
  right: 0;
  left: auto;
}

.dropdown-item {
  display: block;
  width: 100%;
  padding: 0.25rem 1rem;
  clear: both;
  font-weight: 400;
  color: #212529;
  text-align: inherit;
  text-decoration: none;
  white-space: nowrap;
  background-color: transparent;
  border: 0;
}

.dropdown-item:hover {
  color: #1e2125;
  background-color: #f8f9fa;
}

.d-print-none {
  display: none !important;
}

@media print {
  .d-print-none {
    display: none !important;
  }
}
</style>