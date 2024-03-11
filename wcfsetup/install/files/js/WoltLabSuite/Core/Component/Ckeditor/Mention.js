/**
 * Provides mention support for users and groups.
 *
 * @author Alexander Ebert
 * @copyright 2001-2023 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.0
 * @woltlabExcludeBundle tiny
 */
define(["require", "exports", "../../Ajax/Backend", "../../Dom/Util", "./Event"], function (require, exports, Backend_1, Util_1, Event_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setup = void 0;
    async function getPossibleMentions(query) {
        // Prevent excessive attempts to resolve mentions.
        if (query.length > 24) {
            return [];
        }
        // TODO: Provide the URL as a parameter.
        const url = new URL(window.WSC_API_URL + "index.php?api/rpc/core/messages/mentionsuggestions");
        url.searchParams.set("query", query);
        const result = (await (0, Backend_1.prepareRequest)(url.toString())
            .get()
            .allowCaching()
            .disableLoadingIndicator()
            .fetchAsJson());
        return result.map((item) => {
            if (item.type === "user") {
                return {
                    id: `@${item.username}`,
                    text: `@${item.username}`,
                    icon: item.avatarTag,
                    objectId: item.userID,
                    type: item.type,
                };
            }
            else {
                return {
                    id: `@${item.name}`,
                    text: `@${item.name}`,
                    icon: '<fa-icon name="users"></fa-icon>',
                    objectId: item.groupID,
                    type: item.type,
                };
            }
        });
    }
    function getMentionConfiguration() {
        return {
            feeds: [
                {
                    feed: (query) => getPossibleMentions(query),
                    itemRenderer: (item) => {
                        return (0, Util_1.createFragmentFromHtml)(`
            <span class="ckeditor5__mention">${item.icon} ${item.text}</span>
          `).firstElementChild;
                    },
                    marker: "@",
                    minimumCharacters: 3,
                },
            ],
        };
    }
    function setup(element) {
        (0, Event_1.listenToCkeditor)(element).setupConfiguration(({ configuration, features }) => {
            if (!features.mention) {
                return;
            }
            configuration.mention = getMentionConfiguration();
        });
    }
    exports.setup = setup;
});
