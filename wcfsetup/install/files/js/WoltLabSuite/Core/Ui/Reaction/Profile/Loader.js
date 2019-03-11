/**
 * Handles the reaction list in the user profile. 
 *
 * @author	Joshua Ruesweg
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLabSuite/Core/Ui/Reaction/Profile/Loader
 * @since       5.2
 */
define(['Ajax', 'Core', 'Language'], function(Ajax, Core, Language) {
	"use strict";
	
	/**
	 * @constructor
	 */
	function UiReactionProfileLoader(userID, firstReactionTypeID) { this.init(userID, firstReactionTypeID); }
	UiReactionProfileLoader.prototype = {
		/**
		 * Initializes a new ReactionListLoader object.
		 *
		 * @param	integer		userID
		 */
		init: function(userID, firstReactionTypeID) {
			this._container = elById('likeList');
			this._userID = userID;
			this._reactionTypeID = firstReactionTypeID;
			this._targetType = 'received';
			this._options = {
				parameters: []
			};
			
			if (!this._userID) {
				throw new Error("[WoltLabSuite/Core/Ui/Reaction/Profile/Loader] Invalid parameter 'userID' given.");
			}
			
			if (!this._reactionTypeID) {
				throw new Error("[WoltLabSuite/Core/Ui/Reaction/Profile/Loader] Invalid parameter 'firstReactionTypeID' given.");
			}
			
			var loadButtonList = elCreate('li');
			loadButtonList.className = 'likeListMore showMore';
			this._noMoreEntries = elCreate('small');
			this._noMoreEntries.innerHTML = Language.get('wcf.like.reaction.noMoreEntries');
			this._noMoreEntries.style.display = 'none';
			loadButtonList.appendChild(this._noMoreEntries);
			
			this._loadButton = elCreate('button');
			this._loadButton.className = 'small';
			this._loadButton.innerHTML = Language.get('wcf.like.reaction.more');
			this._loadButton.addEventListener(WCF_CLICK_EVENT, this._loadReactions.bind(this));
			this._loadButton.style.display = 'none';
			loadButtonList.appendChild(this._loadButton);
			this._container.appendChild(loadButtonList);
			
			if (elBySel('#likeList > li').length === 2) {
				this._noMoreEntries.style.display = '';
			}
			else {
				this._loadButton.style.display = '';
			}
			
			this._setupReactionTypeButtons();
			this._setupTargetTypeButtons();
		},
		
		/**
		 * Set up the reaction type buttons. 
		 */
		_setupReactionTypeButtons: function() {
			var element, elements = elBySelAll('#reactionType .button');
			for (var i = 0, length = elements.length; i < length; i++) {
				element = elements[i];
				element.addEventListener(WCF_CLICK_EVENT, this._changeReactionTypeValue.bind(this, ~~elData(element, 'reaction-type-id')));
			}
		},
		
		/**
		 * Set up the target type buttons.
		 */
		_setupTargetTypeButtons: function() {
			var element, elements = elBySelAll('#likeType .button');
			for (var i = 0, length = elements.length; i < length; i++) {
				element = elements[i];
				element.addEventListener(WCF_CLICK_EVENT, this._changeTargetType.bind(this, elData(element, 'like-type')));
			}
		},
		
		/**
		 * Changes the reaction target type (given or received) and reload the entire element.
		 * 
		 * @param       {string}           targetType
		 */
		_changeTargetType: function(targetType) {
			if (targetType !== 'given' && targetType !== 'received') {
				throw new Error("[WoltLabSuite/Core/Ui/Reaction/Profile/Loader] Invalid parameter 'targetType' given.");
			}
			
			if (targetType !== this._targetType) {
				// remove old active state
				elBySel('#likeType .button.active').classList.remove('active');
				
				// add active status to new button 
				elBySel('#likeType .button[data-like-type="'+ targetType +'"]').classList.add('active');
				
				this._targetType = targetType;
				this._reload();
			}
		},
		
		/**
		 * Changes the reaction type value and reload the entire element. 
		 * 
		 * @param       {int}           reactionTypeID
		 */
		_changeReactionTypeValue: function(reactionTypeID) {
			if (this._reactionTypeID !== reactionTypeID) {
				// remove old active state
				elBySel('#reactionType .button.active').classList.remove('active');
				
				// add active status to new button 
				elBySel('#reactionType .button[data-reaction-type-id="'+ reactionTypeID +'"]').classList.add('active');
				
				this._reactionTypeID = reactionTypeID;
				this._reload();
			}
		},
		
		/**
		 * Handles reload.
		 */
		_reload: function() {
			var elements = elBySelAll('#likeList > li:not(:first-child):not(:last-child)');
			
			for (var i = 0, length = elements.length; i < length; i++) {
				this._container.removeChild(elements[i]);
			}
			
			elData(this._container, 'last-like-time', 0);
			
			this._loadReactions();
		},
		
		/**
		 * Load a list of reactions. 
		 */
		_loadReactions: function() {
			this._options.parameters.userID = this._userID;
			this._options.parameters.lastLikeTime = elData(this._container, 'last-like-time');
			this._options.parameters.targetType = this._targetType;
			this._options.parameters.reactionTypeID = this._reactionTypeID;
			
			Ajax.api(this, {
				parameters: this._options.parameters
			});
		},
		
		_ajaxSuccess: function(data) {
			if (data.returnValues.template) {
				elBySel('#likeList > li:nth-last-child(1)').insertAdjacentHTML('beforebegin', data.returnValues.template);
				
				elData(this._container, 'last-like-time', data.returnValues.lastLikeTime);
				this._noMoreEntries.style.display = 'none';
				this._loadButton.style.display = '';
			}
			else {
				this._noMoreEntries.style.display = '';
				this._loadButton.style.display = 'none';
			}
		},
		
		_ajaxSetup: function() {
			return {
				data: {
					actionName: 'load',
					className: '\\wcf\\data\\reaction\\ReactionAction'
				}
			};
		}
	};
	
	return UiReactionProfileLoader;
});
