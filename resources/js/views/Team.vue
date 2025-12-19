<template>
  <div class="page">
    <div v-if="toasts.length" class="toast-stack">
      <div v-for="toast in toasts" :key="toast.id" class="toast" :class="toast.type">
        {{ toast.message }}
      </div>
    </div>

    <div class="top">
      <div>
        <p class="muted">Manage your team members</p>
        <p class="muted fine">{{ members.length }} of {{ seatLimitLabel }} seats used</p>
      </div>
      <button class="btn" @click="openInvite">+ Invite</button>
    </div>

    <div class="card list-card">
      <div v-if="loading" class="empty-state">Loading team...</div>
      <div v-else-if="!members.length" class="empty-state muted">No team members yet.</div>
      <template v-else>
        <div
          v-for="(member, idx) in members"
          :key="member.id || member.email"
          class="row"
          :class="{ last: idx === members.length - 1 }"
        >
          <div class="user">
            <div class="avatar">{{ avatarInitial(member.name) }}</div>
            <div>
              <div class="name">{{ member.name }}</div>
              <div class="email">{{ member.email }}</div>
            </div>
          </div>
          <div class="meta">
            <span class="role" :style="{ background: roleColor(member.role).bg, color: roleColor(member.role).fg }">
              {{ roleLabel(member.role) }}
            </span>
            <span
              class="role status"
              v-if="!member.is_owner"
              :style="{ background: statusColor(member).bg, color: statusColor(member).fg }"
            >
              {{ statusLabel(member) }}
            </span>
            <span class="queries muted">{{ (member.total_queries ?? 0).toLocaleString() }} queries</span>
          </div>
          <button
            v-if="!member.is_owner"
            class="ghost-btn"
            aria-label="Remove member"
            title="Remove member"
            @click="removeMember(member)"
            :disabled="deletingId === (member.id || member.email) || sendingDelete"
          >
            ✕
          </button>
        </div>
      </template>
    </div>

    <div class="card highlight">
      <div class="challenge">
        <div class="icon">XYZ%</div>
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
          <button class="ghost-btn" @click="closeInvite" aria-label="Close">x</button>
        </div>
        <div class="modal-body">
          <div class="field">
            <label class="label">Name</label>
            <input v-model="inviteForm.name" type="text" class="input" placeholder="e.g., Jane Doe" />
            <span v-if="inviteForm.errors.has('name')" class="error">{{ inviteForm.errors.get('name') }}</span>
          </div>
          <div class="field">
            <label class="label">Email</label>
            <input v-model="inviteForm.email" type="email" class="input" placeholder="name@company.com" />
            <span v-if="inviteForm.errors.has('email')" class="error">{{ inviteForm.errors.get('email') }}</span>
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
          <button class="btn" @click="sendInvite" :disabled="inviteForm.busy">
            <span v-if="inviteForm.busy">Sending...</span>
            <span v-else>Send Invite</span>
          </button>
        </div>
        <p v-if="inviteError" class="error">{{ inviteError }}</p>
        <p v-if="inviteSuccess" class="success">{{ inviteSuccess }}</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import axios from 'axios'
import { computed, onMounted, reactive, ref } from 'vue'
import { Form } from 'vform'

const members = ref([])
const seatLimit = ref(null)
const inviteLimit = ref(null)
const canInvite = ref(true)
const loading = ref(false)

const showInvite = ref(false)
const inviteError = ref('')
const inviteSuccess = ref('')
const toasts = ref([])
const inviteForm = reactive(
  new Form({
    name: '',
    email: '',
    role: 'member',
  })
)
const deletingId = ref(null)
const sendingDelete = ref(false)

onMounted(loadMembers)

const seatLimitLabel = computed(() => {
  if (seatLimit.value === null || seatLimit.value === undefined) return 'Unlimited'
  return seatLimit.value
})

function avatarInitial(name = '') {
  return name?.trim()?.charAt(0)?.toUpperCase() || '?'
}

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

function statusLabel(member) {
  if (member.is_owner) return 'Owner'
  if (member.status === 'accepted' || member.status === 'active') return 'Joined'
  return 'Pending invite'
}

function statusColor(member) {
  if (member.is_owner) return roleColor('owner')
  if (member.status === 'accepted' || member.status === 'active') return roleColor('member')
  return { bg: 'rgba(14,165,233,0.24)', fg: '#ffffff' } // pending invite
}

function openInvite() {
  inviteError.value = ''
  inviteSuccess.value = ''
  inviteForm.errors.clear()
  if (!canInvite.value) {
    const message =
      inviteLimit.value === 0
        ? 'Upgrade the plan to invite team members.'
        : 'Team member limit reached. Upgrade your plan to invite more.'
    inviteError.value = message
    pushToast(message, 'error')
    return
  }
  showInvite.value = true
}

