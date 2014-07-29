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
RedactorPlugins.wmonkeypatch = {
	/**
	 * Initializes the RedactorPlugins.wmonkeypatch plugin.
	 */
	init: function() {
		var self = this;
		
		var $mpIndentingStart = this.indentingStart;
		this.indentingStart = function(cmd) {
			if (self.mpIndentingStart(cmd)) {
				$mpIndentingStart.call(self, cmd);
			}
		};
		
		var $mpBuildEventKeydown = this.buildEventKeydown;
		this.buildEventKeydown = function(e) {
			if (self.callback('wkeydown', e) !== false) {
				$mpBuildEventKeydown.call(self, e);
			}
		};
		
		var $mpToggleCode = this.toggleCode;
		this.toggleCode = function(direct) {
			var $height = self.normalize(self.$editor.css('height'));
			
			$mpToggleCode.call(self, direct);
			
			self.$source.height($height);
		};
		
		var $mpModalInit = this.modalInit;
		this.modalInit = function(title, content, width, callback) {
			self.mpModalInit();
			
			$mpModalInit.call(self, title, content, width, callback);
		};
		
		var $mpModalShowOnDesktop = this.modalShowOnDesktop;
		this.modalShowOnDesktop = function() {
			$mpModalShowOnDesktop.call(self);
			
			$(document.body).css('overflow', false);
		};
		
		var $mpDestroy = this.destroy;
		this.destroy = function() {
			self.callback('destroy', false, { });
			
			$mpDestroy.call(self);
		};
		
		var $mpSync = this.sync;
		this.sync = function(e, forceSync) {
			if (forceSync === true) {
				$mpSync.call(self, e);
			}
		};
		
		// handle indent/outdent
		var $mpButtonActiveObserver = this.buttonActiveObserver;
		this.buttonActiveObserver = function(e, btnName) {
			$mpButtonActiveObserver.call(self, e, btnName);
			
			self.mpButtonActiveObserver(e, btnName);
		};
		if (this.opts.activeButtons) {
			this.$editor.off('mouseup.redactor keyup.redactor').on('mouseup.redactor keyup.redactor', $.proxy(this.buttonActiveObserver, this));
		}
		this.$toolbar.find('a.re-indent, a.re-outdent').addClass('redactor_button_disabled');
		
		// image editing
		var $mpImageResizeControls = this.imageResizeControls;
		this.imageResizeControls = function($image) {
			if (!$image.data('attachmentID')) {
				$mpImageResizeControls.call(self, $image);
			}
			
			return false;
		};
		
		var $mpImageEdit = this.imageEdit;
		this.imageEdit = function(image) {
			$mpImageEdit.call(self, image);
			
			$('#redactor_image_source').val($(image).prop('src'));
		};
		
		var $mpImageSave = this.imageSave;
		this.imageSave = function(el) {
			$(el).prop('src', $('#redactor_image_source').val());
			
			$mpImageSave.call(self, el);
		};
		
		this.setOption('modalOpenedCallback', $.proxy(this.modalOpenedCallback, this));
		this.setOption('dropdownShowCallback', $.proxy(this.dropdownShowCallback, this));
		
		this.modalTemplatesInit();
	},
	
	cleanRemoveSpaces: function(html, buffer) {
		return html;
	},
	
	/**
	 * Enable/Disable the 'Indent'/'Outdent' for lists/outside lists.
	 * 
	 * @param	object		e
	 * @param	string		btnName
	 */
	mpButtonActiveObserver: function(e, btnName) {
		var parent = this.getParent();
		parent = (parent === false) ? null : $(parent);
		
		if (parent && parent.closest('ul', this.$editor.get()[0]).length != 0) {
			this.$toolbar.find('a.re-indent, a.re-outdent').removeClass('redactor_button_disabled');
		}
		else {
			this.$toolbar.find('a.re-indent, a.re-outdent').addClass('redactor_button_disabled');
		}
		
		if (parent && parent.closest('inline.inlineCode', this.$editor.get()[0]).length != 0) {
			this.$toolbar.find('a.re-__wcf_tt').addClass('redactor_act');
		}
		else {
			this.$toolbar.find('a.re-__wcf_tt').removeClass('redactor_act');
		}
	},
	
	/**
	 * Overwrites $.Redactor.inlineRemoveStyle() to drop empty <inline> elements.
	 * 
	 * @see		$.Redactor.inlineRemoveStyle()
	 * @param	string		rule
	 */
	inlineRemoveStyle: function(rule) {
		this.selectionSave();
		
		this.inlineEachNodes(function(node) {
			$(node).css(rule, '');
			this.removeEmptyAttr(node, 'style');
		});
		
		// WoltLab modifications START
		// drop all <inline> elements without an actual attribute
		this.$editor.find('inline').each(function(index, inlineElement) {
			if (!inlineElement.attributes.length) {
				var $inlineElement = $(inlineElement);
				$inlineElement.replaceWith($inlineElement.html());
			}
		});
		// WoltLab modifications END
		
		this.selectionRestore();
		this.sync();
	},
	
	/**
	 * Overwrites $.Redactor.inlineMethods() to fix calls to inlineSetClass().
	 * 
	 * @see		$.Redactor.inlineMethods()
	 * @param	string		type
	 * @param	string		attr
	 * @param	string		value
	 */
	inlineMethods: function(type, attr, value) {
		this.bufferSet();
		this.selectionSave();

		var range = this.getRange();
		var el = this.getElement();

		if ((range.collapsed || range.startContainer === range.endContainer) && el && !this.nodeTestBlocks(el))
		{
			$(el)[type](attr, value);
		}
		else
		{
			var cmd, arg = value;
			switch (attr)
			{
				case 'font-size':
					cmd = 'fontSize';
					arg = 4;
				break;
				case 'font-family':
					cmd = 'fontName';
				break;
				case 'color':
					cmd = 'foreColor';
				break;
				case 'background-color':
					cmd = 'backColor';
				break;
			}
			
			// WoltLab modifications START
			if (type === 'addClass') {
				cmd = 'fontSize';
				arg = 4;
			}
			// WoltLab modifications END

			this.document.execCommand(cmd, false, arg);

			var fonts = this.$editor.find('font');
			$.each(fonts, $.proxy(function(i, s)
			{
				this.inlineSetMethods(type, s, attr, value);

			}, this));

		}

		this.selectionRestore();
		this.sync();
	},
	
	/**
	 * Drops the indentation if not within a list.
	 * 
	 * @param	string		cmd
	 */
	mpIndentingStart: function(cmd) {
		if (this.getBlock().tagName == 'LI') {
			return true;
		}
		
		return false;
	},
	
	/**
	 * Provides WCF-like overlays.
	 */
	modalTemplatesInit: function() {
		this.setOption('modal_image',
			'<fieldset>'
				+ '<dl>'
					+ '<dt><label for="redactor_file_link">' + this.opts.curLang.image_web_link + '</label></dt>'
					+ '<dd><input type="text" name="redactor_image_source" id="redactor_image_source" class="long"  /></dd>'
				+ '</dl>'
				+ '<dl>'
					+ '<dt><label for="redactor_form_image_align">' + this.opts.curLang.image_position + '</label></dt>'
					+ '<dd>'
						+ '<select id="redactor_form_image_align">'
							+ '<option value="none">' + this.opts.curLang.none + '</option>'
							+ '<option value="left">' + this.opts.curLang.left + '</option>'
							+ '<option value="right">' + this.opts.curLang.right + '</option>'
						+ '</select>'
					+ '</dd>'
				+ '</dl>'
			+ '</fieldset>'
			+ '<div class="formSubmit">'
				+ '<button id="redactor_upload_btn">' + this.opts.curLang.insert + '</button>'
			+ '</div>'
		);
		
		this.setOption('modal_image_edit', this.getOption('modal_image').replace(
			'<button id="redactor_upload_btn">' + this.opts.curLang.insert + '</button>',
			'<button id="redactorSaveBtn">' + this.opts.curLang.save + '</button>'
		));
		
		this.setOption('modal_link',
			'<fieldset>'
				+ '<dl>'
					+ '<dt><label for="redactor_link_url">URL</label></dt>'
					+ '<dd><input type="text" id="redactor_link_url" class="long" /></dd>'
				+ '</dl>'
				+ '<dl>'
					+ '<dt><label for="redactor_link_url_text">' + this.opts.curLang.text + '</label></dt>'
					+ '<dd><input type="text" id="redactor_link_url_text" class="long" /></dd>'
				+ '</dl>'
			+ '</fieldset>'
			+ '<div class="formSubmit">'
				+ '<button id="redactor_insert_link_btn">' + this.opts.curLang.insert + '</button>'
			+ '</div>'
		);
		
		this.setOption('modal_table',
			'<fieldset>'
				+ '<dl>'
					+ '<dt><label for="redactor_table_rows">' + this.opts.curLang.rows + '</label></dt>'
					+ '<dd><input type="number" size="5" value="2" min="0" id="redactor_table_rows" class="tiny" /></dd>'
				+ '</dl>'
				+ '<dl>'
					+ '<dt><label for="redactor_table_columns">' + this.opts.curLang.columns + '</label></dt>'
					+ '<dd><input type="number" size="5" value="3" min="0" id="redactor_table_columns" class="tiny" /></dd>'
				+ '</dl>'
			+ '</fieldset>'
			+ '<div class="formSubmit">'
				+ '<button id="redactor_insert_table_btn">' + this.opts.curLang.insert + '</button>'
			+ '</div>'
		);
		
		$.extend( this.opts, {
			modal_file: String()
			+ '<section id="redactor-modal-file-insert">'
				+ '<div id="redactor-progress" class="redactor-progress-inline" style="display: none;"><span></span></div>'
				+ '<form id="redactorUploadFileForm" method="post" action="" enctype="multipart/form-data">'
					+ '<label>' + this.opts.curLang.filename + '</label>'
					+ '<input type="text" id="redactor_filename" class="redactor_input" />'
					+ '<div style="margin-top: 7px;">'
						+ '<input type="file" id="redactor_file" name="' + this.opts.fileUploadParam + '" />'
					+ '</div>'
				+ '</form>'
			+ '</section>',
			// img edit
			
			// img

			// link
			
			// table
			
			modal_video: String()
			+ '<section id="redactor-modal-video-insert">'
				+ '<form id="redactorInsertVideoForm">'
					+ '<label>' + this.opts.curLang.video_html_code + '</label>'
					+ '<textarea id="redactor_insert_video_area" style="width: 99%; height: 160px;"></textarea>'
				+ '</form>'
			+ '</section>'
			+ '<footer>'
				+ '<button class="redactor_modal_btn redactor_btn_modal_close">' + this.opts.curLang.cancel + '</button>'
				+ '<button id="redactor_insert_video_btn" class="redactor_modal_btn redactor_modal_action_btn">' + this.opts.curLang.insert + '</button>'
			+ '</footer>'

		});
	},
	
	mpModalInit: function() {
		// modal overlay
		if (!$('#redactor_modal_overlay').length) {
			this.$overlay = $('<div id="redactor_modal_overlay" class="dialogOverlay" />').css({ height: '100%', zIndex: 50000 }).hide().appendTo(document.body);
		}
		
		if (!$('#redactor_modal').length) {
			this.$modal = $('<div id="redactor_modal" class="dialogContainer" />').css({ display: 'none', zIndex: 50001 }).appendTo(document.body);
			$('<header class="dialogTitlebar"><span id="redactor_modal_header" class="dialogTitle" /><a id="redactor_modal_close" class="dialogCloseButton" /></header>').appendTo(this.$modal);
			$('<div class="dialogContent"><div id="redactor_modal_inner" /></div>').appendTo(this.$modal);
		}
		
		this.$modal.children('.dialogContent').removeClass('dialogForm');
	},
	
	modalOpenedCallback: function() {
		// handle positioning of form submit controls
		var $heightDifference = 0;
		if (this.$modal.find('.formSubmit').length) {
			$heightDifference = this.$modal.find('.formSubmit').outerHeight();
			
			this.$modal.children('.dialogContent').addClass('dialogForm').css({ marginBottom: $heightDifference + 'px' });
		}
		else {
			this.$modal.children('.dialogContent').removeClass('dialogForm').css({ marginBottom: '0px' });
		}
		
		// fix position
		var $dimensions = this.$modal.getDimensions('outer');
		this.$modal.css({
			marginLeft: -1 * Math.round($dimensions.width / 2) + 'px',
			marginTop: -1 * Math.round($dimensions.height / 2) + 'px'
		});
	},
	
	dropdownShowCallback: function(data) {
		if (!data.dropdown.hasClass('dropdownMenu')) {
			data.dropdown.addClass('dropdownMenu');
			data.dropdown.children('.redactor_separator_drop').replaceWith('<li class="dropdownDivider" />');
			data.dropdown.children('a').wrap('<li />');
		}
	},
	
	/**
	 * Overwrites $.Redactor.inlineEachNodes(), the original method compares "selectionHtml"
	 * and "parentHtml" to check if the callback should be invoked. In some cases the "parentHtml"
	 * may contain a trailing unicode zero-width space and the comparision will fail, even though
	 * the "entire" node is selected.
	 * 
	 * @see	$.Redactor.inlineEachNodes()
	 */
	inlineEachNodes: function(callback) {
		var range = this.getRange(),
			node = this.getElement(),
			nodes = this.getNodes(),
			collapsed;

		if (range.collapsed || range.startContainer === range.endContainer && node)
		{
			nodes = $(node);
			collapsed = true;
		}

		$.each(nodes, $.proxy(function(i, node)
		{
			if (!collapsed && node.tagName !== 'INLINE')
			{
				var selectionHtml = this.getSelectionText();
				var parentHtml = $(node).parent().text();
				// if parentHtml contains a trailing 0x200B, the comparison will most likely fail
				var selected = this.removeZeroWidthSpace(selectionHtml) == this.removeZeroWidthSpace(parentHtml);

				if (selected && node.parentNode.tagName === 'INLINE' && !$(node.parentNode).hasClass('redactor_editor'))
				{
					node = node.parentNode;
				}
				else return;
			}
			callback.call(this, node);

		}, this ) );
	},
	
	/**
	 * Overwrites $.Redactor.imageCallbackLink() to provide proper image insert behavior.
	 * 
	 * @see	$.Redactor.imageCallbackLink()
	 */
	imageCallbackLink: function() {
		var $src = $.trim($('#redactor_image_source').val());
		if ($src.length) {
			var $float = '';
			var $alignment = $('#redactor_form_image_align').val();
			switch ($alignment) {
				case 'left':
					$float = ' style="float: left;"';
				break;
				
				case 'right':
					$float = ' style="float: right;"';
				break;
			}
			
			var $data = '<img id="image-marker" src="' + $src + '"' + $float + ' />';
			
			this.imageInsert($data, true);
		}
		else {
			this.modalClose();
		}
	}
};
