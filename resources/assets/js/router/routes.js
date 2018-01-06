export default ({ authGuard, guestGuard }) => [
  

  // Authenticated routes.
  ...authGuard([
    { path: '/home', name: 'home', component: require('~/pages/home.vue') },
    { path: '/search', name: 'search', component: require('~/pages/search.vue') },
    { path: '/recommendation', name: 'recommendation', component: require('~/pages/recommendation.vue') },
    { path: '/append', name: 'append', component: require('~/pages/append.vue') },
    { path: '/cluster', name: 'cluster', component: require('~/pages/cluster.vue') },
    { path: '/content', name: 'content', component: require('~/pages/content.vue') },
    { path: '/settings',
      component: require('~/pages/settings/index.vue'),
      children: [
      { path: '', redirect: { name: 'settings.profile' } },
      { path: 'profile', name: 'settings.profile', component: require('~/pages/settings/profile.vue') },
      { path: 'password', name: 'settings.password', component: require('~/pages/settings/password.vue') }
      ] }
  ]),

  // Guest routes.
  ...guestGuard([
    { path: '/login', name: 'login', component: require('~/pages/auth/login.vue') },
    { path: '/', name: 'welcome', component: require('~/pages/welcome.vue') },
    { path: '/register', name: 'register', component: require('~/pages/auth/register.vue') },
    { path: '/password/reset', name: 'password.request', component: require('~/pages/auth/password/email.vue') },
    { path: '/password/reset/:token', name: 'password.reset', component: require('~/pages/auth/password/reset.vue') }
  ]),

  { path: '*', component: require('~/pages/errors/404.vue') }
]
