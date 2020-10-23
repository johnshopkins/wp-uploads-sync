const babelify = require('babelify');
const underscorify = require('node-underscorify');

module.exports = {

  js: {

    compile: [
      {
        src: [
          './src/assets/js/*.js'
        ],
        transform: [
          [babelify, {
            presets: ['@babel/preset-env'],
            plugins: ['add-module-exports']
          }],
          [underscorify.transform({ extensions: ['html'] }), { global: true }]
        ]
      }
    ],

    eslint: {
      src: ['./src/assets/js/*.js'],
      eslintrc: '../../../../config/eslintrc.json'
    },

    build: './dist/js',
    dist: './dist/js'

  },

  scss: {

    lint: {},
    src: ['./src/assets/css/*.scss'],
    build: './dist/css',
    dist: './dist/css'

  }

};
