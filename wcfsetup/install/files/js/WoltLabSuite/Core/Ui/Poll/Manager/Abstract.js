define(["require", "exports", "tslib", "../../../Core", "../../../Ajax/Request", "../../../Dom/Util"], function (require, exports, tslib_1, Core, Request_1, Util_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.Abstract = void 0;
    Core = (0, tslib_1.__importStar)(Core);
    Request_1 = (0, tslib_1.__importDefault)(Request_1);
    Util_1 = (0, tslib_1.__importDefault)(Util_1);
    class Abstract {
        constructor(pollID) {
            this.pollID = pollID;
            const poll = document.getElementById(`poll${pollID}`);
            if (poll === null) {
                throw new Error(`Could not find poll with id "${pollID}".`);
            }
            this.poll = poll;
            this.initButton();
        }
        apiCall(actionName, data) {
            const request = new Request_1.default({
                // eslint-disable-next-line @typescript-eslint/restrict-template-expressions
                url: `index.php?poll/&t=${SECURITY_TOKEN}`,
                data: Core.extend({
                    actionName,
                    pollID: this.pollID,
                }, data ? data : {}),
                success: (data) => {
                    this.button.disabled = false;
                    this.success(data);
                },
            });
            request.sendRequest();
        }
        initButton() {
            const button = this.poll.querySelector(this.getButtonSelector()) || null;
            if (!button) {
                throw new Error(`Could not find button with selector "${this.getButtonSelector()}" for poll "${this.pollID}"`);
            }
            this.button = button;
            this.button.addEventListener("click", (event) => {
                if (event) {
                    event.preventDefault();
                }
                this.apiCall(this.getActionName(), this.getData());
                this.button.disabled = true;
            });
        }
        getData() {
            return undefined;
        }
        setInnerContainer(html) {
            Util_1.default.setInnerHtml(this.getInnerContainer(), html);
        }
        getInnerContainer() {
            const innterContainer = this.poll.querySelector(".pollInnerContainer") || null;
            if (!innterContainer) {
                throw new Error(`Could not find inner container for poll "${this.pollID}"`);
            }
            return innterContainer;
        }
    }
    exports.Abstract = Abstract;
    exports.default = Abstract;
});
