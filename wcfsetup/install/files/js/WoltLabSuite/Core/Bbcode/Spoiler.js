/**
 * Generic handler for spoiler boxes.
 *
 * @author      Alexander Ebert
 * @copyright   2001-2020 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module      WoltLabSuite/Core/Bbcode/Spoiler
 */
define(['Language'], function (Language) {
    'use strict';
    var _containers = elByClass('jsSpoilerBox');
    /**
     * @exports	WoltLabSuite/Core/Bbcode/Spoiler
     */
    return {
        observe: function () {
            var container, toggleButton;
            while (_containers.length) {
                container = _containers[0];
                container.classList.remove('jsSpoilerBox');
                toggleButton = elBySel('.jsSpoilerToggle', container);
                container = toggleButton.parentNode.nextElementSibling;
                toggleButton.addEventListener(WCF_CLICK_EVENT, this._onClick.bind(this, container, toggleButton));
            }
        },
        _onClick: function (container, toggleButton, event) {
            event.preventDefault();
            toggleButton.classList.toggle('active');
            var isActive = toggleButton.classList.contains('active');
            window[(isActive ? 'elShow' : 'elHide')](container);
            elAttr(toggleButton, 'aria-expanded', isActive);
            elAttr(container, 'aria-hidden', !isActive);
            if (!elDataBool(toggleButton, 'has-custom-label')) {
                toggleButton.textContent = Language.get(toggleButton.classList.contains('active') ? 'wcf.bbcode.spoiler.hide' : 'wcf.bbcode.spoiler.show');
            }
        }
    };
});
