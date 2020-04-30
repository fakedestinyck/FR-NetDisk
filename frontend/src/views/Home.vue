<template>
    <div>
        <side-navbar :smallScreen="smallScreen" :innerHeight="innerHeight"></side-navbar>
        <topbar :smallScreen="smallScreen"></topbar>
        <div class="main">
            <router-view/>
        </div>
    </div>
</template>

<script>
    import SideNavbar from '@/components/SideNavbar.vue'
    import Topbar from '@/components/Topbar.vue'

    export default {
        name: 'Home',
        components: {
            SideNavbar,
            Topbar,
        },
        mounted() {
            // TODO: 这里检查登录态
            this.smallScreen = window.innerWidth <= 1023
            this.innerHeight = window.innerHeight
            let that = this
            window.onresize = function windowResize() {
                that.smallScreen = window.innerWidth <= 1023
                that.innerHeight = window.innerHeight
            };
        },
        data() {
            return {
                key: 1,
                smallScreen: true,
                innerHeight: 0
            };
        },
        computed: {
            isName() {
                return false;
            }
        },
        methods: {
            name() {},
            
        }
    };
</script>

<style scoped lang="scss">
    .main {
        position: absolute;
        left: 300px;
        width: calc(100vw - 300px);
        margin-top: 85px
    }
    @media screen and (max-width: 1023px) {
        .main {
            position: absolute;
            left: 0;
            width: 100vw;
            top: 0;
            margin-top: 40px
        }
    }
</style>
