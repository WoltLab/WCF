/**
 * Manages the invocation of the background queue.
 *
 * @author  Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
define(["require", "exports", "tslib", "./Ajax"], function (require, exports, tslib_1, Ajax) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setUrl = setUrl;
    exports.invoke = invoke;
    Ajax = tslib_1.__importStar(Ajax);
    class BackgroundQueue {
        _invocations = 0;
        _isBusy = false;
        _url;
        constructor(url) {
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
    /**
     * Invokes the background queue.
     */
    function invoke() {
        if (!queue) {
            console.error("The background queue has not been initialized yet.");
            return;
        }
        queue.invoke();
    }
});
