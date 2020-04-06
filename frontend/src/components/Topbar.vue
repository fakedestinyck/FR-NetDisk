<template>
    <div id="topbar">
        <at-progress :percent="60" :stroke-width="20" v-show="!smallScreen"></at-progress>
        <at-dropdown :placement="navbarPlacement">
            <img src="https://i.pravatar.cc/50" alt="" />
            <at-dropdown-menu slot="menu">
                <at-dropdown-item disabled>个人中心</at-dropdown-item>
                <at-dropdown-item divided>登出</at-dropdown-item>
            </at-dropdown-menu>
        </at-dropdown>
        <at-button icon="icon-bell-off" circle :disabled="!smallScreen" hollow></at-button>
        <div v-show="smallScreen" class="left_part">
            <at-button icon="icon-menu" circle hollow @click="showSideNav"></at-button>
            <img src="../assets/logo_only.png" alt="logo" class="logo"/>
        </div>
        
    </div>
</template>

<script>
export default {
    name: 'Topbar',
    props: {
        msg: String,
        smallScreen: true,
    },
    data() {
        return {
            key: 1,
        };
    },
    computed: {
        isName() {
            return false;
        },
        navbarPlacement() {
            return this.smallScreen ? 'bottom-right' : 'bottom'
        }
    },
    methods: {
        name() {},
        showSideNav() {
            this.$bus.$emit('showSideNav');  //params是传递的参数
        }
    }
};
</script>

<style scoped lang="scss">
#topbar {
    position: absolute;
    top: 0;
    right: 0;
    height: 80px;
    width: 40%;
    // text-align: right;
    background-color: white;
    border-radius: 0px 0px 0px 60px / 0px 0px 0px 75px;
    -moz-box-shadow: -5px 5px 20px #bdbdbd;
    -webkit-box-shadow: -5px 5px 20px #bdbdbd;
    box-shadow: -5px 5px 20px #bdbdbd;
    // line-height: 80px;
}
img {
    width: 50px;
    height: 50px;
    border-radius: 25px;
    margin-top: 15px;
}
.at-dropdown {
    float: right;
    margin-right: 50px;
}
li {
    text-align: center;
}
#topbar > button {
    float: right;
    width: 50px;
    height: 50px;
    margin-right: 20px;
}
.at-btn--circle {
    border-color: transparent;
}
.at-btn--circle:hover {
    border-color: transparent;
}
::v-deep .at-btn__icon {
    font-size: 20px;
}
::v-deep .icon-bell-off {
    line-height: 80px;
}
// ::v-deep .at-btn__icon::before {
//     position: absolute;
//     margin-top: -40px;
//     margin-left: 14px;
// }
.at-progress {
    position: absolute;
    width: 50%;
    top: 30px;
    left: 30px;
}

@media screen and (max-width: 1023px) {
    #topbar {
        position: absolute;
        width: 100%;
        top: 0;
        height: 60px;
        border-radius: 0;
        -moz-box-shadow: none;
        -webkit-box-shadow: none;
        box-shadow: none;
        background-color: lightgray;
        line-height: normal;
    }
    img {
        width: 40px;
        height: 40px;
        border-radius: 20px;
        margin-top: 10px;
    }
    .at-dropdown {
        top: 10px;
        margin-right: 20px;
        right: 0;
    }
    li {
        text-align: right;
        min-width: 80px;
        padding: 6px 8px;
    }
    .at-dropdown-menu__item--divided {
        margin-top: 0;
    }
    .at-dropdown-menu__item--divided:before {
        height: 0;
    }
    ::v-deep .icon-bell-off {
        line-height: 60px;
    }
    .logo {
        position: absolute;
        width: 100px;
        height: 100px;
        top: -30px;
        left: 10%;
    }
    .left_part > button{
        position: absolute;
        left: 10px;
        top: 15px;
    }
    .left_part > button:active {
        background-color: darkgray;
    }
}
</style>
