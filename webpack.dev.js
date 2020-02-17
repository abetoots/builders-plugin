const path = require("path");
const webpack = require("webpack");
const common = require("./webpack.common");
const merge = require("webpack-merge");
const HtmlWebpackPlugin = require("html-webpack-plugin");

module.exports = merge(common, {
  mode: "development",
  devtool: "eval-source-map",
  output: {
    filename: "[name].bundle.js",
    path: path.resolve(__dirname, "build")
  },
  plugins: [
    new HtmlWebpackPlugin({
      template: "./app/public/index.html"
    }),
    new webpack.DefinePlugin({
      BASE_URL: JSON.stringify("http://localhost/builders/"),
      BASE_API_URL: JSON.stringify("http://localhost/builders/wp-json/wp/v2/")
    })
  ],
  module: {
    rules: [
      {
        test: /\.s[ac]ss$/i,
        use: [
          // Creates `style` nodes from JS strings
          "style-loader",
          // Translates CSS into CommonJS
          "css-loader",
          // Compiles Sass to CSS
          "sass-loader",
          {
            loader: "sass-resources-loader",
            options: {
              resources: [
                "./frontend/styles/util/_variables.scss",
                "./frontend/styles/tools/*.scss"
              ]
            }
          }
        ]
      },
      {
        test: /\.css$/i,
        use: [
          // Creates `style` nodes from JS strings
          "style-loader",
          // Translates CSS into CommonJS
          "css-loader"
        ]
      }
    ]
  },
  devServer: {
    //needs to match xampp wordpress path
    publicPath: "/builders/",
    openPage: "builders/",
    contentBase: [path.join(__dirname, "builders")],
    port: 8080,
    historyApiFallback: true //server index.html for any route not found
  }
});
