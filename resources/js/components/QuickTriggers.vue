<template>
  <div class="page-wrap">
    <!-- Emoji Quick Triggers -->
    <div class="card card-base">
      <h3 class="card-title">
        <span class="title-icon">&#9889;</span>
        Emoji Quick Triggers
      </h3>
      <p class="card-sub">
        Click any emoji to instantly get safety guidance. Click &#10005; to remove triggers or delete entire categories.
      </p>

      <div v-for="category in categories" :key="category.id || category.name" class="category-block">
        <div class="category-head">
          <h4 class="category-name" :class="{ custom: !isDefault(category.name) }">
            {{ category.name }}
            <span v-if="!isDefault(category.name)" class="category-chip">Custom</span>
            <span class="category-count">({{ category.triggers.length }} triggers)</span>
          </h4>
          <button
            v-if="!isDefault(category.name)"
            class="btn-ghost danger"
            @click="removeCategory(category)"
          >
            &#10005; Delete Category
          </button>
        </div>

        <div class="trigger-row">
          <div
            v-for="trigger in category.triggers"
            :key="trigger.id || trigger.action"
            class="trigger-btn"
            @click="handleTrigger(trigger.action)"
          >
            <span class="trigger-emoji">{{ trigger.emoji }}</span>
            <span class="trigger-text">{{ trigger.action }}</span>
            <button
              class="chip-delete"
              @click.stop="removeTrigger(category, trigger)"
              aria-label="Delete trigger"
            >&#10005;</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Create Custom Category -->
    <div class="card card-purple">
      <h3 class="card-title">
        <span class="title-icon">&#129668;</span>
        Create Custom Category
      </h3>
      <p class="card-sub">Organize your triggers into custom categories.</p>
      <div class="form-row wrap">
        <input
          v-model="newCategory"
          type="text"
          class="input"
          placeholder="e.g., Site-Specific, Welding, Chemicals"
        />
        <button class="btn" @click="addCategory">+ Add Category</button>
      </div>
      <div class="pill-row">
        <span class="label">Current categories:</span>
        <span
          v-for="cat in categories"
          :key="cat.id || cat.name"
          class="pill"
          :class="{ custom: !isDefault(cat.name) }"
        >
          <span>{{ cat.name }}</span>
          <button
            v-if="!isDefault(cat.name)"
            class="pill-delete"
            @click="removeCategory(cat)"
            aria-label="Delete category"
          >&#10005;</button>
        </span>
      </div>
    </div>

    <!-- Create Custom Protocol Trigger -->
    <div class="card card-teal">
      <h3 class="card-title">
        <span class="title-icon">&#129704;</span>
        Create Custom Protocol Trigger
      </h3>
      <p class="card-sub">Build your own emoji shortcuts for frequently used safety queries.</p>
      <div class="protocol-grid">
        <div>
          <label class="label">Emoji</label>
          <input
            type="text"
            class="input emoji-input"
            :placeholder="String.fromCodePoint(0x1F600)"
            maxlength="2"
            v-model="customEmoji"
          >
        </div>
        <div>
          <label class="label">Safety Query / Protocol Action</label>
          <input
            type="text"
            class="input"
            placeholder="e.g., Welding safety requirements for confined spaces"
            v-model="customAction"
          >
        </div>
        <div>
          <label class="label">Category</label>
          <select id="custom-category" class="input" v-model="customCategory">
            <option v-for="cat in categoryNames" :key="cat" :value="cat">{{ cat }}</option>
          </select>
        </div>
      </div>
      <div class="form-row wrap">
        <button class="btn" @click="addCustomTrigger">+ Add Custom Trigger</button>
        <div class="quick-pick">
          <span class="label">Quick pick:</span>
          <button
            v-for="emoji in quickPick"
            :key="emoji"
            class="pill-btn"
            @click="customEmoji = emoji"
          >{{ emoji }}</button>
        </div>
      </div>
    </div>

    <!-- Stats -->
    <div class="card stats">
      <div class="stat-block">
        <div class="stat-label">Total Triggers</div>
        <div class="stat-value accent-blue">{{ totalTriggers }}</div>
      </div>
      <div class="stat-block">
        <div class="stat-label">Custom</div>
        <div class="stat-value accent-green">{{ customCount }}</div>
      </div>
      <div class="stat-block">
        <div class="stat-label">Categories</div>
        <div class="stat-value accent-amber">{{ categoryNames.length }}</div>
      </div>
      <div class="stat-actions">
        <button class="btn-secondary" @click="exportTriggers">&#10510; Export</button>
        <button class="btn-secondary danger" @click="resetDefaults">&#10227; Reset Defaults</button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import axios from 'axios'

