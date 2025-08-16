import { createRouter, createWebHistory } from 'vue-router'
import { useAuth } from '@/stores/auth'
import HomeView from '../views/HomeView.vue'
import LoginView from '../views/LoginView.vue'
<<<<<<< HEAD

import UserDashboard from '../views/UserDashboard.vue' // legacy / shared if needed
import GeneralUserDashboard from '../views/GeneralUserDashboard.vue'
import NgoStaffDashboard from '../views/NgoStaffDashboard.vue'
import OathHandler from '../views/OathHandler.vue'
import DonationReportsView from '@/views/DonationReportsView.vue'
import MyRequestsView from '@/views/MyRequestsView.vue'
import StateDetails from '@/views/StateDetails.vue'



=======
<<<<<<< HEAD
import UserDashboard from '../views/UserDashboard.vue'
=======
import OathHandler from '../views/OathHandler.vue'
>>>>>>> 7428c91 (Polished Frontend Redirect)
>>>>>>> f79510d (Polished Frontend Redirect)

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
    // Dynamic redirect entry point
    {
      path: '/dashboard',
      name: 'dashboard',
      meta: { requiresAuth: true },
      component: {
        render() {
          return null
        }
      },
    },
    // Internal role resolution route (kept separate to avoid infinite redirect loops)
    {
      path: '/dashboard/role',
      name: 'dashboard-role',
      meta: { requiresAuth: true },
      component: UserDashboard, // temporary shell; replaced in guard
    },
    {
      path: '/dashboard/general',
      name: 'dashboard-general',
      meta: { requiresAuth: true },
      component: GeneralUserDashboard,
    },
    {
      path: '/dashboard/ngo',
      name: 'dashboard-ngo',
      meta: { requiresAuth: true },
      component: NgoStaffDashboard,
    },

    {
      path: '/oauth/callback',
      name: 'OauthCallback',
      component: OathHandler,

    },

    {
      path: '/aid-support',
      name: 'AidSupport',
      component: () => import('../views/AidSupport.vue'),

    },
      // remove duplicate dashboard route (handled above)
    {
      path: '/oauth/callback',
      name: 'OauthCallback',
      component: OathHandler,
    },
    {
      path: '/tasks',
      name: 'TaskController',
      component: () => import('../views/TaskCont.vue'),
    },
    {
      path: '/tasks/create',
      name: 'TaskCreate',
      component: () => import('../components/TaskController.vue'),
    },
    {
      path: '/tasktest',
      name: 'tasktest',
      component: () => import('../views/TaskLogTestView.vue'),
    },

    {
      path: '/donation-reports',
      name: 'DonationReports',
      component: DonationReportsView,
      meta: { requiresAuth: true }
    },
    {
      path: '/reports/ngos',
      name: 'NgoReports',
      component: () => import('../views/NgoReports.vue'),
      meta: { requiresAuth: true }
    },
    {
      path: '/my-ngo',
      name: 'MyNgo',
      component: () => import('../views/MyNgoView.vue'),
      meta: { requiresAuth: true }
    },
    {
      path: '/my-requests',
      name: 'MyRequests',
      component: MyRequestsView,
      meta: { requiresAuth: true }
    },
    {
      path: '/state/:stateName',
      name: 'StateDetails',
      component: StateDetails,
      props: true,
      meta: { requiresAuth: true }
    },


  ],
})



router.beforeEach(async (to, from, next) => {
  const auth = useAuth()

  const requiresAuth = to.meta.requiresAuth
  const guestOnly = to.meta.guestOnly

  // Ensure user loaded if we have a token but no user yet (for hard refresh)
  if (auth.token && !auth.user) {
    try { await auth.fetchUser() } catch (e) { console.warn('fetchUser failed in router guard:', e) }
  }

  if (guestOnly && auth.isAuthenticated) {
    return next({ name: 'home' })
  }

  if (requiresAuth && !auth.isAuthenticated) {
    return next({ name: 'login', query: { redirect: to.fullPath } })
  }

  // Role-based dashboard routing
  if (to.name === 'dashboard') {
    if (auth.isNGO) {return next({ name: 'dashboard-ngo' })}
    return next({ name: 'dashboard-general' })
  }

  // Block NGO from visiting general dashboard directly
  if (to.name === 'dashboard-general' && auth.isNGO) {
    return next({ name: 'dashboard-ngo' })
  }

  // Block general users from visiting NGO dashboard
  if (to.name === 'dashboard-ngo' && auth.isGeneral) {
    return next({ name: 'dashboard-general' })
  }

  return next()
})


export default router
