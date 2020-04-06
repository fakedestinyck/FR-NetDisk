<template>
    <div>
        <side-navbar :smallScreen="smallScreen" :innerHeight="innerHeight"></side-navbar>
        <topbar :smallScreen="smallScreen"></topbar>
    </div>
</template>

<script>
    import SideNavbar from '@/components/SideNavbar.vue'
    import Topbar from '@/components/Topbar.vue'
    export default {
        name: 'Shared',
        components: {
            SideNavbar,
            Topbar
        },
        mounted() {
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
</style>
