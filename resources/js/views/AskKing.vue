<template>
  <div class="flex">
  <!-- <Sidebar @navigate="handleSidebar" /> -->

   <div style="padding:24px; flex:1;">

    <!-- ROI / header card (slim) -->
    <div v-html="getROICard()" />

    <div class="query-layout" style="display:flex;gap:24px;margin-top:18px;">
      <!-- Left sidebar: industry + quick actions -->
      <div class="query-sidebar" style="width:300px;flex-shrink:0;">
        <div class="card" style="padding:16px;margin-bottom:16px;">
          <h3 style="font-size:14px;font-weight:600;margin-bottom:12px;">Select Industry</h3>
          <div style="display:flex;flex-direction:column;gap:6px;">
            <button
              v-for="i in industries"
              :key="i.id"
              @click="selectIndustry(i.id)"
              class="industry-btn"
              :style="{
                background: selectedIndustry === i.id ? i.color + '20' : 'transparent',
                color: selectedIndustry === i.id ? i.color : '#94a3b8',
                padding: '8px 10px',
                border: '1px solid rgba(71,85,105,0.12)',
                borderRadius: '8px',
                textAlign: 'left',
                cursor: 'pointer'
              }"
            >
              <span style="margin-right:8px;">{{ i.emoji }}</span> {{ i.name }}
            </button>
          </div>
        </div>

        <div v-if="currentIndustry" class="card" style="padding:16px;">
          <h3 :style="{ fontSize: '14px', fontWeight: 600, marginBottom: '12px', color: currentIndustry.color }">
            {{ currentIndustry.emoji }} {{ currentIndustry.name }} Quick Actions
          </h3>
          <div style="display:flex;flex-direction:column;gap:8px;">
            <button
              v-for="p in currentIndustry.prompts"
              :key="p.label"
              class="quick-action-btn"
              @click="handleQuery(p.query)"
              style="padding:8px;border-radius:8px;background:transparent;border:1px solid rgba(71,85,105,0.08);text-align:left;cursor:pointer"
            >
              <span style="margin-right:8px;">{{ p.icon }}</span> {{ p.label }}
            </button>
          </div>
        </div>
      </div>

      <!-- Main content -->
      <div style="flex:1;">
        <!-- small stats row -->
        <div class="dashboard-stats" style="display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:20px;">
          <div class="card" style="padding:16px;">
            <div style="font-size:12px;color:#64748b;margin-bottom:4px;">Queries Used</div>
            <div style="font-size:24px;font-weight:bold;">{{ mockUsage.queries_used.toLocaleString() }}</div>
            <div style="font-size:11px;color:#64748b;">of {{ mockUsage.queries_limit.toLocaleString() }}</div>
            <div style="margin-top:8px;height:4px;background:rgba(51,65,85,0.8);border-radius:2px;overflow:hidden;">
              <div :style="{ height: '4px', background: 'linear-gradient(90deg,#0ea5e9,#06b6d4)', width: (mockUsage.queries_limit ? (mockUsage.queries_used / mockUsage.queries_limit) * 100 : 0) + '%' }"></div>
            </div>
          </div>

          <div class="card" style="padding:16px;">
            <div style="font-size:12px;color:#64748b;margin-bottom:4px;">Active Provider</div>
            <div style="font-size:24px;font-weight:bold;">
              {{ lastProviderDisplay || '—' }}
            </div>
            <div style="font-size:11px;color:#64748b;">
              {{ lastProviderVendor || '—' }}
            </div>
          </div>

          <div class="card" style="padding:16px;">
            <div style="font-size:12px;color:#64748b;margin-bottom:4px;">Team Members</div>
            <div style="font-size:24px;font-weight:bold;">{{ teamCount }}</div>
            <div style="font-size:11px;color:#64748b;">Active</div>
          </div>

          <div class="card" style="padding:16px;">
            <div style="font-size:12px;color:#64748b;margin-bottom:4px;">Avg Response</div>
            <div style="font-size:24px;font-weight:bold;">{{ mockUsage.avg_response_time }}s</div>
            <div style="font-size:11px;color:#10b981;">↓ 8% faster</div>
          </div>
        </div>

        <!-- Query card -->
        <div class="card" style="padding:0;overflow:hidden;">
          <div style="padding:16px 20px;background:rgba(30,41,59,0.8);border-bottom:1px solid rgba(71,85,105,0.3);display:flex;align-items:center;justify-content:space-between;">
            <div style="display:flex;align-items:center;gap:8px;">
              <span>🛡️</span>
              <span style="font-weight:600;">How can I help you today?</span>
            </div>
            <span v-if="currentIndustry" :style="{ fontSize: '12px', padding: '4px 10px', borderRadius: '6px', background: currentIndustry.color + '20', color: currentIndustry.color }">
              {{ currentIndustry.emoji }} {{ currentIndustry.name }}
            </span>
          </div>

          <div style="padding:20px;">
            <textarea
              id="query-input"
              v-model="queryInput"
              rows="3"
              class="input"
              placeholder="Type your safety question here..."
              style="width:100%;resize:vertical;padding:12px;border-radius:10px;background:rgba(15,23,42,0.5);border:1px solid rgba(71,85,105,0.3);color:#e2e8f0;font-family:ui-monospace,monospace;"
            ></textarea>

            <div style="display:flex;justify-content:space-between;align-items:center;margin-top:16px;flex-wrap:wrap;gap:12px;">
              <div style="display:flex;gap:6px;flex-wrap:wrap;">
                <button
                  v-for="t in quickTags"
                  :key="t"
                  @click="handleQuery(t + ' safety requirements')"
                  style="padding:6px 12px;border-radius:6px;border:1px solid rgba(71,85,105,0.3);background:transparent;color:#94a3b8;font-size:12px;cursor:pointer;"
                >
                  {{ t }}
                </button>
              </div>
              <div style="display:flex;gap:8px;">
                <button
                  @click="submitQuery"
                  class="btn"
                  :disabled="loading"
                  style="padding:8px 14px;border-radius:8px;background:linear-gradient(135deg,#0ea5e9,#06b6d4);border:none;color:white;cursor:pointer;opacity:{{ loading ? 0.7 : 1 }};"
                >
                  {{ loading ? '⟳ Asking K.I.N.G...' : '🚀 Ask K.I.N.G.' }}
                </button>
              </div>
            </div>
          </div>

          <!-- Response area -->
          <div id="response-area" style="padding:0 20px 20px 20px;"></div>
        </div>
      </div>
    </div>

    <!-- Quick Triggers block -->
    <!-- <QuickTriggers
      @select="handleQuery"
      @add-category="onAddCategory"
      @add-trigger="onAddTrigger"
      @delete-trigger="onDeleteTrigger"
      @delete-category="onDeleteCategory"
      @export="onExportTriggers"
      @reset="onResetTriggers"
    /> -->

    <!-- Query History panel -->
    <div style="margin-top:20px;">
      <div class="card" style="padding:12px;">
        <h3 style="font-weight:600;margin-bottom:8px;">Recent Query History ({{ queryHistory.length }} entries)</h3>
        <div style="max-height:320px;overflow-y:auto;">
          <div v-if="queryHistory.length === 0" style="text-align:center;padding:40px;color:#64748b;">
            <div style="font-size:32px;margin-bottom:12px;">🔭</div>
            <p>No queries yet. Start asking K.I.N.G. safety questions.</p>
          </div>
          <div v-else>
            <div
              v-for="(q, idx) in recentHistory"
              :key="q.id"
              style="padding:12px;border-bottom:1px solid rgba(71,85,105,0.3);"
            >
              <div style="display:flex;justify-content:space-between;margin-bottom:8px;">
                <span style="font-weight:600;font-size:13px;">
                  {{ q.query.length > 60 ? q.query.substring(0, 60) + '...' : q.query }}
                </span>
                <span style="font-size:11px;color:#64748b;">{{ formatDate(q.timestamp) }}</span>
              </div>
              <div style="display:flex;gap:8px;align-items:center;">
                <span
                  class="badge"
                  style="background:rgba(99,102,241,0.2);color:#818cf8;font-size:10px;padding:4px 8px;border-radius:6px;"
                >
                  {{ q.industry }}
                </span>
                <span style="font-size:11px;color:#64748b;">by {{ q.user }}</span>
                <div style="margin-left:auto;display:flex;gap:8px;">
                  <button
                    @click="replayQuery(q)"
                    style="background:none;border:1px solid rgba(71,85,105,0.12);padding:6px 8px;border-radius:6px;cursor:pointer;"
                  >
                    ↺ Replay
                  </button>
                  <button
                    @click="downloadQueryText(q)"
                    style="background:none;border:1px solid rgba(71,85,105,0.12);padding:6px 8px;border-radius:6px;cursor:pointer;"
                  >
                    ⬇
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Toast container (empty, created dynamically) -->
  </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import axios from 'axios'
