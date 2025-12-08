<template>
  <div style="flex: 1; overflow: auto; padding: 24px;" id="main-content">
    <div style="max-width: 800px; margin: 0 auto;">

      <!-- STATS GRID (unchanged) -->
      <div class="dashboard-stats" 
           style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 24px;">

        <div class="card stat-card">
          <div style="font-size: 12px; color: #64748b;">Total Queries</div>
          <div style="font-size: 28px; font-weight: bold; margin: 8px 0 4px;">{{ stats.totalQueries }}</div>
          <div style="font-size: 12px; color: #10b981;">↑ 12%</div>
        </div>

        <div class="card stat-card">
          <div style="font-size: 12px; color: #64748b;">Avg Response</div>
          <div style="font-size: 28px; font-weight: bold; margin: 8px 0 4px;">{{ stats.avgResponse }}s</div>
          <div style="font-size: 12px; color: #22d3ee;">↓ 8%</div>
        </div>

        <div class="card stat-card">
          <div style="font-size: 12px; color: #64748b;">Success Rate</div>
          <div style="font-size: 28px; font-weight: bold; margin: 8px 0 4px;">{{ stats.successRate }}%</div>
          <div style="font-size: 12px; color: #10b981;">↑ 0.3%</div>
        </div>

        <div class="card stat-card">
          <div style="font-size: 12px; color: #64748b;">Tokens Used</div>
          <div style="font-size: 28px; font-weight: bold; margin: 8px 0 4px;">{{ stats.tokensUsed }}</div>
          <div style="font-size: 12px; color: #10b981;">↑ 15%</div>
        </div>

      </div>

      <!-- DAILY USAGE (unchanged) -->
      <div class="card">
        <h3 style="font-weight: 600; margin-bottom: 20px;">Daily Usage</h3>

        <div style="display: flex; align-items: flex-end; justify-content: space-between; height: 160px; gap: 8px;">

          <div v-for="(day, i) in daily" :key="i"
               style="flex: 1; display: flex; flex-direction: column; align-items: center; gap: 8px;">
            
            <!-- Bar Height EXACT AS ORIGINAL -->
            <div :style="{
              width: '100%',
              borderRadius: '6px 6px 0 0',
              background: 'linear-gradient(180deg, #0ea5e9 0%, #06b6d4 100%)',
              height: day.height + '%'
            }"></div>

            <span style="font-size: 11px; color: #64748b;">{{ day.label }}</span>

          </div>

        </div>
      </div>

    </div>
  </div>
</template>

<script>
export default {
  name: "Analytics",

  props: {
    stats: {
      type: Object,
      default: () => ({
        totalQueries: 2847,
        avgResponse: 1.2,
        successRate: 99.2,
        tokensUsed: "1.25M"
      })
    },

    daily: {
      type: Array,
      default: () => [
        { label: 'Mon', height: 42.857142857142854 },
        { label: 'Tue', height: 64.28571428571429 },
        { label: 'Wed', height: 33.92857142857143 },
        { label: 'Thu', height: 78.57142857142857 },
        { label: 'Fri', height: 53.57142857142857 },
        { label: 'Sat', height: 100 },
        { label: 'Sun', height: 69.64285714285714 }
      ]
    }
  }
};
</script>
