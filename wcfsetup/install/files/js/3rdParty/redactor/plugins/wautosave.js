if (!RedactorPlugins) var RedactorPlugins = {};

/**
 * Provides a user storage-based background saving mechanism for Redactor.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
RedactorPlugins.wautosave = function() {
	"use strict";
	
	var _didSave = false;
	var _lastMessage = '';
	var _notice = null;
	var _noticePE = null;
	var _paused = false;
	var _textarea = null;
	var _worker = null;
	
	return {
		init: function() {
			_textarea = this.$textarea[0];
			
			if (this.wutil.getOption('woltlab.autosave').active) {
				this.wautosave.enable();
				
				if (this.wutil.getOption('woltlab.autosave').saveOnInit || this.$textarea.data('saveOnInit')) {
					this.wutil.setOption('woltlab.autosaveOnce', true);
				}
				else {
					this.wautosave.restore();
				}
			}
			
			// prevent Redactor's own autosave
			this.wutil.setOption('autosave', false);
			
			// disable autosave on destroy
			var mpDestroy = this.core.destroy;
			this.core.destroy = (function() {
				this.wautosave.disable();
				
				mpDestroy.call(this);
			}).bind(this);
		},
		
		/**
		 * Enables automatic saving every 15 seconds.
		 * 
		 * @param	{string}	key	storage prefix key
		 */
		enable: function(key) {
			if (!this.wutil.getOption('woltlab.autosave').active) {
				this.wutil.setOption('woltlab.autosave', {
					active: true,
					key: key
				});
			}
			
			if (_worker === null) {
				this.wautosave.purgeOutdated();
				
				_worker = new WCF.PeriodicalExecuter(this.wautosave.save.bind(this), 15 * 1000);
			}
		},
		
		/**
		 * Saves current editor text to local browser storage.
		 * 
		 * @param	{boolean}	force		force save regardless when the last save occured
		 */
		save: function(force) {
			if (force !== true) force = false;
			
			var content = this.wutil.getText();
			if (_lastMessage === content && !force) {
				return;
			}
			
			try {
				localStorage.setItem(this.wutil.getOption('woltlab.autosave').key, JSON.stringify({
					content: content,
					timestamp: Date.now()
				}));
				_lastMessage = content;
				_didSave = true;
				
				if (_noticePE === null) {
					_noticePE = new WCF.PeriodicalExecuter((function(pe) {
						if (_paused === true) {
							return;
						}
						
						if (_didSave === false) {
							pe.stop();
							_noticePE = null;
							
							return;
						}
						
						this.wautosave.showNotice('saved');
						_didSave = false;
					}).bind(this), 120 * 1000);
				}
			}
			catch (e) {
				console.debug("[wautosave.save] Unable to access local storage: " + e.message);
			}
		},
		
		/**
		 * Disables automatic saving.
		 */
		disable: function() {
			if (!this.wutil.getOption('woltlab.autosave').active) {
				return;
			}
			
			_worker.stop();
			_worker = null;
			
			this.wutil.setOption('woltlab.autosave', {
				active: false,
				key: ''
			});
		},
		
		/**
		 * Attempts to purge saved text.
		 */
		purge: function() {
			try {
				localStorage.removeItem(this.wutil.getOption('woltlab.autosave').key);
			}
			catch (e) {
				console.debug("[wautosave.purge] Unable to access local storage: " + e.message);
			}
		},
		
		/**
		 * Attempts to restore a saved text.
		 * 
		 * @return	{boolean}	false if there was no content
		 */
		restore: function() {
			var options = this.wutil.getOption('woltlab.autosave');
			var text = null;
			
			try {
				text = localStorage.getItem(options.key);
			}
			catch (e) {
				console.debug("[wutil.autosaveRestore] Unable to access local storage: " + e.message);
			}
			
			try {
				if (text !== null) text = JSON.parse(text);
			}
			catch (e) {
				text = null;
			}
			
			if (text === null || !text.content) {
				return false;
			}
			
			if (options.lastEditTime && (options.lastEditTime * 1000) > text.timestamp) {
				// stored message is older than last edit time, consider it tainted and discard
				this.wautosave.purge();
				
				return false;
			}
			
			if (options.prompt) {
				this.autosave.showNotice('prompt', text);
				
				return false;
			}
			
			if (this.wutil.inWysiwygMode()) {
				this.wutil.setOption('woltlab.originalValue', text.content);
			}
			else {
				_textarea.value = text.content;
			}
			
			this.wautosave.showNotice('restored', { timestamp: text.timestamp });
			
			return true;
		},
		
		/**
		 * Displays a notice regarding the autosave feature.
		 * 
		 * @param	{string}	type	notification type
		 * @param	{object}	data	notification data
		 */
		showNotice: function(type, data) {
			if (_notice === null) {
				_notice = $('<div class="redactorAutosaveNotice"><span class="redactorAutosaveMessage" /></div>');
				_notice.appendTo(this.$box);
				
				var resetNotice = (function(event) {
					if (event !== null && event.originalEvent.propertyName !== 'opacity') {
						return;
					}
					
					if (_notice.hasClass('open') && event !== null) {
						if (_notice.data('callbackOpen')) {
							_notice.data('callbackOpen')();
						}
					}
					else {
						if (_notice.data('callbackClose')) {
							_notice.data('callbackClose')();
						}
						
						_notice.removeData('callbackClose');
						_notice.removeData('callbackOpen');
						
						_notice.removeClass('redactorAutosaveNoticeIcons');
						_notice.empty();
						$('<span class="redactorAutosaveMessage" />').appendTo(_notice);
					}
				}).bind(this);
				
				_notice.on('transitionend webkitTransitionEnd', resetNotice);
			}
			
			var message = '', uuid = '';
			switch (type) {
				case 'prompt':
					$('<span class="icon icon16 fa-info blue jsTooltip" title="' + WCF.Language.get('wcf.message.autosave.restored.version', { date: new Date(data.timestamp).toLocaleString() }) + '"></span>').prependTo(_notice);
					var accept = $('<span class="icon icon16 fa-check green pointer jsTooltip" title="' + WCF.Language.get('wcf.message.autosave.prompt.confirm') + '"></span>').appendTo(_notice);
					var discard = $('<span class="icon icon16 fa-times red pointer jsTooltip" title="' + WCF.Language.get('wcf.message.autosave.prompt.discard') + '"></span>').appendTo(_notice);
					
					accept.click((function() {
						this.wutil.replaceText(data.content);
						
						resetNotice(null);
						
						this.wautosave.showNotice('restored', data);
					}).bind(this));
					
					discard.click((function() {
						this.wautosave.purge();
						
						_notice.removeClass('open');
					}).bind(this));
					
					message = WCF.Language.get('wcf.message.autosave.prompt');
					_notice.addClass('redactorAutosaveNoticeIcons');
					
					uuid = WCF.System.Event.addListener('com.woltlab.wcf.redactor', 'keydown_' + _textarea.id, (function(data) {
						WCF.System.Event.removeListener('com.woltlab.wcf.redactor', 'keydown_' + _textarea.id, uuid);
						
						setTimeout(function() { _notice.removeClass('open'); }, 3000);
					}).bind(this));
				break;
				
				case 'restored':
					$('<span class="icon icon16 fa-info blue jsTooltip" title="' + WCF.Language.get('wcf.message.autosave.restored.version', { date: new Date(data.timestamp).toLocaleString() }) + '"></span>').prependTo(_notice);
					var accept = $('<span class="icon icon16 fa-check green pointer jsTooltip" title="' + WCF.Language.get('wcf.message.autosave.restored.confirm') + '"></span>').appendTo(_notice);
					var discard = $('<span class="icon icon16 fa-times red pointer jsTooltip" title="' + WCF.Language.get('wcf.message.autosave.restored.revert') + '"></span>').appendTo(_notice);
					
					accept.click(function() { _notice.removeClass('open'); });
					
					discard.click((function() {
						WCF.System.Confirmation.show(WCF.Language.get('wcf.message.autosave.restored.revert.confirmMessage'), (function(action) {
							if (action === 'confirm') {
								this.wutil.reset();
								this.wautosave.purge();
								
								_notice.removeClass('open');
							}
						}).bind(this));
					}).bind(this));
					
					message = WCF.Language.get('wcf.message.autosave.restored');
					_notice.addClass('redactorAutosaveNoticeIcons');
					
					uuid = WCF.System.Event.addListener('com.woltlab.wcf.redactor', 'keydown_' + _textarea.id, (function(data) {
						WCF.System.Event.removeListener('com.woltlab.wcf.redactor', 'keydown_' + _textarea.id, uuid);
						
						setTimeout(function() { accept.trigger('click'); }, 3000);
					}).bind(this));
				break;
				
				case 'saved':
					if (_notice.hasClass('open')) {
						return;
					}
					
					setTimeout(function() {
						_notice.removeClass('open');
					}, 2000);
					
					message = WCF.Language.get('wcf.message.autosave.saved');
				break;
			}
			
			_notice.children('span.redactorAutosaveMessage').text(message);
			_notice.addClass('open');
			
			if (type !== 'saved') {
				WCF.DOMNodeInsertedHandler.execute();
			}
		},
		
		/**
		 * Automatically purges autosaved content older than 7 days.
		 */
		purgeOutdated: function() {
			var lastChecked = 0;
			var prefix = this.wutil.getOption('woltlab.autosave').prefix;
			var master = prefix + '_wcf_master';
			
			try {
				lastChecked = localStorage.getItem(master);
			}
			catch (e) {
				console.debug("[wautosave.purgeOutdated] Unable to access local storage: " + e.message);
			}
			
			if (lastChecked === 0) {
				// unable to access local storage, skip check
				return;
			}
			
			// JavaScript timestamps are in miliseconds
			var oneWeekAgo = Date.now() - (7 * 24 * 3600 * 1000), value;
			if (lastChecked === null || lastChecked < oneWeekAgo) {
				var regExp = new RegExp('^' + prefix + '_');
				for (var key in localStorage) {
					if (key.match(regExp) && key !== master) {
						value = localStorage.getItem(key);
						try {
							value = JSON.parse(value);
						}
						catch (e) {
							value = { timestamp: 0 };
						}
						
						if (value === null || !value.timestamp || value.timestamp < oneWeekAgo) {
							try {
								localStorage.removeItem(key);
							}
							catch (e) {
								console.debug("[wautosave.purgeOutdated] Unable to access local storage: " + e.message);
							}
						}
					}
				}
				
				try {
					localStorage.setItem(master, Date.now());
				}
				catch (e) {
					console.debug("[wautosave.purgeOutdated] Unable to access local storage: " + e.message);
				}
			}
		},
		
		/**
		 * Temporarily pauses autosave worker.
		 */
		autosavePause: function() {
			_paused = true;
		},
		
		/**
		 * Resumes autosave worker.
		 */
		autosaveResume: function() {
			_paused = false;
		}
	};
};
