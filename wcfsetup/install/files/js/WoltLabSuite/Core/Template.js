var __createBinding = (this && this.__createBinding) || (Object.create ? (function(o, m, k, k2) {
    if (k2 === undefined) k2 = k;
    Object.defineProperty(o, k2, { enumerable: true, get: function() { return m[k]; } });
}) : (function(o, m, k, k2) {
    if (k2 === undefined) k2 = k;
    o[k2] = m[k];
}));
var __setModuleDefault = (this && this.__setModuleDefault) || (Object.create ? (function(o, v) {
    Object.defineProperty(o, "default", { enumerable: true, value: v });
}) : function(o, v) {
    o["default"] = v;
});
var __importStar = (this && this.__importStar) || function (mod) {
    if (mod && mod.__esModule) return mod;
    var result = {};
    if (mod != null) for (var k in mod) if (k !== "default" && Object.prototype.hasOwnProperty.call(mod, k)) __createBinding(result, mod, k);
    __setModuleDefault(result, mod);
    return result;
};
define(["require", "exports", "./Template.grammar", "./StringUtil", "./Language", "./I18n/Plural"], function (require, exports, parser, StringUtil, Language, I18nPlural) {
    "use strict";
    parser = __importStar(parser);
    StringUtil = __importStar(StringUtil);
    Language = __importStar(Language);
    I18nPlural = __importStar(I18nPlural);
    // @todo: still required?
    // work around bug in AMD module generation of Jison
    /*function Parser() {
      this.yy = {};
    }
    
    Parser.prototype = parser;
    parser.Parser = Parser;
    parser = new Parser();*/
    /**
     * Compiles the given template.
     *
     * @param  {string}  template  Template to compile.
     * @constructor
     */
    class Template {
        constructor(template) {
            // Fetch Language/StringUtil, as it cannot be provided because of a circular dependency
            if (Language === undefined) { //@ts-ignore
                Language = require('./Language');
            }
            if (StringUtil === undefined) { //@ts-ignore
                StringUtil = require('./StringUtil');
            }
            try {
                template = parser.parse(template);
                template = 'var tmp = {};\n'
                    + 'for (var key in v) tmp[key] = v[key];\n'
                    + 'v = tmp;\n'
                    + 'v.__wcf = window.WCF; v.__window = window;\n'
                    + 'return ' + template;
                this.fetch = new Function('StringUtil', 'Language', 'I18nPlural', 'v', template).bind(undefined, StringUtil, Language, I18nPlural);
            }
            catch (e) {
                console.debug(e.message);
                throw e;
            }
        }
        /**
         * Evaluates the Template using the given parameters.
         *
         * @param  {object}  v  Parameters to pass to the template.
         */
        fetch(v) {
            // this will be replaced in the init function
            throw new Error('This Template is not initialized.');
        }
    }
    Object.defineProperty(Template, 'callbacks', {
        enumerable: false,
        configurable: false,
        get: function () {
            throw new Error('WCF.Template.callbacks is no longer supported');
        },
        set: function (value) {
            throw new Error('WCF.Template.callbacks is no longer supported');
        },
    });
    return Template;
});
