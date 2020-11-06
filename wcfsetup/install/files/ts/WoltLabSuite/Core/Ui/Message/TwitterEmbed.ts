/**
 * Wrapper around Twitter's createTweet API.
 *
 * @author  Tim Duesterhus
 * @copyright  2001-2020 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Ui/Message/TwitterEmbed
 */

import "https://platform.twitter.com/widgets.js";

type CallbackReady = (twttr: Twitter) => void;

const twitterReady = new Promise((resolve: CallbackReady) => {
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
export async function embedTweet(
  container: HTMLElement,
  tweetId: string,
  removeChildren = false,
): Promise<HTMLElement> {
  const twitter = await twitterReady;

  const tweet = await twitter.widgets.createTweet(tweetId, container, {
    dnt: true,
    lang: document.documentElement.lang,
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
export function embedAll(): void {
  document.querySelectorAll("[data-wsc-twitter-tweet]").forEach((container: HTMLElement) => {
    const tweetId = container.dataset.wscTwitterTweet;
    if (tweetId) {
      delete container.dataset.wscTwitterTweet;

      void embedTweet(container, tweetId, true);
    }
  });
}
