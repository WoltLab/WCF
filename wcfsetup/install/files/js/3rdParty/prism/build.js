const fs = require('fs');
const path = require('path');

const meta = JSON.parse(fs.readFileSync('./components.json'));

fs.readdirSync('components').forEach(function (component) {
	component = path.basename(component, '.js').replace(/^prism-/, '');
	const filename = `components/prism-${component}.js`;
	const contents = fs.readFileSync(filename, { encoding: 'utf8' });
	if (/^define/.test(contents)) {
		console.log(`Skipping ${component}`);
		return;
	}
	let requirements = meta.languages[component].require || [];
	if (typeof requirements === 'string') requirements = [ requirements ];
	let peerDependencies = meta.languages[component].peerDependencies || [];
	if (typeof peerDependencies === 'string') peerDependencies = [ peerDependencies ];
	requirements = requirements.concat(peerDependencies).map(item => `prism/components/prism-${item}`)

	const header = `define(${JSON.stringify(['prism/prism'].concat(requirements))}, function () {\n`;
	const footer = `\nreturn Prism; })`;
	fs.writeFileSync(filename, `${header}${contents}${footer}`, 'utf8');
});

{
const header = `define([],function(){return /*START*/`;
const footer = `/*END*/;});`;
const contents = Object.entries(meta.languages).reduce(function(acc, [key, value]) {
	if (key === 'meta') return acc;
	
	acc[key] = {
		title: value.title,
		file: key,
	};
	if (value.alias && value.alias.forEach != null && value.aliasTitles) {
		value.alias.forEach(function (alias) {
			if (!value.aliasTitles[alias]) return;
			acc[alias] = {
				title: value.aliasTitles[alias],
				file: key,
			};
		});
	}
	return acc;
}, {});
fs.writeFileSync('./prism-meta.js', `${header}${JSON.stringify(contents)}${footer}`, 'utf8');
}
