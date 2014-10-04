if (!RedactorPlugins) var RedactorPlugins = {};

/**
 * This plugin makes liberally use of dumb monkey patching to adjust Redactor for our needs. In
 * general this is a collection of methods whose side-effects cannot be prevented in any other
 * way or a work-around would cause a giant pile of boilerplates.
 * 
 * ATTENTION!
 * This plugin partially contains code taken from Redactor, Copyright (c) 2009-2014 Imperavi LLC.
 * Under no circumstances you are allowed to use potions or entire code blocks for use anywhere
 * except when directly working with WoltLab Community Framework.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2014 WoltLab GmbH, 2009-2014 Imperavi LLC.
 * @license	http://imperavi.com/redactor/license/
 */
RedactorPlugins.wmonkeypatch = function() {
	"use strict";
	
	return {
		/**
		 * Initializes the RedactorPlugins.wmonkeypatch plugin.
		 */
		init: function() {
			// module overrides
			this.wmonkeypatch.button();
			this.wmonkeypatch.clean();
			this.wmonkeypatch.dropdown();
			this.wmonkeypatch.image();
			this.wmonkeypatch.insert();
			this.wmonkeypatch.keydown();
			this.wmonkeypatch.modal();
			this.wmonkeypatch.paste();
			this.wmonkeypatch.observe();
			this.wmonkeypatch.utils();
			
			// templates
			this.wmonkeypatch.rebuildTemplates();
			
			// events and callbacks
			this.wmonkeypatch.bindEvents();
			
		},
		
		/**
		 * Setups event listeners and callbacks.
		 */
		bindEvents: function() {
			var $identifier = this.$textarea.wcfIdentify();
			
			// keydown
			this.wutil.setOption('keydownCallback', function(event) {
				var $data = {
					cancel: false,
					event: event
				};
				
				WCF.System.Event.fireEvent('com.woltlab.wcf.redactor', 'keydown_' + $identifier, $data);
				
				return ($data.cancel ? false : true);
			});
			
			// keyup
			this.wutil.setOption('keyupCallback', function(event) {
				var $data = {
					cancel: false,
					event: event
				};
				
				WCF.System.Event.fireEvent('com.woltlab.wcf.redactor', 'keyup_' + $identifier, $data);
				
				return ($data.cancel ? false : true);
			});
			
			// buttons response
			if (this.opts.activeButtons) {
				this.$editor.off('mouseup.redactor keyup.redactor focus.redactor');
				this.$editor.on('mouseup.redactor keyup.redactor focus.redactor', $.proxy(this.observe.buttons, this));
			}
		},
		
		/**
		 * Partially overwrites the 'button' module.
		 * 
		 *  - consistent display of dropdowns
		 */
		button: function() {
			// button.addDropdown
			var $mpAddDropdown = this.button.addDropdown;
			this.button.addDropdown = (function($btn, dropdown) {
				var $dropdown = $mpAddDropdown.call(this, $btn, dropdown);
				
				if (!dropdown) {
					$dropdown.addClass('dropdownMenu');
				}
				
				return $dropdown;
			}).bind(this);
		},
		
		/**
		 * Partially overwrites the 'clean' module.
		 * 
		 *  - convert <div> to <p> during paste
		 */
		clean: function() {
			var $mpOnPaste = this.clean.onPaste;
			this.clean.onPaste = (function(html, setMode) {
				this.opts.replaceDivs = true;
				
				html = $mpOnPaste.call(this, html, setMode);
				
				this.opts.replaceDivs = false;
				
				return html;
			}).bind(this);
		},
		
		/**
		 * Partially overwrites the 'dropdown' module.
		 * 
		 *  - emulate WCF-like dropdowns.
		 */
		dropdown: function() {
			// dropdown.build
			this.dropdown.build = (function(name, $dropdown, dropdownObject) {
				$dropdown.addClass('dropdownMenu');
				
				$.each(dropdownObject, (function(btnName, btnObject) {
					if (btnName == 'dropdownDivider') {
						$('<li class="dropdownDivider" />').appendTo($dropdown);
					}
					else {
						var $listItem = $('<li />');
						var $item = $('<a href="#" class="redactor-dropdown-' + btnName + '">' + btnObject.title + '</a>');
						
						$item.on('click', $.proxy(function(e) {
							var type = 'func';
							var callback = btnObject.func;
							if (btnObject.command) {
								type = 'command';
								callback = btnObject.command;
							}
							else if (btnObject.dropdown) {
								type = 'dropdown';
								callback = btnObject.dropdown;
							}
							
							this.button.onClick(e, btnName, type, callback);
							
						}, this));
						
						$item.appendTo($listItem);
						$listItem.appendTo($dropdown);
					}
				}).bind(this));
				
				/*$dropdown.children('a').each(function() {
					$(this).wrap('li');
				});*/
			}).bind(this);
			
			// dropdown.show
			var $mpShow = this.dropdown.show;
			this.dropdown.show = $.proxy(function(e, key) {
				$mpShow.call(this, e, key);
				
				this.button.get(key).data('dropdown').off('mouseover mouseout');
			}, this);
		},
		
		/**
		 * Partially overwrites the 'image' module.
		 * 
		 *  - WCF-like dialog behavior
		 *  - resolves an existing issue in Redactor 10.0 related to 'imageEditable' and 'imageResize' = false
		 */
		image: function() {
			// image.setEditable
			this.image.setEditable = (function($image) {
				// TODO: remove this entire function once the issue with the option 'imageEditable' has been resolved by Imperavi
				if (!this.opts.imageEditable) return;
				
				$image.on('dragstart', $.proxy(this.image.onDrag, this));
				
				$image.on('mousedown', $.proxy(this.image.hideResize, this));
				$image.on('click touchstart', $.proxy(function(e) {
					this.observe.image = $image;
					
					if (this.$editor.find('#redactor-image-box').size() !== 0) return false;
					
					// resize
					if (!this.opts.imageEditable && !this.opts.imageResizable) return;
					
					this.image.resizer = this.image.loadEditableControls($image);
					if (this.image.resizer === false) {
						// work-around, this.image.hideResize() is not aware of this.image.resizer = false (but legally possible!)
						this.image.resizer = $();
					}
					else {
						this.image.resizer.on('mousedown.redactor touchstart.redactor', $.proxy(function(e) {
							e.preventDefault();
							
							this.image.resizeHandle = {
								x : e.pageX,
								y : e.pageY,
								el : $image,
								ratio: $image.width() / $image.height(),
								h: $image.height()
							};
							
							e = e.originalEvent || e;
							
							if (e.targetTouches) {
								this.image.resizeHandle.x = e.targetTouches[0].pageX;
								this.image.resizeHandle.y = e.targetTouches[0].pageY;
							}
							
							this.image.startResize();
						}, this));
					}
					
					$(document).on('click.redactor-image-resize-hide', $.proxy(this.image.hideResize, this));
					this.$editor.on('click.redactor-image-resize-hide', $.proxy(this.image.hideResize, this));
				}, this));
			}).bind(this);
			
			// image.show
			this.image.show = (function() {
				this.modal.load('image', this.lang.get('image'), 0);
				var $button = this.modal.createActionButton(this.lang.get('insert'));
				$button.click($.proxy(this.wbutton._insertImage, this));
				
				this.selection.save();
				this.modal.show();
			}).bind(this);
			
			// image.showEdit
			this.image.showEdit = (function(image) {
				this.modal.load('imageEdit', this.lang.get('edit'), 0);
				this.image.buttonSave = this.modal.createActionButton(this.lang.get('save'));
				
				this.image.buttonSave.click((function() {
					this.image.update(image);
				}).bind(this));
				
				// set overlay values
				$('#redactor-image-link-source').val(image.attr('src'));
				$('#redactor-image-align').val(image.css('float'));
				
				this.modal.show();
			}).bind(this);
			
			// image.update
			this.image.update = (function(image) {
				this.image.hideResize();
				this.buffer.set();
				
				image.attr('src', $('#redactor-image-link-source').val());
				this.image.setFloating(image);
				
				this.modal.close();
				this.observe.images();
			}).bind(this);
		},
		
		/**
		 * Partially overwrites the 'insert' module.
		 * 
		 *  - fixes insertion in an empty editor w/o prior focus until the issue has been resolved by Imperavi
		 */
		insert: function() {
			var $focusEditor = (function() {
				var $html = this.$editor.html();
				if (this.utils.isEmpty($html)) {
					this.$editor.focus();
					
					this.caret.setEnd(this.$editor.children('p:eq(0)'));
				}
			}).bind(this);
			
			// insert.html
			var $mpHtml = this.insert.html;
			this.insert.html = (function(html, clean) {
				$focusEditor();
				
				$mpHtml.call(this, html, clean);
			}).bind(this);
		},
		
		/**
		 * Partially overwrites the 'keydown' module.
		 * 
		 *  - improve behavior in quotes
		 *  - allow indentation for lists only
		 */
		keydown: function() {
			this.keydown.enterWithinBlockquote = false;
			
			// keydown.onTab
			this.keydown.onTab = (function(e, key) {
				e.preventDefault();
				
				if (e.metaKey && key === 219) this.indent.decrease();
				else if (e.metaKey && key === 221) this.indent.increase();
				else if (!e.shiftKey) this.indent.increase();
				else this.indent.decrease();
				
				return false;
			}).bind(this);
			
			// keydown.replaceDivToParagraph
			var $mpReplaceDivToParagraph = this.keydown.replaceDivToParagraph;
			this.keydown.replaceDivToParagraph = (function() {
				if (this.keydown.enterWithinBlockquote) {
					// do nothing and prevent replacement
					this.keydown.enterWithinBlockquote = false;
				}
				else {
					$mpReplaceDivToParagraph.call(this);
				}
			}).bind(this);
		},
		
		/**
		 * Partially overwrites the 'modal' module.
		 * 
		 *  - delegate modal creation and handling to $.ui.wcfDialog.
		 */
		modal: function() {
			// modal.dialog
			this.modal.dialog = null;
			
			// modal.addTemplate
			var $mpAddTemplate = this.modal.addTemplate;
			this.modal.addTemplate = (function(name, template) {
				// overrides the 'table' template
				if (name !== 'table') {
					$mpAddTemplate.call(this, name, template);
				}
			}).bind(this);
			
			// modal.build
			this.modal.build = function() { /* does nothing */ };
			
			// modal.load
			this.modal.load = (function(templateName, title, width) {
				this.modal.templateName = templateName;
				this.modal.title = title;
				
				this.modal.dialog = $('<div />').hide().appendTo(document.body);
				this.modal.dialog.html(this.modal.getTemplate(this.modal.templateName));
				
				this.$modalFooter = null;
			}).bind(this);
			
			// modal.show
			this.modal.show = (function() {
				this.modal.dialog.wcfDialog({
					onClose: $.proxy(this.modal.close, this),
					title: this.modal.title
				});
			}).bind(this);
			
			// modal.createButton
			var $mpCreateButton = this.modal.createButton;
			this.modal.createButton = (function(label, className) {
				if (this.$modalFooter === null) {
					this.$modalFooter = $('<div class="formSubmit" />').appendTo(this.modal.dialog);
					this.modal.dialog.addClass('dialogForm');
				}
				
				return $mpCreateButton.call(this, label, className);
			}).bind(this);
			
			// modal.close
			this.modal.close = (function() {
				this.modal.dialog.wcfDialog('close');
				this.modal.dialog.remove();
			}).bind(this);
			
			// modal.createCancelButton
			this.modal.createCancelButton = function() { return $(); };
			
			// modal.createDeleteButton
			this.modal.createDeleteButton = function() { return $(); };
		},
		
		/**
		 * Partially overwrites the 'paste' module.
		 * 
		 *  - prevent screwed up, pasted HTML from placing text nodes (and inline elements) in the editor's direct root 
		 */
		paste: function() {
			var $fixDOM = (function() {
				var $current = this.$editor[0].childNodes[0];
				var $nextSibling = $current;
				var $p = null;
				
				while ($nextSibling) {
					$current = $nextSibling;
					$nextSibling = $current.nextSibling;
					
					if ($current.nodeType === Element.ELEMENT_NODE) {
						if (this.reIsBlock.test($current.tagName)) {
							$p = null;
						}
						else {
							if ($p === null) {
								$p = $('<p />').insertBefore($current);
							}
							
							$p.append($current);
						}
					}
					else if ($current.nodeType === Element.TEXT_NODE) {
						if ($p === null) {
							$p = $('<p />').insertBefore($current);
						}
						
						$p.append($current);
					}
				}
			}).bind(this);
			
			// paste.insert
			var $mpInsert = this.paste.insert;
			this.paste.insert = (function(html) {
				$mpInsert.call(this, html);
				
				setTimeout($fixDOM, 20);
			}).bind(this);
		},
		
		/**
		 * Partially overwrites the 'observe' module.
		 * 
		 *  - handles custom button active states.
		 */
		observe: function() {
			var $toggleButtons = (function(parent, searchFor, buttonSelector, inverse, className, skipInSourceMode) {
				var $buttons = this.$toolbar.find(buttonSelector);
				if (parent && parent.closest(searchFor, this.$editor[0]).length != 0) {
					$buttons[(inverse ? 'removeClass' : 'addClass')](className);
				}
				else {
					if (skipInSourceMode && !this.opts.visual) {
						return;
					}
					
					$buttons[(inverse ? 'addClass' : 'removeClass')](className);
				}
			}).bind(this);
			
			var $mpButtons = this.observe.buttons;
			this.observe.buttons = (function(e, btnName) {
				$mpButtons.call(this, e, btnName);
				
				var parent = this.selection.getParent();
				parent = (parent === false) ? null : $(parent);
				
				$toggleButtons(parent, 'ul, ol', 'a.re-indent, a.re-outdent', true, 'redactor-button-disabled');
				//$toggleButtons(parent, 'inline.inlineCode', 'a.re-__wcf_tt', false, 'redactor-act');
				$toggleButtons(parent, 'blockquote.quoteBox', 'a.re-__wcf_quote', false, 'redactor-button-disabled', true);
				$toggleButtons(parent, 'sub', 'a.re-subscript', false, 'redactor-act');
				$toggleButtons(parent, 'sup', 'a.re-superscript', false, 'redactor-act');
			}).bind(this);
		},
		
		/**
		 * Partially overwrites the 'utils' module.
		 * 
		 *  - prevent removing of empty paragraphs/divs
		 */
		utils: function() {
			this.utils.removeEmpty = function(i, s) { /* does nothing */ };
		},
		
		/**
		 * Rebuilds certain templates provided by Redactor to better integrate into WCF.
		 */
		rebuildTemplates: function() {
			// template: image
			this.opts.modal.image =
				'<fieldset id="redactor-modal-image-edit">'
					+ '<dl>'
						+ '<dt><label for="redactor-image-link-source">' + this.lang.get('link') + '</label></dt>'
						+ '<dd><input type="text" id="redactor-image-link-source" class="long"  /></dd>'
					+ '</dl>'
					+ '<dl>'
						+ '<dt><label for="redactor-image-align">' + this.opts.curLang.image_position + '</label></dt>'
						+ '<dd>'
							+ '<select id="redactor-image-align">'
								+ '<option value="none">' + this.lang.get('none') + '</option>'
								+ '<option value="left">' + this.lang.get('left') + '</option>'
								+ '<option value="right">' + this.lang.get('right') + '</option>'
							+ '</select>'
						+ '</dd>'
					+ '</dl>'
				+ '</fieldset>';
			
			// template: imageEdit
			this.opts.modal.imageEdit = this.opts.modal.image;
			
			// template: quote
			this.opts.modal.quote =
				'<fieldset>'
					+ '<dl>'
						+ '<dt><label for="redactorQuoteAuthor">' + WCF.Language.get('wcf.bbcode.quote.edit.author') + '</label></dt>'
						+ '<dd><input type="text" id="redactorQuoteAuthor" class="long" /></dd>'
					+ '</dl>'
					+ '<dl>'
						+ '<dt><label for="redactorQuoteLink">' + WCF.Language.get('wcf.bbcode.quote.edit.link') + '</label></dt>'
						+ '<dd><input type="text" id="redactorQuoteLink" class="long" /></dd>'
					+ '</dl>'
				+ '</fieldset>';
			
			// template: table
			this.opts.modal.table =
				'<fieldset id="redactor-modal-table-insert">'
					+ '<dl>'
						+ '<dt><label for="redactor-table-rows">' + this.lang.get('rows') + '</label></dt>'
						+ '<dd><input type="number" size="5" value="2" min="1" id="redactor-table-rows" class="tiny" /></dd>'
					+ '</dl>'
					+ '<dl>'
						+ '<dt><label for="redactor-table-columns">' + this.lang.get('columns') + '</label></dt>'
						+ '<dd><input type="number" size="5" value="3" min="1" id="redactor-table-columns" class="tiny" /></dd>'
					+ '</dl>'
				+ '</fieldset>';
		}
	};
};
