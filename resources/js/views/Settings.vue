<template>
  <div class="settings-page" id="main-content">
    <div class="settings-inner">
      <div class="card dark-card" style="margin-bottom:20px;">
        <h3 style="font-weight:600;margin-bottom:20px;">Account Settings</h3>
        <div style="display:flex;flex-direction:column;gap:16px;">
          <div>
            <label class="label">Name</label>
            <input type="text" v-model="form.name" class="input dark-input" id="settings-name">
          </div>
          <div>
            <label class="label">Email</label>
            <input type="email" v-model="form.email" class="input dark-input" id="settings-email" disabled>
          </div>
          <div>
            <label class="label">Company</label>
            <input type="text" v-model="form.company" class="input dark-input" id="settings-company">
          </div>
          <button @click="saveSettings" class="btn" :disabled="saving" style="align-self:flex-start;">
            <span v-if="saving">Saving...</span>
            <span v-else>Save Changes</span>
          </button>
          <span v-if="status" class="muted fine">{{ status }}</span>
        </div>
      </div>

      <div class="card dark-card" style="margin-bottom:20px;">
        <h3 style="font-weight:600;margin-bottom:20px;">Notification Preferences</h3>
        <div style="display:flex;flex-direction:column;gap:12px;">
          <label style="display:flex;align-items:center;gap:12px;cursor:pointer;">
            <input type="checkbox" v-model="notifications.safetyAlerts" class="check">
            <span style="color:#cbd5e1;">Email notifications for new safety alerts</span>
          </label>
          <label style="display:flex;align-items:center;gap:12px;cursor:pointer;">
            <input type="checkbox" v-model="notifications.weeklySummary" class="check">
            <span style="color:#cbd5e1;">Weekly usage summary</span>
          </label>
          <label style="display:flex;align-items:center;gap:12px;cursor:pointer;">
            <input type="checkbox" v-model="notifications.marketing" class="check">
            <span style="color:#cbd5e1;">Marketing updates</span>
          </label>
        </div>
      </div>

      <div class="card subscription-card" style="margin-bottom:20px;">
        <h3 style="font-weight:600;margin-bottom:20px;display:flex;align-items:center;gap:8px;">💳 Subscription Management</h3>
        <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:16px;margin-bottom:20px;">
          <div>
            <div style="font-size:24px;font-weight:bold;color:#22d3ee;">Professional Plan</div>
            <div style="color:#64748b;font-size:14px;">$99/month • Renews Jan 15, 2025</div>
          </div>
          <span class="badge" style="background:rgba(16,185,129,0.2);color:#10b981;">Active</span>
        </div>
        <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:12px;margin-bottom:20px;">
          <div style="padding:16px;background:rgba(15,23,42,0.5);border-radius:12px;">
            <div style="font-size:12px;color:#64748b;margin-bottom:4px;">Queries This Month</div>
            <div style="font-size:20px;font-weight:bold;">{{ stats.queries }} / 10,000</div>
          </div>
          <div style="padding:16px;background:rgba(15,23,42,0.5);border-radius:12px;">
            <div style="font-size:12px;color:#64748b;margin-bottom:4px;">Next Billing Date</div>
            <div style="font-size:20px;font-weight:bold;">Jan 15, 2025</div>
          </div>
        </div>
        <div style="display:flex;gap:12px;flex-wrap:wrap;">
          <button @click="openStripePortal('billing')" class="btn">Manage Billing</button>
          <button @click="openStripePortal('payment')" class="btn-secondary">Update Payment Method</button>
          <button @click="openStripePortal('invoices')" class="btn-secondary">View Invoices</button>
        </div>
        <div style="margin-top:16px;padding-top:16px;border-top:1px solid rgba(71,85,105,0.3);">
          <div style="display:flex;align-items:center;gap:8px;margin-bottom:12px;">
            <span style="font-size:16px;">📊</span>
            <span style="font-weight:600;">Plan Comparison</span>
          </div>
          <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;">
            <div style="padding:12px;background:rgba(15,23,42,0.5);border-radius:10px;border:1px solid rgba(71,85,105,0.3);text-align:center;">
              <div style="font-weight:600;font-size:14px;color:#cbd5e1;margin-bottom:4px;">Starter</div>
              <div style="font-size:18px;font-weight:bold;margin-bottom:4px;">$0<span style="font-size:12px;color:#64748b;">/mo</span></div>
              <div style="font-size:11px;color:#64748b;">100/mo</div>
            </div>
            <div style="padding:12px;background:rgba(34,211,238,0.1);border-radius:10px;border:1px solid rgba(34,211,238,0.5);text-align:center;">
              <div style="font-weight:600;font-size:14px;color:#22d3ee;margin-bottom:4px;">Professional</div>
              <div style="font-size:18px;font-weight:bold;margin-bottom:4px;">$99<span style="font-size:12px;color:#64748b;">/mo</span></div>
              <div style="font-size:11px;color:#64748b;">10,000/mo</div>
              <div style="font-size:10px;color:#22d3ee;margin-top:4px;">Current Plan</div>
            </div>
            <div style="padding:12px;background:rgba(15,23,42,0.5);border-radius:10px;border:1px solid rgba(71,85,105,0.3);text-align:center;">
              <div style="font-weight:600;font-size:14px;color:#cbd5e1;margin-bottom:4px;">Enterprise</div>
              <div style="font-size:18px;font-weight:bold;margin-bottom:4px;">$299<span style="font-size:12px;color:#64748b;">/mo</span></div>
              <div style="font-size:11px;color:#64748b;">Unlimited</div>
              <button @click="openUpgradeModal" style="margin-top:8px;padding:4px 12px;background:linear-gradient(135deg,#0ea5e9,#06b6d4);border:none;border-radius:6px;color:#fff;font-size:11px;cursor:pointer;">Upgrade</button>
            </div>
          </div>
        </div>
      </div>

      <div class="card danger-card">
        <h3 style="font-weight:600;margin-bottom:16px;color:#f87171;">Danger Zone</h3>
        <div style="display:flex;flex-direction:column;gap:16px;">
          <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;">
            <div>
              <div style="color:#cbd5e1;">Cancel Subscription</div>
              <div style="color:#64748b;font-size:13px;">Your plan will remain active until the end of the billing period</div>
            </div>
            <button @click="cancelSubscription" style="background:rgba(251,191,36,0.2);border:1px solid rgba(251,191,36,0.5);color:#fbbf24;padding:10px 20px;border-radius:10px;cursor:pointer;font-weight:500;">Cancel Plan</button>
          </div>
          <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;">
            <div>
              <div style="color:#cbd5e1;">Delete Account</div>
              <div style="color:#64748b;font-size:13px;">Permanently delete your account and all data</div>
            </div>
            <button @click="confirmDeleteAccount" style="background:rgba(239,68,68,0.2);border:1px solid rgba(239,68,68,0.5);color:#f87171;padding:10px 20px;border-radius:10px;cursor:pointer;font-weight:500;">Delete Account</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Billing modal -->
    <div v-if="modal.open" class="modal-overlay" @click.self="closeModal">
      <div class="modal">
        <div class="modal-head">
          <h3>{{ modalTitle }}</h3>
          <button class="ghost-btn" @click="closeModal" aria-label="Close">×</button>
        </div>

        <div v-if="modal.type === 'billing'" class="modal-body">
          <div class="panel">
            <div class="panel-row">
              <span class="muted">Current Plan</span>
              <span class="badge success">Active</span>
            </div>
            <div class="panel-title">Professional Plan</div>
            <div class="muted"> $99.00 USD / month</div>
          </div>

          <div>
            <h4 class="section-head">Billing Details</h4>
            <div class="panel list">
              <div class="panel-row">
                <span class="muted">Billing Email</span>
                <span>{{ form.email }}</span>
              </div>
              <div class="panel-row">
                <span class="muted">Billing Cycle</span>
                <span>Monthly</span>
              </div>
              <div class="panel-row">
                <span class="muted">Next Payment</span>
                <span>Jan 15, 2025</span>
              </div>
              <div class="panel-row">
                <span class="muted">Amount</span>
                <span class="accent">$99.00 USD</span>
              </div>
            </div>
          </div>

          <div class="modal-actions">
            <button class="btn" @click="closeModal" style="flex:1;">Done</button>
            <button class="btn-secondary" @click="openStripePortal('payment')" style="flex:1;">Update Payment →</button>
          </div>
        </div>

        <div v-else-if="modal.type === 'payment'" class="modal-body">
          <div class="panel">
            <div class="panel-row card-row">
              <div class="card-chip">VISA</div>
              <div>
                <div>•••• •••• •••• 4242</div>
                <div class="muted fine">Expires 12/2026</div>
              </div>
              <span class="badge success" style="margin-left:auto;">Default</span>
            </div>
          </div>

          <div>
            <h4 class="section-head">Add New Card</h4>
            <div class="form-grid">
              <div>
                <label class="label">Card Number</label>
                <input type="text" class="input dark-input" placeholder="1234 5678 9012 3456" maxlength="19">
              </div>
              <div class="grid-2">
                <div>
                  <label class="label">Expiry Date</label>
                  <input type="text" class="input dark-input" placeholder="MM/YY" maxlength="5">
                </div>
                <div>
                  <label class="label">CVC</label>
                  <input type="text" class="input dark-input" placeholder="123" maxlength="4">
                </div>
              </div>
              <div>
                <label class="label">Name on Card</label>
                <input type="text" class="input dark-input" placeholder="John Doe">
              </div>
            </div>
          </div>

          <div class="modal-actions">
            <button class="btn-secondary" @click="closeModal" style="flex:1;">Cancel</button>
            <button class="btn" @click="savePaymentMethod" style="flex:1;">Save Card</button>
          </div>
          <div class="muted fine secure">🔒 Secured by Stripe</div>
        </div>

        <div v-else-if="modal.type === 'invoices'" class="modal-body">
          <div class="panel-row" style="margin-bottom:12px;">
            <span class="muted fine">Showing last {{ invoices.length }} invoices</span>
            <button class="btn-secondary" @click="exportAllInvoices" style="padding:8px 12px;font-size:12px;">⬇ Export All</button>
          </div>
          <div class="invoice-list">
            <div v-for="inv in invoices" :key="inv.id" class="invoice-row">
              <div>
                <div>{{ inv.id }}</div>
                <div class="muted fine">{{ inv.date }}</div>
              </div>
              <div class="invoice-right">
                <span class="accent">{{ inv.amount }}</span>
                <span class="badge success">{{ inv.status }}</span>
                <button class="ghost-btn" @click="downloadInvoice(inv.id)" aria-label="Download">⬇</button>
              </div>
            </div>
          </div>
          <div class="modal-actions">
            <button class="btn" @click="closeModal" style="flex:1;">Close</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive, computed, ref, onMounted } from 'vue'
