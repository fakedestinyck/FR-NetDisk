import Vue from 'vue'
import App from './App.vue'
import './registerServiceWorker'
import router from './router'
import AtComponents from 'at-ui'
import 'at-ui-style'    // 引入组件样式
import VueCookies from 'vue-cookies'
import VueClipboard from 'vue-clipboard2'

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
Vue.use(VueClipboard)

import { Table, Divider, Icon, Button, Modal, ConfigProvider, Calendar, Skeleton, Result } from 'ant-design-vue'
Vue.use(Table)
Vue.use(Divider)
Vue.use(Icon)
Vue.use(Button)
Vue.use(Modal)
Vue.use(ConfigProvider)
Vue.use(Calendar)
Vue.use(Skeleton)
Vue.use(Result)
Vue.prototype.$confirm = Modal.confirm;

new Vue({
  router,
  render: h => h(App)
}).$mount('#app')
