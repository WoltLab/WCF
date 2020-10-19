/**
 * Prompts the user for their consent before displaying external media.
 *
 * @author      Alexander Ebert
 * @copyright   2001-2020 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module      WoltLabSuite/Core/Ui/Message/UserConsent
 */
define(['Ajax', 'Core', 'User', 'Dom/ChangeListener', 'Dom/Util'], function (Ajax, Core, User, DomChangeListener, DomUtil) {
    var _enableAll = false;
    var _knownButtons = (typeof window.WeakSet === 'function') ? new window.WeakSet() : new window.Set();
    return {
        init: function () {
            if (window.sessionStorage.getItem(Core.getStoragePrefix() + 'user-consent') === 'all') {
                _enableAll = true;
            }
            this._registerEventListeners();
            DomChangeListener.add('WoltLabSuite/Core/Ui/Message/UserConsent', this._registerEventListeners.bind(this));
        },
        _registerEventListeners: function () {
            if (_enableAll) {
                this._enableAll();
            }
            else {
                elBySelAll('.jsButtonMessageUserConsentEnable', undefined, (function (button) {
                    if (!_knownButtons.has(button)) {
                        button.addEventListener('click', this._click.bind(this));
                        _knownButtons.add(button);
                    }
                }).bind(this));
            }
        },
        /**
         * @param {Event} event
         */
        _click: function (event) {
            event.preventDefault();
            _enableAll = true;
            this._enableAll();
            if (User.userId) {
                Ajax.apiOnce({
                    data: {
                        actionName: 'saveUserConsent',
                        className: 'wcf\\data\\user\\UserAction'
                    },
                    silent: true
                });
            }
            else {
                window.sessionStorage.setItem(Core.getStoragePrefix() + 'user-consent', 'all');
            }
        },
        /**
         * @param {Element} container
         */
        _enableExternalMedia: function (container) {
            var payload = atob(elData(container, 'payload'));
            DomUtil.insertHtml(payload, container, 'before');
            elRemove(container);
        },
        _enableAll: function () {
            elBySelAll('.messageUserConsent', undefined, this._enableExternalMedia.bind(this));
        }
    };
});
