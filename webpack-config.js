// Require path.
const path = require("path");

BrowserSyncPlugin = require("browser-sync-webpack-plugin");

// Configuration object.
const config = {
  // Create the entry points.
  // One for frontend and one for the admin area.
  entry: {
    // frontend and admin will replace the [name] portion of the output config below.
    frontend: "./src/front/front-index.js",
    admin: "./src/admin/admin-index.js",
  },

  // Create the output files.
  // One for each of our entry points.
  output: {
    // [name] allows for the entry object keys to be used as file names.
    filename: "js/[name].js",
    // Specify the path to the JS files.
    path: path.resolve(__dirname, "assets"),
  },

  // Setup a loader to transpile down the latest and great JavaScript so older browsers
  // can understand it.
  module: {
    rules: [
      {
        // For pure CSS - /\.css$/i,
        // For Sass/SCSS - /\.((c|sa|sc)ss)$/i,
        // For Less - /\.((c|le)ss)$/i,
        test: /\.((c|sa|sc)ss)$/i,
        use: [
          "style-loader",
          {
            loader: "css-loader",
            options: {
              // Run `postcss-loader` on each CSS `@import`, do not forget that `sass-loader` compile non CSS `@import`'s into a single file
              // If you need run `sass-loader` and `postcss-loader` on each CSS `@import` please set it to `2`
              importLoaders: 1,
              // Automatically enable css modules for files satisfying `/\.module\.\w+$/i` RegExp.
              modules: { auto: true },
            },
          },
          // Can be `less-loader`
          {
            loader: "sass-loader",
          },
        ],
      },
      {
        // Look for any .js files.
        test: /\.js$/,
        // Exclude the node_modules folder.
        exclude: /node_modules/,
        // Use babel loader to transpile the JS files.
        loader: "babel-loader",
      },
    ],
  },
  plugins: [
    new BrowserSyncPlugin({
      files: "**/*.php",
      proxy: "http://one.wordpress.test",
    }),
  ],
};

// Export the config object.
module.exports = config;
