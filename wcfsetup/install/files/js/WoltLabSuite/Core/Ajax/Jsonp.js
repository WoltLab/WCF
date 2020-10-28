/**
 * Provides a utility class to issue JSONP requests.
 *
 * @author  Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  AjaxJsonp (alias)
 * @module  WoltLabSuite/Core/Ajax/Jsonp
 */
define(["require", "exports", "tslib", "../Core"], function (require, exports, tslib_1, Core) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.send = void 0;
    Core = tslib_1.__importStar(Core);
    /**
     * Dispatch a JSONP request, the `url` must not contain a callback parameter.
     */
    function send(url, success, failure, options) {
        url = (typeof url === 'string') ? url.trim() : '';
        if (url.length === 0) {
            throw new Error('Expected a non-empty string for parameter \'url\'.');
        }
        if (typeof success !== 'function') {
            throw new TypeError('Expected a valid callback function for parameter \'success\'.');
        }
        options = Core.extend({
            parameterName: 'callback',
            timeout: 10,
        }, options || {});
        const callbackName = 'wcf_jsonp_' + Core.getUuid().replace(/-/g, '').substr(0, 8);
        let script;
        const timeout = window.setTimeout(() => {
            if (typeof failure === 'function') {
                failure();
            }
            window[callbackName] = undefined;
            script.parentNode.removeChild(script);
        }, (~~options.timeout || 10) * 1000);
        window[callbackName] = (...args) => {
            window.clearTimeout(timeout);
            success.apply(null, args);
            window[callbackName] = undefined;
            script.parentNode.removeChild(script);
        };
        url += (url.indexOf('?') === -1) ? '?' : '&';
        url += options.parameterName + '=' + callbackName;
        script = document.createElement('script');
        script.async = true;
        script.src = url;
        document.head.appendChild(script);
    }
    exports.send = send;
});
