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

// Sanctum SPA session mode
axios.defaults.withCredentials = true;
axios.defaults.xsrfCookieName = "XSRF-TOKEN";
axios.defaults.xsrfHeaderName = "X-XSRF-TOKEN";
const metaToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
if (metaToken) {
  axios.defaults.headers.common['X-CSRF-TOKEN'] = metaToken;
}

// ✅ Dynamic base URL (Vite env → fallback to current origin)
axios.defaults.baseURL =
  import.meta.env.VITE_API_URL || window.location.origin;

// Required before login (Sanctum)
window.getCsrfCookie = () => axios.get("/sanctum/csrf-cookie");

// Global response handler: route to verify or login as needed (no full reload)
axios.interceptors.response.use(
  (response) => response,
  (error) => {
    const status = error?.response?.status;
    const message = error?.response?.data?.message;

    const spaNavigate = (path) => {
      const router = window.__APP_ROUTER__;
      if (router && typeof router.push === "function") {
        router.push(path);
        return;
      }
      if (window.location.pathname !== path) {
        window.history.pushState({}, "", path);
        window.dispatchEvent(new PopStateEvent("popstate"));
      }
    };

    if (status === 403 && message === "verification_required") {
      if (window.location.pathname !== "/verify-code") {
        spaNavigate("/verify-code");
      }
    } else if (status === 401) {
      if (window.location.pathname !== "/login") {
        spaNavigate("/login");
      }
    }

    return Promise.reject(error);
  }
);

// Export vform Tailwind components
export {
  Button,
  HasError,
  AlertError,
  AlertErrors,
  AlertSuccess
};
