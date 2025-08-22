import { createRouter, createWebHistory } from 'vue-router'
import { useAuth } from '@/stores/auth'
import HomeView from '../views/HomeView.vue'
import LoginView from '../views/LoginView.vue'

import UserDashboard from '../views/UserDashboard.vue'
import OathHandler from '../views/OathHandler.vue'
import DonationReportsView from '@/views/DonationReportsView.vue'
import MyRequestsView from '@/views/MyRequestsView.vue'
import StateDetails from '@/views/StateDetails.vue'



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
    {
      path: '/dashboard',
      name: 'dashboard',
      component: UserDashboard,
      meta: { requiresAuth: true },
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
        {
      path: '/dashboard',
      name: 'dashboard',
      component: UserDashboard,
        },
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
