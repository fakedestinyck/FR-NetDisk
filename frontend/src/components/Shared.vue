<template>
    <div class="shared"><at-alert message="你还没有分享过文件哟" type="info" v-if="shareds.length === 0" class="banner-info"></at-alert></div>
</template>

<script>
import { axios } from '@/components/axios/api.js';
export default {
    name: 'Shared',
    components: {
        // HelloWorld
    },
    mounted() {
        if (!this.getToken()) {
            return false;
        }
        this.getAllShared();
    },
    data() {
        return {
            key: 1,
            token: '',
            shareds: []
        };
    },
    computed: {
        isName() {
            return false;
        }
    },
    methods: {
        getCookies(key) {
            return this.$cookies.get(key) ?? null;
        },
        getToken() {
            let token = this.getCookies('token');
            if (token) {
                this.token = token;
                return true;
            } else {
                // this.$router.push('/');
                this.$Modal.error({
                    content: '登陆失效，请重新登录！',
                    width: window.innerWidth >= 577.78 ? 520 : window.innerWidth * 0.8
                });
                // this.$Message.error({
                //     message: '登陆失效，请重新登录！',
                //     duration: 5000
                // });
            }
        },
        getAllShared() {
            axios('shared', 'GET', {}, this.token)
                .then(response => {
                    console.log(response);
                })
                .catch(error => {
                    this.$Message.error(error.code === 406 ? '用户名或密码错误' : error.data ?? '未知错误');
                })
                .then(() => {});
        }
    }
};
</script>

<style scoped lang="scss">
.shared {
}
.banner-info {
    margin-top: 20px;
}
</style>
