<template>
    <div id="nav">
        <div class="overlayer" @touchmove.prevent @click="showSideNav = false" v-show="!smallScreen || showSideNav"></div>
        <div @touchmove.prevent>
            <at-menu
                mode="vertical"
                active-name="2"
                width="100%"
                v-bind:class="{ nav_hide: !showSideNav }"
                :style="{ height: `${innerHeight}px` }"
                v-show="!smallScreen || !firstLoad"
            >
                <img src="../assets/logo_only.png" alt="logo" style="width: 200px;"/>
                <at-menu-item name="1">
                    <i class="icon icon-home"></i>
                    我的文件
                </at-menu-item>
                <at-menu-item name="2">
                    <i class="icon icon-layers"></i>
                    我分享的
                </at-menu-item>
                <at-menu-item name="3" disabled>
                    <i class="icon icon-settings"></i>
                    查看统计
                </at-menu-item>
            </at-menu>
        </div>
    </div>
</template>

<script>
export default {
    name: 'SideNavbar',
    props: {
        msg: String,
        smallScreen: true,
        innerHeight: 0
    },
    created() {
        let that = this;
        this.$bus.$on('showSideNav', function() {
            that.firstLoad = false;
            that.showSideNav = true;
        });
    },
    data() {
        return {
            key: 1,
            showSideNav: false,
            firstLoad: true
        };
    },
    computed: {
        isName() {
            return false;
        },
        menuMode() {
            return this.smallScreen ? 'horizontal' : 'vertical';
        }
    },
    methods: {
        name() {}
    }
};
</script>

<style scoped lang="scss">
#nav {
    width: 300px;
}
#nav ul {
    height: 100vh;
    width: 100%;
    font-size: 18px;
    line-height: 40px;
}
::v-deep .at-menu__item-link {
    padding-left: 0;
}
/*遮罩层*/
.overlayer {
    position: fixed;
    left: 0;
    top: 0;
    width: 300px;
    height: 0;
    z-index: 10;
    background-color: rgba(0, 0, 0, 0.5);
}

@media screen and (max-width: 1023px) {
    #nav ul {
        font-size: 18px;
        line-height: 40px;
        max-width: 300px;
        animation: move 100ms linear 1 normal both;
        z-index: 20;
    }
    .nav_hide {
        animation: moveBack 100ms linear 1 normal both !important;
    }
    ::v-deep .at-menu--horizontal .at-menu__item-link::after {
        display: none;
    }
    /*遮罩层*/
    .overlayer {
        position: fixed;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        z-index: 10;
        background-color: rgba(0, 0, 0, 0.5);
    }
    @keyframes move {
        0% {
            transform: translate(-300px, 0);
        }
        100% {
            transform: translate(0, 0);
        }
    }
    @keyframes moveBack {
        0% {
            transform: translate(0px, 0);
        }
        100% {
            transform: translate(-300px, 0);
        }
    }
}
</style>
