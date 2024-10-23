/**
 * Wrapper around Twitter's createTweet API.
 *
 * @author  Tim Duesterhus
 * @copyright  2001-2020 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
define(["require", "exports", "https://platform.twitter.com/widgets.js"], function (require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.embedTweet = embedTweet;
    exports.embedAll = embedAll;
    const twitterReady = new Promise((resolve) => {
        twttr.ready(resolve);
    });
    /**
     * Embed the tweet identified by the given tweetId into the given container.
     *
     * @param {HTMLElement} container
     * @param {string} tweetId
     * @param {boolean} removeChildren Whether to remove existing children of the given container after embedding the tweet.
     * @return {HTMLElement} The Tweet element created by Twitter.
     */
    async function embedTweet(container, tweetId, removeChildren = false) {
        const twitter = await twitterReady;
        const theme = document.documentElement.dataset.colorScheme === "dark" ? "dark" : "light";
        const tweet = await twitter.widgets.createTweet(tweetId, container, {
            dnt: true,
            lang: document.documentElement.lang,
            theme,
        });
        if (tweet && removeChildren) {
            while (container.lastChild) {
                container.removeChild(container.lastChild);
            }
            container.appendChild(tweet);
        }
        return tweet;
    }
    /**
     * Embeds tweets into all elements with a data-wsc-twitter-tweet attribute, removing
     * existing children.
     */
    function embedAll() {
        document.querySelectorAll("[data-wsc-twitter-tweet]").forEach((container) => {
            const tweetId = container.dataset.wscTwitterTweet;
            if (tweetId) {
                delete container.dataset.wscTwitterTweet;
                void embedTweet(container, tweetId, true);
            }
        });
    }
});