import { Form } from 'vform'
import Sidebar from '../components/Sidebar.vue'
import Header from '../components/Header.vue'
import QuickTriggers from '../components/QuickTriggers.vue'

/**
 * AskKing.vue
 * - Same UI as your HTML design
 * - Now calls Laravel /api/ask-king
 * - Uses provider/model/emergency_detected from backend
 */

/* ---------------------------
   Hardcoded demo user & data
   --------------------------- */
const demoUser = reactive({
  name: 'Claudino Nelson',
  email: 'claudino@shemaksolutions.ca',
  company: 'Shema K Solutions'
})

const mockUsage = reactive({
  queries_used: 0,
  queries_limit: 0,
  hours_saved: 0,
  avg_response_time: 0
})
const teamMembers = ref([])

/* ---------------------------
   Industry data (hardcoded)
   --------------------------- */
const industries = [
  {
    id: 'construction',
    name: 'Construction',
    color: '#F59E0B',
    emoji: '🏗️',
    prompts: [
      {
        label: 'Fall Protection',
        query: 'Fall protection requirements for heights above 6 feet',
        icon: '🪂'
      },
      {
        label: 'Scaffolding',
        query: 'OSHA scaffolding safety requirements',
        icon: '🏗️'
      },
      {
        label: 'PPE Requirements',
        query: 'What PPE is required on a construction site?',
        icon: '🦺'
      }
    ]
  },
  {
    id: 'manufacturing',
    name: 'Manufacturing',
    color: '#6366F1',
    emoji: '🏭',
    prompts: [
      {
        label: 'Machine Guarding',
        query: 'Machine guarding requirements',
        icon: '⚙️'
      },
      {
        label: 'LOTO Procedures',
        query: 'Lockout/tagout procedures step by step',
        icon: '🔒'
      },
      {
        label: 'Forklift Operations',
        query: 'Powered industrial truck safety requirements',
        icon: '🚜'
      }
    ]
  },
  {
    id: 'oilgas',
    name: 'Oil & Gas',
    color: '#EF4444',
    emoji: '🛢️',
    prompts: [
      {
        label: 'H2S Safety',
        query: 'H2S safety requirements and exposure limits',
        icon: '☠️'
      },
      {
        label: 'Hot Work Permits',
        query: 'Hot work permit requirements',
        icon: '🔥'
      },
      {
        label: 'Confined Space',
        query: 'Confined space entry requirements',
        icon: '🚪'
      }
    ]
  },
  {
    id: 'healthcare',
    name: 'Healthcare',
    color: '#EC4899',
    emoji: '🏥',
    prompts: [
      {
        label: 'Bloodborne Pathogens',
        query: 'Bloodborne pathogen exposure control',
        icon: '🩸'
      },
      {
        label: 'Sharps Safety',
        query: 'Sharps injury prevention requirements',
        icon: '💉'
      }
    ]
  },
  {
    id: 'agriculture',
    name: 'Agriculture',
    color: '#84CC16',
    emoji: '🌾',
    prompts: [
      {
        label: 'Tractor Safety',
        query: 'Agricultural tractor and machinery safety',
        icon: '🚜'
      },
      {
        label: 'Grain Handling',
        query: 'Grain bin and silo entry safety',
        icon: '🌾'
      }
    ]
  },
  {
    id: 'warehouse',
    name: 'Warehouse',
    color: '#8B5CF6',
    emoji: '📦',
    prompts: [
      {
        label: 'Forklift Safety',
        query: 'Warehouse forklift operation requirements',
        icon: '🚜'
      },
      {
        label: 'Rack Safety',
        query: 'Pallet rack safety and inspection',
        icon: '🗄️'
      }
    ]
  }
]

