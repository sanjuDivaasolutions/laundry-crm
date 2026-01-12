import Index from './Index.vue'

export default [
  {
    path: '/dashboard',
    name: 'dashboard',
    component: Index,
    meta: {
      title: 'Dashboard',
      requiresAuth: true,
      breadcrumb: [
        { title: 'Home', to: '/' },
        { title: 'Dashboard', active: true }
      ]
    }
  }
]