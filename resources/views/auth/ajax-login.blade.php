<!-- AJAX Login Form -->
<form id="ajax-login-form" style="display:flex;flex-direction:column;gap:16px;">
  <div><label class="label">Email</label><input type="email" id="login_email" placeholder="you@company.com" class="input" required></div>
  <div><label class="label">Password</label><input type="password" id="login_password" placeholder="••••••••" class="input" required></div>
  <div style="display:flex;align-items:center;gap:8px;">
    <input type="checkbox" id="login_remember" style="width:16px;height:16px;">
    <label for="login_remember" style="font-size:13px;color:#94a3b8;">Remember me</label>
  </div>
  <div style="color:#ef4444;font-size:13px;" id="login_error"></div>
  <div style="color:#ef4444;font-size:13px;" id="login_email_error"></div>
  <div style="color:#ef4444;font-size:13px;" id="login_password_error"></div>
  <button type="submit" class="cta-primary" style="width:100%;padding:16px;margin-top:8px;">Sign In</button>
</form>
