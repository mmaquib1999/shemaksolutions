<template>
  <div class="providers-page">

    <!-- Performance Comparison -->
    <div class="card performance-card">
      <h3 class="card-title">
        Provider Performance Comparison
      </h3>

      <div class="performance-grid">
        <div
          v-for="provider in performanceCard"
          :key="provider.name"
          class="performance-tile"
          :style="{ border: provider.border }"
        >
          <div class="performance-head">
            <span :style="{ color: provider.color }">{{ provider.name }}</span>
            <span v-if="provider.active" class="badge badge-active">
              Active
            </span>
          </div>
          <div class="muted">Avg: 1.2s</div>
        </div>
      </div>

      <div class="performance-footer">
        <p class="muted">
          Tip: Claude is currently fastest. Consider Grok for predictive safety edge.
        </p>
      </div>
    </div>

    <!-- Top Row -->
    <div class="top-row">
      <div>
        <p class="muted">Your API keys are AES-256 encrypted</p>
        <p class="muted fine">This month: CA$45 across all providers</p>
      </div>

      <button class="btn" @click="startCreate">
        + Add Provider
      </button>
    </div>

    <!-- Add/Edit Form -->
    <div v-if="showAddKey" class="card form-card">
      <h3 class="card-title">
        {{ editingKeyId ? "Edit Provider" : "Connect Provider" }}
      </h3>

      <div class="card-body">

        <div class="form-grid">
          <!-- Provider -->
          <div>
            <label class="label">Provider</label>
            <select v-model="form.provider" class="input" @change="updateModels">
              <option value="openai">OpenAI</option>
              <option value="anthropic">Anthropic</option>
              <option value="xai">xAI Grok</option>
              <option value="google">Google Gemini</option>
              <option value="deepseek">DeepSeek</option>
            </select>
            <has-error :form="form" field="provider" />
          </div>

          <!-- Model -->
          <div>
            <label class="label">Model</label>
            <select v-model="form.model" class="input">
              <option v-for="m in providerModels" :key="m" :value="m">
                {{ m }}
              </option>
            </select>
            <has-error :form="form" field="model" />
          </div>
        </div>

        <!-- Name -->
        <div>
          <label class="label">Name</label>
          <input type="text" v-model="form.name" class="input" placeholder="e.g., Production Claude" />
          <has-error :form="form" field="name" />
        </div>

        <!-- API Key -->
        <div>
          <label class="label">API Key</label>
          <div class="input-with-button">
            <input
              :type="showApi ? 'text' : 'password'"
              v-model="form.api_key"
              class="input"
              placeholder="sk-..."
              style="font-family: monospace;"
            />
            <button
              type="button"
              @click="showApi = !showApi"
              class="inline-button"
            >
              {{ showApi ? 'Hide' : 'Show' }}
            </button>
          </div>
          <p v-if="editingKeyId" class="muted fine">
            Leave blank to keep the existing key.
          </p>
          <has-error :form="form" field="api_key" />
        </div>

        <div class="actions">
          <button class="btn" @click="saveKey" :disabled="form.busy">
            <span v-if="form.busy">{{ editingKeyId ? "Updating..." : "Saving..." }}</span>
            <span v-else>{{ editingKeyId ? "Update" : "Save" }}</span>
          </button>

          <button class="btn-secondary" @click="cancelForm">
            Cancel
          </button>
        </div>

      </div>
    </div>

    <!-- List of Keys -->
    <div class="keys-list">
      <div
        v-for="key in keys"
        :key="key.id"
        class="card provider-card"
        :class="{ 'is-default': key.is_default }"
      >
        <div class="provider-left">

          <div class="avatar" :style="avatarStyle(key.provider)">
            <span class="avatar-icon">{{ providerIcon(key.provider) }}</span>
          </div>

          <div class="provider-meta">
            <div class="provider-head">
              <span class="provider-name">{{ key.name }}</span>

              <span
                v-if="key.is_default"
                class="badge badge-default"
              >
                Default
              </span>
            </div>

            <div class="muted">
              {{ providerName(key.provider) }} - {{ key.model }}
            </div>

            <div class="muted fine">
              {{ key.total_queries }} queries
            </div>
          </div>

        </div>

        <div class="provider-actions">

          <button
            v-if="!key.is_default"
            class="btn-secondary"
            @click="setDefault(key.id)"
          >
            Set Default
          </button>

          <button class="btn-secondary" @click="startEdit(key)">
            Edit
          </button>

          <button
            @click="deleteKey(key.id)"
            class="icon-button"
          >
            <span class="icon-emoji" aria-hidden="true">🗑️</span>
            <span class="sr-only">Delete</span>
          </button>
        </div>
      </div>
    </div>

  </div>

  <!-- Toast notifications -->
  <div v-if="toasts.length" class="toast-stack">
    <div
      v-for="toast in toasts"
      :key="toast.id"
      class="toast"
      :class="toast.type"
    >
      {{ toast.message }}
    </div>
  </div>
