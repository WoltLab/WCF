/**
 * Provides the trophy icon designer.
 *
 * @author	Joshua Ruesweg
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLabSuite/Core/Acp/Ui/Trophy/Badge
 */
define(['Core', 'Dictionary', 'Language', 'Ui/Dialog', 'WoltLabSuite/Core/Ui/Color/Picker', 'WoltLabSuite/Core/Ui/Style/FontAwesome'], function (Core, Dictionary, Language, UiDialog, UiColorPicker, UiStyleFontAwesome) {
    "use strict";
    var _icon, _iconNameInput, _iconColorInput, _badgeColorInput, _dialogContent, _iconColor, _badgeColor;
    /**
     * @exports     WoltLabSuite/Core/Acp/Ui/Trophy/Badge
     */
    return {
        /**
         * Initializes the badge designer.
         */
        init: function () {
            var iconContainer = elById('badgeContainer');
            elBySel('.button', iconContainer).addEventListener(WCF_CLICK_EVENT, this._click.bind(this));
            _iconNameInput = elBySel('input[name="iconName"]', iconContainer);
            _iconColorInput = elBySel('input[name="iconColor"]', iconContainer);
            _badgeColorInput = elBySel('input[name="badgeColor"]', iconContainer);
        },
        /**
         * Opens the icon designer.
         *
         * @param       {Event}         event           event object
         * @protected
         */
        _click: function (event) {
            event.preventDefault();
            UiDialog.open(this);
        },
        /**
         * Sets the icon name.
         *
         * @param       {string}        iconName        icon name
         * @protected
         */
        _setIcon: function (iconName) {
            _icon.textContent = iconName;
            this._renderIcon();
        },
        /**
         * Sets the icon color, can be either a string or an object holding the
         * individual r, g, b and a values.
         *
         * @param       {(string|Object)}       color           color data
         * @protected
         */
        _setIconColor: function (color) {
            if (typeof color !== "string") {
                color = 'rgba(' + color.r + ', ' + color.g + ', ' + color.b + ', ' + color.a + ')';
            }
            elData(_iconColor, 'color', color);
            _iconColor.style.setProperty('background-color', color, '');
            this._renderIcon();
        },
        /**
         * Sets the badge color, can be either a string or an object holding the
         * individual r, g, b and a values.
         *
         * @param       {(string|Object)}       color           color data
         * @protected
         */
        _setBadgeColor: function (color) {
            if (typeof color !== "string") {
                color = 'rgba(' + color.r + ', ' + color.g + ', ' + color.b + ', ' + color.a + ')';
            }
            elData(_badgeColor, 'color', color);
            _badgeColor.style.setProperty('background-color', color, '');
            this._renderIcon();
        },
        /**
         * Renders the custom icon preview.
         *
         * @protected
         */
        _renderIcon: function () {
            var iconColor = _iconColor.style.getPropertyValue('background-color');
            var badgeColor = _badgeColor.style.getPropertyValue('background-color');
            var icon = elBySel('.jsTrophyIcon', _dialogContent);
            // set icon
            icon.className = icon.className.replace(/\b(fa-[a-z0-9\-]+)\b/, '');
            icon.classList.add('fa-' + _icon.textContent);
            icon.style.setProperty('color', iconColor, '');
            icon.style.setProperty('background-color', badgeColor, '');
        },
        /**
         * Saves the custom icon design.
         *
         * @param       {Event}         event           event object
         * @protected
         */
        _save: function (event) {
            event.preventDefault();
            var iconColor = _iconColor.style.getPropertyValue('background-color');
            var badgeColor = _badgeColor.style.getPropertyValue('background-color');
            var icon = _icon.textContent;
            _iconNameInput.value = icon;
            _badgeColorInput.value = badgeColor;
            _iconColorInput.value = iconColor;
            var previewIcon = elBySel('.jsTrophyIcon', elById('iconContainer'));
            // set icon
            previewIcon.className = previewIcon.className.replace(/\b(fa-[a-z0-9\-]+)\b/, '');
            previewIcon.classList.add('fa-' + icon);
            previewIcon.style.setProperty('color', iconColor, '');
            previewIcon.style.setProperty('background-color', badgeColor, '');
            UiDialog.close(this);
        },
        _dialogSetup: function () {
            return {
                id: 'trophyIconEditor',
                options: {
                    onSetup: (function (context) {
                        _dialogContent = context;
                        _iconColor = elBySel('#jsIconColorContainer .colorBoxValue', context);
                        _badgeColor = elBySel('#jsBadgeColorContainer .colorBoxValue', context);
                        _icon = elBySel('.jsTrophyIconName', context);
                        elBySel('.jsTrophyIconName + .button', context).addEventListener(WCF_CLICK_EVENT, (function (event) {
                            event.preventDefault();
                            UiStyleFontAwesome.open(this._setIcon.bind(this));
                        }).bind(this));
                        elBySel('.jsButtonIconColorPicker', elById('jsIconColorContainer')).addEventListener(WCF_CLICK_EVENT, function (event) {
                            event.preventDefault();
                            Core.triggerEvent(elBySel('.jsColorPicker', elById('jsIconColorContainer')), WCF_CLICK_EVENT);
                        });
                        elBySel('.jsButtonBadgeColorPicker', elById('jsBadgeColorContainer')).addEventListener(WCF_CLICK_EVENT, function (event) {
                            event.preventDefault();
                            Core.triggerEvent(elBySel('.jsColorPicker', elById('jsBadgeColorContainer')), WCF_CLICK_EVENT);
                        });
                        var colorPicker = new WCF.ColorPicker('.jsColorPicker');
                        colorPicker.setCallbackSubmit(this._renderIcon.bind(this));
                        elBySel('.formSubmit > .buttonPrimary', context).addEventListener(WCF_CLICK_EVENT, this._save.bind(this));
                        return;
                    }).bind(this),
                    onShow: (function () {
                        this._setIcon(_iconNameInput.value);
                        this._setIconColor(_iconColorInput.value);
                        this._setBadgeColor(_badgeColorInput.value);
                    }).bind(this),
                    title: Language.get('wcf.acp.trophy.badge.edit')
                }
            };
        }
    };
});
