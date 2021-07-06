const isTest = process.env.NODE_ENV === 'test'
const isDevelopemnt = process.env.NODE_ENV === 'development'
const isProduction = process.env.NODE_ENV === 'production'

const isBuildMode = process.env.BUILDTYPE === 'DOWNLOABLE_ASSETS'
const usesBrowserSync = ! process.argv.includes('-no-bs')

const webpack = require('webpack');
const mix = require('laravel-mix');

let webpackConfig = {
  plugins: [
    new webpack.IgnorePlugin(/^\.\/locale$/, /moment$/),
  ]
}

mix.alias({
  '@': require('path').resolve(__dirname, 'resources/js')
})

if(! isTest )
{
  mix.extract()
}

if (isDevelopemnt)
{
  webpackConfig.plugins.push(new (require('webpack-bundle-analyzer').BundleAnalyzerPlugin)())
}

mix.webpackConfig(webpackConfig)

if (isBuildMode || isTest)
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

if(! isTest )
{
  mix.copy('resources/images','public/images')
  mix.copy('resources/favicon','public')  
}

if(isProduction)
{
  mix.version()
}

if(isTest)
{
  mix.disableNotifications()
}

if(! isTest && usesBrowserSync)
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