</template>

<script>
import { Form } from "vform";
import axios from "axios";

export default {
  data() {
    return {
      showAddKey: false,
      editingKeyId: null,

      providers: {
        openai: {
          name: "OpenAI",
          models: ["gpt-4o", "gpt-4-turbo", "gpt-3.5-turbo"],
          color: "#10B981",
          icon: "\u{1F916}",
          avatarBg: "linear-gradient(135deg, rgba(16,185,129,0.16), rgba(16,185,129,0.08))",
        },
        anthropic: {
          name: "Anthropic",
          models: ["claude-sonnet-4-20250514", "claude-3-5-haiku-20241022"],
          color: "#F97316",
          icon: "\u{1F4DA}",
          avatarBg: "linear-gradient(135deg, rgba(249,115,22,0.16), rgba(249,115,22,0.08))",
        },
        xai: {
          name: "xAI Grok",
          models: ["grok-3"],
          color: "#3B82F6",
          icon: "\u{1F680}",
          avatarBg: "linear-gradient(135deg, rgba(59,130,246,0.16), rgba(59,130,246,0.08))",
        },
        google: {
          name: "Google Gemini",
          models: ["gemini-1.5-pro"],
          color: "#EF4444",
          icon: "\u{1F310}",
          avatarBg: "linear-gradient(135deg, rgba(239,68,68,0.16), rgba(239,68,68,0.08))",
        },
        deepseek: {
          name: "DeepSeek",
          models: ["deepseek-chat"],
          color: "#8B5CF6",
          icon: "\u{2728}",
          avatarBg: "linear-gradient(135deg, rgba(139,92,246,0.16), rgba(139,92,246,0.08))",
        },
      },

      // Loaded from backend
      keys: [],

      // Simple toasts
      toasts: [],

      // vform instance
      form: new Form({
        provider: "openai",
        model: "gpt-4o",
        name: "",
        api_key: "",
        is_default: false,
      }),

      showApi: false,
    };
  },

  computed: {
    providerModels() {
      return this.providers[this.form.provider].models;
    },

    performanceCard() {
      return [
        { name: "OpenAI", color: "#10B981", border: "1px solid rgba(71,85,105,0.3)", active: false },
        { name: "Anthropic", color: "#F97316", border: "1px solid rgba(34,211,238,0.5)", active: true },
        { name: "xAI Grok", color: "#3B82F6", border: "1px solid rgba(71,85,105,0.3)", active: false },
        { name: "Google Gemini", color: "#EF4444", border: "1px solid rgba(71,85,105,0.3)", active: false },
      ];
    },
  },

  mounted() {
    this.loadKeys();
  },

  methods: {
    startCreate() {
      this.editingKeyId = null;
      this.form.reset();
      this.form.clear();
      this.showAddKey = true;
      this.showApi = false;
    },

    startEdit(key) {
      this.showAddKey = true;
      this.editingKeyId = key.id;
      this.form.clear();
      this.form.fill({
        provider: key.provider,
        model: key.model,
        name: key.name,
        api_key: key.api_key,
        is_default: key.is_default ?? false,
      });
      this.showApi = false;
    },

    loadKeys() {
      axios.get("/api/provider-keys").then((res) => {
        this.keys = res.data;
      });
    },

    updateModels() {
      this.form.model = this.providers[this.form.provider].models[0];
    },

    providerIcon(id) {
      return this.providers[id]?.icon || "\u{1F916}";
    },

    avatarStyle(id) {
      const provider = this.providers[id] || {};
      return {
        background: provider.avatarBg || `${provider.color || "#22d3ee"}1a`,
        color: provider.color || "#22d3ee",
        boxShadow: provider.color ? `0 10px 40px ${provider.color}26` : "none",
      };
    },

    providerName(id) {
      return this.providers[id].name;
    },

    async saveKey() {
      try {
        if (this.editingKeyId) {
          await this.form.put(`/api/provider-keys/${this.editingKeyId}`);
          this.pushToast("Provider updated", "success");
        } else {
          await this.form.post("/api/provider-keys");
          this.pushToast("Provider added", "success");
        }
        this.loadKeys();
        this.cancelForm();
      } catch (error) {
        this.pushToast(this.errorMessage(error), "error");
      }
    },

    cancelForm() {
      this.showAddKey = false;
      this.editingKeyId = null;
      this.form.reset();
      this.form.clear();
      this.showApi = false;
    },

    async setDefault(id) {
      try {
        await axios.put(`/api/provider-keys/${id}/default`);
        this.pushToast("Default provider updated", "success");
        this.loadKeys();
      } catch (error) {
        this.pushToast(this.errorMessage(error), "error");
      }
    },

    async deleteKey(id) {
      try {
        const confirmed = window.confirm("Delete this provider? This cannot be undone.");
        if (!confirmed) return;
        const del = new Form({});
        await del.delete(`/api/provider-keys/${id}`);
        this.keys = this.keys.filter((k) => k.id !== id);
        this.pushToast("Provider deleted", "success");
      } catch (error) {
        this.pushToast(this.errorMessage(error), "error");
      }
    },

    pushToast(message, type = "success") {
      const id = Date.now();
      this.toasts.push({ id, message, type });
      setTimeout(() => {
        this.toasts = this.toasts.filter((t) => t.id !== id);
      }, 3500);
    },

    errorMessage(error) {
      if (error?.response?.data?.message) return error.response.data.message;
      if (error?.message) return error.message;
      return "Something went wrong";
    },
  },
};
</script>

