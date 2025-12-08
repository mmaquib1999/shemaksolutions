<template>
  <div style="flex: 1; overflow: auto; padding: 24px;" id="main-content">
    <div style="max-width: 900px; margin: 0 auto;">

      <!-- CURRENT PLAN CARD -->
      <div
        class="card"
        style="background: linear-gradient(135deg, rgba(14, 165, 233, 0.15) 0%, rgba(6, 182, 212, 0.1) 100%);
               border: 1px solid rgba(34, 211, 238, 0.3);
               margin-bottom: 24px;"
      >
        <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 16px;">
          <div>
            <div style="font-size: 13px; color: #22d3ee; font-weight: 500;">Current Plan</div>
            <div style="font-size: 32px; font-weight: bold; margin-top: 4px;">Professional</div>
            <div style="color: #94a3b8; margin-top: 8px; font-size: 14px;">$49/month • Renews Dec 15, 2025</div>
          </div>

          <button class="btn-secondary">
            <i data-lucide="external-link" style="width: 14px; height: 14px;"></i>
            Manage
          </button>
        </div>

        <div style="margin-top: 20px; padding-top: 20px; border-top: 1px solid rgba(34, 211, 238, 0.2);">
          <div style="display: flex; justify-content: space-between; font-size: 13px; margin-bottom: 8px;">
            <span style="color: #94a3b8;">Queries used</span>
            <span style="font-weight: 500;">2,847 / 10,000</span>
          </div>

          <div
            style="height: 8px; background: rgba(51, 65, 85, 0.8); border-radius: 4px; overflow: hidden;"
          >
            <div
              style="height: 8px;
                     background: linear-gradient(90deg, #0ea5e9, #06b6d4);
                     border-radius: 4px;
                     width: 28.47%;"
            ></div>
          </div>
        </div>
      </div>

      <!-- PLANS -->
      <h3 style="font-weight: 600; margin-bottom: 16px;">Plans</h3>

      <div
        class="pricing-grid"
        style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; margin-bottom: 32px;"
      >
        <!-- STARTER -->
        <div class="card" style="border: 1px solid rgba(71, 85, 105, 0.5);">
          <h4 style="font-weight: 600;">Starter</h4>
          <div style="font-size: 28px; font-weight: bold; margin: 12px 0;">
            $0
            <span style="font-size: 13px; color: #64748b; font-weight: normal;">/mo</span>
          </div>
          <button @click="selectPlan('starter')" class="btn w-full justify-center">
            Select
          </button>
        </div>

        <!-- PROFESSIONAL (current) -->
        <div
          class="card"
          style="border: 2px solid rgba(34, 211, 238, 0.5);
                 background: linear-gradient(135deg, rgba(14,165,233,0.1) 0%, rgba(6,182,212,0.1) 100%);"
        >
          <div
            style="color: #22d3ee; font-size: 11px; font-weight: 600; margin-bottom: 8px;
                   text-transform: uppercase;"
          >
            Popular
          </div>

          <h4 style="font-weight: 600;">Professional</h4>

          <div style="font-size: 28px; font-weight: bold; margin: 12px 0;">
            $49
            <span style="font-size: 13px; color: #64748b; font-weight: normal;">/mo</span>
          </div>

          <button class="btn w-full justify-center" style="background: rgba(71, 85, 105, 0.5);">
            Current
          </button>
        </div>

        <!-- ENTERPRISE -->
        <div class="card" style="border: 1px solid rgba(71, 85, 105, 0.5);">
          <h4 style="font-weight: 600;">Enterprise</h4>
          <div style="font-size: 28px; font-weight: bold; margin: 12px 0;">
            $299
            <span style="font-size: 13px; color: #64748b; font-weight: normal;">/mo</span>
          </div>

          <button @click="selectPlan('enterprise')" class="btn w-full justify-center">
            Select
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
function selectPlan(plan) {
  // Replace backend event later if needed
  console.log("Selected plan:", plan);
}
</script>
