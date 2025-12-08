<template>
  <!-- ORIGINAL SIDEBAR EXACTLY AS IN THE HTML FILE -->
  <aside 
    style="width:240px;background:rgba(15,23,42,0.95);border-right:1px solid rgba(71,85,105,0.3);display:flex;flex-direction:column;backdrop-filter:blur(12px);"
  >

    <!-- Logo section -->
    <div style="padding:16px;display:flex;align-items:center;gap:12px;border-bottom:1px solid rgba(71,85,105,0.3);">
      <svg viewBox="0 0 100 100" style="width:36px;height:36px;">
        <defs>
          <linearGradient id="sidebarCrown" x1="0%" y1="0%" x2="100%" y2="100%">
            <stop offset="0%" style="stop-color:#0ea5e9"/>
            <stop offset="100%" style="stop-color:#22d3ee"/>
          </linearGradient>
        </defs>
        <path d="M15 75 L15 45 L30 55 L50 25 L70 55 L85 45 L85 75 Z" fill="url(#sidebarCrown)" />
        <circle cx="50" cy="30" r="6" fill="#fbbf24"/>
        <rect x="20" y="75" width="60" height="8" rx="2" fill="url(#sidebarCrown)"/>
      </svg>

      <span style="font-weight:bold;font-size:16px;">K.I.N.G.</span>
    </div>

    <!-- Navigation -->
    <nav style="flex:1;padding:12px;overflow-y:auto;">
      <!-- Buttons populated EXACTLY as original nav-btn elements -->
      <button @click="$router.push('/dashboard')" class="nav-btn">🚀 Dashboard</button>
<button @click="$router.push('/triggers')" class="nav-btn">⚡ Quick Triggers</button>
<button @click="$router.push('/resources')" class="nav-btn">📚 Resources</button>
<button @click="$router.push('/provider')" class="nav-btn">📚 AI Provider</button>
<button @click="$router.push('/leaderboard')" class="nav-btn">🏆 Leaderboard</button>
<button @click="$router.push('/team')" class="nav-btn">👥 Team</button>
<button @click="$router.push('/usage')" class="nav-btn">📊 Analytics</button>
<button @click="$router.push('/audit')" class="nav-btn">📋 Audit Export</button>
<button @click="$router.push('/settings')" class="nav-btn">⚙️ Settings</button>

    </nav>

    <!-- Logout -->
    <div style="padding:12px;border-top:1px solid rgba(71,85,105,0.3);">
      <button 
        class="nav-btn" 
        @click="logout"
        style="width:100%;display:flex;align-items:center;gap:10px;padding:10px 12px;border-radius:10px;border:none;cursor:pointer;background:transparent;color:#94a3b8;"
      >
        🚪 Logout
      </button>
    </div>

  </aside>
</template>

<script setup>
const emit = defineEmits(["navigate"])
import { useRouter } from "vue-router";

const router = useRouter();

function navigate(tab) {
  emit("navigate", tab)
}

async function logout() {
  try {
    // Debug (check logged-in user before logout)
    const user = await axios.get("/api/user");
    console.log("Logged user before logout:", user.data);

    // ⭐ Sanctum logout (POST request)
    await axios.post("/logout");

    console.log("User logged out successfully.");

    // Redirect to home page
    // router.push("/");

     window.location.href = '/';
  } catch (error) {
    console.error("Logout failed:", error);
  }
}

</script>

<style scoped>
/* EXACT SAME HOVER EFFECTS taken from your file */
.nav-btn:hover {
  background: rgba(51,65,85,0.4);
  color: #cbd5e1;
  transition: all 0.2s ease;
}
.nav-btn.active {
  background: linear-gradient(135deg, rgba(14,165,233,0.2), rgba(6,182,212,0.1));
  color: #22d3ee;
}
</style>


