import Vue from 'vue'
import App from './App.vue'
import './registerServiceWorker'
import router from './router'
import AtComponents from 'at-ui'
import 'at-ui-style'    // 引入组件样式
import VueCookies from 'vue-cookies'
// import axios from 'axios'

// Vue.prototype.axios = axios
var EventBus = new Vue();

Object.defineProperties(Vue.prototype, {
    $bus: {
        get: function () {
            return EventBus
        }
    }
})




Vue.use(AtComponents)
Vue.use(VueCookies)
Vue.config.productionTip = false


new Vue({
  router,
  render: h => h(App)
}).$mount('#app')
