<template>
    <div class="home">
        <img src="../assets/logo.png" alt="FR网盘logo">
        <h1>
            欢迎使用FR网盘，一个
            <span style="font-style: italic;">永不限速</span>
            的网盘
        </h1>
    </div>
</template>

<script>
// @ is an alias to /src
// import HelloWorld from '@/components/HelloWorld.vue'

export default {
    name: 'Home',
    components: {
        // HelloWorld
    },
    data() {
        return {
            key: 1
        };
    },
    methods: {
        name() {}
    }
};
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style scoped lang="scss">
h1 {
    font-size: 2em;
}
</style>