/* ---------------------------
   Component state
   --------------------------- */
const selectedIndustry = ref('manufacturing') // default
const queryInput = ref('')
const lastResponse = ref('')
const queryHistory = ref([])
const loading = ref(false)
const lastQueryId = ref(0)
const form = new Form({
  message: ''
})
const teamCount = ref(teamMembers.value.length)
const apiError = ref('')

// last provider info from API
const lastProvider = ref('')
const lastModel = ref('')

const quickTags = ['🔥 Fire', '⚡ Electrical', '🔒 LOTO', '🦺 PPE', '🪜 Ladders', '😷 Respiratory']

const currentIndustry = computed(
  () => industries.find(i => i.id === selectedIndustry.value) || null
)

/* derived provider display text */
const lastProviderDisplay = computed(() => {
  if (!lastProvider.value) return ''
  if (lastProvider.value === 'grok' || lastProvider.value === 'xai') return 'Grok-4.1'
  if (lastProvider.value === 'openai') return 'GPT'
  if (lastProvider.value === 'anthropic' || lastProvider.value === 'claude') return 'Claude'
  if (lastProvider.value === 'gemini' || lastProvider.value === 'google') return 'Gemini'
  if (lastProvider.value === 'deepseek') return 'DeepSeek'
  return lastProvider.value
})

