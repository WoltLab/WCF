/**
 * Automatic URL rewrite rule generation.
 *
 * @author	Florian Gail
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLabSuite/Core/Acp/Ui/Option/RewriteTest
 */
define(['Ajax', 'Language', 'Ui/Dialog'], function (Ajax, Language, UiDialog) {
    "use strict";
    var _buttonGenerate = null;
    var _container = null;
    /**
     * @exports     WoltLabSuite/Core/Acp/Ui/Option/RewriteTest
     */
    return {
        /**
         * Initializes the generator for rewrite rules
         */
        init: function () {
            var urlOmitIndexPhp = elById('url_omit_index_php');
            // This configuration part is unavailable when running in enterprise mode.
            if (urlOmitIndexPhp === null) {
                return;
            }
            _container = elCreate('dl');
            var dt = elCreate('dt');
            dt.classList.add('jsOnly');
            var dd = elCreate('dd');
            _buttonGenerate = elCreate('a');
            _buttonGenerate.className = 'button';
            _buttonGenerate.href = '#';
            _buttonGenerate.textContent = Language.get('wcf.acp.rewrite.generate');
            _buttonGenerate.addEventListener('click', this._onClick.bind(this));
            dd.appendChild(_buttonGenerate);
            var description = elCreate('small');
            description.textContent = Language.get('wcf.acp.rewrite.description');
            dd.appendChild(description);
            _container.appendChild(dt);
            _container.appendChild(dd);
            var insertAfter = urlOmitIndexPhp.closest('dl');
            insertAfter.parentNode.insertBefore(_container, insertAfter.nextSibling);
        },
        /**
         * Fires an AJAX request and opens the dialog
         *
         * @param       {Event}         event
         */
        _onClick: function (event) {
            event.preventDefault();
            Ajax.api(this);
        },
        _dialogSetup: function () {
            return {
                id: 'dialogRewriteRules',
                source: null,
                options: {
                    title: Language.get('wcf.acp.rewrite')
                }
            };
        },
        _ajaxSetup: function () {
            return {
                data: {
                    actionName: 'generateRewriteRules',
                    className: 'wcf\\data\\option\\OptionAction'
                }
            };
        },
        _ajaxSuccess: function (data) {
            UiDialog.open(this, data.returnValues);
        }
    };
});
