const fs = require("fs");
const uglify = require("uglify-js");

let uglifyJsConfig = {
    compress: {
        sequences: true,
        properties: true,
        dead_code: true,
        conditionals: true,
        comparisons: true,
        booleans: true,
        loops: true,
        hoist_funs: true,
        hoist_vars: true,
        if_return: true,
        join_vars: true,
        cascade: true,
        /* this is basically the `--define` argument */
        global_defs: {
            COMPILER_TARGET_DEFAULT: false
        }
    }
};

function compile(destination, files) {
    let minifiedData = [];

    files.forEach(filename => {
        minifiedData.push({
            filename: filename,
            content: uglify.minify(filename, uglifyJsConfig)
        });
    });

    let content = `// ${destination} -- DO NOT EDIT\n\n`;

    minifiedData.forEach(fileData => {
        content += `// ${fileData.filename}\n`;
        content += `(function (window, undefined) { ${fileData.content.code} })(this);`;
        content += "\n\n";
    });

    fs.writeFileSync(destination, content);
};

//
// step 1) build `WCF.Combined.min.js` and `WCF.Combined.tiny.min.js`
//
process.chdir("../wcfsetup/install/files/js/");
[true, false].forEach(COMPILER_TARGET_DEFAULT => {
    uglifyJsConfig.compress.global_defs.COMPILER_TARGET_DEFAULT = COMPILER_TARGET_DEFAULT;

    let output = "WCF.Combined" + (COMPILER_TARGET_DEFAULT ? "" : ".tiny") + ".min.js";
    console.time(output);
    {
        let data = fs.readFileSync(".buildOrder", "utf8");
        let files = data
            .trim()
            .split(/\r?\n/)
            .map(filename => `${filename}.js`);

        compile(output, files);
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
    let files = ['redactor.js'];
    fs.readdirSync("./plugins/").forEach(file => {
        file = `plugins/${file}`;
        let stat = fs.statSync(file);
        if (stat.isFile() && !stat.isSymbolicLink()) {
            files.push(file);
        }
    });

    compile(redactorCombined, files);
}
console.timeEnd(redactorCombined);
