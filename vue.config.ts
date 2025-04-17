module.exports = ({
  transpileDependencies: true,
  runtimeCompiler: true,
  css: {
    loaderOptions: {
      css: {
        modules: {
          auto: () => true
        }
      }
    }
  },
   pwa: {
    name: "workbox",
    themeColor: "#fff3e0",
    msTileColor: "#fff3e0",
    appleMobileWbeAppCapable: "yes",
    appleMobileWebAppStatusBarStyle: "#fff3e0",
    workboxPluginMode: "InjectManifest",
    workboxOptions: {
      swSrc: "./service-worker.js",
      exclude: [/_redirect/, /\.map$/, /_headers/],
    },
    manifestOptions: {
      background_color: "#ffe24a",
    }
  }
})
