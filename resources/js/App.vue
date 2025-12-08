<template>
  <div style="display:flex;">
    <Sidebar />

    <div style="flex:1">
      <Header :title="pageTitle" :user="user" />
      <router-view />
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import { useRoute } from 'vue-router'
import axios from 'axios'
import Sidebar from './components/Sidebar.vue'
import Header from './components/Header.vue'

const route = useRoute()

const user = ref({
  name: "Loading...",
  tier: "pro"
})

async function fetchUser() {
  try {
    const res = await axios.get('/api/user')
    if (res?.data?.name) {
      user.value = {
        name: res.data.name,
        tier: res.data?.role || res.data?.plan || 'pro'
      }
    }
  } catch (error) {
    // keep defaults if unauthenticated or API unavailable
    console.warn('Could not load user profile', error?.response?.status || error?.message)
  }
}

onMounted(fetchUser)

// Dynamically updates header title
const pageTitle = computed(() => {
  switch (route.name) {
    case 'dashboard': return 'Dashboard'
    case 'triggers': return 'Triggers'
    case 'resources': return 'Resource Library'
    case 'leaderboard': return 'Leaderboard'
    case 'team': return 'Team Members'
    case 'usage': return 'Analytics Overview'
    case 'audit': return 'Audit Export'
    case 'settings': return 'Settings'
    default: return 'Dashboard'
  }
})
</script>
