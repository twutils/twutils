const webpack = require('webpack');

let mix = require('laravel-mix');
const BundleAnalyzerPlugin = require('webpack-bundle-analyzer').BundleAnalyzerPlugin;

let webpackConfig = {
  plugins: [
    new webpack.IgnorePlugin(/^\.\/locale$/, /moment$/),
  ]
}

mix.alias({
  '@': require('path').resolve(__dirname, 'resources/js')
})

if(process.env.NODE_ENV !== 'test')
  mix.extract()

if (process.env.NODE_ENV === 'development')
  webpackConfig.plugins.push(new BundleAnalyzerPlugin())

mix.webpackConfig(webpackConfig)

let isBuildMode = process.env.BUILDTYPE === 'DOWNLOABLE_ASSETS'

if (isBuildMode || process.env.NODE_ENV === 'test')
{
  if (isBuildMode)
  {
    mix.setResourceRoot('../')
  }

  Mix.manifest.refresh = _ => void 0
}

mix.js('resources/js/app.js', 'public/js')
   .vue()
   .sass('resources/sass/app.scss', `public/${isBuildMode ? 'build_':''}css`)

mix.js('resources/js/welcome.js', 'public/js')
   .vue()
   .sass('resources/sass/welcome.scss', `public/${isBuildMode ? 'build_':''}css`)

mix.sourceMaps(true, 'inline-source-map')

if(process.env.NODE_ENV !== 'test')
{
  mix.copy('resources/images','public/images')
  mix.copy('resources/favicon','public')  
}

if(process.env.NODE_ENV === 'production')
  mix.version()

if(process.env.NODE_ENV === 'test')
  mix.disableNotifications()

if(process.env.NODE_ENV != 'test' && ! process.argv.includes('-no-bs'))
{
  mix.browserSync({
      ghostMode: false,
      proxy: 'localhost:8000',
      notify: false,
      socket: {
        heartbeatTimeout: 15000,
      },
      // thanks to @maykefreitas
      // https://github.com/turbolinks/turbolinks/issues/147#issuecomment-236443089
      snippetOptions: {
        rule: {
          match: /<\/head>/i,
          fn: function (snippet, match) {
            return snippet + match;
          }
        }
      },
  })
}
