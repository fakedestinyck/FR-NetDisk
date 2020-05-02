import Vue from 'vue'
import VueRouter from 'vue-router'
import Welcome from '../views/Welcome.vue'
import Shared from '../components/Shared.vue'
import MyDisk from '../components/MyDisk.vue'

Vue.use(VueRouter)

const routes = [
  {
    path: '/',
    name: 'Welcome',
    component: Welcome
  },
  {
      path: '/home',
      name: 'Home',
      component: () => import('../views/Home.vue'),
      children: [
          {
              path: 'shared',
              name: 'Shared',
              component: Shared
          },
          {
              path: 'mydisk',
              name: 'Mydisk',
              component: MyDisk
          }
      ],
      redirect: '/home/shared'
  },
  {
      path: '/shared/:share_event_id/:t/:token',
      name: 'GetPrivateShare',
      component: () => import('../views/GetShare.vue'),
  },
  {
    path: '/about',
    name: 'About',
    component: () => import(/* webpackChunkName: "about" */ '../views/About.vue')
  },
  {
      path: '*',
      redirect: '/home'
  }
]

const router = new VueRouter({
  mode: 'history',
  base: process.env.BASE_URL,
  routes
})

export default router
