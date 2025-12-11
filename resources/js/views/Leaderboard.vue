<template>
  <div class="page">
    <div class="card highlight gold">
      <div class="hero">
        <div class="icon">🏆</div>
        <div>
          <h2>Team Safety Champions</h2>
          <p>More queries = more lives protected.</p>
        </div>
      </div>
    </div>

    <div class="card list-card">
      <div class="list-head">
        <h3>This Month's Rankings</h3>
      </div>

      <div class="list-body">
        <div
          v-for="(member, idx) in sorted"
          :key="member.id"
          class="row"
          :class="rankClass(idx)"
        >
          <div class="rank-label">
            <span v-if="idx === 0">🥇</span>
            <span v-else-if="idx === 1">🥈</span>
            <span v-else-if="idx === 2">🥉</span>
            <span v-else>#{{ idx + 1 }}</span>
          </div>
          <div class="avatar">{{ avatarInitial(member.name) }}</div>
          <div class="meta">
            <div class="name">{{ member.name }}</div>
            <div class="badges">
              <span v-for="badge in member.badges" :key="badge" class="badge" :class="badgeClass(badge)">
                {{ badgeLabel(badge) }}
              </span>
            </div>
          </div>
          <div class="queries">
            <div class="count">{{ member.queries }}</div>
            <div class="label">queries</div>
          </div>
          <div class="streak">
            <div class="streak-icon">🔥</div>
            <div class="streak-label">{{ member.streak }} day streak</div>
          </div>
        </div>
      </div>
    </div>

    <div class="card challenge">
      <div class="challenge-body">
        <div class="icon">🎉</div>
        <div class="copy">
          <h3>Invite Challenge Active!</h3>
          <p>Invite 2 more team members and everyone gets a <span class="accent">20% discount</span> on upgrades.</p>
        </div>
        <button class="btn">Invite</button>
      </div>
    </div>
  </div>
</template>

<script setup>
import axios from 'axios'
import { computed, onMounted, ref } from 'vue'

const MAX_LEADERS = 5
const fallbackMembers = [
  { id: 1, name: 'Alex Johnson', queries: 240, streak: 6, badges: ['loto', 'fire'] },
  { id: 2, name: 'Priya Singh', queries: 210, streak: 4, badges: ['ppe'] },
  { id: 3, name: 'Michael Chen', queries: 180, streak: 3, badges: ['electrical'] },
  { id: 4, name: 'Sara Lopez', queries: 140, streak: 2, badges: [] },
  { id: 5, name: 'Daniel Park', queries: 120, streak: 1, badges: [] },
]

const members = ref([])

onMounted(loadLeaderboard)

async function loadLeaderboard() {
  try {
    const { data } = await axios.get('/api/team')
    const apiMembers = (data?.members || []).map((m) => ({
      id: m.id || m.email,
      name: m.name || m.email || 'Member',
      queries: Number(m.total_queries ?? m.queries ?? 0),
      streak: m.streak ?? 0,
      badges: badgesForRole(m.role, m.is_owner),
    }))
    members.value = apiMembers
  } catch (error) {
    // keep fallback data if API fails or unauthenticated
    console.warn('Failed to load leaderboard data; showing fallback.', error?.message)
    members.value = fallbackMembers
  }
}

// Only expose the top leaders; API may return more members
const sorted = computed(() =>
  [...members.value]
    .sort((a, b) => b.queries - a.queries)
    .slice(0, MAX_LEADERS)
)

function rankClass(idx) {
  if (idx === 0) return 'gold'
  if (idx === 1) return 'silver'
  if (idx === 2) return 'bronze'
  return ''
}

function badgeClass(badge) {
  return {
    loto: 'badge-loto',
    ppe: 'badge-ppe',
    fire: 'badge-fire',
    electrical: 'badge-electrical',
  }[badge] || ''
}

function badgeLabel(badge) {
  return {
    loto: 'LOTO',
    ppe: 'PPE',
    fire: 'Fire',
    electrical: 'Electrical',
  }[badge] || badge
}

function avatarInitial(name = '') {
  return name?.trim()?.charAt(0)?.toUpperCase() || '?'
}

function badgesForRole(role, isOwner) {
  if (isOwner) return ['loto', 'fire', 'ppe']
  if (role === 'admin') return ['ppe']
  return []
}
</script>

