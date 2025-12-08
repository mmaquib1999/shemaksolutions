<template>
  <div class="page-wrap">
    <!-- Emoji Quick Triggers -->
    <div class="card card-main">
      <div class="card-head">
        <h3>⚡ Emoji Quick Triggers</h3>
        <p>Click any emoji to instantly get safety guidance. Click ✕ to remove triggers or delete entire categories.</p>
      </div>

      <div class="categories">
        <div v-for="category in categories" :key="category.name" class="category">
          <div class="category-head">
            <div class="category-title">
              <span class="category-name">{{ category.name }}</span>
              <span class="category-count">({{ category.triggers.length }} triggers)</span>
              <span v-if="!isDefault(category.name)" class="badge-custom">Custom</span>
            </div>
            <button
              v-if="!isDefault(category.name)"
              class="category-delete"
              @click="removeCategory(category.name)"
            >
              ✕ Delete Category
            </button>
          </div>

          <div class="trigger-grid">
            <div
              v-for="trigger in category.triggers"
              :key="trigger.action"
              class="trigger-chip"
              @click="handleTrigger(trigger.action)"
            >
              <div class="chip-content">
                <span class="emoji">{{ trigger.emoji }}</span>
                <span class="chip-text">{{ trigger.action }}</span>
              </div>
              <button class="chip-delete" @click.stop="removeTrigger(category.name, trigger.action)" aria-label="Delete trigger">✕</button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Create Custom Category -->
    <div class="card card-accent purple">
      <div class="card-head">
        <h3>📁 Create Custom Category</h3>
        <p>Organize your triggers into custom categories.</p>
      </div>

      <div class="row">
        <input
          v-model="newCategory"
          type="text"
          class="input"
          placeholder="e.g., Site-Specific, Welding, Chemicals"
        />
        <button class="btn" @click="addCategory">+ Add Category</button>
      </div>

      <div class="current-categories">
        <span class="label">Current categories:</span>
        <span
          v-for="cat in categoryNames"
          :key="cat"
          class="pill"
          :class="{ custom: !isDefault(cat) }"
        >
          <span>{{ cat }}</span>
          <button
            v-if="!isDefault(cat)"
            class="pill-delete"
            @click="removeCategory(cat)"
            aria-label="Delete category"
          >✕</button>
        </span>
      </div>
    </div>

    <!-- Create Custom Protocol Trigger -->
    <div class="card card-accent teal">
      <div class="card-head">
        <h3>🎯 Create Custom Protocol Trigger</h3>
        <p>Build your own emoji shortcuts for frequently used safety queries.</p>
      </div>

      <div class="custom-grid">
        <div>
          <label class="label">Emoji</label>
          <input
            v-model="customEmoji"
            type="text"
            class="input"
            maxlength="2"
            placeholder="🔧"
            style="text-align:center;font-size:24px;"
          />
        </div>
        <div>
          <label class="label">Safety Query / Protocol Action</label>
          <input
            v-model="customAction"
            type="text"
            class="input"
            placeholder="e.g., Welding safety requirements for confined spaces"
          />
        </div>
        <div>
          <label class="label">Category</label>
          <select v-model="customCategory" class="input">
            <option v-for="cat in categoryNames" :key="cat" :value="cat">{{ cat }}</option>
          </select>
        </div>
      </div>

      <div class="row row-wrap">
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
        <button class="btn-secondary" @click="exportTriggers">⬇ Export</button>
        <button class="btn-secondary danger" @click="resetDefaults">🔄 Reset Defaults</button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, reactive, ref } from 'vue'

const emit = defineEmits(['select', 'delete-trigger', 'delete-category', 'add-category', 'add-trigger', 'export', 'reset'])

const baseCategories = reactive([
  {
    name: 'Emergency',
    triggers: [
      { emoji: '🔥', action: 'Fire safety and emergency procedures' },
      { emoji: '🚨', action: 'Emergency response procedures' },
      { emoji: '🩹', action: 'First aid procedures' },
    ],
  },
  {
    name: 'Hazards',
    triggers: [
      { emoji: '⚡', action: 'Electrical safety requirements' },
      { emoji: '☠️', action: 'Toxic substance handling' },
      { emoji: '⚠️', action: 'Hazard identification' },
    ],
  },
  {
    name: 'Procedures',
    triggers: [
      { emoji: '🔒', action: 'Lockout/Tagout procedures' },
      { emoji: '📋', action: 'Safety inspection checklist' },
      { emoji: '📝', action: 'Incident reporting procedures' },
    ],
  },
  {
    name: 'Equipment',
    triggers: [
      { emoji: '🦺', action: 'PPE requirements' },
      { emoji: '😷', action: 'Respiratory protection' },
      { emoji: '👂', action: 'Hearing protection requirements' },
      { emoji: '👀', action: 'Eye protection requirements' },
      { emoji: '🧤', action: 'Hand protection requirements' },
      { emoji: '🥾', action: 'Foot protection requirements' },
      { emoji: '🪜', action: 'Ladder safety' },
      { emoji: '🚜', action: 'Heavy equipment safety' },
    ],
  },
  {
    name: 'Construction',
    triggers: [
      { emoji: '🏗️', action: 'Scaffolding safety' },
    ],
  },
  {
    name: 'Environment',
    triggers: [
      { emoji: '💨', action: 'Ventilation requirements' },
      { emoji: '🌡️', action: 'Heat/cold stress prevention' },
    ],
  },
])

