@echo off

call node _build.js

cd ..\wcfsetup\install\files\js\
rem default build
call r.js.cmd -o require.build.js uglify2.global_defs.COMPILER_TARGET_DEFAULT=true
rem tiny build
call r.js.cmd -o require.build.js uglify2.global_defs.COMPILER_TARGET_DEFAULT=false out=WoltLabSuite.Core.tiny.min.js

pause