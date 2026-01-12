import Module from './Module'

export default {
  path: '/quotations',
  name: 'quotations',
  component: Module,
  meta: {
    requiresAuth: true,
    permission: 'view-quotations'
  },
  children: [
    {
      path: '',
      name: 'quotations.index',
      component: () => import('./Index.vue'),
      meta: {
        requiresAuth: true,
        permission: 'view-quotations'
      }
    },
    {
      path: 'create',
      name: 'quotations.create',
      component: () => import('./Create.vue'),
      meta: {
        requiresAuth: true,
        permission: 'create-quotations'
      }
    },
    {
      path: ':id',
      name: 'quotations.show',
      component: () => import('./Show.vue'),
      meta: {
        requiresAuth: true,
        permission: 'view-quotations'
      }
    },
    {
      path: ':id/edit',
      name: 'quotations.edit',
      component: () => import('./Edit.vue'),
      meta: {
        requiresAuth: true,
        permission: 'edit-quotations'
      }
    }
  ]
}