import axios from 'axios'

const form = reactive({
  name: 'Claudino Nelson',
  email: 'claudino@shemaksolutions.ca',
  company: 'Shema K Solutions',
})

const notifications = reactive({
  safetyAlerts: true,
  weeklySummary: true,
  marketing: false,
})

const stats = reactive({
  queries: '2,847',
})

const saving = ref(false)
const status = ref('')

const modal = reactive({
  open: false,
  type: '',
})

const invoices = reactive([
  { id: 'INV-2024-012', date: 'Dec 15, 2024', amount: '$99.00', status: 'Paid' },
  { id: 'INV-2024-011', date: 'Nov 15, 2024', amount: '$99.00', status: 'Paid' },
  { id: 'INV-2024-010', date: 'Oct 15, 2024', amount: '$99.00', status: 'Paid' },
  { id: 'INV-2024-009', date: 'Sep 15, 2024', amount: '$99.00', status: 'Paid' },
  { id: 'INV-2024-008', date: 'Aug 15, 2024', amount: '$99.00', status: 'Paid' },
  { id: 'INV-2024-007', date: 'Jul 15, 2024', amount: '$99.00', status: 'Paid' },
])

const modalTitle = computed(() => {
  if (modal.type === 'billing') return '💳 Manage Billing'
  if (modal.type === 'payment') return '💳 Update Payment Method'
  if (modal.type === 'invoices') return '📄 Invoice History'
  return ''
})