const emit = defineEmits([
  'select',
  'delete-trigger',
  'delete-category',
  'add-category',
  'add-trigger',
  'export',
  'reset',
])
const router = useRouter()
const route = useRoute()

const baseCategoriesSeed = [
  {
    name: 'Emergency',
    triggers: [
      { emoji: '\u{1F525}', action: 'Fire safety and emergency procedures' },
      { emoji: '\u{1F691}', action: 'Emergency response procedures' },
      { emoji: '\u{1FA79}', action: 'First aid procedures' },
    ],
  },
  {
    name: 'Hazards',
    triggers: [
      { emoji: '\u{26A1}', action: 'Electrical safety requirements' },
      { emoji: '\u{2620}', action: 'Toxic substance handling' },
      { emoji: '\u{26A0}', action: 'Hazard identification' },
    ],
  },
  {
    name: 'Equipment',
    triggers: [
      { emoji: '\u{1F9BA}', action: 'PPE requirements' },
      { emoji: '\u{1F637}', action: 'Respiratory protection' },
      { emoji: '\u{1F3A7}', action: 'Hearing protection requirements' },
      { emoji: '\u{1F97D}', action: 'Eye protection requirements' },
      { emoji: '\u{1F9E4}', action: 'Hand protection requirements' },
      { emoji: '\u{1F97E}', action: 'Foot protection requirements' },
      { emoji: '\u{1FA9C}', action: 'Ladder safety' },
      { emoji: '\u{1F3D7}', action: 'Heavy equipment safety' },
    ],
  },
  {
    name: 'Procedures',
    triggers: [
      { emoji: '\u{1F512}', action: 'Lockout/Tagout procedures' },
      { emoji: '\u{1F4CB}', action: 'Safety inspection checklist' },
      { emoji: '\u{1F4DD}', action: 'Incident reporting procedures' },
    ],
  },
  {
    name: 'Environment',
    triggers: [
      { emoji: '\u{1F32C}', action: 'Ventilation requirements' },
      { emoji: '\u{1F321}', action: 'Heat/cold stress prevention' },
    ],
  },
  {
    name: 'Construction',
    triggers: [{ emoji: '\u{1F3D7}', action: 'Scaffolding safety' }],
  },
]

const quickPick = [
  '\u{1FA9C}',
  '\u{1F477}',
  '\u{1F4CC}', // pin icon as default for custom
  '\u{1F9EF}',
  '\u{1F6E0}',
  '\u{1F6A8}',
  '\u{1F9EA}',
  '\u{1F6D1}',
  '\u{1F6A7}',
  '\u{1F527}',
  '\u{1F9F1}',
]

const defaultCustomEmoji = '\u{1F4CC}'

const defaultCategories = ['Emergency', 'Hazards', 'Equipment', 'Procedures', 'Environment', 'Construction']

const cloneBaseCategories = () =>
  baseCategoriesSeed.map(cat => ({
    name: cat.name,
    is_default: true,
    triggers: cat.triggers.map(t => ({ ...t, is_default: true })),
  }))

// Start with static defaults so the UI renders immediately, then hydrate from API on mount.
const categories = ref(cloneBaseCategories())
const newCategory = ref('')
const customEmoji = ref('')
const customAction = ref('')
const customCategory = ref(categories.value[0]?.name || '')
const loading = ref(false)

const categoryNames = computed(() => categories.value.map(c => c.name))
const totalTriggers = computed(() => categories.value.reduce((sum, c) => sum + c.triggers.length, 0))
const customCount = computed(() =>
  categories.value.filter(c => !isDefault(c.name)).reduce((sum, c) => sum + c.triggers.length, 0)
)

function normalizeCategory(cat) {
  return {
    id: cat.id,
    name: cat.name,
    is_default: !!cat.is_default,
    triggers: (cat.triggers || []).map(t => ({
      id: t.id,
      emoji: t.emoji,
      action: t.action,
      is_default: !!t.is_default,
    })),
  }
}

