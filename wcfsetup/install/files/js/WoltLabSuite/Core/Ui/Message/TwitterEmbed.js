/**
 * Wrapper around Twitter's createTweet API.
 *
 * @author	Tim Duesterhus
 * @copyright	2001-2020 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLabSuite/Core/Ui/Message/TwitterEmbed
 */
define(['https://platform.twitter.com/widgets.js'], function(Widgets) {
	"use strict";
	
	var twitterReady = new Promise(function(resolve, reject) {
		twttr.ready(resolve);
	});
	
	/**
	 * @exports	WoltLabSuite/Core/Ui/Message/TwitterEmbed
	 */
	return {
		/**
		 * Embed the tweet identified by the given tweetId into the given container.
		 * 
		 * @param {HTMLElement} container
		 * @param {string} tweetId
		 * @param {boolean} removeChildren Whether to remove existing children of the given container after embedding the tweet.
		 * @return {HTMLElement} The Tweet element created by Twitter.
		 */
		embedTweet: function(container, tweetId, removeChildren) {
			if (removeChildren === undefined) removeChildren = false;
			
			return twitterReady.then(function() {
				return twttr.widgets.createTweet(tweetId, container, {
					dnt: true,
					lang: document.documentElement.lang,
				});
			}).then(function(tweet) {
				if (tweet && removeChildren) {
					while (container.lastChild) {
						container.removeChild(container.lastChild);
					}
					container.appendChild(tweet);
				}
				
				return tweet;
			});
		},
		/**
		 * Embeds tweets into all elements with a data-wsc-twitter-tweet attribute, removing
		 * existing children.
		 */
		embedAll: function() {
			elBySelAll("[data-wsc-twitter-tweet]", undefined, function(container) {
				var tweetId = elData(container, "wsc-twitter-tweet");
				if (tweetId) {
					this.embedTweet(container, tweetId, true);
					elData(container, "wsc-twitter-tweet", "");
				}
			}.bind(this))
		}
	};
});
