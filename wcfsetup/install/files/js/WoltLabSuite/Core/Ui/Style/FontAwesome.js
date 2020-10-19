/**
 * Provides a selection dialog for FontAwesome icons with filter capabilities.
 *
 * @author	Alexander Ebert
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLabSuite/Core/Ui/Style/FontAwesome
 */
define(['Language', 'Ui/Dialog', 'WoltLabSuite/Core/Ui/ItemList/Filter'], function (Language, UiDialog, UiItemListFilter) {
    "use strict";
    if (!COMPILER_TARGET_DEFAULT) {
        var Fake = function () { };
        Fake.prototype = {
            setup: function () { },
            open: function () { },
            _click: function () { },
            _dialogSetup: function () { }
        };
        return Fake;
    }
    var _callback, _iconList, _itemListFilter;
    var _icons = [];
    /**
     * @exports     WoltLabSuite/Core/Ui/Style/FontAwesome
     */
    return {
        /**
         * Sets the list of available icons, must be invoked prior to any call
         * to the `open()` method.
         *
         * @param       {string[]}      icons   list of icon names excluding the `fa-` prefix
         */
        setup: function (icons) {
            _icons = icons;
        },
        /**
         * Shows the FontAwesome selection dialog, supplied callback will be
         * invoked with the selection icon's name as the only argument.
         *
         * @param       {Function<string>}      callback        callback on icon selection, receives icon name only
         */
        open: function (callback) {
            if (_icons.length === 0) {
                throw new Error("Missing icon data, please include the template before calling this method using `{include file='fontAwesomeJavaScript'}`.");
            }
            _callback = callback;
            UiDialog.open(this);
        },
        /**
         * Selects an icon, notifies the callback and closes the dialog.
         *
         * @param       {Event}         event           event object
         * @protected
         */
        _click: function (event) {
            event.preventDefault();
            var item = event.target.closest('li');
            var icon = elBySel('small', item).textContent.trim();
            UiDialog.close(this);
            _callback(icon);
        },
        _dialogSetup: function () {
            return {
                id: 'fontAwesomeSelection',
                options: {
                    onSetup: (function () {
                        _iconList = elById('fontAwesomeIcons');
                        // build icons
                        var icon, html = '';
                        for (var i = 0, length = _icons.length; i < length; i++) {
                            icon = _icons[i];
                            html += '<li><span class="icon icon48 fa-' + icon + '"></span><small>' + icon + '</small></li>';
                        }
                        _iconList.innerHTML = html;
                        _iconList.addEventListener(WCF_CLICK_EVENT, this._click.bind(this));
                        _itemListFilter = new UiItemListFilter('fontAwesomeIcons', {
                            callbackPrepareItem: function (item) {
                                var small = elBySel('small', item);
                                var text = small.textContent.trim();
                                return {
                                    item: item,
                                    span: small,
                                    text: text
                                };
                            },
                            enableVisibilityFilter: false,
                            filterPosition: 'top'
                        });
                    }).bind(this),
                    onShow: function () {
                        _itemListFilter.reset();
                    },
                    title: Language.get('wcf.global.fontAwesome.selectIcon')
                },
                source: '<ul class="fontAwesomeIcons" id="fontAwesomeIcons"></ul>'
            };
        }
    };
});
