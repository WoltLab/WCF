/**
 * Manages spoilers.
 *
 * @author      Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module      WoltLabSuite/Core/Ui/Redactor/Spoiler
 * @woltlabExcludeBundle tiny
 */
define(["require", "exports", "tslib", "../../Core", "../../Dom/Util", "../../Event/Handler", "../../Language", "../Dialog", "./PseudoHeader"], function (require, exports, tslib_1, Core, Util_1, EventHandler, Language, Dialog_1, UiRedactorPseudoHeader) {
    "use strict";
    Core = tslib_1.__importStar(Core);
    Util_1 = tslib_1.__importDefault(Util_1);
    EventHandler = tslib_1.__importStar(EventHandler);
    Language = tslib_1.__importStar(Language);
    Dialog_1 = tslib_1.__importDefault(Dialog_1);
    UiRedactorPseudoHeader = tslib_1.__importStar(UiRedactorPseudoHeader);
    let _headerHeight = 0;
    class UiRedactorSpoiler {
        /**
         * Initializes the spoiler management.
         */
        constructor(editor) {
            this._spoiler = null;
            this._editor = editor;
            this._elementId = this._editor.$element[0].id;
            EventHandler.add("com.woltlab.wcf.redactor2", `bbcode_spoiler_${this._elementId}`, (data) => this._bbcodeSpoiler(data));
            EventHandler.add("com.woltlab.wcf.redactor2", `observe_load_${this._elementId}`, () => this._observeLoad());
            // bind listeners on init
            this._observeLoad();
        }
        /**
         * Intercepts the insertion of `[spoiler]` tags and uses
         * the custom `<woltlab-spoiler>` element instead.
         */
        _bbcodeSpoiler(data) {
            data.cancel = true;
            this._editor.button.toggle({}, "woltlab-spoiler", "func", "block.format");
            let spoiler = this._editor.selection.block();
            if (spoiler) {
                // iOS Safari might set the caret inside the spoiler.
                if (spoiler.nodeName === "P") {
                    spoiler = spoiler.parentElement;
                }
                if (spoiler.nodeName === "WOLTLAB-SPOILER") {
                    this._setTitle(spoiler);
                    spoiler.addEventListener("click", (ev) => this._edit(ev));
                    // work-around for Safari
                    this._editor.caret.end(spoiler);
                }
            }
        }
        /**
         * Binds event listeners and sets quote title on both editor
         * initialization and when switching back from code view.
         */
        _observeLoad() {
            this._editor.$editor[0].querySelectorAll("woltlab-spoiler").forEach((spoiler) => {
                spoiler.addEventListener("mousedown", (ev) => this._edit(ev));
                this._setTitle(spoiler);
            });
        }
        /**
         * Opens the dialog overlay to edit the spoiler's properties.
         */
        _edit(event) {
            const spoiler = event.currentTarget;
            if (_headerHeight === 0) {
                _headerHeight = UiRedactorPseudoHeader.getHeight(spoiler);
            }
            // check if the click hit the header
            const offset = Util_1.default.offset(spoiler);
            if (event.pageY > offset.top && event.pageY < offset.top + _headerHeight) {
                event.preventDefault();
                this._editor.selection.save();
                this._spoiler = spoiler;
                Dialog_1.default.open(this);
            }
        }
        /**
         * Saves the changes to the spoiler's properties.
         *
         * @protected
         */
        _dialogSubmit() {
            const spoiler = this._spoiler;
            const label = document.getElementById("redactor-spoiler-" + this._elementId + "-label");
            spoiler.dataset.label = label.value;
            this._setTitle(spoiler);
            this._editor.caret.after(spoiler);
            Dialog_1.default.close(this);
        }
        /**
         * Sets or updates the spoiler's header title.
         */
        _setTitle(spoiler) {
            const title = Language.get("wcf.editor.spoiler.title", { label: spoiler.dataset.label || "" });
            if (spoiler.dataset.title !== title) {
                spoiler.dataset.title = title;
            }
        }
        _delete(event) {
            event.preventDefault();
            const spoiler = this._spoiler;
            let caretEnd = spoiler.nextElementSibling || spoiler.previousElementSibling;
            if (caretEnd === null && spoiler.parentElement !== this._editor.core.editor()[0]) {
                caretEnd = spoiler.parentElement;
            }
            if (caretEnd === null) {
                this._editor.code.set("");
                this._editor.focus.end();
            }
            else {
                spoiler.remove();
                this._editor.caret.end(caretEnd);
            }
            Dialog_1.default.close(this);
        }
        _dialogSetup() {
            const id = `redactor-spoiler-${this._elementId}`;
            const idButtonDelete = `${id}-button-delete`;
            const idButtonSave = `${id}-button-save`;
            const idLabel = `${id}-label`;
            return {
                id: id,
                options: {
                    onClose: () => {
                        this._editor.selection.restore();
                        Dialog_1.default.destroy(this);
                    },
                    onSetup: () => {
                        const button = document.getElementById(idButtonDelete);
                        button.addEventListener("click", (ev) => this._delete(ev));
                    },
                    onShow: () => {
                        const label = document.getElementById(idLabel);
                        label.value = this._spoiler.dataset.label || "";
                    },
                    title: Language.get("wcf.editor.spoiler.edit"),
                },
                source: `<div class="section">
          <dl>
            <dt>
              <label for="${idLabel}">${Language.get("wcf.editor.spoiler.label")}</label>
            </dt>
            <dd>
              <input type="text" id="${idLabel}" class="long" data-dialog-submit-on-enter="true">
              <small>${Language.get("wcf.editor.spoiler.label.description")}</small>
            </dd>
          </dl>
        </div>
        <div class="formSubmit">
          <button id="${idButtonSave}" class="buttonPrimary" data-type="submit">${Language.get("wcf.global.button.save")}</button>
          <button id="${idButtonDelete}">${Language.get("wcf.global.button.delete")}</button>
        </div>`,
            };
        }
    }
    Core.enableLegacyInheritance(UiRedactorSpoiler);
    return UiRedactorSpoiler;
});
