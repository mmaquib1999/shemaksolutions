<template>
  <header
    style="
      padding:16px 24px;
      display:flex;
      align-items:center;
      justify-content:space-between;
      background:rgba(15,23,42,0.5);
      border-bottom:1px solid rgba(71,85,105,0.3);
    "
  >
    <!-- Left side: hamburger + page title -->
    <div style="display:flex;align-items:center;gap:12px;">
      <!-- Mobile menu trigger -->
      <button
        @click="$emit('open-mobile-menu')"
        class="hamburger-btn"
        style="display:none;background:none;border:none;color:#fff;cursor:pointer;padding:8px;"
      >
        ☰
      </button>

      <!-- Page Title -->
      <h1 style="font-size:18px;font-weight:600;">
        {{ title }}
      </h1>
    </div>

    <!-- Right-side user block -->
    <div style="display:flex;align-items:center;gap:12px;">
      <div style="text-align:right;">
        <div style="font-weight:500;font-size:14px;">
          {{ user.name }}
        </div>
        <div style="font-size:12px;color:#22d3ee;">
          {{ user.tier.toUpperCase() }}
        </div>
      </div>

      <div
        style="
          width:40px;
          height:40px;
          border-radius:50%;
          background:linear-gradient(135deg,#0ea5e9,#06b6d4);
          display:flex;
          align-items:center;
          justify-content:center;
          font-weight:600;
        "
      >
        {{ user.name.charAt(0) }}
      </div>
    </div>
  </header>
</template>

<script setup>
/**
 * Props so the header is reusable
 * – title comes from each page (Ask K.I.N.G., Analytics, Team, etc.)
 * – user info comes from your Vue app or backend
 */
defineProps({
  title: {
    type: String,
    default: '🛡️ Safety Assistant',
  },
  user: {
    type: Object,
    default: () => ({
      name: 'Claudino Nelson',
      tier: 'pro',
    }),
  },
});
</script>

<style scoped>
/* keep original responsive behavior */
@media(max-width:768px) {
  .hamburger-btn {
    display: flex !important;
  }
}
</style>
