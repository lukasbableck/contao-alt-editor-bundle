const Encore = require('@symfony/webpack-encore');

Encore
    .setOutputPath('public/')
    .setPublicPath('/bundles/contaoalteditor')
    .setManifestKeyPrefix('')
    .cleanupOutputBeforeBuild()
    .disableSingleRuntimeChunk()
    .enableSourceMaps(!Encore.isProduction())
    .enableVersioning(Encore.isProduction())
    .addEntry('backend', './assets/js/backend.js')
	.enablePostCssLoader()
;

const config = Encore.getWebpackConfig();
config.watchOptions = {
	poll: 150
};

module.exports = [config];