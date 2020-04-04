import Vue from 'vue'
import App from './App.vue'
import './registerServiceWorker'
import router from './router'
import AtComponents from 'at-ui'
import 'at-ui-style'    // 引入组件样式
Vue.use(AtComponents)
Vue.config.productionTip = false


new Vue({
  router,
  render: h => h(App)
}).$mount('#app')
