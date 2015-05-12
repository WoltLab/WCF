({
	mainConfigFile: 'require.config.js',
	name: "WoltLab/WCF/Bootstrap",
	out: "WCF.Combined.min.js",
	useStrict: true,
	paths: {
		"requireLib": "require",
		
		"jquery": "empty:"
	},
	include: [
		"requireLib"
	]
})
