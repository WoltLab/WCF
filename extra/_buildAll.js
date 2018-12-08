const childProcess = require("child_process");
const fs = require("fs");

if (process.argv.length !== 3) {
    throw new Error("Requires the base path as argument.");
}

const basePath = process.argv[2];
if (!basePath.match(/[\\\/]$/)) {
    throw new Error("Path must end with a slash - any slash will do.");
}
else if (!fs.existsSync(basePath)) {
    throw new Error(`Invalid path, '${basePath}' does not exist or is not readable.`);
}

fs.readdirSync(basePath)
    .filter(directory => {
        if (directory.indexOf('.') !== 0 && fs.statSync(basePath + directory).isDirectory()) {
            // filter by known repository name patterns
            if (directory === "WCF" || directory.indexOf("com.woltlab.") === 0) {
                return true;
            }
        }

        return false;
    })
    .forEach(directory => {
        console.log(`##### Building ${directory} #####\n`);

        let path = basePath + directory;
        if (directory === "WCF") {
            childProcess.execSync(`node _buildCore.js`, {
                stdio: [0, 1, 2]
            });
        }
        else {
            childProcess.execSync(`node _buildExternal.js ${path}`, {
                stdio: [0, 1, 2]
            });
        }
        console.log("\n");
    });