import "./bootstrap";

import { createApp } from "vue";
import App from "./App.vue";
import router from "./router";

import {
  Button,
  HasError,
  AlertError,
  AlertErrors,
  AlertSuccess
} from "./bootstrap";

const appRoot = document.getElementById("app");

window.__APP_ROUTER__ = router;

if (appRoot) {
  const app = createApp(App);

  // Register vform components (Tailwind version)
  app.component(Button.name, Button);
  app.component(HasError.name, HasError);
  app.component(AlertError.name, AlertError);
  app.component(AlertErrors.name, AlertErrors);
  app.component(AlertSuccess.name, AlertSuccess);

  app.use(router);
  app.mount(appRoot);
}
