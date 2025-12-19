<template>
  <div class="settings-page" id="main-content">
    <div v-if="toasts.length" class="toast-stack">
      <div v-for="toast in toasts" :key="toast.id" class="toast" :class="toast.type">
        {{ toast.message }}
      </div>
    </div>
    <div class="settings-inner">
      <div class="card dark-card" style="margin-bottom:20px;">
        <h3 style="font-weight:600;margin-bottom:20px;">Account Settings</h3>
        <div style="display:flex;flex-direction:column;gap:16px;">
          <div>
            <label class="label">Name</label>
            <input type="text" v-model="form.name" class="input dark-input" id="settings-name" />
          </div>
          <div>
            <label class="label">Email</label>
            <input type="email" v-model="form.email" class="input dark-input" id="settings-email" disabled />
          </div>
          <div>
            <label class="label">Company</label>
            <input type="text" v-model="form.company" class="input dark-input" id="settings-company" />
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
            <input type="checkbox" v-model="notifications.safetyAlerts" class="check" />
            <span style="color:#cbd5e1;">Email notifications for new safety alerts</span>
          </label>
          <label style="display:flex;align-items:center;gap:12px;cursor:pointer;">
            <input type="checkbox" v-model="notifications.weeklySummary" class="check" />
            <span style="color:#cbd5e1;">Weekly usage summary</span>
          </label>
          <label style="display:flex;align-items:center;gap:12px;cursor:pointer;">
            <input type="checkbox" v-model="notifications.marketing" class="check" />
            <span style="color:#cbd5e1;">Marketing updates</span>
          </label>
        </div>
      </div>

      <div class="card subscription-card" style="margin-bottom:20px;">
        <h3 style="font-weight:600;margin-bottom:20px;display:flex;align-items:center;gap:8px;">Subscription Management</h3>
        <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:16px;margin-bottom:20px;">
          <div>
            <div style="font-size:24px;font-weight:bold;color:#22d3ee;">{{ subscription.name }}</div>
            <div style="color:#64748b;font-size:14px;">
              {{ subscription.priceFormatted }} / {{ subscription.interval }}
              <span v-if="subscription.renewsAt">- Renews {{ formatDate(subscription.renewsAt) }}</span>
              <span v-else-if="subscription.cancelAt">- Ends {{ formatDate(subscription.cancelAt) }}</span>
              <span v-else>- Not scheduled</span>
            </div>
          </div>
          <span
            class="badge"
            :style="subscription.onGracePeriod ? 'background:rgba(251,191,36,0.2);color:#fbbf24;' : 'background:rgba(16,185,129,0.2);color:#10b981;'"
          >
            {{ subscriptionBadge }}
          </span>
        </div>
        <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:12px;margin-bottom:20px;">
          <div style="padding:16px;background:rgba(15,23,42,0.5);border-radius:12px;">
            <div style="font-size:12px;color:#64748b;margin-bottom:4px;">Queries This Month</div>
            <div style="font-size:20px;font-weight:bold;">{{ formatNumber(stats.queriesUsed) }} / {{ formatNumber(stats.queriesLimit) }}</div>
          </div>
          <div style="padding:16px;background:rgba(15,23,42,0.5);border-radius:12px;">
            <div style="font-size:12px;color:#64748b;margin-bottom:4px;">Next Billing Date</div>
            <div style="font-size:20px;font-weight:bold;">{{ stats.nextBillingDate ? formatDate(stats.nextBillingDate) : '-' }}</div>
          </div>
        </div>
        <div style="display:flex;gap:12px;flex-wrap:wrap;">
          <button @click="openStripePortal('billing')" class="btn" :disabled="portalLoading || loadingSubscription">Manage Billing</button>
          <button @click="openStripePortal('invoices')" class="btn-secondary">View Invoices</button>
        </div>
        <div style="margin-top:16px;padding-top:16px;border-top:1px solid rgba(71,85,105,0.3);">
          <div style="display:flex;align-items:center;gap:8px;margin-bottom:12px;">
            <span style="font-size:16px;">Plans</span>
            <span style="font-weight:600;">Plan Comparison</span>
          </div>
          <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;">
            <div
              v-for="plan in availablePlans"
              :key="plan.price_id || plan.name"
              :style="`padding:12px;border-radius:10px;text-align:center;border:1px solid ${plan.price_id === subscription.priceId ? 'rgba(34,211,238,0.5)' : 'rgba(71,85,105,0.3)'};background:${plan.price_id === subscription.priceId ? 'rgba(34,211,238,0.1)' : 'rgba(15,23,42,0.5)'}`"
            >
              <div
                :style="`font-weight:600;font-size:14px;margin-bottom:4px;color:${plan.price_id === subscription.priceId ? '#22d3ee' : '#cbd5e1'}`"
              >
                {{ plan.name }}
              </div>
              <div style="font-size:18px;font-weight:bold;margin-bottom:4px;">
                {{ plan.amount_formatted || '$0.00' }}<span style="font-size:12px;color:#64748b;">/mo</span>
              </div>
              <div style="font-size:11px;color:#64748b;">{{ plan.query_limit ? formatNumber(plan.query_limit) + '/mo' : 'Unlimited' }}</div>
              <div style="font-size:11px;color:#64748b;">{{ formatTeamLimit(plan) }}</div>
              <div v-if="plan.price_id === subscription.priceId" style="font-size:10px;color:#22d3ee;margin-top:4px;">Current Plan</div>
              <button
                v-else
                @click="openPaymentForPlan(plan)"
                :disabled="!plan.price_id || plan.price_id === subscription.priceId"
                :style="{
                  marginTop: '8px',
                  padding: '4px 12px',
                  background: 'linear-gradient(135deg,#0ea5e9,#06b6d4)',
                  border: 'none',
                  borderRadius: '6px',
                  color: '#fff',
                  fontSize: '11px',
                  cursor: 'pointer',
                  opacity: !plan.price_id || plan.price_id === subscription.priceId ? '0.6' : '1',
                }"
              >
                {{ plan.price_id ? 'Choose' : 'Set price id' }}
              </button>
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

    <!-- Billing / Invoices modal -->
    <div v-if="modal.open" class="modal-overlay" @click.self="closeModal">
      <div class="modal">
        <div class="modal-head">
          <h3>{{ modalTitle }}</h3>
          <button class="ghost-btn" @click="closeModal" aria-label="Close">X</button>
        </div>

        <div v-if="modal.type === 'billing'" class="modal-body">
          <div class="panel">
            <div class="panel-row">
              <span class="muted">Current Plan</span>
              <span class="badge success">{{ subscriptionBadge }}</span>
            </div>
            <div class="panel-title">{{ subscription.name }}</div>
            <div class="muted">{{ subscription.priceFormatted }} / {{ subscription.interval }}</div>
          </div>

          <div v-if="selectedPlan.priceId" class="panel" style="border-color:rgba(34,211,238,0.35);">
            <div class="panel-row">
              <span class="muted">Selected Plan</span>
              <span class="badge success" style="background:rgba(34,211,238,0.15);color:#22d3ee;">Pending Checkout</span>
            </div>
            <div class="panel-title">{{ selectedPlan.name }}</div>
            <div class="muted">{{ selectedPlan.amountFormatted || 'Monthly' }}</div>
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
                <span>{{ subscription.interval }}</span>
              </div>
              <div class="panel-row">
                <span class="muted">Next Payment</span>
                <span>{{ subscription.renewsAt ? formatDate(subscription.renewsAt) : 'Not scheduled' }}</span>
              </div>
              <div class="panel-row">
                <span class="muted">Amount</span>
                <span class="accent">{{ subscription.priceFormatted }}</span>
              </div>
            </div>
          </div>

          <div class="modal-actions">
            <button
              v-if="selectedPlan.priceId"
              class="btn"
              @click="startCheckout(selectedPlan.priceId)"
              :disabled="portalLoading"
              style="flex:1;"
            >
              Subscribe Monthly
            </button>
            <button
              v-else
              class="btn"
              @click="visitPortal"
              :disabled="portalLoading"
              style="flex:1;"
            >
              Open Stripe Portal
            </button>
            <button class="btn-secondary" @click="closeModal" style="flex:1;">Close</button>
          </div>
          <span v-if="portalError" class="muted fine">{{ portalError }}</span>
        </div>

        <div v-else-if="modal.type === 'invoices'" class="modal-body">
          <div class="panel-row" style="margin-bottom:12px;">
            <span class="muted fine">Showing {{ invoices.length }} invoices</span>
            <div style="display:flex;gap:8px;">
              <button class="btn-secondary" @click="exportAllInvoices" style="padding:8px 12px;font-size:12px;">Export All</button>
            </div>
          </div>
          <div class="invoice-list">
            <div v-for="inv in invoices" :key="inv.id" class="invoice-row" @click="downloadInvoice(inv.id)" style="cursor:pointer;">
              <div>
                <div>{{ inv.id }}</div>
                <div class="muted fine">{{ formatDate(inv.date) }}</div>
              </div>
              <div class="invoice-right">
                <span class="accent">{{ inv.amount }}</span>
                <span class="badge success">{{ inv.status }}</span>
                <button class="ghost-btn" @click.stop="downloadInvoice(inv.id)" aria-label="Download">Download</button>
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
import { reactive, computed, ref, onMounted } from "vue"
import axios from "axios"

