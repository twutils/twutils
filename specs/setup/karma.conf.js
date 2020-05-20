// This is a karma config file. For more details see
//   http://karma-runner.github.io/0.13/config/configuration-file.html
// we are also using it with karma-webpack
//   https://github.com/webpack/karma-webpack
const puppeteer = require(`puppeteer`)
process.env.CHROME_BIN = puppeteer.executablePath()

const webpackConfig = require(`laravel-mix/setup/webpack.config.js`)

delete webpackConfig.entry // no need for entries in test context
webpackConfig.devtool = `#inline-source-map`

module.exports = function karmaConfig (config) {
  config.set({
    proxies: {
      '/images': `../../public/images/`,
      '/storage': `../../public/storage/`,
      '/fonts': `../../public/fonts/`,
    },
    browsers: [`ChromeHeadless`, ],
    frameworks: [`mocha`, `sinon-chai`, ],
    reporters: [`spec`, `coverage`, ],
    files: [
      `./index.js`,
      //      '@/**/*.js',
      //      '@/**/*.vue',
      `../**/*.spec.js`,
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