const lastProviderVendor = computed(() => {
  if (!lastProvider.value) return ''
  if (lastProvider.value === 'grok' || lastProvider.value === 'xai') return 'xAI'
  if (lastProvider.value === 'openai') return 'OpenAI'
  if (lastProvider.value === 'anthropic' || lastProvider.value === 'claude') return 'Anthropic'
  if (lastProvider.value === 'gemini' || lastProvider.value === 'google') return 'Google'
  if (lastProvider.value === 'deepseek') return 'DeepSeek'
  return lastProvider.value
})

/* helper: format date */
function formatDate(ts) {
  const d = new Date(ts)
  return d.toLocaleString()
}

/* ---------- Toast helper ---------- */
function showToast(msg) {
  const t = document.createElement('div')
  t.style.cssText =
    'position:fixed;bottom:24px;right:24px;background:linear-gradient(135deg,#10b981,#059669);color:#fff;padding:12px 20px;border-radius:10px;font-size:14px;font-weight:500;z-index:1000;animation:slideUp 0.3s ease-out;box-shadow:0 8px 24px rgba(16,185,129,0.4);'
  t.textContent = msg
  document.body.appendChild(t)
  setTimeout(() => {
    t.remove()
  }, 3000)
}

/* ---------------------------
   Core functions
   --------------------------- */
function selectIndustry(id) {
  selectedIndustry.value = id
}

function submitQuery() {
  if (!queryInput.value || !queryInput.value.trim()) {
    showToast('⚠️ Please enter a question')
    return
  }
  handleQuery(queryInput.value.trim())
}

/* Detect high-risk using same regex as original */
function isHighRiskQuery(q) {
  return /loto|lockout|tagout|confined\s*space|electrical|high\s*voltage|arc\s*flash|energized/i.test(
    q
  )
}

/* API helper */
async function callAskKingAPI(message) {
  apiError.value = ''
  try {
    form.message = message
    const res = await form.post('/api/ask-king')
    return res.data
  } catch (error) {
    const msg = error?.response?.data?.error || error?.message || 'Unknown error'
    apiError.value = msg
    showToast('ƒ?O ' + msg)
    return {
      success: false,
      content: 'API Error: ' + msg
    }
  }
}

