define(["prism/prism"], function () {
// https://tools.ietf.org/html/rfc4180

Prism.languages.csv = {
	'value': /[^\r\n,"]+|"(?:[^"]|"")*"(?!")/,
	'punctuation': /,/
};

return Prism; })