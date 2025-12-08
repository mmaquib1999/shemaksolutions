<template>
  <div style="flex: 1; overflow: auto; padding: 24px;" id="main-content">
    <div style="max-width: 900px; margin: 0 auto;">

      <div class="card" style="margin-bottom: 24px;">
        <h3 style="font-size: 16px; font-weight: 600; margin-bottom: 8px; display: flex; align-items: center; gap: 8px;">
          ⚡ Emoji Quick Triggers
        </h3>
        <p style="color: #64748b; font-size: 14px; margin-bottom: 24px;">
          Click any emoji to instantly get safety guidance on that topic. These work great on mobile devices!
        </p>

        <!-- ------------------------- EMERGENCY ------------------------- -->
        <div style="margin-bottom: 20px;">
          <h4 style="font-size: 13px; font-weight: 600; color: #94a3b8; margin-bottom: 10px; text-transform: uppercase; letter-spacing: 0.5px;">
            Emergency
          </h4>

          <div style="display: flex; gap: 8px; flex-wrap: wrap;">

            <button @click="emitTrigger('Fire safety and emergency procedures')" class="quick-action-btn" style="flex: none;">
              <span style="font-size: 20px;">🔥</span>
              <span>Fire safety and emergency procedures</span>
            </button>

            <button @click="emitTrigger('Emergency response procedures')" class="quick-action-btn" style="flex: none;">
              <span style="font-size: 20px;">🚨</span>
              <span>Emergency response procedures</span>
            </button>

            <button @click="emitTrigger('First aid procedures')" class="quick-action-btn" style="flex: none;">
              <span style="font-size: 20px;">🩹</span>
              <span>First aid procedures</span>
            </button>

          </div>
        </div>

        <!-- ------------------------- HAZARDS ------------------------- -->
        <div style="margin-bottom: 20px;">
          <h4 style="font-size: 13px; font-weight: 600; color: #94a3b8; margin-bottom: 10px; text-transform: uppercase; letter-spacing: 0.5px;">
            Hazards
          </h4>

          <div style="display: flex; gap: 8px; flex-wrap: wrap;">

            <button @click="emitTrigger('Electrical safety requirements')" class="quick-action-btn" style="flex: none;">
              <span style="font-size: 20px;">⚡</span>
              <span>Electrical safety requirements</span>
            </button>

            <button @click="emitTrigger('Toxic substance handling')" class="quick-action-btn" style="flex: none;">
              <span style="font-size: 20px;">☠️</span>
              <span>Toxic substance handling</span>
            </button>

            <button @click="emitTrigger('Hazard identification')" class="quick-action-btn" style="flex: none;">
              <span style="font-size: 20px;">⚠️</span>
              <span>Hazard identification</span>
            </button>

          </div>
        </div>

        <!-- ------------------------- EQUIPMENT ------------------------- -->
        <div style="margin-bottom: 20px;">
          <h4 style="font-size: 13px; font-weight: 600; color: #94a3b8; margin-bottom: 10px; text-transform: uppercase; letter-spacing: 0.5px;">
            Equipment
          </h4>

          <div style="display: flex; gap: 8px; flex-wrap: wrap;">

            <button @click="emitTrigger('PPE requirements')" class="quick-action-btn" style="flex: none;">
              <span style="font-size: 20px;">🦺</span>
              <span>PPE requirements</span>
            </button>

            <button @click="emitTrigger('Respiratory protection')" class="quick-action-btn" style="flex: none;">
              <span style="font-size: 20px;">😷</span>
              <span>Respiratory protection</span>
            </button>

            <button @click="emitTrigger('Hearing protection requirements')" class="quick-action-btn" style="flex: none;">
              <span style="font-size: 20px;">👂</span>
              <span>Hearing protection requirements</span>
            </button>

            <button @click="emitTrigger('Eye protection requirements')" class="quick-action-btn" style="flex: none;">
              <span style="font-size: 20px;">👀</span>
              <span>Eye protection requirements</span>
            </button>

            <button @click="emitTrigger('Hand protection requirements')" class="quick-action-btn" style="flex: none;">
              <span style="font-size: 20px;">🧤</span>
              <span>Hand protection requirements</span>
            </button>

            <button @click="emitTrigger('Foot protection requirements')" class="quick-action-btn" style="flex: none;">
              <span style="font-size: 20px;">🥾</span>
              <span>Foot protection requirements</span>
            </button>

            <button @click="emitTrigger('Ladder safety')" class="quick-action-btn" style="flex: none;">
              <span style="font-size: 20px;">🪜</span>
              <span>Ladder safety</span>
            </button>

            <button @click="emitTrigger('Heavy equipment safety')" class="quick-action-btn" style="flex: none;">
              <span style="font-size: 20px;">🚜</span>
              <span>Heavy equipment safety</span>
            </button>

          </div>
        </div>

        <!-- ------------------------- PROCEDURES ------------------------- -->
        <div style="margin-bottom: 20px;">
          <h4 style="font-size: 13px; font-weight: 600; color: #94a3b8; margin-bottom: 10px; text-transform: uppercase; letter-spacing: 0.5px;">
            Procedures
          </h4>

          <div style="display: flex; gap: 8px; flex-wrap: wrap;">

            <button @click="emitTrigger('Lockout/Tagout procedures')" class="quick-action-btn" style="flex: none;">
              <span style="font-size: 20px;">🔒</span>
              <span>Lockout/Tagout procedures</span>
            </button>

            <button @click="emitTrigger('Safety inspection checklist')" class="quick-action-btn" style="flex: none;">
              <span style="font-size: 20px;">📋</span>
              <span>Safety inspection checklist</span>
            </button>

            <button @click="emitTrigger('Incident reporting procedures')" class="quick-action-btn" style="flex: none;">
              <span style="font-size: 20px;">📝</span>
              <span>Incident reporting procedures</span>
            </button>

          </div>
        </div>

        <!-- ------------------------- ENVIRONMENT ------------------------- -->
        <div style="margin-bottom: 20px;">
          <h4 style="font-size: 13px; font-weight: 600; color: #94a3b8; margin-bottom: 10px; text-transform: uppercase; letter-spacing: 0.5px;">
            Environment
          </h4>

          <div style="display: flex; gap: 8px; flex-wrap: wrap;">

            <button @click="emitTrigger('Ventilation requirements')" class="quick-action-btn" style="flex: none;">
              <span style="font-size: 20px;">💨</span>
              <span>Ventilation requirements</span>
            </button>

            <button @click="emitTrigger('Heat/cold stress prevention')" class="quick-action-btn" style="flex: none;">
              <span style="font-size: 20px;">🌡️</span>
              <span>Heat/cold stress prevention</span>
            </button>

          </div>
        </div>

        <!-- ------------------------- CONSTRUCTION ------------------------- -->
        <div style="margin-bottom: 20px;">
          <h4 style="font-size: 13px; font-weight: 600; color: #94a3b8; margin-bottom: 10px; text-transform: uppercase; letter-spacing: 0.5px;">
            Construction
          </h4>

          <div style="display: flex; gap: 8px; flex-wrap: wrap;">

            <button @click="emitTrigger('Scaffolding safety')" class="quick-action-btn" style="flex: none;">
              <span style="font-size: 20px;">🏗️</span>
              <span>Scaffolding safety</span>
            </button>

          </div>
        </div>

      </div>

      <!-- Custom Trigger Box -->
      <div class="card">
        <h3 style="font-size: 16px; font-weight: 600; margin-bottom: 16px; display: flex; align-items: center; gap: 8px;">
          🎯 Create Custom Trigger
        </h3>

        <div style="display: grid; grid-template-columns: 80px 1fr auto; gap: 12px; align-items: end;">

          <div>
            <label class="label">Emoji</label>
            <input v-model="customEmoji"
                   class="input"
                   placeholder="🔧"
                   maxlength="2"
                   style="text-align:center;font-size:20px;">
          </div>

          <div>
            <label class="label">Action / Query</label>
            <input v-model="customAction"
                   class="input"
                   placeholder="e.g., Welding safety requirements" />
          </div>

          <button @click="addCustomTrigger"
                  class="btn">
            <i data-lucide="plus" style="width: 16px; height: 16px;"></i> Add
          </button>

        </div>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'

const customEmoji = ref('')
const customAction = ref('')

function emitTrigger(action) {
  // Emit to parent (AskKing.vue or router page)
  emit('trigger', action)
}

const emit = defineEmits(['trigger'])

function addCustomTrigger() {
  if (!customEmoji.value || !customAction.value) return;

  emit('trigger', customAction.value)

  customEmoji.value = ''
  customAction.value = ''
}
</script>