/* Main handler: now uses REAL API */
async function handleQuery(query) {
  queryInput.value = query
  lastQueryId.value = Date.now()
  const id = lastQueryId.value
  const timestamp = new Date().toISOString()
  const industry = selectedIndustry.value
  const user = demoUser.name

  // show processing
  const ra = document.getElementById('response-area')
  if (ra) {
    ra.innerHTML = `
      <div style="padding:20px;background:rgba(15,23,42,0.5);border-top:1px solid rgba(71,85,105,0.3);">
        <div style="display:flex;align-items:center;gap:8px;color:#22d3ee;">
          <span style="display:inline-block;animation:spin 1s linear infinite;">⟳</span>
          Processing with K.I.N.G. Framework v17.0...
        </div>
      </div>`
  }

  // push to history
  queryHistory.value.push({ id, query, timestamp, industry, user })

  loading.value = true

  const apiRes = await callAskKingAPI(query)

  loading.value = false

  if (!apiRes.success) {
    lastResponse.value = apiRes.content || 'No response'
    renderResponse(query, lastResponse.value, false, '', '', false)
    showToast('❌ ' + (apiRes.content || 'Request failed'))
    return
  }

  // backend fields: content, model, provider, emergency_detected
  lastResponse.value = apiRes.content
  lastProvider.value = apiRes.provider || ''
  lastModel.value = apiRes.model || ''
  if (typeof apiRes.total_queries === 'number') {
    mockUsage.queries_used = apiRes.total_queries
  }
  if (typeof apiRes.team_members === 'number') {
    teamCount.value = apiRes.team_members
  }
  if (typeof apiRes.avg_response === 'number') {
    mockUsage.avg_response_time = apiRes.avg_response
  }

  const highRisk = isHighRiskQuery(query) || !!apiRes.emergency_detected

  renderResponse(
    query,
    lastResponse.value,
    highRisk,
    apiRes.model,
    apiRes.provider,
    apiRes.emergency_detected
  )

  // bump mock usage a bit to simulate real usage
  mockUsage.queries_used++
}

/* Render response area with controls (export buttons + high risk badge) */
function renderResponse(
  query,
  responseText,
  highRisk,
  model = '',
  provider = '',
  isEmergency = false
) {
  const ra = document.getElementById('response-area')
  if (!ra) return

  const meta = provider || model ? `(${provider || 'provider'} • ${model || 'model'})` : ''

  ra.innerHTML = `
    <div style="padding:20px;background:rgba(15,23,42,0.5);border-top:1px solid rgba(71,85,105,0.3);">
      <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;flex-wrap:wrap;gap:12px;">
        <div style="display:flex;align-items:center;gap:8px;color:#10b981;">
          ✓ <span style="font-weight:600;">Here's what I found</span>
          ${
            meta
              ? `<span style="font-size:11px;color:#64748b;margin-left:8px;">${meta}</span>`
              : ''
          }
          ${
            highRisk
              ? `<span class="badge" style="background:rgba(239,68,68,0.2);color:#ef4444;margin-left:8px;padding:4px 8px;border-radius:8px;">⚠️ HIGH RISK</span>`
              : ''
          }
          ${
            isEmergency
              ? `<span class="badge" style="background:rgba(248,113,113,0.2);color:#f97373;margin-left:8px;padding:4px 8px;border-radius:8px;">🚨 EMERGENCY DETECTED</span>`
              : ''
          }
        </div>
        <div style="display:flex;gap:8px;">
          <button id="export-pdf-btn" style="padding:8px 12px;border-radius:8px;border:1px solid rgba(71,85,105,0.12);background:transparent;cursor:pointer;">📄 Export PDF</button>
          <button id="export-jsa-btn" style="${
            highRisk
              ? 'padding:10px 16px;font-size:13px;background:linear-gradient(135deg,#ef4444,#dc2626);border:none;color:white;border-radius:8px;animation:pulse-glow 2s ease-in-out infinite;cursor:pointer;'
              : 'padding:8px 12px;border-radius:8px;border:1px solid rgba(71,85,105,0.12);background:transparent;cursor:pointer;'
          }">📋 Create JSA${highRisk ? ' ⚠️' : ''}</button>
        </div>
      </div>
      <pre style="white-space:pre-wrap;font-family:ui-monospace,monospace;font-size:13px;line-height:1.6;padding:16px;background:rgba(15,23,42,0.8);border:1px solid ${
        highRisk ? 'rgba(239,68,68,0.3)' : 'rgba(71,85,105,0.3)'
      };border-radius:10px;color:#e2e8f0;margin:0;overflow-x:auto;">${escapeHtml(
    responseText
  )}</pre>
      ${
        highRisk
          ? `<div style="margin-top:12px;padding:12px;background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.3);border-radius:8px;display:flex;align-items:center;gap:10px;"><span style="font-size:20px;">⚠️</span><div><div style="font-weight:600;color:#f87171;">High-Risk Activity Detected</div><div style="font-size:12px;color:#94a3b8;">A Job Safety Analysis (JSA) is strongly recommended before proceeding.</div></div></div>`
          : ''
      }
    </div>
  `

  // Attach event listeners for export buttons
  const pdfBtn = document.getElementById('export-pdf-btn')
  const jsaBtn = document.getElementById('export-jsa-btn')
  if (pdfBtn) pdfBtn.addEventListener('click', exportToPDF)
  if (jsaBtn) jsaBtn.addEventListener('click', exportToJSA)
}

