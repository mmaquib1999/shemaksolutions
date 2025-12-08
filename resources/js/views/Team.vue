<template>
  <div class="page">
    <div class="top">
      <div>
        <p class="muted">Manage your team members</p>
        <p class="muted fine">{{ members.length }} of 10 seats used</p>
      </div>
      <button class="btn" @click="openInvite">+ Invite</button>
    </div>

    <div class="card list-card">
      <div
        v-for="(member, idx) in members"
        :key="member.id"
        class="row"
        :class="{ last: idx === members.length - 1 }"
      >
        <div class="user">
          <div class="avatar">{{ member.name.charAt(0) }}</div>
          <div>
            <div class="name">{{ member.name }}</div>
            <div class="email">{{ member.email }}</div>
            <div class="badges">
              <span
                v-for="badge in member.badges"
                :key="badge"
                class="badge"
                :class="badgeClass(badge)"
              >
                {{ badgeLabel(badge) }}
              </span>
            </div>
          </div>
        </div>
        <div class="meta">
          <span class="role" :style="{ background: roleColor(member.role).bg, color: roleColor(member.role).fg }">
            {{ roleLabel(member.role) }}
          </span>
          <span class="muted">{{ member.queries }} queries</span>
        </div>
        <button v-if="member.role !== 'owner'" class="ghost-btn" aria-label="Remove">✕</button>
      </div>
    </div>

    <div class="card highlight">
      <div class="challenge">
        <div class="icon">🎉</div>
        <div class="copy">
          <h3>Invite Challenge Active!</h3>
          <p>Invite 2 more team members and everyone gets a <span class="accent">20% discount</span> on upgrades.</p>
        </div>
        <button class="btn" @click="openInvite">Invite Now</button>
      </div>
    </div>

    <!-- Invite Modal -->
    <div v-if="showInvite" class="modal-overlay" @click.self="closeInvite">
      <div class="modal">
        <div class="modal-head">
          <h3>Invite Teammate</h3>
          <button class="ghost-btn" @click="closeInvite" aria-label="Close">✕</button>
        </div>
        <div class="modal-body">
          <div class="field">
            <label class="label">Name</label>
            <input v-model="inviteForm.name" type="text" class="input" placeholder="e.g., Jane Doe" />
          </div>
          <div class="field">
            <label class="label">Email</label>
            <input v-model="inviteForm.email" type="email" class="input" placeholder="name@company.com" />
          </div>
          <div class="field">
            <label class="label">Role</label>
            <select v-model="inviteForm.role" class="input">
              <option value="member">Member</option>
              <option value="admin">Admin</option>
            </select>
          </div>
        </div>
        <div class="modal-actions">
          <button class="btn-secondary" @click="closeInvite">Cancel</button>
          <button class="btn" @click="sendInvite">Send Invite</button>
        </div>
        <p v-if="inviteError" class="error">{{ inviteError }}</p>
        <p v-if="inviteSuccess" class="success">{{ inviteSuccess }}</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref } from 'vue'

const members = [
  { id: 1, name: 'Alex Johnson', email: 'alex@example.com', role: 'owner', queries: 520, badges: ['loto', 'fire'] },
  { id: 2, name: 'Priya Singh', email: 'priya@example.com', role: 'admin', queries: 410, badges: ['ppe'] },
  { id: 3, name: 'Michael Chen', email: 'michael@example.com', role: 'member', queries: 320, badges: ['electrical'] },
  { id: 4, name: 'Sara Lopez', email: 'sara@example.com', role: 'member', queries: 260, badges: [] },
]

const showInvite = ref(false)
const inviteError = ref('')
const inviteSuccess = ref('')
const inviteForm = reactive({
  name: '',
  email: '',
  role: 'member',
})

function roleLabel(role) {
  if (role === 'owner') return 'Owner'
  if (role === 'admin') return 'Admin'
  return 'Member'
}