onMounted(loadAccount)

async function loadAccount() {
  try {
    const { data } = await axios.get('/api/account')
    form.name = data?.name || form.name
    form.email = data?.email || form.email
    form.company = data?.company || form.company
  } catch (error) {
    console.error('Failed to load account', error)
  }
}

function saveSettings() {
  saving.value = true
  status.value = ''
  axios
    .put('/api/account', {
      name: form.name,
      company: form.company,
    })
    .then(({ data }) => {
      form.name = data?.name || form.name
      form.company = data?.company || form.company
      status.value = 'Account updated.'
    })
    .catch((error) => {
      console.error('Save failed', error)
      status.value = 'Could not save changes.'
    })
    .finally(() => {
      saving.value = false
    })
}

function openStripePortal(action) {
  modal.type = action
  modal.open = true
}

function openUpgradeModal() {
  console.log('Open upgrade modal')
}

function cancelSubscription() {
  console.log('Cancel subscription')
}

function confirmDeleteAccount() {
  console.log('Delete account')
}

function closeModal() {
  modal.open = false
}

function savePaymentMethod() {
  console.log('Save card')
  modal.open = false
}

function exportAllInvoices() {
  console.log('Export invoices')
}

function downloadInvoice(id) {
  console.log('Download invoice', id)
}
</script>

<style scoped>
.settings-page {
  flex: 1;
  overflow: auto;
  padding: 24px;
  background: radial-gradient(circle at 20% 20%, rgba(34, 211, 238, 0.08), transparent 30%),
    radial-gradient(circle at 80% 10%, rgba(59, 130, 246, 0.08), transparent 25%),
    #0f172a;
}

