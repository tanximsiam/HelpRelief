import { createRouter, createWebHistory } from 'vue-router'
import { useAuth } from '@/stores/auth'
import HomeView from '../views/HomeView.vue'
import LoginView from '../views/LoginView.vue'

import GeneralUserDashboard from '../views/GeneralUserDashboard.vue'
import NgoStaffDashboard from '../views/NgoStaffDashboard.vue'
import OathHandler from '../views/OathHandler.vue'
import DonationReportsView from '@/views/DonationReportsView.vue'
import MyRequestsView from '@/views/MyRequestsView.vue'
import StateDetails from '@/views/StateDetails.vue'

// Extend RouteMeta to include our custom properties
declare module 'vue-router' {
  interface RouteMeta {
    requiresAuth?: boolean
    guestOnly?: boolean
    requiresRole?: 'general' | 'ngo_staff' | 'admin'
  }
}




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
      redirect: () => {
        // This redirect will be overridden by the router guard for role-based routing
        // But it serves as a fallback and ensures the route exists
        return { name: 'dashboard-general' }
      }
    },
    {
      path: '/dashboard/general',
      name: 'dashboard-general',
      meta: { requiresAuth: true, requiresRole: 'general' },
      component: GeneralUserDashboard,
    },
    {
      path: '/dashboard/ngo',
      name: 'dashboard-ngo',
      meta: { requiresAuth: true, requiresRole: 'ngo_staff' },
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
    {
      path: '/profile',
      name: 'Profile',
      component: () => import('../views/ProfileView.vue'),
      meta: { requiresAuth: true }
    },


  ],
})



router.beforeEach(async (to, from, next) => {
  const auth = useAuth()

  const requiresAuth = to.meta.requiresAuth
  const guestOnly = to.meta.guestOnly
  const requiresRole = to.meta.requiresRole

  // Ensure user loaded if we have a token but no user yet (for hard refresh)
  if (auth.token && !auth.user) {
    try {
      await auth.fetchUser()
    } catch (e) {
      console.error('Failed to fetch user in router guard:', e)
      // If fetch fails, clear invalid token and redirect to login
      auth.logout()
      return next({ name: 'login' })
    }
  }

  if (guestOnly && auth.isAuthenticated) {
    return next({ name: 'home' })
  }

  if (requiresAuth && !auth.isAuthenticated) {
    return next({ name: 'login', query: { redirect: to.fullPath } })
  }

  // Role-based access control
  if (requiresRole && auth.isAuthenticated) {
    const userRole = auth.user?.role

    // Check if user has the required role
    if (requiresRole === 'general' && userRole !== 'general') {
      // NGO staff or admin trying to access general dashboard - redirect to their dashboard
      if (userRole === 'ngo_staff') {
        return next({ name: 'dashboard-ngo' })
      }
      // For other roles, redirect to home or appropriate page
      return next({ name: 'home' })
    }

    if (requiresRole === 'ngo_staff' && userRole !== 'ngo_staff') {
      // General user trying to access NGO dashboard - redirect to general dashboard
      if (userRole === 'general') {
        return next({ name: 'dashboard-general' })
      }
      // For other roles, redirect to home or appropriate page
      return next({ name: 'home' })
    }
  }

  // Role-based dashboard routing for /dashboard
  if (to.name === 'dashboard' || to.name === 'dashboard-role') {
    if (auth.isNGO) return next({ name: 'dashboard-ngo' })
    return next({ name: 'dashboard-general' })
  }

  return next()
})


export default router
