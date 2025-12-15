<template>
  <div
    v-if="state.open"
    id="auth-modal"
    class="modal-overlay"
    @click.self="close"
    style="position:fixed;inset:0;background:rgba(0,0,0,0.9);backdrop-filter:blur(8px);display:flex;align-items:center;justify-content:center;z-index:200;padding:24px;"
  >
    <div class="glass-card animate-scale-in" style="width:100%;max-width:420px;padding:40px;">
      <div style="text-align:center;margin-bottom:32px;">
        <div class="animate-float" style="width:64px;height:64px;margin:0 auto 16px;">
          <svg viewBox="0 0 100 100" style="width:100%;height:100%;">
            <defs>
              <linearGradient id="authCrown" x1="0%" y1="0%" x2="100%" y2="100%">
                <stop offset="0%" style="stop-color:#0ea5e9" />
                <stop offset="100%" style="stop-color:#22d3ee" />
              </linearGradient>
            </defs>
            <path
              d="M15 75 L15 45 L30 55 L50 25 L70 55 L85 45 L85 75 Z"
              fill="url(#authCrown)"
              stroke="#38bdf8"
              stroke-width="2"
            />
            <circle cx="50" cy="30" r="6" fill="#fbbf24" />
            <rect x="20" y="75" width="60" height="8" rx="2" fill="url(#authCrown)" />
          </svg>
        </div>
        <h2 style="font-size:26px;font-weight:800;">
          Welcome to <span style="color:#22d3ee;">K.I.N.G.</span>
        </h2>
      </div>

      <div style="display:flex;margin-bottom:24px;background:rgba(51,65,85,0.5);border-radius:12px;padding:4px;">
        <button
          :style="tabStyle('login')"
          @click="switchMode('login')"
          style="flex:1;padding:12px;border-radius:10px;border:none;font-size:14px;font-weight:600;cursor:pointer;"
        >
          Login
        </button>
        <button
          :style="tabStyle('register')"
          @click="switchMode('register')"
          style="flex:1;padding:12px;border-radius:10px;border:none;font-size:14px;font-weight:600;cursor:pointer;"
        >
          Register
        </button>
      </div>

      <form @submit.prevent="submit">
        <div v-if="state.globalError" style="background:#7f1d1d;color:#fff;padding:12px;border-radius:8px;margin-bottom:12px;font-size:14px;">
          {{ state.globalError }}
        </div>

        <div style="display:flex;flex-direction:column;gap:16px;">
          <div>
            <label class="label" style="color:#cbd5e1;">Email</label>
            <input
              v-model="state.form.email"
              type="email"
              name="email"
              class="input"
              placeholder="you@company.com"
              required
            />
            <small class="error-text" style="color:#ef4444;" v-if="fieldError('email')">{{ fieldError('email') }}</small>
          </div>

          <div>
            <label class="label" style="color:#cbd5e1;">Password</label>
            <input
              v-model="state.form.password"
              type="password"
              name="password"
              class="input"
              placeholder="••••••••"
              required
            />
            <small class="error-text" style="color:#ef4444;" v-if="fieldError('password')">{{ fieldError('password') }}</small>
          </div>

          <div v-if="state.mode === 'register'" style="display:flex;flex-direction:column;gap:16px;">
            <div>
              <label class="label" style="color:#cbd5e1;">Full Name</label>
              <input
                v-model="state.form.name"
                type="text"
                name="name"
                class="input"
                placeholder="Your full name"
                required
              />
              <small class="error-text" style="color:#ef4444;" v-if="fieldError('name')">{{ fieldError('name') }}</small>
            </div>

            <div>
              <label class="label" style="color:#cbd5e1;">Company Name</label>
              <input
                v-model="state.form.company"
                type="text"
                name="company"
                class="input"
                placeholder="Your Company"
                required
              />
              <small class="error-text" style="color:#ef4444;" v-if="fieldError('company')">{{ fieldError('company') }}</small>
            </div>

            <div>
              <label class="label" style="color:#cbd5e1;">Confirm Password</label>
              <input
                v-model="state.form.password_confirmation"
                type="password"
                name="password_confirmation"
                class="input"
                placeholder="Confirm password"
                required
              />
              <small class="error-text" style="color:#ef4444;" v-if="fieldError('password_confirmation')">
                {{ fieldError('password_confirmation') }}
              </small>
            </div>
          </div>

          <button
            type="submit"
            class="btn"
            :disabled="state.loading"
            style="width:100%;justify-content:center;"
          >
            {{ state.loading ? 'Processing...' : submitLabel }}
          </button>
        </div>
      </form>

      <div style="text-align:center;margin-top:12px;">
        <button @click="close" type="button" style="background:none;border:none;color:#94a3b8;cursor:pointer;font-size:13px;">
          ← Back to home
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, reactive } from 'vue'
import axios from 'axios'

const state = reactive({
  open: false,
  mode: 'login',
  loading: false,
  globalError: '',
  errors: {},
  form: {
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    company: ''
  }
})

const submitLabel = computed(() => (state.mode === 'login' ? 'Sign In' : 'Create Account'))

function tabStyle(tab) {
  const isActive = state.mode === tab
  return {
    background: isActive ? 'linear-gradient(135deg,#0ea5e9,#06b6d4)' : 'transparent',
    color: isActive ? '#fff' : '#94a3b8'
  }
}

function fieldError(field) {
  const message = state.errors?.[field]
  return Array.isArray(message) ? message[0] : message || ''
}

function switchMode(mode) {
  state.mode = mode
  state.errors = {}
  state.globalError = ''
}

function open(mode = 'login') {
  switchMode(mode)
  state.open = true
  // Prime CSRF cookie when opening modal
  ensureCsrf()
}

function close() {
  state.open = false
}

async function ensureCsrf() {
  try {
    if (typeof window.getCsrfCookie === 'function') {
      await window.getCsrfCookie()
    }
    const match = document.cookie.match(/XSRF-TOKEN=([^;]+)/)
    if (match?.[1]) {
      const token = decodeURIComponent(match[1])
      axios.defaults.headers.common['X-XSRF-TOKEN'] = token
    }
  } catch (e) {
    // ignore; cookie/meta fallback may already exist
  }
}

async function submit() {
  state.loading = true
  state.errors = {}
  state.globalError = ''

  // Ensure CSRF cookie is set (Sanctum) and header applied
  await ensureCsrf()

  const url = state.mode === 'login' ? '/login' : '/register'
  const payload = {
    email: state.form.email,
    password: state.form.password
  }

  if (state.mode === 'register') {
    payload.name = state.form.name
    payload.company = state.form.company
    payload.password_confirmation = state.form.password_confirmation
  }

  try {
    await axios.post(url, payload)
    window.location.href = '/verify-code'
  } catch (error) {
    const status = error?.response?.status
    const errors = error?.response?.data?.errors

    if (status === 422 && errors) {
      state.errors = errors
      return
    }

    if (status === 401) {
      state.globalError = 'Invalid email or password.'
      return
    }

    if (status === 403 && error?.response?.data?.message === 'verification_required') {
      window.location.href = '/verify-code'
      return
    }

    state.globalError = 'Authentication failed.'
  } finally {
    state.loading = false
  }
}

onMounted(() => {
  window.showAuth = open
  window.closeAuth = close
})

onBeforeUnmount(() => {
  if (window.showAuth === open) delete window.showAuth
  if (window.closeAuth === close) delete window.closeAuth
})
</script>
