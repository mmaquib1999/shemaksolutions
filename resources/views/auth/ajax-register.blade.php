<!-- AJAX Register Form -->
<form id="ajax-register-form" style="display:flex;flex-direction:column;gap:16px;">
  <div><label class="label">Name</label><input type="text" id="register_name" placeholder="Your Name" class="input" required></div>
  <div><label class="label">Email</label><input type="email" id="register_email" placeholder="you@company.com" class="input" required></div>
  <div><label class="label">Password</label><input type="password" id="register_password" placeholder="••••••••" class="input" required></div>
  <div><label class="label">Confirm Password</label><input type="password" id="register_password_confirmation" placeholder="Confirm password" class="input" required></div>
  <div><label class="label">Company Name</label><input type="text" id="register_company" placeholder="Your Company" class="input"></div>
  <div style="color:#ef4444;font-size:13px;" id="register_error"></div>
  <div style="color:#ef4444;font-size:13px;" id="register_name_error"></div>
  <div style="color:#ef4444;font-size:13px;" id="register_email_error"></div>
  <div style="color:#ef4444;font-size:13px;" id="register_password_error"></div>
  <div style="color:#ef4444;font-size:13px;" id="register_password_confirmation_error"></div>
  <button type="submit" class="cta-primary" style="width:100%;padding:16px;margin-top:8px;">Create Account</button>
</form>