<style scoped>
.providers-page {
  max-width: 880px;
  margin: 0 auto;
  display: flex;
  flex-direction: column;
  gap: 16px;
  color: #e2e8f0;
}

.card {
  background: linear-gradient(145deg, rgba(15, 23, 42, 0.95), rgba(15, 23, 42, 0.85));
  border: 1px solid rgba(71, 85, 105, 0.35);
  border-radius: 16px;
  padding: 18px;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.35);
}

.performance-card {
  margin-bottom: 4px;
  background: linear-gradient(135deg, rgba(59, 130, 246, 0.08), rgba(139, 92, 246, 0.04));
  border: 1px solid rgba(59, 130, 246, 0.3);
}

.card-title {
  font-size: 15px;
  font-weight: 700;
  margin: 0 0 14px;
  display: flex;
  align-items: center;
  gap: 8px;
}

.performance-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 12px;
}

.performance-tile {
  padding: 12px;
  background: rgba(15, 23, 42, 0.5);
  border-radius: 10px;
}

.performance-head {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 6px;
  font-weight: 600;
  font-size: 13px;
}

.performance-footer {
  margin-top: 12px;
  padding-top: 12px;
  border-top: 1px solid rgba(71, 85, 105, 0.3);
}

.top-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 12px;
}

.muted {
  color: #94a3b8;
  font-size: 13px;
}

.fine {
  font-size: 12px;
}