const defaultCategories = ['Emergency', 'Hazards', 'Procedures', 'Equipment', 'Construction', 'Environment']

const customTriggers = ref([])
const categories = ref([...baseCategories])
const newCategory = ref('')
const customEmoji = ref('')
const customAction = ref('')
const customCategory = ref(categories.value[0]?.name || '')

const quickPick = ['🔧', '⛽', '🏗️', '💊', '🧯', '⚗️', '🔌', '🪓', '🛠️', '⛑️']

const categoryNames = computed(() => categories.value.map(c => c.name))
const totalTriggers = computed(() => categories.value.reduce((sum, c) => sum + c.triggers.length, 0))
const customCount = computed(() =>
  categories.value.filter(c => !isDefault(c.name)).reduce((sum, c) => sum + c.triggers.length, 0)
)

function handleTrigger(action) {
  emit('select', action)
}

function removeTrigger(categoryName, action) {
  const cat = categories.value.find(c => c.name === categoryName)
  if (!cat) return
  cat.triggers = cat.triggers.filter(t => t.action !== action)
  emit('delete-trigger', { category: categoryName, action })
}

function addCategory() {
  const name = newCategory.value.trim()
  if (!name || categoryNames.value.includes(name)) return
  categories.value.push({ name, triggers: [] })
  customCategory.value = name
  newCategory.value = ''
  emit('add-category', { name })
}

function removeCategory(name) {
  if (isDefault(name)) return
  categories.value = categories.value.filter(c => c.name !== name)
  if (customCategory.value === name) {
    customCategory.value = categories.value[0]?.name || ''
  }
  emit('delete-category', { category: name })
}

function addCustomTrigger() {
  const emoji = (customEmoji.value || '').trim() || '❓'
  const action = customAction.value.trim()
  const catName = customCategory.value
  if (!action || !catName) return
  const cat = categories.value.find(c => c.name === catName)
  if (!cat) return
  const trigger = { emoji, action }
  cat.triggers.push(trigger)
  customTriggers.value.push(trigger)
  customEmoji.value = ''
  customAction.value = ''
  emit('add-trigger', { category: catName, ...trigger })
}

function isDefault(name) {
  return defaultCategories.includes(name)
}

function exportTriggers() {
  emit('export')
}

function resetDefaults() {
  emit('reset')
}
</script>

<style scoped>
.page-wrap {
  max-width: 1100px;
  margin: 0 auto;
  display: flex;
  flex-direction: column;
  gap: 16px;
  color: #e2e8f0;
}

.card {
  background: linear-gradient(135deg, rgba(17, 24, 39, 0.95), rgba(12, 18, 35, 0.88));
  border: 1px solid rgba(71, 85, 105, 0.35);
  border-radius: 18px;
  padding: 18px;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
}

.card-main {
  padding: 20px;
}

.card-head h3 {
  margin: 0 0 6px;
  font-size: 16px;
  font-weight: 700;
  display: flex;
  align-items: center;
  gap: 8px;
}

.card-head p {
  margin: 0 0 12px;
  color: #94a3b8;
  font-size: 14px;
}

.categories {
  display: flex;
  flex-direction: column;
  gap: 14px;
}

