/**
 * Provides the AJAX status overlay.
 *
 * @author  Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
define(["require", "exports"], function (require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.show = show;
    exports.hide = hide;
    class AjaxStatus {
        _activeRequests = 0;
        _overlay;
        _timer = null;
        constructor() {
            this._overlay = document.createElement("div");
            this._overlay.classList.add("spinner");
            this._overlay.setAttribute("role", "status");
            const loadingIndicator = document.createElement("woltlab-core-loading-indicator");
            loadingIndicator.size = 48;
            this._overlay.append(loadingIndicator);
            document.body.append(this._overlay);
        }
        show() {
            this._activeRequests++;
            if (this._timer === null) {
                this._timer = window.setTimeout(() => {
                    if (this._activeRequests) {
                        this._overlay.classList.add("active");
                    }
                    this._timer = null;
                }, 250);
            }
        }
        hide() {
            if (--this._activeRequests === 0) {
                if (this._timer !== null) {
                    window.clearTimeout(this._timer);
                    this._timer = null;
                }
                this._overlay.classList.remove("active");
            }
        }
    }
    let status;
    function getStatus() {
        if (status === undefined) {
            status = new AjaxStatus();
        }
        return status;
    }
    /**
     * Shows the loading overlay.
     */
    function show() {
        getStatus().show();
    }
    /**
     * Hides the loading overlay.
     */
    function hide() {
        getStatus().hide();
    }
});
