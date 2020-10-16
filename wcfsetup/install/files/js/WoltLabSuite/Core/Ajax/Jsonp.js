/**
 * Provides a utility class to issue JSONP requests.
 *
 * @author  Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  AjaxJsonp (alias)
 * @module  WoltLabSuite/Core/Ajax/Jsonp
 */
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
define(["require", "exports", "../Core"], function (require, exports, Core) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.send = void 0;
    Core = __importStar(Core);
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
        const timeout = window.setTimeout(function () {
            if (typeof failure === 'function') {
                failure();
            }
            window[callbackName] = undefined;
            script.parentNode.removeChild(script);
        }, (~~options.timeout || 10) * 1000);
        window[callbackName] = function () {
            window.clearTimeout(timeout);
            //@ts-ignore
            success.apply(null, arguments);
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