<style scoped>
.page {
  max-width: 900px;
  margin: 0 auto;
  display: flex;
  flex-direction: column;
  gap: 16px;
  color: #e2e8f0;
}

.card {
  background: rgba(30, 41, 59, 0.5);
  border: 1px solid rgba(71, 85, 105, 0.35);
  border-radius: 16px;
  padding: 18px;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
}

.highlight.gold {
  background: linear-gradient(135deg, rgba(251, 191, 36, 0.12), rgba(245, 158, 11, 0.08));
  border-color: rgba(251, 191, 36, 0.4);
}

.hero {
  display: flex;
  align-items: center;
  gap: 16px;
}

.hero .icon {
  font-size: 42px;
}

.hero h2 {
  margin: 0 0 4px;
  font-size: 20px;
  font-weight: 700;
}

.hero p {
  margin: 0;
  color: #94a3b8;
}

.list-card {
  padding: 0;
  overflow: hidden;
}

.list-head {
  padding: 16px 20px;
  background: rgba(30, 41, 59, 0.8);
  border-bottom: 1px solid rgba(71, 85, 105, 0.3);
}

.list-head h3 {
  margin: 0;
  font-weight: 600;
}

.list-body {
  padding: 16px;
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.row {
  display: grid;
  grid-template-columns: 70px 50px 1fr 120px 140px;
  align-items: center;
  gap: 12px;
  padding: 12px;
  border-radius: 12px;
  background: rgba(15, 23, 42, 0.5);
  border: 1px solid rgba(71, 85, 105, 0.3);
}

.row.gold {
  border-left: 3px solid #fbbf24;
}
.row.silver {
  border-left: 3px solid #cbd5e1;
}
.row.bronze {
  border-left: 3px solid #d97757;
}

.rank-label {
  font-size: 18px;
  text-align: center;
}

.avatar {
  width: 44px;
  height: 44px;
  border-radius: 50%;
  background: linear-gradient(135deg, #0ea5e9, #06b6d4);
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
}

.meta {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.name {
  font-weight: 600;
  font-size: 14px;
}

.badges {
  display: flex;
  gap: 6px;
  flex-wrap: wrap;
}

.badge {
  font-size: 11px;
  padding: 4px 8px;
  border-radius: 12px;
  font-weight: 700;
}

.badge-loto { background: rgba(251, 191, 36, 0.2); color: #fbbf24; }
.badge-ppe { background: rgba(139, 92, 246, 0.2); color: #8b5cf6; }
.badge-fire { background: rgba(239, 68, 68, 0.2); color: #ef4444; }
.badge-electrical { background: rgba(59, 130, 246, 0.2); color: #3b82f6; }

.queries {
  text-align: right;
}

.count {
  font-size: 20px;
  font-weight: 700;
  color: #22d3ee;
}

.label {
  font-size: 11px;
  color: #64748b;
}

.streak {
  display: flex;
  flex-direction: column;
  gap: 2px;
  align-items: center;
  border-left: 1px solid rgba(71, 85, 105, 0.3);
  padding-left: 10px;
}

.streak-icon {
  font-size: 16px;
}

.streak-label {
  font-size: 11px;
  color: #f97316;
}

.challenge {
  background: linear-gradient(135deg, rgba(14, 165, 233, 0.1), rgba(6, 182, 212, 0.05));
  border: 1px solid rgba(34, 211, 238, 0.3);
}

.challenge-body {
  display: flex;
  align-items: center;
  gap: 16px;
  flex-wrap: wrap;
}

.challenge .icon {
  font-size: 32px;
}

.challenge h3 {
  margin: 0 0 4px;
}

.challenge p {
  margin: 0;
  color: #94a3b8;
  font-size: 13px;
}

.accent {
  color: #10b981;
  font-weight: 700;
}

.btn {
  background: linear-gradient(135deg, #0ea5e9, #22d3ee);
  color: #0b1729;
  border: none;
  padding: 10px 16px;
  border-radius: 12px;
  font-weight: 700;
  cursor: pointer;
}

@media (max-width: 800px) {
  .row {
    grid-template-columns: 1fr;
    align-items: start;
  }
  .queries, .streak, .rank-label {
    text-align: left;
    border-left: none;
    padding-left: 0;
  }
  .streak {
    align-items: flex-start;
  }
}
</style>
