define(["prism/prism","prism/components/prism-jsx","prism/components/prism-typescript"], function () {
var typescript = Prism.util.clone(Prism.languages.typescript);
Prism.languages.tsx = Prism.languages.extend('jsx', typescript);
return Prism; })