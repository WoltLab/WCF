({
	mainConfigFile: 'require.config.js',
	name: "WoltLab/_Meta",
	out: "WCF.ACP.min.js",
	useStrict: true,
	preserveLicenseComments: false,
	optimize: 'uglify2',
	uglify2: {},
	excludeShallow: [
		'WoltLab/_Meta'
	],
	exclude: [
		'WoltLab/WCF/Bootstrap'
	],
	rawText: {
		'WoltLab/_Meta': 'define([], function() {});'
	},
	onBuildRead: function(moduleName, path, contents) {
		if (!process.versions.node) {
			throw new Error('You need to run node.js');
		}
		
		if (moduleName === 'WoltLab/_Meta') {
			if (global.allModules == undefined) {
				var fs   = module.require('fs'),
				    path = module.require('path');
				global.allModules = [];
				
				var queue = ['WoltLab/WCF/Acp'];
				var folder;
				while (folder = queue.shift()) {
					var files = fs.readdirSync(folder);
					for (var i = 0; i < files.length; i++) {
						var filename = path.join(folder, files[i]);

						if (path.extname(filename) == '.js') {
							global.allModules.push(filename);
						}
						else if (fs.statSync(filename).isDirectory()) {
							queue.push(filename);
						}
					}
				}
			}
			
			return 'define([' + global.allModules.map(function (item) { return "'" + item.replace(/\\/g, '\\\\').replace(/'/g, "\\'").replace(/\.js$/, '') + "'"; }).join(', ') + '], function() { });';
		}
		
		return contents;
	}
});
