<template>
  <div style="flex: 1; overflow: auto; padding: 24px;" id="main-content">
    <div style="max-width: 800px; margin: 0 auto;">

      <div style="display: flex; gap: 24px; flex-wrap: wrap;">

        <!-- LEFT NAV -->
        <div style="width: 180px; flex-shrink: 0;">
          <button
            class="nav-btn"
            :class="{ active: activeSection === 'account' }"
            @click="activeSection = 'account'"
          >
            👤 Account
          </button>

          <button
            class="nav-btn"
            :class="{ active: activeSection === 'security' }"
            @click="activeSection = 'security'"
          >
            🔒 Security
          </button>

          <button
            class="nav-btn"
            :class="{ active: activeSection === 'notifications' }"
            @click="activeSection = 'notifications'"
          >
            🔔 Notifications
          </button>
        </div>

        <!-- RIGHT CONTENT -->
        <div style="flex: 1; min-width: 300px;" id="settings-content">

          <!-- ACCOUNT TAB -->
          <div v-if="activeSection === 'account'" class="card">
            <h3 style="font-weight: 600; margin-bottom: 20px;">Account</h3>

            <div style="display: flex; flex-direction: column; gap: 16px;">
              <div>
                <label class="label">Name</label>
                <input type="text" class="input" v-model="form.name">
              </div>

              <div>
                <label class="label">Email</label>
                <input type="email" class="input" v-model="form.email">
              </div>

              <div>
                <label class="label">Company</label>
                <input type="text" class="input" v-model="form.company">
              </div>

              <button class="btn" style="align-self: flex-start;" @click="saveAccount">
                Save Changes
              </button>
            </div>
          </div>

          <!-- SECURITY TAB -->
          <div v-if="activeSection === 'security'" class="card">
            <h3 style="font-weight: 600; margin-bottom: 20px;">Security</h3>

            <div style="display: flex; flex-direction: column; gap: 16px;">

              <div>
                <label class="label">Current Password</label>
                <input type="password" class="input" v-model="security.current">
              </div>

              <div>
                <label class="label">New Password</label>
                <input type="password" class="input" v-model="security.newPassword">
              </div>

              <div>
                <label class="label">Confirm Password</label>
                <input type="password" class="input" v-model="security.confirmPassword">
              </div>

              <button class="btn" style="align-self: flex-start;" @click="updatePassword">
                Update Password
              </button>
            </div>
          </div>

          <!-- NOTIFICATIONS TAB -->
          <div v-if="activeSection === 'notifications'" class="card">
            <h3 style="font-weight: 600; margin-bottom: 20px;">Notifications</h3>

            <div style="display: flex; flex-direction: column; gap: 16px;">
              
              <label class="label">
                <input type="checkbox" v-model="notifications.emailAlerts" style="margin-right: 8px;">
                Email Alerts
              </label>

              <label class="label">
                <input type="checkbox" v-model="notifications.systemUpdates" style="margin-right: 8px;">
                System Updates
              </label>

              <label class="label">
                <input type="checkbox" v-model="notifications.billing" style="margin-right: 8px;">
                Billing Notifications
              </label>

              <button class="btn" style="align-self: flex-start;" @click="saveNotifications">
                Save Preferences
              </button>
            </div>
          </div>

        </div>
      </div>

    </div>
  </div>
</template>

<script setup>
import { reactive, ref } from "vue";

const activeSection = ref("account");

const form = reactive({
  name: "Claudino Nelson",
  email: "claudino@shemaksolutions.ca",
  company: "Shema K Solutions"
});

const security = reactive({
  current: "",
  newPassword: "",
  confirmPassword: ""
});

const notifications = reactive({
  emailAlerts: true,
  systemUpdates: true,
  billing: false
});

function saveAccount() {
  console.log("Saved account:", form);
}

function updatePassword() {
  console.log("Password updated.");
}

function saveNotifications() {
  console.log("Notifications saved:", notifications);
}
</script>
