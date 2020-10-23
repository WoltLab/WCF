/**
 * Utility class to provide a 'Jump To' overlay.
 *
 * @author  Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Ui/Page/JumpTo
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
var __importDefault = (this && this.__importDefault) || function (mod) {
    return (mod && mod.__esModule) ? mod : { "default": mod };
};
define(["require", "exports", "../../Language", "../Dialog"], function (require, exports, Language, Dialog_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.init = void 0;
    Language = __importStar(Language);
    Dialog_1 = __importDefault(Dialog_1);
    class UiPageJumpTo {
        constructor() {
            this.elements = new Map();
        }
        /**
         * Initializes a 'Jump To' element.
         */
        init(element, callback) {
            if (!callback) {
                const redirectUrl = element.dataset.link;
                if (redirectUrl) {
                    callback = function (pageNo) {
                        window.location.href = redirectUrl.replace(/pageNo=%d/, 'pageNo=' + pageNo);
                    };
                }
                else {
                    callback = function () {
                    };
                }
            }
            else if (typeof callback !== 'function') {
                throw new TypeError("Expected a valid function for parameter 'callback'.");
            }
            if (!this.elements.has(element)) {
                element.querySelectorAll('.jumpTo').forEach(jumpTo => {
                    jumpTo.addEventListener('click', this.click.bind(this, element));
                    this.elements.set(element, callback);
                });
            }
        }
        /**
         * Handles clicks on the trigger element.
         */
        click(element, event) {
            event.preventDefault();
            this.activeElement = element;
            Dialog_1.default.open(this);
            const pages = element.dataset.pages || '0';
            this.input.value = pages;
            this.input.max = pages;
            this.input.select();
            this.description.textContent = Language.get('wcf.page.jumpTo.description').replace(/#pages#/, pages);
        }
        /**
         * Handles changes to the page number input field.
         *
         * @param  {object}  event    event object
         */
        _keyUp(event) {
            if (event.key === 'Enter' && !this.submitButton.disabled) {
                this.submit();
                return;
            }
            const pageNo = +this.input.value;
            this.submitButton.disabled = pageNo < 1 || pageNo > +this.input.max;
        }
        /**
         * Invokes the callback with the chosen page number as first argument.
         */
        submit() {
            const callback = this.elements.get(this.activeElement);
            callback(+this.input.value);
            Dialog_1.default.close(this);
        }
        _dialogSetup() {
            const source = `<dl>
        <dt><label for="jsPaginationPageNo">${Language.get('wcf.page.jumpTo')}</label></dt>
                <dd>
          <input type="number" id="jsPaginationPageNo" value="1" min="1" max="1" class="tiny">
          <small></small>
        </dd>
      </dl>
      <div class="formSubmit">
        <button class="buttonPrimary">${Language.get('wcf.global.button.submit')}</button>
      </div>`;
            return {
                id: 'paginationOverlay',
                options: {
                    onSetup: content => {
                        this.input = content.querySelector('input');
                        this.input.addEventListener('keyup', this._keyUp.bind(this));
                        this.description = content.querySelector('small');
                        this.submitButton = content.querySelector('button');
                        this.submitButton.addEventListener('click', this.submit.bind(this));
                    },
                    title: Language.get('wcf.global.page.pagination'),
                },
                source: source,
            };
        }
    }
    let jumpTo = null;
    function getUiPageJumpTo() {
        if (jumpTo === null) {
            jumpTo = new UiPageJumpTo();
        }
        return jumpTo;
    }
    /**
     * Initializes a 'Jump To' element.
     */
    function init(element, callback) {
        getUiPageJumpTo().init(element, callback);
    }
    exports.init = init;
});
