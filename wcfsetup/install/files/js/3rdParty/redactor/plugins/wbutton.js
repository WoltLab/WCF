if (!RedactorPlugins) var RedactorPlugins = {};

/**
 * Provides custom BBCode buttons for Redactor.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
RedactorPlugins.wbutton = function() {
	"use strict";
	
	return {
		/**
		 * list of button names and their associated bbcode tag
		 * @var	object<string>
		 */
		_bbcodes: { },
		
		/**
		 * Initializes the RedactorPlugins.wbutton plugin.
		 */
		init: function() {
			this._bbcodes = { };
			
			for (var $i = 0, $length = __REDACTOR_BUTTONS.length; $i < $length; $i++) {
				this.wbutton._addBBCodeButton(__REDACTOR_BUTTONS[$i]);
			}
			
			// this list contains overrides for built-in buttons, if a button is not present
			// Redactor's own icon will be used instead. This solves the problem of FontAwesome
			// not providing an icon for everything we need (especially the core stuff)
			var $faIcons = {
				'html': 'fa-square-o',
				'bold': 'fa-bold',
				'italic': 'fa-italic',
				'underline': 'fa-underline',
				'deleted': 'fa-strikethrough',
				'subscript': 'fa-subscript',
				'superscript': 'fa-superscript',
				'orderedlist': 'fa-list-ol',
				'unorderedlist': 'fa-list-ul',
				'outdent': 'fa-outdent',
				'indent': 'fa-indent',
				'link': 'fa-link',
				'alignment': 'fa-align-left',
				'table': 'fa-table'
			};
			
			var $buttonTitles = {
				'fontcolor': WCF.Language.get('wcf.bbcode.button.fontColor'),
				'fontfamily': WCF.Language.get('wcf.bbcode.button.fontFamily'),
				'fontsize': WCF.Language.get('wcf.bbcode.button.fontSize'),
				'image': WCF.Language.get('wcf.bbcode.button.image'),
				'subscript': WCF.Language.get('wcf.bbcode.button.subscript'),
				'superscript': WCF.Language.get('wcf.bbcode.button.superscript')
			};
			
			var $buttons = this.wutil.getOption('buttons');
			var $lastButton = '';
			for (var $i = 0, $length = $buttons.length; $i < $length; $i++) {
				var $button = $buttons[$i];
				if ($button == 'separator') {
					this.button.get($lastButton).parent().addClass('separator');
					
					continue;
				}
				
				// check if button exists
				var $buttonObj = this.button.get($button);
				if ($buttonObj.length) {
					if ($faIcons[$button]) {
						this.button.setAwesome($button, $faIcons[$button]);
					}
					
					// the 'table' button is added through the official plugin and is therefore misplaced
					if ($button === 'table' && $lastButton) {
						$buttonObj.parent().insertAfter(this.button.get($lastButton).parent());
					}
				}
				else {
					this.wbutton._addCoreButton($button, ($buttonTitles[$button] ? $buttonTitles[$button] : null), ($faIcons[$button] ? $faIcons[$button] : null), $lastButton);
				}
				
				$lastButton = $button;
			}
			
			// handle image insert
			this.button.addCallback(this.button.get('image'), $.proxy(this.wbutton.insertImage, this));
			
			// handle redo/undo buttons
			var $undoButton = this.button.addAfter('html', 'undo', WCF.Language.get('wcf.bbcode.button.undo'));
			var $redoButton = this.button.addAfter('undo', 'redo', WCF.Language.get('wcf.bbcode.button.redo'));
			this.button.addCallback($undoButton, this.buffer.undo);
			this.button.addCallback($redoButton, this.buffer.redo);
			
			$redoButton.parent().addClass('separator');
		},
		
		/**
		 * Modifies an existing button belonging to Redactor.
		 * 
		 * @param	string		buttonName
		 * @param	string		buttonTitle
		 * @param	string		faIcon
		 * @param	string		insertAfter
		 */
		_addCoreButton: function(buttonName, buttonTitle, faIcon, insertAfter) {
			var $buttonObj = { title: (buttonTitle === null ? buttonName : buttonTitle) };
			if (buttonName === 'subscript' || buttonName === 'superscript') {
				$buttonObj.command = buttonName;
			}
			
			var $button = this.button.build(buttonName, $buttonObj);
			$('<li />').append($button).insertAfter(this.button.get(insertAfter).parent());
			
			if (faIcon !== null) {
				this.button.setAwesome(buttonName, faIcon);
			}
		},
		
		/**
		 * Adds a custom button.
		 * 
		 * @param	object<string>		data
		 */
		_addBBCodeButton: function(data) {
			var $buttonName = '__wcf_' + data.name;
			var $button = this.button.add($buttonName, data.label);
			this.button.addCallback($button, this.wbutton._insertBBCode);
			
			this._bbcodes[$buttonName] = {
				name: data.name,
				voidElement: (data.voidElement === true)
			};
			
			// FontAwesome class name
			if (data.icon.match(/^fa\-[a-z\-]+$/)) {
				this.button.setAwesome($buttonName, data.icon);
			}
			else {
				// image reference
				$button.css('background-image', 'url(' + __REDACTOR_ICON_PATH + data.icon + ')');
			}
		},
		
		/**
		 * Inserts the specified BBCode.
		 * 
		 * @param	string		buttonName
		 */
		_insertBBCode: function(buttonName) {
			var $bbcode = this._bbcodes[buttonName].name;
			var $eventData = {
				buttonName: buttonName,
				cancel: false,
				redactor: this
			};
			
			WCF.System.Event.fireEvent('com.woltlab.wcf.redactor', 'insertBBCode_' + $bbcode + '_' + this.$textarea.wcfIdentify(), $eventData);
			
			if ($eventData.cancel === false) {
				var $selectedHtml = this.selection.getHtml();
				$selectedHtml = $selectedHtml.replace(/<p>@@@wcf_empty_line@@@<\/p>/g, '<p><br></p>');
				
				// TODO: this behaves pretty weird at this time, fix or remove
				if (false && $bbcode === 'tt') {
					var $parent = (this.selection.getParent()) ? $(this.selection.getParent()) : null;
					if ($parent && $parent.closest('inline.inlineCode', this.$editor.get()[0]).length) {
						this.inline.toggleClass('inlineCode');
					}
					else {
						this.inline.toggleClass('inlineCode');
					}
				}
				else {
					this.buffer.set();
					
					if (this.utils.browser('mozilla') && !$selectedHtml.length) {
						var $container = getSelection().getRangeAt(0).startContainer;
						if ($container.nodeType === Node.ELEMENT_NODE && $container.tagName === 'P' && $container.innerHTML === '<br>') {
							// <br> is not removed in Firefox, instead content gets inserted afterwards creating a leading empty line
							$container.removeChild($container.children[0]);
						}
					}
					
					if (this._bbcodes[buttonName].voidElement) {
						this.insert.html($selectedHtml + this.selection.getMarkerAsHtml() + '[' + $bbcode + ']', false);
					}
					else {
						this.insert.html('[' + $bbcode + ']' + $selectedHtml + this.selection.getMarkerAsHtml() + '[/' + $bbcode + ']', false);
					}
					
					this.selection.restore();
				}
			}
		},
		
		insertImage: function() {
			this.image.show();
		},
		
		_insertImage: function() {
			var $source = $('#redactor-image-link-source');
			var $url = $source.val().trim();
			if ($url.length) {
				this.buffer.set();
				
				var $align = $('#redactor-image-align').val();
				var $style = '';
				if ($align === 'left' || $align === 'right') {
					$style = ' style="float: ' + $align + '"';
				}
				
				this.insert.html('<img src="' + $url + '"' + $style + '>', false);
				
				this.modal.close();
				this.observe.images();
			}
			else if (!$source.next('small.innerError')) {
				$('<small class="innerError">' + WCF.Language.get('wcf.global.form.error.empty') + '</small>').insertAfter($source);
			}
		}
	};
};
