const fs = require("fs");
const terser = require("terser");
const merge = require('deepmerge')

const terserConfig = {
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
    },
    format: {
        comments: false,
    }
};

module.exports = {
    compile: (filename, overrides) => {
        if (overrides === undefined) overrides = {};
        const config = merge(terserConfig, overrides);

        return terser.minify(
            filename,
            config
        );
    }
}
