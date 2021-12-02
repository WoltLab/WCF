(function () {
	var config = {
		mainConfigFile: 'require.config.js',
		generateSourceMaps: true,
		useStrict: true,
		preserveLicenseComments: false,
		optimize: 'none',
		paths: {
			"requireLib": "require",
			
			"jquery": "empty:"
		},
		deps: [
			"require.config",
			"wcf.globalHelper"
		],
		include: [
			"requireLib",
			"require.linearExecution"
		],
		excludeShallow: [
			'WoltLabSuite.Core.min',
			'WoltLabSuite.Core.tiny.min',
		],
		rawText: {
			'WoltLabSuite.Core.min': 'define([], function() {});',
			'WoltLabSuite.Core.tiny.min': 'define([], function() {});',
		},
		onBuildRead: function(moduleName, _modulePath, moduleContents) {
			if (!process.versions.node) {
				throw new Error('You need to run node.js');
			}
			const fs   = module.require('fs');
			const path = module.require('path');
			
			if (global.allModules === undefined) {
				global.allModules = [];
				
				var queue = ['WoltLabSuite'];
				var folder;
				while (folder = queue.shift()) {
					var files = fs.readdirSync(folder);
					for (var i = 0; i < files.length; i++) {
						var filename = path.join(folder, files[i]).replace(/\\/g, '/');
						if (filename === 'WoltLabSuite/Core/Acp') continue;

						if (path.extname(filename) === '.js') {
							global.allModules.push(filename);
						}
						else if (fs.statSync(filename).isDirectory()) {
							queue.push(filename);
						}
					}
				}
			}
			
			if (moduleName === 'WoltLabSuite.Core.min' || moduleName === 'WoltLabSuite.Core.tiny.min') {
				const includedModules = global.allModules.filter((module) => {
					if (!fs.existsSync(module)) {
						return true;
					}
					
					const contents = fs.readFileSync(module, {
						encoding: 'utf8'
					});
					
					let matches
					if ((matches = contents.match(/@woltlabExcludeBundle\s+(tiny|all)/))) {
						switch (matches[1]) {
							case 'all':
								return false;
							case 'tiny':
								return moduleName !== 'WoltLabSuite.Core.tiny.min';
						}
					}

					return true;
				});

				return 'define([' + includedModules.map((item) => {
					return "'" + item.replace(/\\/g, '\\\\').replace(/'/g, "\\'").replace(/\.js$/, '') + "'";
				}).join(', ') + '], function() { });';
			}
			
			return moduleContents;
		}
	};
	
	var _isSupportedBuildUrl = require._isSupportedBuildUrl;
	require._isSupportedBuildUrl = function (url) {
		var result = _isSupportedBuildUrl(url);
		if (!result) return result;
		
		if (Object.keys(config.rawText).map(function (item) { return (process.cwd() + '/' + item + '.js').replace(/\\/g, '/'); }).indexOf(url.replace(/\\/g, '/')) !== -1) return result;

		var fs = module.require('fs');
		try {
			fs.statSync(url);
		}
		catch (e) {
			console.log('Unable to find module:', url, 'ignoring.');

			return false;
		}
		return true;
	};
	
	if (module) module.exports = config;

	return config;
})();