.btn {
  background: linear-gradient(135deg, #0ea5e9, #22d3ee);
  color: #0b1729;
  border: none;
  padding: 10px 16px;
  border-radius: 12px;
  font-weight: 700;
  cursor: pointer;
  transition: transform 0.15s ease, box-shadow 0.15s ease;
  box-shadow: 0 10px 30px rgba(34, 211, 238, 0.2);
}

.btn:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

.btn:hover:not(:disabled) {
  transform: translateY(-1px);
}

.btn-secondary {
  background: rgba(148, 163, 184, 0.12);
  color: #cbd5e1;
  border: 1px solid rgba(148, 163, 184, 0.3);
  padding: 10px 16px;
  border-radius: 12px;
  cursor: pointer;
  transition: border-color 0.15s ease, background 0.15s ease;
}

.btn-secondary:hover {
  border-color: rgba(34, 211, 238, 0.3);
  background: rgba(148, 163, 184, 0.18);
}

.form-card {
  border: 1px solid rgba(34, 211, 238, 0.35);
}

.card-body {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.form-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
  gap: 12px;
}

.label {
  display: block;
  font-size: 12px;
  color: #cbd5e1;
  margin-bottom: 6px;
  letter-spacing: 0.01em;
}

.input {
  width: 100%;
  height: 44px;
  border-radius: 10px;
  border: 1px solid rgba(148, 163, 184, 0.25);
  background: rgba(148, 163, 184, 0.08);
  color: #e2e8f0;
  padding: 10px 12px;
  transition: border-color 0.15s ease, box-shadow 0.15s ease, background 0.15s ease;
}

.input:focus {
  outline: none;
  border-color: #22d3ee;
  box-shadow: 0 0 0 3px rgba(34, 211, 238, 0.12);
  background: rgba(148, 163, 184, 0.1);
}

.input-with-button {
  display: grid;
  grid-template-columns: 1fr auto;
  align-items: center;
  gap: 8px;
  padding: 0 6px 0 0;
  border-radius: 10px;
  border: 1px solid rgba(148, 163, 184, 0.25);
  background: rgba(148, 163, 184, 0.08);
}

.input-with-button:focus-within {
  border-color: #22d3ee;
  box-shadow: 0 0 0 3px rgba(34, 211, 238, 0.12);
  background: rgba(148, 163, 184, 0.1);
}

.input-with-button .input {
  border: none;
  background: transparent;
  box-shadow: none;
  height: 48px;
}

.input-with-button .input:focus {
  border: none;
  background: transparent;
  box-shadow: none;
}

.inline-button {
  background: rgba(34, 211, 238, 0.1);
  color: #22d3ee;
  border: 1px solid rgba(34, 211, 238, 0.3);
  border-radius: 10px;
  padding: 10px 12px;
  cursor: pointer;
  font-weight: 600;
  font-size: 12px;
  transition: background 0.15s ease, border-color 0.15s ease;
}

.inline-button:hover {
  background: rgba(34, 211, 238, 0.16);
  border-color: rgba(34, 211, 238, 0.45);
}

.actions {
  display: flex;
  gap: 12px;
  flex-wrap: wrap;
}

.keys-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.provider-card {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  flex-wrap: wrap;
  border: 1px solid rgba(71, 85, 105, 0.5);
  background: linear-gradient(135deg, rgba(15, 23, 42, 0.82), rgba(15, 23, 42, 0.74));
}

.provider-card.is-default {
  border-color: rgba(34, 211, 238, 0.5);
  box-shadow: 0 12px 40px rgba(34, 211, 238, 0.12);
}

.provider-left {
  display: flex;
  align-items: center;
  gap: 16px;
}

.avatar {
  width: 48px;
  height: 48px;
  border-radius: 14px;
  display: flex;
  align-items: center;
  justify-content: center;
  border: 1px solid rgba(255, 255, 255, 0.04);
}

.avatar-icon {
  font-size: 20px;
  line-height: 1;
}

.provider-meta {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.provider-head {
  display: flex;
  align-items: center;
  gap: 8px;
  flex-wrap: wrap;
}

.provider-name {
  font-weight: 700;
  font-size: 15px;
}

.badge {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 4px 8px;
  border-radius: 999px;
  font-size: 11px;
  font-weight: 700;
}

.badge-active,
.badge-default {
  background: rgba(34, 211, 238, 0.2);
  color: #22d3ee;
}

.provider-actions {
  display: flex;
  align-items: center;
  gap: 8px;
  flex-wrap: wrap;
}

.icon-button {
  background: rgba(148, 163, 184, 0.08);
  border: 1px solid rgba(148, 163, 184, 0.2);
  color: #cbd5e1;
  cursor: pointer;
  padding: 10px;
  border-radius: 12px;
  transition: border-color 0.15s ease, background 0.15s ease;
}

.icon-button:hover {
  border-color: rgba(34, 211, 238, 0.3);
  background: rgba(148, 163, 184, 0.14);
}

.icon-button i {
  display: block;
}

.icon-emoji {
  font-size: 16px;
  line-height: 1;
}

.sr-only {
  position: absolute;
  width: 1px;
  height: 1px;
  padding: 0;
  margin: -1px;
  overflow: hidden;
  clip: rect(0, 0, 0, 0);
  white-space: nowrap;
  border: 0;
}

.actions .btn,
.actions .btn-secondary {
  min-width: 120px;
  justify-content: center;
  display: inline-flex;
  align-items: center;
  text-align: center;
}

.performance-grid .muted,
.provider-meta .muted {
  color: #94a3b8;
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
