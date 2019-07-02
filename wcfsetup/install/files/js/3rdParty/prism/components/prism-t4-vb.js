define(["prism/prism","prism/components/prism-t4-templating","prism/components/prism-visual-basic"], function () {
Prism.languages['t4-vb'] = Prism.languages['t4-templating'].createT4('visual-basic');

return Prism; })