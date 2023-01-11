define(["require", "exports", "../../Ajax/Backend"], function (require, exports, Backend_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.getPossibleMentions = void 0;
    async function getPossibleMentions(query) {
        // TODO: Provide the URL as a parameter.
        const url = new URL(window.WSC_API_URL + "index.php?editor-get-mention-suggestions/");
        url.searchParams.set("query", query);
        const result = (await (0, Backend_1.prepareRequest)(url.toString())
            .get()
            .disableLoadingIndicator()
            .fetchAsJson());
        return result.map((item) => {
            return {
                id: `@${item.username}`,
                text: item.username,
                icon: item.avatarTag,
            };
        });
    }
    exports.getPossibleMentions = getPossibleMentions;
});
