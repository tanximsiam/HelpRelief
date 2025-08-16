import { createRouter, createWebHistory } from 'vue-router'
import { useAuth } from '../stores/auth.ts'
import HomeView from '../views/HomeView.vue'
import LoginView from '../views/LoginView.vue'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      name: 'home',
      component: HomeView,
      // meta: { requiresAuth: true },
    },
    {
      path: '/about',
      name: 'about',
      // route level code-splitting
      // this generates a separate chunk (About.[hash].js) for this route
      // which is lazy-loaded when the route is visited.
      component: () => import('../views/AboutView.vue'),
    },
    {
      path: '/login',
      name: 'login',
      component: LoginView,
      // meta: { guestOnly: true },
    },

  ],
})



router.beforeEach(async (to, from, next) => {
  const auth = useAuth()

  // const requiresAuth = to.meta.requiresAuth
  const guestOnly = to.meta.guestOnly

  // if (requiresAuth && !auth.isAuthenticated) {
  //   return next({ name: 'login', query: { redirect: to.fullPath } })
  // }

  if (guestOnly && auth.isAuthenticated) {
    return next({ name: 'home' }) // or role-based redirect
  }

  return next()
})


export default router
