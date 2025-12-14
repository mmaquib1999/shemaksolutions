<template>
  <!-- ORIGINAL SIDEBAR EXACTLY AS IN THE HTML FILE, now with active highlighting -->
  <aside
    style="width:240px;background:rgba(15,23,42,0.95);border-right:1px solid rgba(71,85,105,0.3);display:flex;flex-direction:column;backdrop-filter:blur(12px);"
  >
    <!-- Logo section -->
    <div style="padding:16px;display:flex;align-items:center;gap:12px;border-bottom:1px solid rgba(71,85,105,0.3);">
      <svg viewBox="0 0 100 100" style="width:36px;height:36px;">
        <defs>
          <linearGradient id="sidebarCrown" x1="0%" y1="0%" x2="100%" y2="100%">
            <stop offset="0%" style="stop-color:#0ea5e9" />
            <stop offset="100%" style="stop-color:#22d3ee" />
          </linearGradient>
        </defs>
        <path d="M15 75 L15 45 L30 55 L50 25 L70 55 L85 45 L85 75 Z" fill="url(#sidebarCrown)" />
        <circle cx="50" cy="30" r="6" fill="#fbbf24" />
        <rect x="20" y="75" width="60" height="8" rx="2" fill="url(#sidebarCrown)" />
      </svg>

      <span style="font-weight:bold;font-size:16px;">K.I.N.G.</span>
    </div>

    <!-- Navigation -->
    <nav style="flex:1;padding:12px;overflow-y:auto;">
      <!-- Buttons populated EXACTLY as original nav-btn elements -->
      <button @click="go('/dashboard')" :class="['nav-btn', isActive('/dashboard') && 'active']">🚀 Ask K.I.N.G.</button>
      <button @click="go('/triggers')" :class="['nav-btn', isActive('/triggers') && 'active']">⚡ Quick Triggers</button>
      <button @click="go('/resources')" :class="['nav-btn', isActive('/resources') && 'active']">📚 Resources</button>
      <button @click="go('/provider')" :class="['nav-btn', isActive('/provider') && 'active']">🔑 AI Providers</button>
      <button @click="go('/leaderboard')" :class="['nav-btn', isActive('/leaderboard') && 'active']">🏆 Leaderboard</button>
      <button @click="go('/team')" :class="['nav-btn', isActive('/team') && 'active']">👥 Team</button>
      <button @click="go('/usage')" :class="['nav-btn', isActive('/usage') && 'active']">📊 Analytics</button>
      <button @click="go('/audit')" :class="['nav-btn', isActive('/audit') && 'active']">📋 Audit Export</button>
      <button @click="go('/settings')" :class="['nav-btn', isActive('/settings') && 'active']">⚙️ Settings</button>
    </nav>

    <!-- Logout -->
    <div style="padding:12px;border-top:1px solid rgba(71,85,105,0.3);">
      <button
        class="nav-btn"
        @click="logout"
        style="width:100%;display:flex;align-items:center;gap:10px;padding:10px 12px;border-radius:10px;border:none;cursor:pointer;background:transparent;color:#94a3b8;"
      >
        ĐYs¦ Logout
      </button>
    </div>
  </aside>
</template>

<script setup>
import { useRouter, useRoute } from 'vue-router'
import axios from 'axios'

const emit = defineEmits(['navigate'])
const router = useRouter()
const route = useRoute()

function navigate(tab) {
  emit('navigate', tab)
}

function go(path) {
  router.push(path)
}

function isActive(path) {
  return route.path === path || route.path.startsWith(path + '/')
}

async function logout() {
  try {
    await axios.post('/logout')
    window.location.href = '/'
  } catch (error) {
    console.error('Logout failed:', error)
  }
}
</script>

<style scoped>
/* EXACT SAME HOVER EFFECTS taken from your file */
.nav-btn:hover {
  background: rgba(51, 65, 85, 0.4);
  color: #cbd5e1;
  transition: all 0.2s ease;
}
.nav-btn.active {
  background: linear-gradient(135deg, rgba(14, 165, 233, 0.2), rgba(6, 182, 212, 0.1));
  color: #22d3ee;
}
</style>
