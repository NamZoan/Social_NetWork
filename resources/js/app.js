import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { createPinia } from 'pinia';

// CSS
import '../css/app.css';
import '../css/boxicons.min.css';
import '../css/bootstrap/bootstrap.min.css';
import '../css/style.css';
import '../css/components.css';
import '../css/media.css';
import '../css/fontawesome/css/all.min.css';

// JS
import '../js/Custom/app.js';
import './bootstrap';

createInertiaApp({
  resolve: name => {
    const pages = import.meta.glob('./Pages/**/*.vue', { eager: true });
    return pages[`./Pages/${name}.vue`];
  },
  setup({ el, App, props, plugin }) {
    const appElement = document.getElementById('app');

    const app = createApp({ render: () => h(App, props) });

    // Initialize Pinia
    const pinia = createPinia();
    app.use(pinia);

    app.mixin({
      mounted() {
        if (this.$options.bodyClass) {
          appElement.className = this.$options.bodyClass;
        }
      },
      updated() {
        if (this.$options.bodyClass) {
          appElement.className = this.$options.bodyClass;
        }
      },
    });

    app.use(plugin).mount(el);
  },
});