function roleColor(role) {
  if (role === 'owner') return { bg: 'rgba(245,158,11,0.2)', fg: '#f59e0b' }
  if (role === 'admin') return { bg: 'rgba(139,92,246,0.2)', fg: '#8b5cf6' }
  return { bg: 'rgba(59,130,246,0.2)', fg: '#3b82f6' }
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

function openInvite() {
  inviteError.value = ''
  inviteSuccess.value = ''
  showInvite.value = true
}

function closeInvite() {
  showInvite.value = false
}

function sendInvite() {
  inviteError.value = ''
  inviteSuccess.value = ''
  if (!inviteForm.name.trim() || !inviteForm.email.trim()) {
    inviteError.value = 'Please enter a name and email.'
    return
  }
  // placeholder for API call
  inviteSuccess.value = `Invite sent to ${inviteForm.email}`
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

.top {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 12px;
  flex-wrap: wrap;
}

.card {
  background: linear-gradient(135deg, rgba(15, 23, 42, 0.92), rgba(12, 18, 35, 0.85));
  border: 1px solid rgba(71, 85, 105, 0.35);
  border-radius: 16px;
  padding: 16px;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.35);
}

.highlight {
  background: linear-gradient(135deg, rgba(14, 165, 233, 0.12), rgba(6, 182, 212, 0.06));
  border: 1px solid rgba(34, 211, 238, 0.35);
}

.list-card {
  padding: 0;
  overflow: hidden;
}

.row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  padding: 16px;
  border-bottom: 1px solid rgba(71, 85, 105, 0.3);
  flex-wrap: wrap;
}

.row.last {
  border-bottom: none;
}

.user {
  display: flex;
  align-items: center;
  gap: 12px;
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
  font-size: 14px;
}

.name {
  font-weight: 600;
  font-size: 14px;
}

.email {
  font-size: 13px;
  color: #64748b;
}

.badges {
  display: flex;
  gap: 4px;
  margin-top: 4px;
  flex-wrap: wrap;
}

.badge {
  font-size: 10px;
  padding: 4px 8px;
  border-radius: 10px;
  font-weight: 700;
}

.badge-loto { background: rgba(251, 191, 36, 0.2); color: #fbbf24; }
.badge-ppe { background: rgba(139, 92, 246, 0.2); color: #8b5cf6; }
.badge-fire { background: rgba(239, 68, 68, 0.2); color: #ef4444; }
.badge-electrical { background: rgba(59, 130, 246, 0.2); color: #3b82f6; }

.meta {
  display: flex;
  flex-direction: column;
  gap: 6px;
  align-items: flex-end;
}

.role {
  padding: 4px 10px;
  border-radius: 10px;
  font-weight: 700;
  font-size: 11px;
}

.muted {
  color: #94a3b8;
}

.fine {
  font-size: 12px;
}

.ghost-btn {
  background: none;
  border: none;
  color: #94a3b8;
  cursor: pointer;
  padding: 6px;
  font-size: 14px;
}

.challenge {
  display: flex;
  align-items: center;
  gap: 16px;
  flex-wrap: wrap;
}

.challenge .icon {
  font-size: 28px;
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
  box-shadow: 0 12px 30px rgba(34, 211, 238, 0.25);
}

.btn-secondary {
  background: rgba(148, 163, 184, 0.12);
  color: #cbd5e1;
  border: 1px solid rgba(148, 163, 184, 0.3);
  padding: 10px 16px;
  border-radius: 12px;
  cursor: pointer;
}

.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.7);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 2000;
  padding: 16px;
}

.modal {
  background: linear-gradient(145deg, rgba(15, 23, 42, 0.96), rgba(15, 23, 42, 0.86));
  border: 1px solid rgba(71, 85, 105, 0.45);
  border-radius: 16px;
  padding: 18px;
  width: 100%;
  max-width: 440px;
  box-shadow: 0 24px 70px rgba(0, 0, 0, 0.45);
}

.modal-head {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 12px;
}

.modal-head h3 {
  margin: 0;
}

.modal-body {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.field {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.modal-actions {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
  margin-top: 12px;
}

.error {
  color: #fca5a5;
  font-size: 12px;
  margin-top: 8px;
}

.success {
  color: #34d399;
  font-size: 12px;
  margin-top: 8px;
}

.input {
  background: rgba(148, 163, 184, 0.08);
  border: 1px solid rgba(148, 163, 184, 0.35);
  border-radius: 12px;
  color: #e2e8f0;
  padding: 10px 12px;
}

.input:focus {
  outline: none;
  border-color: #22d3ee;
  box-shadow: 0 0 0 3px rgba(34, 211, 238, 0.12);
}

@media (max-width: 700px) {
  .meta {
    align-items: flex-start;
  }
}
</style>
