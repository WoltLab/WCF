const childProcess = require("child_process");
const compiler = require("./compiler");
const fs = require("fs");
const path = require("path");

if (process.argv.length !== 3) {
    throw new Error("Requires the path to an existing repository.");
}

const repository = process.argv[2];
if (!fs.existsSync(repository)) {
    throw new Error(`Unable to locate repsitory, the path ${repository} is invalid.`);
}
process.chdir(repository);

let rjsPaths = [];

// get all directories at the repository root
fs.readdirSync("./")
    .filter(directory => fs.statSync(directory).isDirectory())
    .forEach(directory => {
        // look for a generic `js` directory
        let path = `./${directory}/js/`;
        if (fs.existsSync(path)) {
            fs.readdirSync(path)
                .filter(filename => {
                    // ignore build configurations
                    if (filename === "require.build.js") {
                        if (rjsPaths.indexOf(path) === -1) rjsPaths.push(path);

                        return false;
                    }

                    let stat = fs.statSync(path + filename);
                    // allow only non-minified *.js files
                    if (stat.isFile() && !stat.isSymbolicLink() && filename.match(/\.js$/) && !filename.match(/\.min\.js$/)) {
                        return true;
                    }

                    return false;
                })
                .forEach(filename => {
                    [true, false].forEach(COMPILER_TARGET_DEFAULT => {
                        let outFilename = filename.replace(/\.js$/, (COMPILER_TARGET_DEFAULT ? "" : ".tiny") + ".min.js");
                        console.time(outFilename);
                        {
                            let output = compiler.compile(fs.readFileSync(path + filename, 'utf-8'), {
                                compress: {
                                    global_defs: {
                                        COMPILER_TARGET_DEFAULT: COMPILER_TARGET_DEFAULT
                                    }
                                }
                            });

                            fs.writeFileSync(path + outFilename, output.code);
                        }
                        console.timeEnd(outFilename);
                    });
                });
        }
    });

const rjsCmd = (process.platform === "win32") ? "r.js.cmd" : "r.js";
rjsPaths.forEach(path => {
    let buildConfig = `${path}require.build.js`;
    let outFilename = require(process.cwd() + `/${buildConfig}`).out;

    [true, false].forEach(COMPILER_TARGET_DEFAULT => {
        let overrides = "uglify2.compress.global_defs.COMPILER_TARGET_DEFAULT=" + (COMPILER_TARGET_DEFAULT ? "true" : "false");
        if (!COMPILER_TARGET_DEFAULT) {
            outFilename = outFilename.replace(/\.min\.js$/, '.tiny.min.js');
            overrides += " out=" + outFilename;
        }

        console.time(outFilename);
        childProcess.execSync(`${rjsCmd} -o require.build.js ${overrides}`, {
            cwd: path,
            stdio: [0, 1, 2]
        });
        console.timeEnd(outFilename);
    });
});