function findCategoryByName(name) {
  return categories.value.find(c => c.name === name)
}

function mergeWithDefaults(remoteCategories) {
  const defaultsByName = Object.fromEntries(
    cloneBaseCategories().map(cat => [cat.name, { ...cat }])
  )

  const merged = []

  // ensure defaults always present
  defaultCategories.forEach(name => {
    const remote = remoteCategories.find(c => c.name === name)
    merged.push(remote ? remote : defaultsByName[name])
  })

  // append any custom categories from remote
  remoteCategories.forEach(cat => {
    if (!defaultCategories.includes(cat.name)) {
      merged.push(cat)
    }
  })

  return merged
}

async function loadCategories() {
  try {
    loading.value = true
    const { data } = await axios.get('/api/quick-triggers')
    const remote = (data?.categories || []).map(normalizeCategory)
    categories.value = mergeWithDefaults(remote)
    customCategory.value = categories.value[0]?.name || ''
  } catch (error) {
    console.error('Failed to load quick triggers', error)
    categories.value = cloneBaseCategories()
    customCategory.value = categories.value[0]?.name || ''
  } finally {
    loading.value = false
  }
}

function handleTrigger(action) {
  emit('select', action)
  if (route.path !== '/dashboard') {
    router.push({ path: '/dashboard', query: { ask: action } })
  }
}

async function removeTrigger(category, trigger) {
  if (!category || !trigger) return
  const cat = findCategoryByName(category.name)
  if (!cat) return
  try {
    if (cat.id && trigger.id) {
      await axios.delete(`/api/quick-triggers/categories/${cat.id}/triggers/${trigger.id}`)
    }
    cat.triggers = cat.triggers.filter(t => t.action !== trigger.action)
    emit('delete-trigger', { category: category.name, action: trigger.action })
  } catch (error) {
    console.error('Failed to delete trigger', error)
  }
}

async function addCategory() {
  const name = newCategory.value.trim()
  if (!name || categoryNames.value.includes(name)) return
  try {
    const { data } = await axios.post('/api/quick-triggers/categories', { name })
    const category = normalizeCategory(data.category || {})
    // add placeholder trigger server-side
    const placeholder = `${name} placeholder - edit or delete`
    const triggerRes = await axios.post(`/api/quick-triggers/categories/${category.id}/triggers`, {
      emoji: defaultCustomEmoji,
      action: placeholder,
    })
    category.triggers.push({
      id: triggerRes.data?.trigger?.id,
      emoji: defaultCustomEmoji,
      action: placeholder,
      is_default: false,
    })
    categories.value.push(category)
    customCategory.value = name
    newCategory.value = ''
    emit('add-category', { name })
  } catch (error) {
    console.error('Failed to add category', error)
    // Fallback to local-only add if API is unavailable
    const category = {
      name,
      is_default: false,
      triggers: [{ emoji: defaultCustomEmoji, action: `${name} placeholder - edit or delete`, is_default: false }],
    }
    categories.value.push(category)
    customCategory.value = name
    newCategory.value = ''
    emit('add-category', { name })
  }
}

async function removeCategory(category) {
  if (!category) return
  if (isDefault(category.name)) return
  try {
    if (category.id) {
      await axios.delete(`/api/quick-triggers/categories/${category.id}`)
    }
    categories.value = categories.value.filter(c => c.name !== category.name)
    if (customCategory.value === category.name) {
      customCategory.value = categories.value[0]?.name || ''
    }
    emit('delete-category', { category: category.name })
  } catch (error) {
    console.error('Failed to delete category', error)
  }
}

async function addCustomTrigger() {
  const emoji = (customEmoji.value || '').trim() || defaultCustomEmoji
  const action = customAction.value.trim()
  const catName = customCategory.value
  if (!action || !catName) return
  const cat = findCategoryByName(catName)
  if (!cat) return
  const actionKey = action.toLowerCase()
  const exists = cat.triggers.some(t => t.action.toLowerCase() === actionKey)
  if (exists) return
  try {
    const { data } = await axios.post(`/api/quick-triggers/categories/${cat.id}/triggers`, {
      emoji,
      action,
    })
    const trigger = {
      id: data?.trigger?.id,
      emoji,
      action,
      is_default: false,
    }
    cat.triggers.push(trigger)
    customEmoji.value = ''
    customAction.value = ''
    emit('add-trigger', { category: catName, ...trigger })
  } catch (error) {
    console.error('Failed to add trigger', error)
  }
}

