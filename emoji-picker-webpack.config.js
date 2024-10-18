"use strict";

const path = require("path");

module.exports = {
	entry: "./node_modules/emoji-picker-element/index.js",
	output: {
		path: path.resolve(__dirname, "wcfsetup", "install", "files", "js", "3rdparty"),
		filename: "emoji-picker-element.min.js",
		libraryTarget: "amd",
	},
	mode: "production",
};
