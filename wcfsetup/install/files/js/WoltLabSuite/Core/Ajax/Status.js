/**
 * Provides the AJAX status overlay.
 *
 * @author  Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Ajax/Status
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
define(["require", "exports", "../Language"], function (require, exports, Language) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.hide = exports.show = void 0;
    Language = __importStar(Language);
    class AjaxStatus {
        constructor() {
            this._activeRequests = 0;
            this._timer = null;
            this._overlay = document.createElement('div');
            this._overlay.classList.add('spinner');
            this._overlay.setAttribute('role', 'status');
            const icon = document.createElement('span');
            icon.className = 'icon icon48 fa-spinner';
            this._overlay.appendChild(icon);
            const title = document.createElement('span');
            title.textContent = Language.get('wcf.global.loading');
            this._overlay.appendChild(title);
            document.body.appendChild(this._overlay);
        }
        show() {
            this._activeRequests++;
            if (this._timer === null) {
                this._timer = window.setTimeout(() => {
                    if (this._activeRequests) {
                        this._overlay.classList.add('active');
                    }
                    this._timer = null;
                }, 250);
            }
        }
        hide() {
            if (--this._activeRequests === 0) {
                if (this._timer !== null) {
                    window.clearTimeout(this._timer);
                }
                this._overlay.classList.remove('active');
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
    exports.show = show;
    /**
     * Hides the loading overlay.
     */
    function hide() {
        getStatus().hide();
    }
    exports.hide = hide;
});
