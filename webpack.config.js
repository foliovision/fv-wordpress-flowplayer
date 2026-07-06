/**
 * External Dependencies
 */
const path = require( 'path' );
const DependencyExtractionWebpackPlugin = require( '@wordpress/dependency-extraction-webpack-plugin' );

module.exports = ( env, argv ) => ( {
    devtool: argv.mode === 'development' ? 'source-map' : false,
    entry: {
        index: path.resolve( __dirname, 'blocks', 'index.js' ),
    },
    output: {
        path: path.resolve( __dirname, 'blocks/build' ),
        filename: '[name].js',
        clean: true,
    },
    module: {
        rules: [
            {
                test: /\.jsx?$/,
                exclude: /node_modules/,
                use: {
                    loader: require.resolve( 'babel-loader' ),
                    options: {
                        presets: [
                            [ '@babel/preset-env', {
                                targets: {
                                    browsers: [ 'last 2 versions', '> 5%', 'Firefox ESR' ],
                                },
                            } ],
                            [ '@babel/preset-react', {
                                runtime: 'automatic',
                            } ],
                        ],
                    },
                },
            },
        ],
    },
    plugins: [
        new DependencyExtractionWebpackPlugin(),
    ],
    resolve: {
        extensions: [ '.js', '.jsx' ],
    },
} );