.settings-inner {
  max-width: 880px;
  margin: 0 auto;
  display: flex;
  flex-direction: column;
  gap: 18px;
}

.dark-card {
  background: linear-gradient(135deg, rgba(22, 30, 49, 0.96), rgba(13, 19, 35, 0.95));
  border: 1px solid rgba(71, 85, 105, 0.4);
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.35);
  border-radius: 14px;
}

.subscription-card {
  background: linear-gradient(145deg, rgba(9, 26, 41, 0.95), rgba(7, 20, 34, 0.95));
  border: 1px solid rgba(34, 211, 238, 0.35);
  box-shadow: 0 18px 55px rgba(34, 211, 238, 0.18);
  border-radius: 14px;
}

.danger-card {
  background: linear-gradient(135deg, rgba(26, 32, 44, 0.95), rgba(18, 24, 34, 0.95));
  border: 1px solid rgba(248, 113, 113, 0.35);
  border-radius: 14px;
}

.dark-input {
  background: rgba(15, 23, 42, 0.8);
  border: 1px solid rgba(148, 163, 184, 0.4);
  color: #e2e8f0;
  border-radius: 10px;
}

.dark-input:focus {
  outline: none;
  border-color: #22d3ee;
  box-shadow: 0 0 0 3px rgba(34, 211, 238, 0.15);
}

.dark-input[disabled] {
  opacity: 0.6;
  cursor: not-allowed;
}

.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.55);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 16px;
  z-index: 5000;
}

.modal {
  background: linear-gradient(145deg, rgba(14, 22, 38, 0.98), rgba(9, 14, 24, 0.95));
  border: 1px solid rgba(71, 85, 105, 0.4);
  border-radius: 14px;
  padding: 18px;
  width: 100%;
  max-width: 640px;
  box-shadow: 0 30px 80px rgba(0, 0, 0, 0.55);
}

.modal-head {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 12px;
}

.modal-body {
  display: flex;
  flex-direction: column;
  gap: 14px;
}

.ghost-btn {
  background: none;
  border: none;
  color: #cbd5e1;
  cursor: pointer;
  font-size: 18px;
}

.panel {
  background: rgba(15, 23, 42, 0.7);
  border: 1px solid rgba(71, 85, 105, 0.35);
  border-radius: 12px;
  padding: 14px;
}

.panel-title {
  font-weight: 700;
  font-size: 18px;
  color: #e2e8f0;
}

.panel-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 10px;
}

.panel.list .panel-row + .panel-row {
  margin-top: 8px;
  padding-top: 8px;
  border-top: 1px solid rgba(71, 85, 105, 0.25);
}

.card-row {
  gap: 12px;
}

.card-chip {
  width: 56px;
  height: 36px;
  background: linear-gradient(135deg, #1a1f71, #00579f);
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #fff;
  font-weight: 700;
  font-size: 11px;
}

.modal-actions {
  display: flex;
  gap: 10px;
  margin-top: 6px;
  flex-wrap: wrap;
}

.muted {
  color: #94a3b8;
}

.fine {
  font-size: 12px;
}

.section-head {
  margin: 0 0 8px;
  color: #cbd5e1;
  font-weight: 600;
}

.accent {
  color: #22d3ee;
  font-weight: 700;
}

.check {
  appearance: none;
  width: 16px;
  height: 16px;
  border: 2px solid #199cd9;
  border-radius: 2px;
  background: #0f172a;
  display: inline-block;
  position: relative;
  cursor: pointer;
  box-shadow: 0 0 0 1px rgba(25, 156, 217, 0.25);
  transition: background 0.15s ease, border-color 0.15s ease, box-shadow 0.15s ease;
}

.check:checked {
  background: #0ea5e9;
  border-color: #0ea5e9;
  box-shadow: 0 0 0 1px rgba(14, 165, 233, 0.45);
}

.check:checked::after {
  content: '✓';
  color: #fff;
  font-size: 12px;
  line-height: 14px;
  position: absolute;
  left: 2px;
  top: -1px;
}

.check:focus-visible {
  outline: none;
  box-shadow: 0 0 0 2px rgba(14, 165, 233, 0.55);
}

.badge.success {
  background: rgba(16, 185, 129, 0.2);
  color: #10b981;
  border-radius: 999px;
  padding: 4px 10px;
  font-size: 11px;
  font-weight: 700;
}

.form-grid {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.grid-2 {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 12px;
}

.secure {
  text-align: center;
  margin-top: 4px;
}

.invoice-list {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.invoice-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 12px;
  background: rgba(15, 23, 42, 0.5);
  border-radius: 10px;
  border: 1px solid rgba(71, 85, 105, 0.3);
}

.invoice-right {
  display: flex;
  align-items: center;
  gap: 10px;
}
</style>