function closeInvite() {
  inviteForm.errors.clear()
  showInvite.value = false
}

function resetInviteForm() {
  inviteForm.reset()
}

async function loadMembers() {
  loading.value = true
  inviteError.value = ''
  try {
    const { data } = await axios.get('/api/team')
    members.value = data?.members || []
    seatLimit.value = data?.seat_limit ?? data?.seatLimit ?? members.value.length
    inviteLimit.value = data?.invite_limit ?? null
    canInvite.value = data?.can_invite ?? true
  } catch (error) {
    inviteError.value = errorMessage(error)
  } finally {
    loading.value = false
  }
}

async function sendInvite() {
  inviteError.value = ''
  inviteSuccess.value = ''
  inviteForm.errors.clear()

  if (!canInvite.value) {
    const message =
      inviteLimit.value === 0
        ? 'Upgrade the plan to invite team members.'
        : 'Team member limit reached. Upgrade your plan to invite more.'
    inviteError.value = message
    pushToast(message, 'error')
    return
  }

  const name = inviteForm.name?.trim()
  const email = inviteForm.email?.trim()

  const fieldErrors = {}
  if (!name) fieldErrors.name = ['Name is required.']
  if (!email) fieldErrors.email = ['Email is required.']

  if (Object.keys(fieldErrors).length) {
    inviteForm.errors.set(fieldErrors)
    inviteError.value = 'Please fix the highlighted fields.'
    return
  }

  inviteForm.name = name
  inviteForm.email = email

  try {
    await inviteForm.post('/api/team/invitations')
    inviteSuccess.value = `Invite sent to ${inviteForm.email}`
    pushToast(inviteSuccess.value, 'success')
    resetInviteForm()
    showInvite.value = false
    loadMembers()
  } catch (error) {
    inviteError.value = errorMessage(error)
    pushToast(inviteError.value, 'error')
  }
}

async function removeMember(member) {
  if (!member?.id && !member?.email) {
    inviteError.value = 'Cannot remove member: missing id or email.'
    return
  }

  const identifier = member.name || member.email || member.id
  const ok = window.confirm(`Remove ${identifier || 'this member'}?`)
  if (!ok) return

  inviteError.value = ''
  const key = member.id || member.email
  deletingId.value = key
  sendingDelete.value = true

  try {
    const endpoint = member.id ? `/api/team/members/${member.id}` : '/api/team/members'
    const config = member.id ? undefined : { data: { email: member.email } }
    await axios.delete(endpoint, config)
    await loadMembers()
  } catch (error) {
    inviteError.value = errorMessage(error)
  } finally {
    deletingId.value = null
    sendingDelete.value = false
  }
}

function errorMessage(error) {
  if (error?.response?.data?.errors) {
    const first = Object.values(error.response.data.errors)[0]
    if (Array.isArray(first) && first.length) return first[0]
  }
  if (error?.response?.data?.message) return error.response.data.message
  if (error?.message) return error.message
  return 'Something went wrong'
}

function pushToast(message, type = 'success') {
  const id = Date.now() + Math.random()
  toasts.value.push({ id, message, type })
  setTimeout(() => {
    toasts.value = toasts.value.filter((t) => t.id !== id)
  }, 3500)
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
  flex-direction: row;
  gap: 8px;
  align-items: center;
  justify-content: flex-end;
  flex-wrap: wrap;
}

.role {
  padding: 4px 10px;
  border-radius: 10px;
  font-weight: 700;
  font-size: 11px;
}

.status {
  display: inline-flex;
  align-items: center;
  gap: 6px;
}

.queries {
  font-size: 12px;
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

.ghost-btn[disabled] {
  opacity: 0.5;
  cursor: not-allowed;
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

.empty-state {
  padding: 18px;
  text-align: center;
}

.toast-stack {
  position: fixed;
  top: 20px;
  right: 20px;
  display: flex;
  flex-direction: column;
  gap: 10px;
  z-index: 9999;
}

.toast {
  padding: 10px 14px;
  border-radius: 10px;
  color: #e2e8f0;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
  min-width: 220px;
  font-size: 14px;
}

.toast.success {
  background: linear-gradient(90deg, rgba(34, 197, 94, 0.9), rgba(16, 185, 129, 0.9));
}

.toast.error {
  background: linear-gradient(90deg, rgba(239, 68, 68, 0.9), rgba(248, 113, 113, 0.9));
}

@media (max-width: 700px) {
  .meta {
    align-items: flex-start;
    justify-content: flex-start;
  }
}
</style>
