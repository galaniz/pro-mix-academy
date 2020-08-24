
/* Imports */

const BundleAnalyzerPlugin = require( 'webpack-bundle-analyzer' ).BundleAnalyzerPlugin;
const MiniCssExtractPlugin = require( 'mini-css-extract-plugin' );
const OptimizeCssAssetsPlugin = require( 'optimize-css-assets-webpack-plugin' );
const path = require( 'path' );

/* Resolve to root */

let resolve = {
    alias: {
        // Formation: path.resolve( __dirname, '../../../formation/src' )
        Formation: '@alanizcreative/formation/src'
    },
    extensions: [
      '.sass',
      '.scss',
      '.css',
      '.js',
      '.json',
      '.jsx'
    ]
};

/* Rules */

let rules = [
    {
        test: /\.js$/,
        exclude: /node_modules/,
        loaders: 'babel-loader',
        options: {
            rootMode: 'upward',
            presets: [
                [
                    '@babel/preset-env',
                    {
                        modules: false,
                        targets: {
                            browsers: [
                                'last 3 versions',
                                'ie >= 10'
                            ]
                        }
                    }
                ]
            ]
        }
    },
    {
        test: /\.(css|sass|scss)$/,
        use: [
            {
                loader: MiniCssExtractPlugin.loader
            },
            {
                loader: 'css-loader',
                options: {
                    url: false,
                    importLoaders: 1
                }
            },
            {
                loader: 'string-replace-loader',
                options: {
                    search: '--',
                    replace: '\\--',
                    flags: 'g'
                }
            },
            {
                loader: 'postcss-loader',
                options: {
                    ident: 'postcss',
                    plugins: [
                        require( 'autoprefixer' )( {} ),
                        require( 'cssnano' )( { preset: 'default' } ),
                        require( 'postcss-combine-duplicated-selectors' )
                    ],
                    minimize: true
                }
            },
            {
                loader: 'sass-loader',
                options: {
                    implementation: require( 'sass' )
                }
            }
        ]
    }
];

/* Exports */

module.exports = [

    /* Front end assets */

    {
        mode: 'production',
        entry: {
            pma: [
                __dirname + '/assets/src/index.js',
                __dirname + '/assets/src/index.scss'
            ]
        },
        output: {
            path: __dirname + '/assets/public/',
            publicPath: '/',
            filename: 'js/[name].js'
        },
        module: {
            rules: rules
        },
        resolve: resolve,
        plugins: [
            new MiniCssExtractPlugin( {
                filename: '../../style.css'
            } ),
            new OptimizeCssAssetsPlugin(),
            new BundleAnalyzerPlugin( {
                analyzerMode: 'static',
                openAnalyzer: false
            } )
        ]
    }

];
