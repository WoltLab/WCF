/**
 * Automatic URL rewrite rule generation.
 *
 * @author  Florian Gail
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
define(["require", "exports", "tslib", "../../../Ajax", "../../../Language", "../../../Ui/Dialog"], function (require, exports, tslib_1, Ajax, Language, Dialog_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.init = init;
    Ajax = tslib_1.__importStar(Ajax);
    Language = tslib_1.__importStar(Language);
    Dialog_1 = tslib_1.__importDefault(Dialog_1);
    class RewriteGenerator {
        buttonGenerate;
        container;
        /**
         * Initializes the generator for rewrite rules
         */
        constructor() {
            const urlOmitIndexPhp = document.getElementById("url_omit_index_php");
            // This configuration part is unavailable when running in enterprise mode.
            if (urlOmitIndexPhp === null) {
                return;
            }
            this.container = document.createElement("dl");
            const dt = document.createElement("dt");
            dt.classList.add("jsOnly");
            const dd = document.createElement("dd");
            this.buttonGenerate = document.createElement("a");
            this.buttonGenerate.className = "button";
            this.buttonGenerate.href = "#";
            this.buttonGenerate.textContent = Language.get("wcf.acp.rewrite.generate");
            this.buttonGenerate.addEventListener("click", (ev) => this._onClick(ev));
            dd.appendChild(this.buttonGenerate);
            const description = document.createElement("small");
            description.textContent = Language.get("wcf.acp.rewrite.description");
            dd.appendChild(description);
            this.container.appendChild(dt);
            this.container.appendChild(dd);
            const insertAfter = urlOmitIndexPhp.closest("dl");
            insertAfter.insertAdjacentElement("afterend", this.container);
        }
        /**
         * Fires an AJAX request and opens the dialog
         */
        _onClick(event) {
            event.preventDefault();
            Ajax.api(this);
        }
        _dialogSetup() {
            return {
                id: "dialogRewriteRules",
                source: null,
                options: {
                    title: Language.get("wcf.acp.rewrite"),
                },
            };
        }
        _ajaxSetup() {
            return {
                data: {
                    actionName: "generateRewriteRules",
                    className: "wcf\\data\\option\\OptionAction",
                },
            };
        }
        _ajaxSuccess(data) {
            Dialog_1.default.open(this, data.returnValues);
        }
    }
    let rewriteGenerator;
    function init() {
        if (!rewriteGenerator) {
            rewriteGenerator = new RewriteGenerator();
        }
    }
});
