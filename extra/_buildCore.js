const childProcess = require("child_process");
const compiler = require("./compiler");
const fs = require("fs");

async function compile(destination, files, overrides) {
	let content = `// ${destination} -- DO NOT EDIT\n\n`;

	for (const filename of files) {
		const output = await compiler.compile(fs.readFileSync(process.cwd() + `/${filename}`, "utf-8"), overrides);

		content += `// ${filename}\n`;
		content += `(function (window, undefined) { ${output.code} })(this);`;
		content += "\n\n";
	};

	fs.writeFileSync(destination, content);
}

(async () => {
	//
	// step 1) build `WCF.Combined.min.js` and `WCF.Combined.tiny.min.js`
	//
	process.chdir("../wcfsetup/install/files/js/");
	for (const COMPILER_TARGET_DEFAULT of [true, false]) {
		let output = "WCF.Combined" + (COMPILER_TARGET_DEFAULT ? "" : ".tiny") + ".min.js";
		console.time(output);
		{
			let data = fs.readFileSync(".buildOrder", "utf8");
			let files = data
				.trim()
				.split(/\r?\n/)
				.map((filename) => `${filename}.js`);

			await compile(output, files, {
				compress: {
					global_defs: {
						COMPILER_TARGET_DEFAULT: COMPILER_TARGET_DEFAULT,
					},
				},
			});
		}
		console.timeEnd(output);
	}

	//
	// step 2) build rjs
	//

	{
		const configFile = "require.build.js";

		for (const COMPILER_TARGET_DEFAULT of [true, false]) {
			const name = `WoltLabSuite.Core${COMPILER_TARGET_DEFAULT ? '' : '.tiny'}.min`;

			console.time(name);
			{
				childProcess.execSync(`node ${__dirname}/node_modules/.bin/r.js -o ${configFile} name=${name} out=${name}.js`, {
					cwd: process.cwd(),
					stdio: [0, 1, 2],
				});
				const sourceMap = JSON.parse(fs.readFileSync(`${name}.js.map`, "utf-8"));

				const output = await compiler.compile(
					fs.readFileSync(`${name}.js`, "utf-8"),
					{
						sourceMap: {
							content: JSON.stringify(sourceMap),
							url: `${name}.js.map`,
							includeSources: true,
						},
						compress: {
							global_defs: {
								COMPILER_TARGET_DEFAULT: COMPILER_TARGET_DEFAULT,
							},
						}
					}
				);
				fs.writeFileSync(`${name}.js`, output.code);
				fs.writeFileSync(`${name}.js.map`, output.map);
			}
			console.timeEnd(name);
		}
	}

	//
	// step 3) legacy ACP scripts
	//
	process.chdir("../acp/js/");

	await Promise.all(fs.readdirSync("./")
		.filter((filename) => {
			const stat = fs.statSync(filename);
			if (stat.isFile() && !stat.isSymbolicLink()) {
				return filename.match(/\.js$/) && !filename.match(/\.min\.js$/);
			}

			return false;
		})
		.map(async (filename) => {
			console.time(filename);
			{
				const output = await compiler.compile(fs.readFileSync(process.cwd() + `/${filename}`, "utf-8"));
				fs.writeFileSync(filename.replace(/\.js$/, ".min.js"), output.code);
			}
			console.timeEnd(filename);
		}));

})();
