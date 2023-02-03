define(["require", "exports", "../../Ajax/Backend", "../../Dom/Util"], function (require, exports, Backend_1, Util_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.initializeMention = exports.getPossibleMentions = void 0;
    async function getPossibleMentions(query) {
        // TODO: Provide the URL as a parameter.
        const url = new URL(window.WSC_API_URL + "index.php?editor-get-mention-suggestions/");
        url.searchParams.set("query", query);
        const result = (await (0, Backend_1.prepareRequest)(url.toString())
            .get()
            .allowCaching()
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
    function getMentionConfiguration() {
        return {
            feeds: [
                {
                    feed: (query) => {
                        // TODO: The typings are outdated, cast the result to `any`.
                        return getPossibleMentions(query);
                    },
                    itemRenderer: (item) => {
                        // TODO: This is ugly.
                        return (0, Util_1.createFragmentFromHtml)(`
            <span>${item.icon} ${item.text}</span>
          `).firstElementChild;
                    },
                    marker: "@",
                    minimumCharacters: 3,
                },
            ],
        };
    }
    function initializeMention(configuration) {
        configuration.mention = getMentionConfiguration();
    }
    exports.initializeMention = initializeMention;
});
