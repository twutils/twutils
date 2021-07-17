// jest.config.js
module.exports = {
  testRegex: 'specs/.*.spec.js$',
  testEnvironment: 'jsdom',
  moduleNameMapper: {
    "^@/(.*)$": "<rootDir>/resources/js/$1"
  },
  moduleFileExtensions: ['js', 'json', 'vue'],
  transform: {
    '^.+\\.js$': '<rootDir>/node_modules/babel-jest',
    '.*\\.(vue)$': '<rootDir>/node_modules/vue-jest'
  },
  collectCoverageFrom: [
    'resources/js/**/*.{js,jsx,ts,tsx,vue}',
  ],
  collectCoverage: true,
  coverageReporters: ['html', 'lcov', 'text-summary'],
  coverageDirectory: './coverage'
}