function isDefault(name) {
  return defaultCategories.includes(name)
}

function exportTriggers() {
  const payload = { categories: categories.value, total: totalTriggers.value, custom: customCount.value }
  emit('export', payload)
  const lines = ['K.I.N.G. Quick Triggers Export', `Total: ${payload.total}`, `Custom: ${payload.custom}`, '']
  categories.value.forEach(cat => {
    lines.push(`[${cat.name}]`)
    cat.triggers.forEach(t => lines.push(`${t.emoji} ${t.action}`))
    lines.push('')
  })
  const blob = new Blob([lines.join('\n')], { type: 'text/plain' })
  const url = URL.createObjectURL(blob)
  const a = document.createElement('a')
  a.href = url
  a.download = `quick-triggers-${new Date().toISOString().split('T')[0]}.txt`
  a.click()
  URL.revokeObjectURL(url)
}

async function resetDefaults() {
  try {
    const { data } = await axios.post('/api/quick-triggers/reset')
    categories.value = (data?.categories || []).map(normalizeCategory)
    customCategory.value = categories.value[0]?.name || ''
    customEmoji.value = ''
    customAction.value = ''
    newCategory.value = ''
    emit('reset')
  } catch (error) {
    console.error('Failed to reset quick triggers', error)
    categories.value = cloneBaseCategories()
    customCategory.value = categories.value[0]?.name || ''
    customEmoji.value = ''
    customAction.value = ''
    newCategory.value = ''
  }
}

onMounted(loadCategories)
</script>

<style scoped>
.page-wrap {
  max-width: 900px;
  margin: 0 auto;
  display: flex;
  flex-direction: column;
  gap: 20px;
  color: #cbd5e1;
}

.card {
  background: rgba(30, 41, 59, 0.5);
  border: 1px solid rgba(71, 85, 105, 0.5);
  border-radius: 16px;
  padding: 24px;
  backdrop-filter: blur(12px);
  transition: all 0.3s ease;
}

.card:hover {
  border-color: rgba(100, 116, 139, 0.6);
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.25);
}

.card-base {
  margin-bottom: 4px;
}

.card-sub {
  margin: 0 0 20px;
  color: #64748b;
  font-size: 14px;
}

.card-title {
  font-size: 16px;
  font-weight: 700;
  margin: 0 0 10px;
  display: flex;
  align-items: center;
  gap: 10px;
}

.title-icon {
  font-size: 18px;
}

.card-purple {
  background: linear-gradient(135deg, rgba(139, 92, 246, 0.1) 0%, rgba(99, 102, 241, 0.05) 100%);
  border: 1px solid rgba(139, 92, 246, 0.3);
}

.card-teal {
  background: linear-gradient(135deg, rgba(14, 165, 233, 0.1) 0%, rgba(6, 182, 212, 0.05) 100%);
  border: 1px solid rgba(34, 211, 238, 0.3);
}

.category-block {
  margin-bottom: 20px;
}

.category-head {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 10px;
}

.category-name {
  font-size: 13px;
  font-weight: 600;
  color: #94a3b8;
  text-transform: uppercase;
  display: flex;
  align-items: center;
  gap: 8px;
}

.category-name.custom {
  color: #22d3ee;
}

.category-chip {
  font-size: 10px;
  padding: 2px 8px;
  background: rgba(34, 211, 238, 0.2);
  border-radius: 10px;
  text-transform: none;
}

.category-count {
  font-size: 11px;
  color: #64748b;
  font-weight: normal;
  text-transform: none;
}

.trigger-row {
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
}

.trigger-btn {
  position: relative;
  background: rgba(15, 23, 42, 0.5);
  border: 1px solid rgba(71, 85, 105, 0.45);
  border-radius: 10px;
  padding: 10px 12px;
  display: inline-flex;
  align-items: center;
  gap: 8px;
  color: #cbd5e1;
  cursor: pointer;
  transition: all 0.2s ease;
}

.trigger-btn:hover {
  background: rgba(30, 41, 59, 0.75);
  border-color: rgba(100, 116, 139, 0.5);
  transform: translateY(-1px);
}

