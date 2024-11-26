const path = require('path');

module.exports = {
  mode: 'development',
  entry: {
    creditCardMaxipago: './Public/js/creditCard/maxipago/lknIntegrationMaxipagoForWoocommerceCheckout.js',
    debitCardMaxipago: './Public/js/debitCard/maxipago/lknIntegrationMaxipagoForWoocommerceCheckout.js',
    creditCardRede: './Public/js/creditCard/rede/lknIntegrationRedeForWoocommerceCheckout.js',
    debitCardRede: './Public/js/debitCard/rede/lknIntegrationRedeForWoocommerceCheckout.js'
  },
  output: {
    filename: (pathData) => {
      const name = pathData.chunk.name;
      const entryPath = module.exports.entry[name];
      const baseName = path.basename(entryPath, '.js');
      return entryPath.replace(baseName, `${baseName}Compiled`);
    },
    path: path.resolve(__dirname)
  },
  module: {
    rules: [
      {
        test: /\.js$/,
        exclude: /node_modules/,
        use: {
          loader: 'babel-loader',
          options: {
            presets: ['@babel/preset-env', '@babel/preset-react']
          }
        }
      },
      {
        test: /\.css$/,
        use: ['style-loader', 'css-loader']
      }
    ]
  }
};