const form = reactive({
  name: "Claudino Nelson",
  email: "claudino@shemaksolutions.ca",
  company: "Shema K Solutions",
})

const notifications = reactive({
  safetyAlerts: true,
  weeklySummary: true,
  marketing: false,
})

const subscription = reactive({
  name: "Professional Plan",
  priceFormatted: "$99.00",
  interval: "month",
  status: "inactive",
  renewsAt: "",
  cancelAt: "",
  onGracePeriod: false,
  isActive: false,
  priceId: "",
})

const stats = reactive({
  queriesUsed: 0,
  queriesLimit: 10000,
  nextBillingDate: "",
})

const invoices = reactive([])
const availablePlans = ref([])

const saving = ref(false)
const status = ref("")
const cardError = ref("")
const portalUrl = ref("")
const portalError = ref("")
const portalLoading = ref(false)
const loadingSubscription = ref(false)
const toasts = ref([])

const cardForm = reactive({
  number: "",
  expiry: "",
  cvc: "",
  name: "",
})

const selectedPlan = reactive({
  priceId: "",
  name: "",
})

const modal = reactive({
  open: false,
  type: "",
})

const modalTitle = computed(() => {
  if (modal.type === "billing") return "Manage Billing"
  if (modal.type === "invoices") return "Invoice History"
  return ""
})

