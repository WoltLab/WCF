const fs = require("fs");
const uglify = require("uglify-js");

const uglifyJsConfig = {
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
        /* this is basically the `--define` argument */
        global_defs: {
            COMPILER_TARGET_DEFAULT: false
        }
    }
};

module.exports = {
    compile: (filename, overrides) => {
        if (overrides === undefined) overrides = {};

        return uglify.minify(
            filename,
            Object.assign(uglifyJsConfig, overrides));
    }
}
