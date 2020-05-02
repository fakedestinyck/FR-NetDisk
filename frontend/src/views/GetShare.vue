<template>
    <a-config-provider :getPopupContainer="getPopupContainer" :locale="locale">
        <div class="shared">
            <div class="topbar">
                <img src="../assets/logo_horizontal.png" alt="FR网盘logo" class="logo" @click="$router.push('/shared')"/>
                <h1 class="title" v-show="!loading">{{sharedInfo.from ? sharedInfo.from : '找不到文件'}}</h1>
            </div>
            <div class="get-share">
                <a-result :title="sharedErrorMsg" v-if="sharedErrorMsg !== ''">
                    <template #icon>
                        <a-icon type="question-circle" theme="twoTone" two-tone-color="red" />
                    </template>
                </a-result>
            </div>
        </div>
    </a-config-provider>
</template>

<script>
    import { axios } from '@/components/axios/api.js';
import SideNavbar from '@/components/SideNavbar.vue';
import Topbar from '@/components/Topbar.vue';
import zhCN from 'ant-design-vue/es/locale/zh_CN';
import moment from 'moment';
import 'moment/locale/zh-cn';
moment.locale('zh-cn');
export default {
    name: 'GetShare',
    components: {
        SideNavbar,
        Topbar
    },
    data() {
        return {
            key: 1,
            smallScreen: true,
            innerHeight: 0,
            locale: zhCN,
            moment,
            zhCN,
            sharedErrorMsg: '',
            sharedInfo: {},
            loading: true,
        };
    },
    computed: {
        isName() {
            return false;
        }
    },
    mounted() {
        this.initialize();
        this.parseParams();
    },
    methods: {
        initialize() {
            this.smallScreen = window.innerWidth <= 1023;
            this.innerHeight = window.innerHeight;
            let that = this;
            window.onresize = function windowResize() {
                that.smallScreen = window.innerWidth <= 1023;
                that.innerHeight = window.innerHeight;
            };
        },
        parseParams() {
            let params = this.$route.params;
            let shareEventid = params.share_event_id;
            let t = params.t;
            let token = params.token;
            if (!shareEventid || !t || !token) {
                this.sharedErrorMsg = '找不到对应的分享';
                this.loading = false
                return false;
            }
            
            let numberPattern = /^[0-9]+$/;
            let tokenPattern = /^[A-Za-z0-9%]+$/;
            // 检查是否被解码过
            if (token.indexOf('%') === -1){
                // 没有%，说明被解码过了，这里重新编码回去
                token = encodeURIComponent(token)
            }
            if (!numberPattern.test(shareEventid) || !numberPattern.test(t) || !tokenPattern.test(token)) {
                console.log(token)
                this.sharedErrorMsg = '找不到对应的分享';
                this.loading = false
                return false;
            }

            this.requestSharedData(shareEventid, t, token);
        },
        requestSharedData(shareEventid, t, token) {
            axios(
                'shared/get',
                'POST',
                {
                    share_event_id: shareEventid,
                    t: t,
                    token: token
                },
                this.token
            )
                .then(response => {
                    console.log(response)
                    // this.$Message.success(response.data.msg);
                })
                .catch(error => {
                    console.log(error)
                    // this.$Message.error(error.data ?? '遇到未知错误');
                });
        },
        getPopupContainer(el, dialogContext) {
            if (dialogContext) {
                return dialogContext.getDialogWrap();
            } else {
                return document.body;
            }
        }
    }
};
</script>

<style scoped lang="scss">
.topbar {
    height: 80px;
    position: absolute;
    top: 0;
    width: 100vw;
    left: 0;
    background-color: rgb(248, 248, 249);
}
.logo {
    position: absolute;
    top: 0;
    bottom: 0;
    left: 0;
    right: 0;
    margin: auto auto auto 30px;
    height: 60px;
}
.logo:hover {
    cursor: pointer;
}
.title {
    line-height: 80px;
    font-size: 30px;
}
.get-share {
    height: 66.6666666666vh;
    display: flex;
    align-items: center;
    zoom: 1.5;
}
.ant-result {
    margin: auto;
}
.main {
    position: absolute;
    left: 300px;
    width: calc(100vw - 300px);
    margin-top: 85px;
    height: calc(100vh - 85px);
}
@media screen and (max-width: 1023px) {
    .topbar {
        height: 60px;
    }
    .logo {
        margin: auto auto auto 20px;
        height: 50px;
    }
    .title {
        line-height: 60px;
        font-size: 25px;
    }
    // .main {
    //     position: absolute;
    //     left: 0;
    //     width: 100vw;
    //     top: 0;
    //     margin-top: 40px;
    // }
}
</style>