.category {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.category-head {
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.category-title {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 13px;
  font-weight: 700;
  text-transform: uppercase;
  color: #cbd5e1;
}

.category-count {
  font-size: 11px;
  color: #64748b;
  font-weight: 500;
  text-transform: none;
}

.trigger-grid {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
}

.trigger-chip {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 10px 12px;
  border-radius: 12px;
  border: 1px solid rgba(71, 85, 105, 0.45);
  background: linear-gradient(135deg, rgba(26, 34, 56, 0.9), rgba(21, 27, 46, 0.8));
  cursor: pointer;
  position: relative;
  min-width: 180px;
  transition: transform 0.15s ease, border-color 0.15s ease;
}

.trigger-chip:hover {
  transform: translateY(-2px);
  border-color: rgba(34, 211, 238, 0.4);
}

.chip-content {
  display: inline-flex;
  align-items: center;
  gap: 8px;
}

.emoji {
  font-size: 18px;
}

.chip-text {
  font-size: 13px;
  color: #e2e8f0;
  white-space: nowrap;
}

.chip-delete {
  position: absolute;
  top: -6px;
  right: -6px;
  width: 20px;
  height: 20px;
  border-radius: 50%;
  border: none;
  background: rgba(239, 68, 68, 0.8);
  color: #fff;
  font-size: 12px;
  cursor: pointer;
  opacity: 0.8;
  transition: opacity 0.2s ease;
}

.chip-delete:hover {
  opacity: 1;
}

.category-delete {
  background: rgba(239, 68, 68, 0.12);
  border: 1px solid rgba(239, 68, 68, 0.35);
  color: #fca5a5;
  padding: 6px 10px;
  border-radius: 10px;
  cursor: pointer;
  font-size: 12px;
}

.badge-custom {
  font-size: 10px;
  padding: 2px 8px;
  background: rgba(34, 211, 238, 0.2);
  border-radius: 10px;
  color: #22d3ee;
  text-transform: none;
}

.card-accent {
  padding: 20px;
}

.card-accent.purple {
  background: linear-gradient(135deg, rgba(139, 92, 246, 0.12), rgba(99, 102, 241, 0.07));
  border-color: rgba(139, 92, 246, 0.35);
}

.card-accent.teal {
  background: linear-gradient(135deg, rgba(14, 165, 233, 0.12), rgba(6, 182, 212, 0.08));
  border-color: rgba(34, 211, 238, 0.35);
}

.row {
  display: flex;
  gap: 12px;
}

.row-wrap {
  flex-wrap: wrap;
  align-items: center;
}

.input {
  flex: 1;
  min-width: 200px;
  height: 44px;
  border-radius: 12px;
  border: 1px solid rgba(148, 163, 184, 0.35);
  background: rgba(148, 163, 184, 0.08);
  color: #e2e8f0;
  padding: 10px 12px;
}

.input:focus {
  outline: none;
  border-color: #22d3ee;
  box-shadow: 0 0 0 3px rgba(34, 211, 238, 0.14);
}

.btn {
  background: linear-gradient(135deg, #0ea5e9, #22d3ee);
  color: #0b1729;
  border: none;
  padding: 11px 16px;
  border-radius: 12px;
  font-weight: 700;
  cursor: pointer;
  box-shadow: 0 12px 30px rgba(34, 211, 238, 0.25);
}

.btn-secondary {
  background: rgba(148, 163, 184, 0.12);
  color: #cbd5e1;
  border: 1px solid rgba(148, 163, 184, 0.3);
  padding: 11px 16px;
  border-radius: 12px;
  cursor: pointer;
}

.btn-secondary.danger {
  background: rgba(239, 68, 68, 0.12);
  border-color: rgba(239, 68, 68, 0.35);
  color: #fca5a5;
}

.current-categories {
  margin-top: 12px;
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
  align-items: center;
}

.label {
  font-size: 12px;
  color: #94a3b8;
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
  color: #22d3ee;
  border: 1px solid rgba(34, 211, 238, 0.4);
}

.pill-delete {
  background: none;
  border: none;
  color: inherit;
  cursor: pointer;
  font-size: 12px;
}

.pill-btn {
  background: rgba(51, 65, 85, 0.5);
  border: 1px solid rgba(71, 85, 105, 0.5);
  border-radius: 8px;
  padding: 6px 10px;
  font-size: 16px;
  cursor: pointer;
  color: #e2e8f0;
}

.quick-pick {
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
  align-items: center;
}

.custom-grid {
  display: grid;
  grid-template-columns: 120px 1fr 180px;
  gap: 14px;
  margin-bottom: 14px;
}

.stats {
  display: grid;
  grid-template-columns: repeat(3, 1fr) auto;
  align-items: center;
  gap: 12px;
}

.stat-block {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.stat-label {
  color: #94a3b8;
  font-size: 12px;
}

.stat-value {
  font-size: 26px;
  font-weight: 800;
}

.accent-blue { color: #22d3ee; }
.accent-green { color: #10b981; }
.accent-amber { color: #fbbf24; }

.stat-actions {
  display: flex;
  gap: 8px;
  justify-content: flex-end;
  flex-wrap: wrap;
}

@media (max-width: 900px) {
  .custom-grid {
    grid-template-columns: 1fr;
  }
  .stats {
    grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
  }
}
</style>