/* Escape HTML for safe pre text insertion */
function escapeHtml(str) {
  return String(str)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
}

/* Export functions (text blobs) */
function exportToPDF() {
  const content = lastResponse.value || 'No response'
  const blob = new Blob([content], { type: 'text/plain' })
  const url = URL.createObjectURL(blob)
  const a = document.createElement('a')
  a.href = url
  a.download = `KING-Report-${new Date().toISOString().split('T')[0]}.txt`
  a.click()
  URL.revokeObjectURL(url)
  showToast('📄 Report exported!')
}

function exportToJSA() {
  const jsa = `JOB SAFETY ANALYSIS (JSA)
Generated by K.I.N.G. Framework v17.0
Date: ${new Date().toLocaleDateString()}
${'━'.repeat(40)}

JOB TITLE: ____________________
DEPARTMENT: ____________________

REQUIRED PPE:
☐ Hard Hat  ☐ Safety Glasses  ☐ Steel-Toe Boots
☐ Hi-Vis Vest  ☐ Gloves  ☐ Hearing Protection

STEP | HAZARD | CONTROL
-----|--------|--------
1.   |        |
2.   |        |
3.   |        |

${lastResponse.value ? '\nREFERENCE:\n' + lastResponse.value : ''}`
  const blob = new Blob([jsa], { type: 'text/plain' })
  const url = URL.createObjectURL(blob)
  const a = document.createElement('a')
  a.href = url
  a.download = `JSA-${new Date().toISOString().split('T')[0]}.txt`
  a.click()
  URL.revokeObjectURL(url)
  showToast('📋 JSA exported!')
}

/* Additional exports: download single query text */
function downloadQueryText(q) {
  const content = `Query: ${q.query}
Time: ${formatDate(q.timestamp)}
Industry: ${q.industry}
User: ${q.user}`
  const blob = new Blob([content], { type: 'text/plain' })
  const url = URL.createObjectURL(blob)
  const a = document.createElement('a')
  a.href = url
  a.download = `KING-Query-${new Date(q.timestamp).toISOString().split('T')[0]}.txt`
  a.click()
  URL.revokeObjectURL(url)
  showToast('⬇ Query downloaded')
}

/* Replay query */
function replayQuery(q) {
  handleQuery(q.query)
}

/* convenience: recent history reversed */
const recentHistory = computed(() => queryHistory.value.slice().reverse())

/* export all audit log */
function exportAuditLog(type = 'summary') {
  const header = `K.I.N.G. FRAMEWORK AUDIT LOG
Generated: ${new Date().toISOString()}
Organization: ${demoUser.company}
${'━'.repeat(50)}

`
  let content = header
  if (type === 'full') {
    content += 'FULL QUERY LOG\n' + '-'.repeat(50) + '\n\n'
    queryHistory.value.forEach((q, i) => {
      content += `[${i + 1}] ${formatDate(q.timestamp)}
User: ${q.user}
Industry: ${q.industry}
Query: ${q.query}

`
    })
  } else {
    content += `SUMMARY REPORT
${'-'.repeat(50)}

Total Queries: ${mockUsage.queries_used}
Hours Saved: ${mockUsage.hours_saved}
Active Team Members: ${teamCount.value}
Average Response Time: ${mockUsage.avg_response_time}s

COMPLIANCE STATUS: ✓ OSHA Ready
`
  }
  content += `

${'━'.repeat(50)}
Generated by K.I.N.G. Framework v17.0
© 2025 Shema K Solutions Corporation`
  const blob = new Blob([content], { type: 'text/plain' })
  const url = URL.createObjectURL(blob)
  const a = document.createElement('a')
  a.href = url
  a.download = `KING-Audit-${type}-${new Date().toISOString().split('T')[0]}.txt`
  a.click()
  URL.revokeObjectURL(url)
  showToast('📋 Audit log exported!')
}

