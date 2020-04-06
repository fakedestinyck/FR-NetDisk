<template>
    <div class="home">
        <img src="../assets/logo.png" alt="FR网盘logo" />
        <h1>
            欢迎使用FR网盘
            <br />
            一个
            <span style="font-style: italic;">永不限速</span>
            的网盘
        </h1>
        <at-button type="primary" size="large" @click="modal1 = true">点我进入</at-button>
        <at-modal v-model="modal1" :styles="{ width: '90%', maxWidth: '520px' }" :mask-closable="false" :show-close="!isLoggingIn">
            <div slot="header" style="text-align:center;"><span>登陆</span></div>
            <at-input v-model="username" placeholder="请输入用户名" @keyup.enter.native="handleLogin">
                <template slot="prepend">
                    <i class="icon icon-user"></i>
                </template>
            </at-input>
            <div style="height: 1em;"></div>
            <at-input v-model="password" placeholder="请输入密码" type="password" @keyup.enter.native="handleLogin">
                <template slot="prepend">
                    <i class="icon icon-lock"></i>
                </template>
            </at-input>
            <div slot="footer"><at-button type="primary" @click="handleLogin" :disabled="isLoginBtnDisabled" :loading="isLoggingIn">登陆</at-button></div>
        </at-modal>
    </div>
</template>

<script>
// @ is an alias to /src
// import HelloWorld from '@/components/HelloWorld.vue'
// 这个axios是自定义的
import { axios } from '@/components/axios/api.js';
export default {
    name: 'Home',
    components: {
        // HelloWorld
    },
    data() {
        return {
            key: 1,
            modal1: true,
            username: '',
            password: '',
            email: '',
            isLoggingIn: false
        };
    },
    computed: {
        isLoginBtnDisabled() {
            return this.username.replace(/\ /g, '') === '' || this.password.replace(/\ /g, '') === '' || this.isLoggingIn;
        }
    },
    methods: {
        name() {},
        handleLogin() {
            if (this.username.replace(/\ /g, '') === '' || this.password.replace(/\ /g, '') === '' || this.isLoggingIn) {
                return false;
            }
            this.isLoggingIn = true;
            axios(
                'login',
                'POST',
                {
                    username: this.username,
                    password: this.password
                }
            )
                .then(response => {
                    let username = response.data.username
                    let token = response.data.token
                    this.$cookies.set('username',username)
                    this.$cookies.set('token',token)
                    this.$router.push('shared')
                })
                .catch(error => {
                    this.$Message.error(error.status === 406 ? '用户名或密码错误' : error.data ?? '未知错误')
                })
                .then(() => {
                    this.isLoggingIn = false;
                })
        }
    }
};
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style scoped lang="scss">
h1 {
    font-size: 2em;
    margin-bottom: 2em;
}
.at-btn--primary {
    border-color: #52b2bc;
    background-color: #52b2bc;
}
.at-btn--primary:hover {
    border-color: #58cbcf;
    background-color: #58cbcf;
}
</style>
