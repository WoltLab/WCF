/**
 * Provides the touch-friendly fullscreen main menu.
 *
 * @author	Alexander Ebert
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLabSuite/Core/Ui/Page/Menu/Main
 */
define(['Core', 'Language', 'Dom/Traverse', './Abstract'], function (Core, Language, DomTraverse, UiPageMenuAbstract) {
    "use strict";
    var _optionsTitle = null, _hasItems = null, _list = null, _navigationList = null, _callbackClose = null;
    /**
     * @constructor
     */
    function UiPageMenuMain() { this.init(); }
    Core.inherit(UiPageMenuMain, UiPageMenuAbstract, {
        /**
         * Initializes the touch-friendly fullscreen main menu.
         */
        init: function () {
            UiPageMenuMain._super.prototype.init.call(this, 'com.woltlab.wcf.MainMenuMobile', 'pageMainMenuMobile', '#pageHeader .mainMenu');
            _optionsTitle = elById('pageMainMenuMobilePageOptionsTitle');
            if (_optionsTitle !== null) {
                _list = DomTraverse.childByClass(_optionsTitle, 'menuOverlayItemList');
                _navigationList = elBySel('.jsPageNavigationIcons');
                _callbackClose = (function (event) {
                    this.close();
                    event.stopPropagation();
                }).bind(this);
            }
            elAttr(this._button, 'aria-label', Language.get('wcf.menu.page'));
            elAttr(this._button, 'role', 'button');
        },
        open: function (event) {
            if (!UiPageMenuMain._super.prototype.open.call(this, event)) {
                return false;
            }
            if (_optionsTitle === null) {
                return true;
            }
            _hasItems = _navigationList && _navigationList.childElementCount > 0;
            if (_hasItems) {
                var item, link;
                while (_navigationList.childElementCount) {
                    item = _navigationList.children[0];
                    item.classList.add('menuOverlayItem');
                    item.classList.add('menuOverlayItemOption');
                    item.addEventListener(WCF_CLICK_EVENT, _callbackClose);
                    link = item.children[0];
                    link.classList.add('menuOverlayItemLink');
                    link.classList.add('box24');
                    link.children[1].classList.remove('invisible');
                    link.children[1].classList.add('menuOverlayItemTitle');
                    _optionsTitle.parentNode.insertBefore(item, _optionsTitle.nextSibling);
                }
                elShow(_optionsTitle);
            }
            else {
                elHide(_optionsTitle);
            }
            return true;
        },
        close: function (event) {
            if (!UiPageMenuMain._super.prototype.close.call(this, event)) {
                return false;
            }
            if (_hasItems) {
                elHide(_optionsTitle);
                var item = _optionsTitle.nextElementSibling;
                var link;
                while (item && item.classList.contains('menuOverlayItemOption')) {
                    item.classList.remove('menuOverlayItem');
                    item.classList.remove('menuOverlayItemOption');
                    item.removeEventListener(WCF_CLICK_EVENT, _callbackClose);
                    link = item.children[0];
                    link.classList.remove('menuOverlayItemLink');
                    link.classList.remove('box24');
                    link.children[1].classList.add('invisible');
                    link.children[1].classList.remove('menuOverlayItemTitle');
                    _navigationList.appendChild(item);
                    item = item.nextElementSibling;
                }
            }
            return true;
        }
    });
    return UiPageMenuMain;
});