/* ROI Card HTML builder */
function getROICard() {
  const usagePct =
    mockUsage.queries_limit > 0
      ? (mockUsage.queries_used / mockUsage.queries_limit) * 100
      : 0
  const dailyAvg = mockUsage.queries_used / 18
  const daysToLimit =
    mockUsage.queries_limit > 0
      ? Math.round((mockUsage.queries_limit - mockUsage.queries_used) / (dailyAvg || 1))
      : 0
  const nagBanner =
    usagePct >= 90
      ? `<div style="padding:12px 20px;border-radius:12px;display:flex;align-items:center;justify-content:space-between;gap:16px;margin-bottom:16px;background:linear-gradient(135deg,rgba(239,68,68,0.15) 0%,rgba(220,38,38,0.1) 100%);border:1px solid rgba(239,68,68,0.3);"><div style="display:flex;align-items:center;gap:12px;"><span style="font-size:24px;">🚨</span><div><div style="font-weight:600;color:#f87171;">Only ${
          mockUsage.queries_limit - mockUsage.queries_used
        } queries left!</div><div style="font-size:13px;color:#94a3b8;">Upgrade now to keep your team protected.</div></div></div><button onclick="document.dispatchEvent(new CustomEvent('open-upgrade'))" class="btn" style="white-space:nowrap;">Upgrade Now</button></div>`
      : usagePct >= 50
      ? `<div style="padding:12px 20px;border-radius:12px;display:flex;align-items:center;justify-content:space-between;gap:16px;margin-bottom:16px;background:linear-gradient(135deg,rgba(14,165,233,0.1) 0%,rgba(6,182,212,0.05) 100%);border:1px solid rgba(34,211,238,0.3);"><div style="display:flex;align-items:center;gap:12px;"><span style="font-size:24px;">📈</span><div><div style="font-weight:600;color:#22d3ee;">Great momentum! ${Math.round(
          100 - usagePct
        )}% remaining</div><div style="font-size:13px;color:#94a3b8;">~${daysToLimit} days until limit at current pace. Preview unlimited?</div></div></div><button onclick="document.dispatchEvent(new CustomEvent('open-upgrade'))" class="btn-secondary" style="white-space:nowrap;font-size:12px;">Preview Unlimited</button></div>`
      : ''
  const card = `${nagBanner}<div class="card roi-card" style="margin-bottom:20px;padding:16px;border-radius:12px;background:linear-gradient(135deg,rgba(15,23,42,0.6),rgba(10,20,30,0.6));">
    <div style="display:flex;align-items:center;gap:8px;margin-bottom:16px;"><span style="font-size:24px;">💰</span><h3 style="font-size:16px;font-weight:600;margin:0;">This Month's Impact</h3><span style="background:rgba(16,185,129,0.2);color:#10b981;margin-left:auto;padding:6px 8px;border-radius:8px;font-size:12px;">Live ROI</span></div>
    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:16px;">
      <div style="text-align:center;padding:16px;background:rgba(15,23,42,0.5);border-radius:12px;"><div style="font-size:28px;font-weight:bold;color:#22d3ee;">${
        mockUsage.hours_saved
      }</div><div style="font-size:12px;color:#64748b;">Hours Saved</div><div style="font-size:11px;color:#10b981;margin-top:4px;">CA$7,100 value</div></div>
      <div style="text-align:center;padding:16px;background:rgba(15,23,42,0.5);border-radius:12px;"><div style="font-size:28px;font-weight:bold;color:#10b981;">5</div><div style="font-size:12px;color:#64748b;">Incidents Prevented</div><div style="font-size:11px;color:#10b981;margin-top:4px;">CA$210K impact</div></div>
      <div style="text-align:center;padding:16px;background:rgba(15,23,42,0.5);border-radius:12px;"><div style="font-size:28px;font-weight:bold;color:#fbbf24;">${
        mockUsage.avg_response_time
      }s</div><div style="font-size:12px;color:#64748b;">Avg Response</div><div style="font-size:11px;color:#10b981;margin-top:4px;">↓8% faster</div></div>
      <div style="text-align:center;padding:16px;background:rgba(15,23,42,0.5);border-radius:12px;"><div style="font-size:28px;font-weight:bold;color:#8b5cf6;">CA$45</div><div style="font-size:12px;color:#64748b;">API Cost</div><div style="font-size:11px;color:#64748b;margin-top:4px;">This month</div></div>
    </div>
    <div style="margin-top:16px;padding-top:16px;border-top:1px solid rgba(71,85,105,0.3);display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;">
      <div style="font-size:13px;color:#94a3b8;"><strong style="color:#10b981;">Total Value:</strong> CA$217,100</div>
      <div style="display:flex;align-items:center;gap:8px;"><span style="font-size:12px;color:#64748b;">ROI:</span><span style="font-size:16px;font-weight:bold;color:#fbbf24;">2300× 👑</span></div>
      <div style="display:flex;align-items:center;gap:6px;padding:6px 12px;background:rgba(139,92,246,0.1);border:1px solid rgba(139,92,246,0.3);border-radius:8px;"><span style="font-size:14px;">📊</span><span style="font-size:12px;color:#a78bfa;"><strong>${daysToLimit}</strong> days to limit at current pace</span></div>
    </div>
  </div>`
  return card
}

