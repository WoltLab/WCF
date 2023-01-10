define(["require", "exports", "../../Ajax"], function (require, exports, Ajax_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.getPossibleMentions = void 0;
    async function getPossibleMentions(query) {
        const result = (await (0, Ajax_1.dboAction)("getSearchResultList", "wcf\\data\\user\\UserAction")
            .disableLoadingIndicator()
            .payload({
            data: {
                includeUserGroups: false,
                scope: "mention",
                searchString: query,
            },
        })
            .dispatch());
        return result.map((item) => {
            return {
                id: `@${item.label}`,
                text: item.label,
            };
        });
    }
    exports.getPossibleMentions = getPossibleMentions;
});
