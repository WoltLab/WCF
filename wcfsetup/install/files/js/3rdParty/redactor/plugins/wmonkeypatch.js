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
			$mpIndentingStart.call(self, cmd);
			self.mpIndentingStart(cmd);
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
		
		this.setOption('modalOpenedCallback', $.proxy(this.modalOpenedCallback, this));
		
		this.modalTemplatesInit();
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
	 * Drops the indentation if not within a list.
	 * 
	 * @param	string		cmd
	 */
	mpIndentingStart: function(cmd) {
		if (cmd === 'indent') {
			var block = this.getBlock();
			if (block.tagName === 'DIV' && block.getAttribute('data-tagblock') !== null) {
				this.selectionSave();
				
				// drop the indention block again. bye bye block
				block = $(block);
				block.replaceWith(block.html());
				
				this.selectionRestore();
				this.sync();
			}
		}
	},
	
	/**
	 * Provides WCF-like overlays.
	 */
	modalTemplatesInit: function() {
		this.setOption('modal_image',
			'<fieldset>'
				+ '<dl>'
					+ '<dt><label for="redactor_file_link">' + this.opts.curLang.image_web_link + '</label></dt>'
					+ '<dd><input type="text" name="redactor_file_link" id="redactor_file_link" class="long"  /></dd>'
				+ '</dl>'
			+ '</fieldset>'
			+ '<div class="formSubmit">'
				+ '<button id="redactor_upload_btn">' + this.opts.curLang.insert + '</button>'
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

			modal_image_edit: String()
			+ '<section id="redactor-modal-image-edit">'
				+ '<label>' + this.opts.curLang.title + '</label>'
				+ '<input type="text" id="redactor_file_alt" class="redactor_input" />'
				+ '<label>' + this.opts.curLang.link + '</label>'
				+ '<input type="text" id="redactor_file_link" class="redactor_input" />'
				+ '<label><input type="checkbox" id="redactor_link_blank"> ' + this.opts.curLang.link_new_tab + '</label>'
				+ '<label>' + this.opts.curLang.image_position + '</label>'
				+ '<select id="redactor_form_image_align">'
					+ '<option value="none">' + this.opts.curLang.none + '</option>'
					+ '<option value="left">' + this.opts.curLang.left + '</option>'
					+ '<option value="center">' + this.opts.curLang.center + '</option>'
					+ '<option value="right">' + this.opts.curLang.right + '</option>'
				+ '</select>'
			+ '</section>'
			+ '<footer>'
				+ '<button id="redactor_image_delete_btn" class="redactor_modal_btn redactor_modal_delete_btn">' + this.opts.curLang._delete + '</button>'
				+ '<button class="redactor_modal_btn redactor_btn_modal_close">' + this.opts.curLang.cancel + '</button>'
				+ '<button id="redactorSaveBtn" class="redactor_modal_btn redactor_modal_action_btn">' + this.opts.curLang.save + '</button>'
			+ '</footer>',

			// img

			modal_link: String()
			+ '<section id="redactor-modal-link-insert">'
				+ '<label>URL</label>'
				+ '<input type="text" class="redactor_input" id="redactor_link_url" />'
				+ '<label>' + this.opts.curLang.text + '</label>'
				+ '<input type="text" class="redactor_input" id="redactor_link_url_text" />'
				+ '<label><input type="checkbox" id="redactor_link_blank"> ' + this.opts.curLang.link_new_tab + '</label>'
			+ '</section>'
			+ '<footer>'
				+ '<button class="redactor_modal_btn redactor_btn_modal_close">' + this.opts.curLang.cancel + '</button>'
				+ '<button id="redactor_insert_link_btn" class="redactor_modal_btn redactor_modal_action_btn">' + this.opts.curLang.insert + '</button>'
			+ '</footer>',
			
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
	}
}