/* ---------------------------
   QuickTriggers event handlers
   --------------------------- */
function onAddCategory({ name }) {
  showToast(`Category added: ${name}`)
}

function onDeleteCategory({ category }) {
  showToast(`Category removed: ${category}`)
}

function onAddTrigger({ category, emoji, action }) {
  showToast(`Trigger added to ${category}: ${emoji} ${action}`)
}

function onDeleteTrigger({ category, action }) {
  showToast(`Trigger removed from ${category}: ${action}`)
}

function onExportTriggers(payload) {
  const total = payload?.total ?? 0
  const custom = payload?.custom ?? 0
  showToast(`Exported ${total} triggers (${custom} custom)`)
}

function onResetTriggers() {
  showToast('Triggers reset to defaults')
}

async function loadDashboardStats() {
  try {
    const res = await axios.get('/api/dashboard-stats')
    if (typeof res.data.queries_used === 'number') {
      mockUsage.queries_used = res.data.queries_used
    }
    if (typeof res.data.queries_limit === 'number') {
      mockUsage.queries_limit = res.data.queries_limit
    }
    if (typeof res.data.team_members === 'number') {
      teamCount.value = res.data.team_members
    }
    if (typeof res.data.avg_response === 'number') {
      mockUsage.avg_response_time = res.data.avg_response
    }
    if (res.data.active_provider) {
      lastProvider.value = res.data.active_provider
    }
    if (res.data.active_model) {
      lastModel.value = res.data.active_model
    }
  } catch (error) {
    console.error('Failed to load dashboard stats', error)
  }
}

onMounted(() => {
  loadDashboardStats()
})
</script>

<style scoped>
@keyframes spin {
  from {
    transform: rotate(0deg);
  }
  to {
    transform: rotate(360deg);
  }
}
@keyframes slideUp {
  from {
    transform: translateY(8px);
    opacity: 0;
  }
  to {
    transform: translateY(0);
    opacity: 1;
  }
}
.badge {
  padding: 6px 8px;
  border-radius: 8px;
  font-size: 12px;
}
.card {
  background: rgba(15, 23, 42, 0.6);
  border-radius: 12px;
  padding: 12px;
  border: 1px solid rgba(71, 85, 105, 0.15);
  color: #e2e8f0;
}
.btn {
  background: linear-gradient(135deg, #0ea5e9, #06b6d4);
  color: #fff;
  border: none;
  padding: 8px 12px;
  border-radius: 8px;
  cursor: pointer;
}
.btn-secondary {
  background: transparent;
  border: 1px solid rgba(71, 85, 105, 0.12);
  color: #cbd5e1;
  padding: 8px 12px;
  border-radius: 8px;
  cursor: pointer;
}
.industry-btn {
  transition: all 0.12s ease;
}
.industry-btn:hover {
  transform: translateY(-2px);
}
.quick-action-btn {
  padding: 8px;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.12s ease;
}
.quick-action-btn:hover {
  transform: translateY(-2px);
}
.leaderboard-item {
  display: flex;
  gap: 12px;
  align-items: center;
  padding: 8px 0;
}
</style>