const subscriptionBadge = computed(() => {
  if (subscription.onGracePeriod) return "Cancels soon"
  if (subscription.status === "active") return "Active"
  if (subscription.status === "trialing") return "Trialing"
  return "Inactive"
})

onMounted(() => {
  loadAccount()
  loadSubscription()
  handleCheckoutReturn()
})

async function loadAccount() {
  try {
    const { data } = await axios.get("/api/account")
    form.name = data?.name || form.name
    form.email = data?.email || form.email
    form.company = data?.company || form.company
  } catch (error) {
    console.error("Failed to load account", error)
  }
}

async function loadSubscription() {
  loadingSubscription.value = true
  portalError.value = ""
  try {
    const { data } = await axios.get("/api/subscription")
    applySubscription(data?.subscription)
    stats.queriesUsed = data?.usage?.queries_used ?? stats.queriesUsed
    stats.queriesLimit = data?.usage?.queries_limit ?? stats.queriesLimit
    stats.nextBillingDate = data?.subscription?.renews_at || data?.subscription?.cancel_at || ""
    availablePlans.value = data?.plans || []
    if (!availablePlans.value.length) {
      availablePlans.value = [
        { name: "Starter", amount_formatted: "$0.00", query_limit: 100, team_limit: 0, price_id: "price_dummy_starter" },
        { name: "Professional", amount_formatted: "$99.00", query_limit: 10000, team_limit: 5, price_id: "price_dummy_pro" },
        { name: "Enterprise", amount_formatted: "$299.00", query_limit: null, team_limit: null, price_id: "price_dummy_enterprise" },
      ]
    }
    invoices.splice(0, invoices.length, ...(data?.invoices || []))
    form.email = data?.customer?.email || form.email
  } catch (error) {
    console.error("Failed to load subscription", error)
  } finally {
    loadingSubscription.value = false
  }
}

