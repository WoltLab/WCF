/**
 * Promise-based API to submit a form builder form that is embedded directly
 * into a page to a PSR-15 controller.
 *
 * The form is expected to be the only content of the container element. The
 * controller responds with `{ form }` if the validation failed and with
 * `{ form, result }` on success, the form is re-rendered in both cases.
 *
 * @author Marcel Werk
 * @copyright 2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */
define(["require", "exports", "tslib", "../../Ajax/Backend", "../../Dom/Util", "../../Form/Builder/Manager", "../Ckeditor", "../Quote/Storage"], function (require, exports, tslib_1, Backend_1, DomUtil, FormBuilderManager, Ckeditor_1, Storage_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setupInlineForm = setupInlineForm;
    DomUtil = tslib_1.__importStar(DomUtil);
    FormBuilderManager = tslib_1.__importStar(FormBuilderManager);
    class InlineForm {
        #container;
        #endpoint;
        #formId;
        #onSuccess;
        #isSubmitting = false;
        constructor(container, formId, endpoint, onSuccess) {
            this.#container = container;
            this.#formId = formId;
            this.#endpoint = endpoint;
            this.#onSuccess = onSuccess;
            this.#initSubmitButton();
        }
        #initSubmitButton() {
            const submitButton = this.#getSubmitButton();
            submitButton.addEventListener("click", () => {
                void this.#submit();
            });
        }
        #getSubmitButton() {
            const submitButton = document.getElementById(`${this.#formId}_submitButton`);
            if (!(submitButton instanceof HTMLButtonElement)) {
                throw new Error(`Unable to find the submit button of the form '${this.#formId}'.`);
            }
            return submitButton;
        }
        async #submit() {
            if (this.#isSubmitting) {
                return;
            }
            this.#isSubmitting = true;
            const submitButton = this.#getSubmitButton();
            submitButton.disabled = true;
            try {
                const data = await FormBuilderManager.getData(this.#formId);
                const response = (await (0, Backend_1.prepareRequest)(this.#endpoint).post(data).fetchAsJson());
                if ("result" in response) {
                    this.#resetEditors();
                    this.#render(response.form);
                    await this.#onSuccess(response.result);
                }
                else {
                    this.#render(response.form);
                    this.#scrollToFirstError();
                }
            }
            finally {
                this.#isSubmitting = false;
                // The button is replaced when the form is re-rendered.
                if (submitButton.isConnected) {
                    submitButton.disabled = false;
                }
            }
        }
        /**
         * Resets the editors before the form is discarded, this removes the drafts
         * and the attachments of the editors.
         */
        #resetEditors() {
            this.#container.querySelectorAll("textarea.wysiwygTextarea").forEach((textarea) => {
                const ckeditor = (0, Ckeditor_1.getCkeditor)(textarea);
                if (ckeditor === undefined) {
                    return;
                }
                ckeditor.reset();
                (0, Storage_1.clearQuotesForEditor)(textarea.id);
            });
        }
        #render(html) {
            if (FormBuilderManager.hasForm(this.#formId)) {
                FormBuilderManager.unregisterForm(this.#formId);
            }
            DomUtil.setInnerHtml(this.#container, html);
            this.#initSubmitButton();
        }
        #scrollToFirstError() {
            const error = this.#container.querySelector('.formField__errors, woltlab-core-notice[type="error"]');
            error?.scrollIntoView({ behavior: "smooth", block: "center" });
        }
    }
    /**
     * Sets up the form with the given id that has been rendered into the given
     * container. The callback is invoked with the result of a successful
     * submission after the form has been reset.
     */
    function setupInlineForm(container, formId, endpoint, onSuccess) {
        new InlineForm(container, formId, endpoint, onSuccess);
    }
});
