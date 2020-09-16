define(["prism/prism","prism/components/prism-t4-templating","prism/components/prism-vbnet"], function () {
Prism.languages['t4-vb'] = Prism.languages['t4-templating'].createT4('vbnet');

return Prism; })