function applySubscription(payload) {
  if (!payload) return
  subscription.name = payload.name || subscription.name
  subscription.priceFormatted = payload.amount_formatted || subscription.priceFormatted
  subscription.interval = payload.interval || subscription.interval
  subscription.status = payload.status || subscription.status
  subscription.renewsAt = payload.renews_at || ""
  subscription.cancelAt = payload.cancel_at || ""
  subscription.onGracePeriod = payload.on_grace_period || false
  subscription.isActive = payload.is_active ?? subscription.isActive
  subscription.priceId = payload.price_id || subscription.priceId
}

function saveSettings() {
  saving.value = true
  status.value = ""
  axios
    .put("/api/account", {
      name: form.name,
      company: form.company,
    })
    .then(({ data }) => {
      form.name = data?.name || form.name
      form.company = data?.company || form.company
      status.value = "Account updated."
      pushToast(status.value, "success")
    })
    .catch((error) => {
      console.error("Save failed", error)
      status.value = errorMessage(error)
      pushToast(status.value, "error")
    })
    .finally(() => {
      saving.value = false
    })
}

async function ensurePortalUrl() {
  if (portalUrl.value) return portalUrl.value
  portalError.value = ""
  portalLoading.value = true
  try {
    const { data } = await axios.post("/api/subscription/portal", {
      return_url: window.location.origin + "/settings",
    })
    portalUrl.value = data?.url || ""
    return portalUrl.value
  } catch (error) {
    const apiMessage = error?.response?.data?.message
    portalError.value = apiMessage || "Could not open Stripe billing portal."
    console.error("Portal error", error)
    throw error
  } finally {
    portalLoading.value = false
  }
}

async function openStripePortal(action) {
  modal.type = action
  modal.open = true
  cardError.value = ""
  selectedPlan.priceId = ""
  selectedPlan.name = ""
  if (action === "billing") {
    try {
      await ensurePortalUrl()
    } catch (e) {
      // handled in ensurePortalUrl
    }
  }
  if (action === "invoices") {
    await loadInvoices()
  }
}

function openPaymentForPlan(plan) {
  selectedPlan.priceId = plan.price_id
  selectedPlan.name = plan.name
  selectedPlan.amountFormatted = plan.amount_formatted || ''
  cardError.value = ""
  modal.type = "billing"
  modal.open = true
  if (plan.price_id) {
    // ensure billing portal is ready in case user wants to switch inside portal
    ensurePortalUrl().catch(() => {})
  }
}

async function visitPortal() {
  const url = await ensurePortalUrl()
  if (url) {
    window.location.href = url
  }
}

async function loadInvoices() {
  try {
    const { data } = await axios.get("/api/subscription/invoices")
    invoices.splice(0, invoices.length, ...(data?.invoices || []))
  } catch (error) {
    console.error("Failed to load invoices", error)
  }
}

async function startCheckout(priceId) {
  const targetPrice = priceId || subscription.priceId
  if (!targetPrice) {
    status.value = "No Stripe price is configured yet."
    return
  }

  try {
    const origin = window.location.origin || `${window.location.protocol}//${window.location.host}`
    const successUrl = new URL("/settings?checkout=success&session_id={CHECKOUT_SESSION_ID}", origin).toString()
    const cancelUrl = new URL("/settings?checkout=cancelled", origin).toString()
    const { data } = await axios.post("/api/subscription/checkout", {
      price_id: targetPrice,
    })

    if (data?.url) {
      window.location.href = data.url
    }
  } catch (error) {
    console.error("Checkout error", error)
    status.value = "Unable to start checkout."
  }
}

async function cancelSubscription() {
  if (!confirm("Cancel your subscription at the end of the current billing period?")) return
  try {
    const { data } = await axios.post("/api/subscription/cancel")
    applySubscription(data?.subscription)
    status.value = data?.message || "Subscription updated."
  } catch (error) {
    console.error("Cancel failed", error)
    status.value = "Could not cancel subscription."
  }
}

function confirmDeleteAccount() {
  console.log("Delete account")
}

function closeModal() {
  modal.open = false
}

async function savePaymentMethod() {
  cardError.value = ""
  if (selectedPlan.priceId) {
    if (!validateCardForm()) return
    await startCheckout(selectedPlan.priceId)
  } else {
    await visitPortal()
  }
  modal.open = false
}

