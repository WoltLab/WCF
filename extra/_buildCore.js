const childProcess = require("child_process");
const compiler = require("./compiler");
const fs = require("fs");

function compile(destination, files, overrides) {
	let minifiedData = [];

	files.forEach((filename) => {
		minifiedData.push({
			filename: filename,
			content: compiler.compile(fs.readFileSync(process.cwd() + `/${filename}`, "utf-8"), overrides),
		});
	});

	let content = `// ${destination} -- DO NOT EDIT\n\n`;

	minifiedData.forEach((fileData) => {
		content += `// ${fileData.filename}\n`;
		content += `(function (window, undefined) { ${fileData.content.code} })(this);`;
		content += "\n\n";
	});

	fs.writeFileSync(destination, content);
}

//
// step 1) build `WCF.Combined.min.js` and `WCF.Combined.tiny.min.js`
//
process.chdir("../wcfsetup/install/files/js/");
[true, false].forEach((COMPILER_TARGET_DEFAULT) => {
	let output = "WCF.Combined" + (COMPILER_TARGET_DEFAULT ? "" : ".tiny") + ".min.js";
	console.time(output);
	{
		let data = fs.readFileSync(".buildOrder", "utf8");
		let files = data
			.trim()
			.split(/\r?\n/)
			.map((filename) => `${filename}.js`);

		compile(output, files, {
			compress: {
				global_defs: {
					COMPILER_TARGET_DEFAULT: COMPILER_TARGET_DEFAULT,
				},
			},
		});
	}
	console.timeEnd(output);
});

//
// step 2) Redactor II + plugins
//
const redactorCombined = "redactor.combined.min.js";
process.chdir("3rdParty/redactor2/");

console.time(redactorCombined);
{
	let files = ["redactor.js"];
	fs.readdirSync("./plugins/").forEach((file) => {
		file = `plugins/${file}`;
		let stat = fs.statSync(file);
		if (stat.isFile() && !stat.isSymbolicLink()) {
			files.push(file);
		}
	});

	compile(redactorCombined, files);
}
console.timeEnd(redactorCombined);

//
// step3) build rjs
//
const rjsCmd = process.platform === "win32" ? "r.js.cmd" : "r.js";
process.chdir("../../");

{
	let configFile = "require.build.js";
	let outFilename = require(process.cwd() + `/${configFile}`).out;

	[true, false].forEach((COMPILER_TARGET_DEFAULT) => {
		let overrides =
			"uglify2.compress.global_defs.COMPILER_TARGET_DEFAULT=" +
			(COMPILER_TARGET_DEFAULT ? "true" : "false");
		if (!COMPILER_TARGET_DEFAULT) {
			outFilename = outFilename.replace(/\.min\.js$/, ".tiny.min.js");
			overrides += " out=" + outFilename;
		}

		console.time(outFilename);
		childProcess.execSync(`${rjsCmd} -o ${configFile} ${overrides}`, {
			cwd: process.cwd(),
			stdio: [0, 1, 2],
		});
		console.timeEnd(outFilename);
	});
}

//
// step 4) legacy ACP scripts
//
process.chdir("../acp/js/");

fs.readdirSync("./")
	.filter((filename) => {
		let stat = fs.statSync(filename);
		if (stat.isFile() && !stat.isSymbolicLink()) {
			return filename.match(/\.js$/) && !filename.match(/\.min\.js$/);
		}

		return false;
	})
	.forEach((filename) => {
		console.time(filename);
		{
			let output = compiler.compile(fs.readFileSync(process.cwd() + `/${filename}`, "utf-8"));
			fs.writeFileSync(filename.replace(/\.js$/, ".min.js"), output.code);
		}
		console.timeEnd(filename);
	});
