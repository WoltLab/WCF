/**
 * Manages the invocation of the background queue.
 *
 * @author  Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/BackgroundQueue
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
define(["require", "exports", "./Ajax"], function (require, exports, Ajax) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.invoke = exports.setUrl = void 0;
    Ajax = __importStar(Ajax);
    class BackgroundQueue {
        constructor(url) {
            this._invocations = 0;
            this._isBusy = false;
            this._url = url;
        }
        invoke() {
            if (this._isBusy)
                return;
            this._isBusy = true;
            Ajax.api(this);
        }
        _ajaxSuccess(data) {
            this._invocations++;
            // invoke the queue up to 5 times in a row
            if (data > 0 && this._invocations < 5) {
                window.setTimeout(() => {
                    this._isBusy = false;
                    this.invoke();
                }, 1000);
            }
            else {
                this._isBusy = false;
                this._invocations = 0;
            }
        }
        _ajaxSetup() {
            return {
                url: this._url,
                ignoreError: true,
                silent: true,
            };
        }
    }
    let queue;
    /**
     * Sets the url of the background queue perform action.
     */
    function setUrl(url) {
        if (!queue) {
            queue = new BackgroundQueue(url);
        }
    }
    exports.setUrl = setUrl;
    /**
     * Invokes the background queue.
     */
    function invoke() {
        if (!queue) {
            console.error('The background queue has not been initialized yet.');
            return;
        }
        queue.invoke();
    }
    exports.invoke = invoke;
});
