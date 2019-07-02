define(["prism/prism","prism/components/prism-t4-templating","prism/components/prism-csharp"], function () {
Prism.languages.t4 = Prism.languages['t4-cs'] = Prism.languages['t4-templating'].createT4('csharp');

return Prism; })