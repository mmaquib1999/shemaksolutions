import axios from "axios";
import { Form } from "vform";
import {
  Button,
  HasError,
  AlertError,
  AlertErrors,
  AlertSuccess
} from "vform/src/components/tailwind";

// Make axios global
window.axios = axios;
window.Form = Form;

// Laravel default header
axios.defaults.headers.common["X-Requested-With"] = "XMLHttpRequest";

// ⭐ Sanctum SPA session mode
axios.defaults.withCredentials = true;

// ⭐ Your backend domain
axios.defaults.baseURL = "http://shemaksolutions.test";

// ⭐ Required before login
window.getCsrfCookie = () => axios.get("/sanctum/csrf-cookie");

// Export vform Tailwind components for Vue 3 registration
export {
  Button,
  HasError,
  AlertError,
  AlertErrors,
  AlertSuccess
};
