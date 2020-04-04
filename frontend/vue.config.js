module.exports = {
    devServer: {
        port: 8080,
        open: 'Google Chrome',
        hot: true,
    },
    pwa: {
        workboxOptions: {
            skipWaiting: true,
            clientsClaim: true,
            importWorkboxFrom: 'local',
            importsDirectory: 'js',
            navigateFallback: '/',
            navigateFallbackBlacklist: [/\/api\//]
        }
    },
    publicPath: process.env.NODE_ENV === 'production'
        ? '/'
        : '/'
}
