/**
 * WoltLab/WCF/Template provides a template scripting compiler similar
 * to the PHP one of WoltLab Suite Core. It supports a limited
 * set of useful commands and compiles templates down to a pure
 * JavaScript Function.
 * 
 * @author	Tim Duesterhus
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLab/WCF/Template
 */
define(['./Template.grammar', './StringUtil', 'Language'], function(parser, StringUtil, Language) {
	"use strict";
	
	// work around bug in AMD module generation of Jison
	function Parser() {
		this.yy = {};
	}
	Parser.prototype = parser;
	parser.Parser = Parser;
	parser = new Parser();

	/**
	 * Compiles the given template.
	 * 
	 * @param	{string}	template	Template to compile.
	 * @constructor
	 */
	function Template(template) {
		// Fetch Language/StringUtil, as it cannot be provided because of a circular dependency
		if (Language === undefined) Language = require('Language');
		if (StringUtil === undefined) StringUtil = require('StringUtil');
		
		try {
			template = parser.parse(template);
			template = "var tmp = {};\n"
			+ "for (var key in v) tmp[key] = v[key];\n"
			+ "v = tmp;\n"
			+ "v.__wcf = window.WCF; v.__window = window;\n"
			+ "return " + template;
			
			this.fetch = new Function("StringUtil", "Language", "v", template).bind(undefined, StringUtil, Language);
		}
		catch (e) {
			console.debug(e.message);
			throw e;
		}
	};
	
	Object.defineProperty(Template, 'callbacks', {
		enumerable: false,
		configurable: false,
		get: function() {
			throw new Error('WCF.Template.callbacks is no longer supported');
		},
		set: function(value) {
			throw new Error('WCF.Template.callbacks is no longer supported');
		}
	});
	
	Template.prototype = {
		/**
		 * Evaluates the Template using the given parameters.
		 * 
		 * @param	{object}	v	Parameters to pass to the template.
		 */
		fetch: function(v) {
			// this will be replaced in the init function
			throw new Error('This Template is not initialized.');
		}
	};
	
	return Template;
});