function exportAllInvoices() {
  axios
    .get("/api/subscription/invoices/export", { responseType: "blob" })
    .then((response) => {
      const blob = new Blob([response.data], { type: "text/csv" })
      const url = window.URL.createObjectURL(blob)
      const a = document.createElement("a")
      a.href = url
      a.download = "invoices.csv"
      a.click()
      window.URL.revokeObjectURL(url)
    })
    .catch((error) => {
      console.error("Failed to export invoices", error)
      status.value = "Could not export invoices."
    })
}

function downloadInvoice(id) {
  const invoice = invoices.find((inv) => inv.id === id)
  const target = invoice?.invoice_pdf || invoice?.hosted_invoice_url
  if (target) {
    window.open(target, "_blank", "noopener")
  }
}

function formatNumber(value) {
  if (value === null || value === undefined) return "0"
  return Number(value).toLocaleString()
}

function formatTeamLimit(plan) {
  if (!plan) return "Invites: -"
  if (plan.team_limit === null || plan.team_limit === undefined) return "Invites: Unlimited"
  if (plan.team_limit === 0) return "Invites: 0"
  return `Invites: Up to ${plan.team_limit}`
}

function formatDate(date) {
  if (!date) return "?"
  const parsed = new Date(date)
  if (Number.isNaN(parsed.getTime())) return date
  return parsed.toLocaleDateString(undefined, { year: "numeric", month: "short", day: "numeric" })
}

function handleCheckoutReturn() {
  const params = new URLSearchParams(window.location.search)
  const checkout = params.get("checkout")
  if (checkout === "success") {
    status.value = "Payment succeeded. Updating your plan..."
    loadSubscription()
    params.delete("checkout")
    params.delete("session_id")
    window.history.replaceState({}, "", `${window.location.pathname}${params.toString() ? "?" + params.toString() : ""}`)
  }
}

function validateCardForm() {
  const numberDigits = cardForm.number.replace(/\D/g, "")
  const expiryDigits = cardForm.expiry.replace(/\D/g, "")
  const cvcDigits = cardForm.cvc.replace(/\D/g, "")

  if (numberDigits.length < 13 || numberDigits.length > 19) {
    cardError.value = "Enter a valid card number."
    return false
  }

  if (expiryDigits.length !== 4) {
    cardError.value = "Enter expiry as MM/YY."
    return false
  }

  if (cvcDigits.length < 3 || cvcDigits.length > 4) {
    cardError.value = "Enter a valid CVC."
    return false
  }

  if (!cardForm.name.trim()) {
    cardError.value = "Cardholder name is required."
    return false
  }

  return true
}

function handleCardNumberInput(event) {
  const digits = event.target.value.replace(/\D/g, "").slice(0, 19)
  const parts = digits.match(/.{1,4}/g) || []
  cardForm.number = parts.join(" ")
}

function handleExpiryInput(event) {
  const digits = event.target.value.replace(/\D/g, "").slice(0, 4)
  if (digits.length >= 3) {
    cardForm.expiry = `${digits.slice(0, 2)}/${digits.slice(2)}`
  } else if (digits.length >= 1) {
    cardForm.expiry = digits
  } else {
    cardForm.expiry = ""
  }
}

function handleCvcInput(event) {
  cardForm.cvc = event.target.value.replace(/\D/g, "").slice(0, 4)
}

function errorMessage(error) {
  if (error?.response?.data?.errors) {
    const first = Object.values(error.response.data.errors)[0]
    if (Array.isArray(first) && first.length) return first[0]
  }
  if (error?.response?.data?.message) return error.response.data.message
  if (error?.message) return error.message
  return "Could not save changes."
}

function pushToast(message, type = "success") {
  const id = Date.now() + Math.random()
  toasts.value.push({ id, message, type })
  setTimeout(() => {
    toasts.value = toasts.value.filter((t) => t.id !== id)
  }, 3500)
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
  content: '✔';
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

.secure {
  text-align: center;
  margin-top: 4px;
}

.invoice-list {
  display: flex;
  flex-direction: column;
  gap: 10px;
  max-height: 320px;
  overflow-y: auto;
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
</style>
