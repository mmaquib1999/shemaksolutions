<template>
  <div style="flex: 1; overflow: auto; padding: 24px;" id="main-content">
    <div style="max-width: 900px; margin: 0 auto;">
      <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
        <div>
          <h2 style="margin: 0; font-size: 18px; font-weight: 700;">Usage &amp; Performance</h2>
          <p style="margin: 2px 0 0; color: #94a3b8; font-size: 13px;">Live stats from your team and providers</p>
        </div>
        <span v-if="error" style="color: #f87171; font-size: 12px;">{{ error }}</span>
      </div>

      <div class="dashboard-stats" style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 24px;">
        <div class="card stat-card">
          <div style="font-size: 12px; color: #64748b;">Total Queries</div>
          <div style="font-size: 28px; font-weight: bold; margin: 8px 0 4px;">{{ stats.totalQueries.toLocaleString() }}</div>
          <div style="font-size: 12px; color: #10b981;">▲ live</div>
        </div>

        <div class="card stat-card">
          <div style="font-size: 12px; color: #64748b;">Avg Response</div>
          <div style="font-size: 28px; font-weight: bold; margin: 8px 0 4px;">{{ stats.avgResponse }}s</div>
          <div style="font-size: 12px; color: #22d3ee;">▼ optimized</div>
        </div>

        <div class="card stat-card">
          <div style="font-size: 12px; color: #64748b;">Success Rate</div>
          <div style="font-size: 28px; font-weight: bold; margin: 8px 0 4px;">{{ stats.successRate }}%</div>
          <div style="font-size: 12px; color: #10b981;">▲ stable</div>
        </div>

        <div class="card stat-card">
          <div style="font-size: 12px; color: #64748b;">Tokens Used (est.)</div>
          <div style="font-size: 28px; font-weight: bold; margin: 8px 0 4px;">{{ stats.tokensUsed }}</div>
          <div style="font-size: 12px; color: #10b981;">▲ forecast</div>
        </div>
      </div>

      <div class="card" style="margin-bottom: 20px;">
        <h3 style="font-weight: 600; margin-bottom: 16px;">Usage by User</h3>
        <div v-if="loading" style="padding: 12px; color: #94a3b8;">Loading usage...</div>
        <div v-else-if="!users.length" style="padding: 12px; color: #94a3b8;">No usage data yet.</div>
        <div v-else class="table">
          <div class="table-head">
            <span>Name</span>
            <span>Email</span>
            <span style="text-align: center;">Providers</span>
            <span style="text-align: right;">Total Queries</span>
            <span style="text-align: right;">Avg Response</span>
            <span style="text-align: right;">Success Rate</span>
          </div>
          <div
            v-for="user in users"
            :key="user.id"
            class="table-row"
          >
            <span class="cell-name">{{ user.name }}</span>
            <span class="cell-email">{{ user.email }}</span>
            <span style="text-align: center;">{{ user.providers }}</span>
            <span style="text-align: right;">{{ (user.total_queries ?? 0).toLocaleString() }}</span>
            <span style="text-align: right;">{{ user.avg_response }}s</span>
            <span style="text-align: right;">{{ user.success_rate }}%</span>
          </div>
        </div>
      </div>

      <div class="card">
        <h3 style="font-weight: 600; margin-bottom: 20px;">Daily Usage</h3>

        <div style="display: flex; align-items: flex-end; justify-content: space-between; height: 160px; gap: 8px;">
          <div
            v-for="(day, i) in daily"
            :key="i"
            style="flex: 1; display: flex; flex-direction: column; align-items: center; gap: 8px;"
          >
            <div
              :style="{
                width: '100%',
                borderRadius: '6px 6px 0 0',
                background: 'linear-gradient(180deg, #0ea5e9 0%, #06b6d4 100%)',
                height: day.height + '%'
              }"
            ></div>

            <span style="font-size: 11px; color: #64748b;">{{ day.label }}</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import axios from 'axios'
import { onMounted, ref } from 'vue'

const loading = ref(false)
const error = ref('')
const stats = ref({
  totalQueries: 0,
  avgResponse: '0.00',
  successRate: '99.0',
  tokensUsed: '--',
})

const users = ref([])

const daily = ref([
  { label: 'Mon', height: 42.857142857142854 },
  { label: 'Tue', height: 64.28571428571429 },
  { label: 'Wed', height: 33.92857142857143 },
  { label: 'Thu', height: 78.57142857142857 },
  { label: 'Fri', height: 53.57142857142857 },
  { label: 'Sat', height: 100 },
  { label: 'Sun', height: 69.64285714285714 }
])

onMounted(loadUsage)

async function loadUsage() {
  loading.value = true
  error.value = ''
  try {
    const { data } = await axios.get('/api/usage')
    stats.value = {
      totalQueries: data?.summary?.total_queries ?? 0,
      avgResponse: (data?.summary?.avg_response ?? 0).toFixed(2),
      successRate: (data?.summary?.success_rate ?? 0).toFixed(2),
      tokensUsed: data?.summary?.tokens_used ?? '--',
    }
    users.value = data?.users || []
    daily.value = (data?.daily || daily.value).map((d) => ({
      label: d.label,
      height: d.height ?? 0,
    }))
  } catch (err) {
    error.value = err?.response?.data?.message || err?.message || 'Unable to load usage data'
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.card {
  background: linear-gradient(135deg, rgba(15, 23, 42, 0.92), rgba(12, 18, 35, 0.85));
  border: 1px solid rgba(71, 85, 105, 0.35);
  border-radius: 16px;
  padding: 16px;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.35);
}

.table {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.table-head,
.table-row {
  display: grid;
  grid-template-columns: 1.2fr 1.4fr 0.8fr 1fr 1fr 1fr;
  gap: 8px;
  align-items: center;
}

.table-head {
  font-size: 12px;
  color: #94a3b8;
  padding-bottom: 6px;
  border-bottom: 1px solid rgba(71, 85, 105, 0.3);
}

.table-row {
  padding: 8px 0;
  border-bottom: 1px solid rgba(71, 85, 105, 0.15);
}

.table-row:last-child {
  border-bottom: none;
}

.cell-name {
  font-weight: 600;
  color: #e2e8f0;
}

.cell-email {
  color: #94a3b8;
  font-size: 12px;
}

@media (max-width: 840px) {
  .table-head,
  .table-row {
    grid-template-columns: 1fr 1fr;
    grid-template-areas:
      "name name"
      "email email"
      "providers queries"
      "avg success";
  }

  .cell-name { grid-area: name; }
  .cell-email { grid-area: email; }
  .table-row span:nth-child(3) { grid-area: providers; }
  .table-row span:nth-child(4) { grid-area: queries; }
  .table-row span:nth-child(5) { grid-area: avg; }
  .table-row span:nth-child(6) { grid-area: success; }
}
</style>
