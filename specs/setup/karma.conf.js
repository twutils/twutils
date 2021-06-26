/*
This is a karma config file. For more details see
  http://karma-runner.github.io/0.13/config/configuration-file.html
we are also using it with karma-webpack
  https://github.com/webpack/karma-webpack
*/
const path = require('path');
const puppeteer = require(`puppeteer`)

process.env.CHROME_BIN = puppeteer.executablePath()
process.env.MIX_FILE = process.cwd() + '/webpack.mix.js'

let webpackConfig = null;

(require(`laravel-mix/setup/webpack.config.js`))()
    .then(result => webpackConfig = result)

require('deasync').loopWhile(function(){return webpackConfig === null;});

delete webpackConfig.entry // no need for entries in test context
delete webpackConfig.output // no need for entries in test context

webpackConfig.devtool = `inline-source-map`

module.exports = function karmaConfig (config) {
  console.log(webpackConfig.module.rules)
  config.set({
    proxies: {
      '/images': path.resolve(process.cwd(), `public/images/`),
      '/storage': path.resolve(process.cwd(), `public/storage/`),
      '/fonts': path.resolve(process.cwd(), `public/fonts/`),
    },
    browsers: [`ChromeHeadless`, ],
    frameworks: [`webpack`, `mocha`, `sinon-chai`, ],
    reporters: [`spec`, `coverage`, ],
    files: [
      `./index.js`,
      //      '@/**/*.js',
      //      '@/**/*.vue',
      // `../**/*.spec.js`,
    ],
    preprocessors: {
      './index.js': [`webpack`, `sourcemap`, ],
      '@/**/*.js': [`webpack`, `sourcemap`, `coverage`, ],
      '@/**/*.vue': [`webpack`, `sourcemap`, `coverage`, ],
      '../**/*.spec.js': [`webpack`, `sourcemap`, ],
    },
    webpack: webpackConfig,
    webpackMiddleware: {
      noInfo: true,
    },
    coverageReporter: {
      dir: `./../../coverage/karma`,
      reporters: [
        { type: `lcov`, subdir: `.`, },
        { type: `text-summary`, },
      ],
    },
  })
}
