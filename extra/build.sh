#!/bin/sh
node _build.js

cd ../wcfsetup/install/files/js/
# default build
r.js -o require.build.js uglify2.global_defs.COMPILER_TARGET_DEFAULT=true
# tiny build
r.js -o require.build.js uglify2.global_defs.COMPILER_TARGET_DEFAULT=false out=WoltLabSuite.Core.tiny.min.js