.trigger-emoji {
  font-size: 20px;
}

.trigger-text {
  font-size: 13px;
  text-align: left;
}

.chip-delete {
  position: absolute;
  top: -6px;
  right: -6px;
  width: 18px;
  height: 18px;
  background: rgba(239, 68, 68, 0.8);
  border-radius: 50%;
  border: none;
  color: #fff;
  font-size: 12px;
  cursor: pointer;
  opacity: 0.75;
  transition: opacity 0.2s ease;
}

.chip-delete:hover {
  opacity: 1;
}

.form-row {
  display: flex;
  gap: 12px;
  align-items: center;
}

.form-row.wrap {
  flex-wrap: wrap;
}

.input {
  flex: 1;
  min-width: 200px;
  height: 44px;
  border-radius: 8px;
  border: 1px solid rgba(71, 85, 105, 0.4);
  background: rgba(15, 23, 42, 0.8);
  color: #e2e8f0;
  padding: 10px 12px;
  transition: all 0.2s ease;
}

.input:focus {
  border-color: #0ea5e9;
  box-shadow: 0 0 0 3px rgba(14, 165, 233, 0.15);
}

.btn {
  background: linear-gradient(135deg, #0ea5e9, #06b6d4);
  color: #fff;
  border: none;
  padding: 11px 16px;
  border-radius: 10px;
  font-weight: 700;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  gap: 8px;
  transition: all 0.3s ease;
}

.btn:hover {
  box-shadow: 0 8px 24px rgba(14, 165, 233, 0.35);
  transform: translateY(-1px);
}

.btn-secondary {
  background: rgba(148, 163, 184, 0.12);
  color: #cbd5e1;
  border: 1px solid rgba(148, 163, 184, 0.3);
  padding: 11px 16px;
  border-radius: 10px;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  gap: 8px;
}

.btn-secondary.danger {
  background: rgba(239, 68, 68, 0.12);
  border-color: rgba(239, 68, 68, 0.35);
  color: #fca5a5;
}

.btn-ghost {
  background: rgba(239, 68, 68, 0.1);
  border: 1px solid rgba(239, 68, 68, 0.3);
  color: #f87171;
  padding: 6px 12px;
  border-radius: 8px;
  font-size: 12px;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-ghost:hover {
  background: rgba(239, 68, 68, 0.2);
}

.label {
  font-size: 12px;
  color: #94a3b8;
}

.pill-row {
  margin-top: 12px;
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
  align-items: center;
}

.pill {
  font-size: 12px;
  padding: 6px 10px;
  background: rgba(51, 65, 85, 0.5);
  border-radius: 8px;
  color: #cbd5e1;
  display: inline-flex;
  align-items: center;
  gap: 6px;
}

.pill.custom {
  background: rgba(34, 211, 238, 0.2);
  border: 1px solid rgba(34, 211, 238, 0.4);
  color: #22d3ee;
}

.pill-delete {
  background: none;
  border: none;
  color: inherit;
  cursor: pointer;
  font-size: 12px;
}

.protocol-grid {
  display: grid;
  grid-template-columns: 110px 1fr 180px;
  gap: 12px;
  margin-bottom: 16px;
  align-items: end;
}

.emoji-input {
  text-align: center;
  font-size: 24px;
  padding: 0;
}

.quick-pick {
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
  align-items: center;
}

.pill-btn {
  background: rgba(51, 65, 85, 0.5);
  border: 1px solid rgba(71, 85, 105, 0.5);
  border-radius: 8px;
  padding: 6px 10px;
  font-size: 16px;
  cursor: pointer;
  color: #e2e8f0;
  transition: all 0.2s ease;
}

.pill-btn:hover {
  transform: translateY(-1px);
  border-color: rgba(34, 211, 238, 0.5);
}

.stats {
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-wrap: wrap;
  gap: 16px;
}

.stat-block {
  min-width: 120px;
}

.stat-label {
  color: #94a3b8;
  font-size: 13px;
}

.stat-value {
  font-size: 28px;
  font-weight: 800;
}

.accent-blue { color: #22d3ee; }
.accent-green { color: #10b981; }
.accent-amber { color: #fbbf24; }

.stat-actions {
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
}

@media (max-width: 960px) {
  .protocol-grid {
    grid-template-columns: 1fr;
  }
}
</style>
