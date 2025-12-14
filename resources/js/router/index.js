import { createRouter, createWebHistory } from 'vue-router'

// Import your pages
import AskKing from '../views/AskKing.vue'
import Triggers from '../views/Triggers.vue'
import Resources from '../views/Resources.vue'
import AIProviders from '../views/AIProviders.vue'
import Leaderboard from '../views/Leaderboard.vue'
import Team from '../views/Team.vue'
import Usage from '../views/Analytics.vue'
import Audit from '../views/Billing.vue'
import Settings from '../views/Settings.vue'
import QuickTriggers from '../components/QuickTriggers.vue'

const routes = [
  { path: '/', redirect: '/dashboard' },

  { path: '/dashboard', name: 'dashboard', component: AskKing },
  { path: '/triggers', name: 'triggers', component: QuickTriggers },
  { path: '/resources', name: 'resources', component: Resources },
  { path: '/provider', name: 'provider', component: AIProviders },
  { path: '/leaderboard', name: 'leaderboard', component: Leaderboard },
  { path: '/team', name: 'team', component: Team },
  { path: '/usage', name: 'usage', component: Usage },
  { path: '/audit', name: 'audit', component: Audit },
  { path: '/settings', name: 'settings', component: Settings },
  { path: '/:pathMatch(.*)*', redirect: '/dashboard' }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

export default router
