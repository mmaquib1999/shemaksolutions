import './bootstrap'
import { createApp } from 'vue'
import AuthModal from './components/AuthModal.vue'

const el = document.getElementById('auth-root')
if (el) {
  createApp(AuthModal).mount(el)
}
