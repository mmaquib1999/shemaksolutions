<template>
  <div style="max-width:500px;margin:80px auto;padding:24px;background:rgba(15,23,42,0.8);border:1px solid rgba(71,85,105,0.3);border-radius:14px;">
    <h2 style="margin:0 0 12px;font-size:20px;font-weight:700;display:flex;align-items:center;gap:8px;">🔒 Verify Your Account</h2>
    <p style="color:#94a3b8;margin:0 0 16px;">Enter the 6-digit code sent to your email to unlock the dashboard.</p>
    <div style="display:flex;gap:10px;align-items:center;margin-bottom:12px;">
      <input
        v-model="code"
        maxlength="6"
        style="flex:1;height:46px;border-radius:10px;border:1px solid rgba(71,85,105,0.4);background:rgba(15,23,42,0.8);color:#e2e8f0;padding:10px 12px;font-size:16px;letter-spacing:4px;text-align:center;"
        placeholder="••••••"
      />
      <button class="btn" :disabled="loading" @click="submit" style="min-width:100px;">Verify</button>
    </div>
    <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
      <button class="btn-secondary" :disabled="loading || resendCooldown" @click="resend">
        {{ resendCooldown ? `Resend (${resendCooldown}s)` : 'Resend code' }}
      </button>
      <button class="btn-secondary" @click="logout">Logout</button>
    </div>
    <p v-if="error" style="color:#f87171;margin-top:12px;font-size:13px;">{{ error }}</p>
    <p v-if="success" style="color:#10b981;margin-top:12px;font-size:13px;">Verified! Redirecting...</p>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import axios from 'axios'
import { useRouter } from 'vue-router'

const code = ref('')
const loading = ref(false)
const error = ref('')
const success = ref(false)
const resendCooldown = ref(0)
const router = useRouter()

async function submit() {
  error.value = ''
  if (!code.value || code.value.length !== 6) {
    error.value = 'Enter the 6-digit code.'
    return
  }
  loading.value = true
  try {
    await axios.post('/api/verify-code', { code: code.value })
    success.value = true
    setTimeout(() => router.push('/dashboard'), 500)
  } catch (err) {
    error.value = err.response?.data?.message || 'Verification failed'
  } finally {
    loading.value = false
  }
}

async function resend() {
  error.value = ''
  loading.value = true
  try {
    await axios.post('/api/verify-code/resend')
    resendCooldown.value = 30
    const interval = setInterval(() => {
      resendCooldown.value--
      if (resendCooldown.value <= 0) clearInterval(interval)
    }, 1000)
  } catch (err) {
    error.value = err.response?.data?.message || 'Could not resend code'
  } finally {
    loading.value = false
  }
}

async function logout() {
  try {
    await axios.post('/logout')
    window.location.href = '/'
  } catch (err) {
    console.error(err)
  }
}
</script>

<style scoped>
.btn {
  background: linear-gradient(135deg, #0ea5e9, #06b6d4);
  color: #fff;
  border: none;
  padding: 11px 16px;
  border-radius: 10px;
  font-weight: 700;
  cursor: pointer;
}
.btn-secondary {
  background: rgba(148, 163, 184, 0.12);
  color: #cbd5e1;
  border: 1px solid rgba(148, 163, 184, 0.3);
  padding: 10px 14px;
  border-radius: 10px;
  cursor: pointer;
}
.btn:disabled,
.btn-secondary:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}
</style>
