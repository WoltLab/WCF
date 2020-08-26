{event name='beforeEditorJavaScript'}

<script data-relocate="true">
	head.load([
		{if ENABLE_DEBUG_MODE}
			{* Imperavi *}
			'{@$__wcf->getPath()}js/3rdParty/redactor2/redactor.js?v={@LAST_UPDATE_TIME}',
			'{@$__wcf->getPath()}js/3rdParty/redactor2/plugins/alignment.js?v={@LAST_UPDATE_TIME}',
			'{@$__wcf->getPath()}js/3rdParty/redactor2/plugins/source.js?v={@LAST_UPDATE_TIME}',
			'{@$__wcf->getPath()}js/3rdParty/redactor2/plugins/table.js?v={@LAST_UPDATE_TIME}',
			
			{* WoltLab *}
			'{@$__wcf->getPath()}js/3rdParty/redactor2/plugins/WoltLabAttachment.js?v={@LAST_UPDATE_TIME}',
			'{@$__wcf->getPath()}js/3rdParty/redactor2/plugins/WoltLabAutosave.js?v={@LAST_UPDATE_TIME}',
			'{@$__wcf->getPath()}js/3rdParty/redactor2/plugins/WoltLabBlock.js?v={@LAST_UPDATE_TIME}',
			'{@$__wcf->getPath()}js/3rdParty/redactor2/plugins/WoltLabButton.js?v={@LAST_UPDATE_TIME}',
			'{@$__wcf->getPath()}js/3rdParty/redactor2/plugins/WoltLabCaret.js?v={@LAST_UPDATE_TIME}',
			'{@$__wcf->getPath()}js/3rdParty/redactor2/plugins/WoltLabClean.js?v={@LAST_UPDATE_TIME}',
			'{@$__wcf->getPath()}js/3rdParty/redactor2/plugins/WoltLabCode.js?v={@LAST_UPDATE_TIME}',
			'{@$__wcf->getPath()}js/3rdParty/redactor2/plugins/WoltLabColor.js?v={@LAST_UPDATE_TIME}',
			'{@$__wcf->getPath()}js/3rdParty/redactor2/plugins/WoltLabDragAndDrop.js?v={@LAST_UPDATE_TIME}',
			'{@$__wcf->getPath()}js/3rdParty/redactor2/plugins/WoltLabEvent.js?v={@LAST_UPDATE_TIME}',
			'{@$__wcf->getPath()}js/3rdParty/redactor2/plugins/WoltLabFont.js?v={@LAST_UPDATE_TIME}',
			'{@$__wcf->getPath()}js/3rdParty/redactor2/plugins/WoltLabFullscreen.js?v={@LAST_UPDATE_TIME}',
			'{@$__wcf->getPath()}js/3rdParty/redactor2/plugins/WoltLabHtml.js?v={@LAST_UPDATE_TIME}',
			'{@$__wcf->getPath()}js/3rdParty/redactor2/plugins/WoltLabImage.js?v={@LAST_UPDATE_TIME}',
			'{@$__wcf->getPath()}js/3rdParty/redactor2/plugins/WoltLabIndent.js?v={@LAST_UPDATE_TIME}',
			//'{@$__wcf->getPath()}js/3rdParty/redactor2/plugins/WoltLabInlineCode.js?v={@LAST_UPDATE_TIME}',
			'{@$__wcf->getPath()}js/3rdParty/redactor2/plugins/WoltLabInsert.js?v={@LAST_UPDATE_TIME}',
			'{@$__wcf->getPath()}js/3rdParty/redactor2/plugins/WoltLabKeydown.js?v={@LAST_UPDATE_TIME}',
			'{@$__wcf->getPath()}js/3rdParty/redactor2/plugins/WoltLabKeyup.js?v={@LAST_UPDATE_TIME}',
			'{@$__wcf->getPath()}js/3rdParty/redactor2/plugins/WoltLabLine.js?v={@LAST_UPDATE_TIME}',
			'{@$__wcf->getPath()}js/3rdParty/redactor2/plugins/WoltLabLink.js?v={@LAST_UPDATE_TIME}',
			'{@$__wcf->getPath()}js/3rdParty/redactor2/plugins/WoltLabList.js?v={@LAST_UPDATE_TIME}',
			'{@$__wcf->getPath()}js/3rdParty/redactor2/plugins/WoltLabMedia.js?v={@LAST_UPDATE_TIME}',
			'{@$__wcf->getPath()}js/3rdParty/redactor2/plugins/WoltLabMention.js?v={@LAST_UPDATE_TIME}',
			'{@$__wcf->getPath()}js/3rdParty/redactor2/plugins/WoltLabModal.js?v={@LAST_UPDATE_TIME}',
			'{@$__wcf->getPath()}js/3rdParty/redactor2/plugins/WoltLabObserve.js?v={@LAST_UPDATE_TIME}',
			'{@$__wcf->getPath()}js/3rdParty/redactor2/plugins/WoltLabPaste.js?v={@LAST_UPDATE_TIME}',
			'{@$__wcf->getPath()}js/3rdParty/redactor2/plugins/WoltLabQuote.js?v={@LAST_UPDATE_TIME}',
			'{@$__wcf->getPath()}js/3rdParty/redactor2/plugins/WoltLabReply.js?v={@LAST_UPDATE_TIME}',
			'{@$__wcf->getPath()}js/3rdParty/redactor2/plugins/WoltLabSize.js?v={@LAST_UPDATE_TIME}',
			'{@$__wcf->getPath()}js/3rdParty/redactor2/plugins/WoltLabSmiley.js?v={@LAST_UPDATE_TIME}',
			'{@$__wcf->getPath()}js/3rdParty/redactor2/plugins/WoltLabSource.js?v={@LAST_UPDATE_TIME}',
			'{@$__wcf->getPath()}js/3rdParty/redactor2/plugins/WoltLabSpoiler.js?v={@LAST_UPDATE_TIME}',
			'{@$__wcf->getPath()}js/3rdParty/redactor2/plugins/WoltLabTable.js?v={@LAST_UPDATE_TIME}',
			'{@$__wcf->getPath()}js/3rdParty/redactor2/plugins/WoltLabUtils.js?v={@LAST_UPDATE_TIME}'
		{else}
			'{@$__wcf->getPath()}js/3rdParty/redactor2/redactor.combined.min.js?v={@LAST_UPDATE_TIME}'
		{/if}
		
		{if $__redactorJavaScript|isset}{@$__redactorJavaScript}{/if}
		{assign var=__redactorJavaScript value=''}
		
		{event name='redactorJavaScript'}
	], function () {
		require(['Environment', 'Language', 'WoltLabSuite/Core/Ui/Redactor/Autosave', 'WoltLabSuite/Core/Ui/Redactor/Metacode'], function(Environment, Language, UiRedactorAutosave, UiRedactorMetacode) {
			Language.addObject({
				'wcf.attachment.dragAndDrop.dropHere': '{jslang}wcf.attachment.dragAndDrop.dropHere{/jslang}',
				'wcf.attachment.dragAndDrop.dropNow': '{jslang}wcf.attachment.dragAndDrop.dropNow{/jslang}',
				
				'wcf.editor.autosave.discard': '{jslang}wcf.editor.autosave.discard{/jslang}',
				'wcf.editor.autosave.keep': '{jslang}wcf.editor.autosave.keep{/jslang}',
				'wcf.editor.autosave.restored': '{jslang}wcf.editor.autosave.restored{/jslang}',
				
				'wcf.editor.code.edit': '{jslang}wcf.editor.code.edit{/jslang}',
				'wcf.editor.code.file': '{jslang}wcf.editor.code.file{/jslang}',
				'wcf.editor.code.file.description': '{jslang}wcf.editor.code.file.description{/jslang}',
				'wcf.editor.code.highlighter': '{jslang}wcf.editor.code.highlighter{/jslang}',
				'wcf.editor.code.highlighter.description': '{jslang}wcf.editor.code.highlighter.description{/jslang}',
				'wcf.editor.code.highlighter.detect': '{jslang}wcf.editor.code.highlighter.detect{/jslang}',
				'wcf.editor.code.highlighter.plain': '{jslang}wcf.editor.code.highlighter.plain{/jslang}',
				'wcf.editor.code.line': '{jslang}wcf.editor.code.line{/jslang}',
				'wcf.editor.code.line.description': '{jslang}wcf.editor.code.line.description{/jslang}',
				'wcf.editor.code.title': '{jslang __literal=true}wcf.editor.code.title{/jslang}',
				
				'wcf.editor.html.description': '{jslang}wcf.editor.html.description{/jslang}',
				'wcf.editor.html.title': '{jslang}wcf.editor.html.title{/jslang}',
				
				'wcf.editor.image.edit': '{jslang}wcf.editor.image.edit{/jslang}',
				'wcf.editor.image.insert': '{jslang}wcf.editor.image.insert{/jslang}',
				'wcf.editor.image.link': '{jslang}wcf.editor.image.link{/jslang}',
				'wcf.editor.image.link.error.invalid': '{jslang}wcf.editor.image.link.error.invalid{/jslang}',
				'wcf.editor.image.float': '{jslang}wcf.editor.image.float{/jslang}',
				'wcf.editor.image.float.left': '{jslang}wcf.editor.image.float.left{/jslang}',
				'wcf.editor.image.float.right': '{jslang}wcf.editor.image.float.right{/jslang}',
				'wcf.editor.image.source': '{jslang}wcf.editor.image.source{/jslang}',
				'wcf.editor.image.source.error.blocked': '{jslang}wcf.editor.image.source.error.blocked{/jslang}',
				'wcf.editor.image.source.error.insecure': '{jslang}wcf.editor.image.source.error.insecure{/jslang}',
				'wcf.editor.image.source.error.invalid': '{jslang}wcf.editor.image.source.error.invalid{/jslang}',
				
				'wcf.editor.link.add': '{jslang}wcf.editor.link.add{/jslang}',
				'wcf.editor.link.edit': '{jslang}wcf.editor.link.edit{/jslang}',
				'wcf.editor.link.error.invalid': '{jslang}wcf.editor.link.error.invalid{/jslang}',
				'wcf.editor.link.url': '{jslang}wcf.editor.link.url{/jslang}',
				'wcf.editor.link.text': '{jslang}wcf.editor.link.text{/jslang}',
				
				'wcf.editor.list.indent': '{jslang}wcf.editor.list.indent{/jslang}',
				'wcf.editor.list.outdent': '{jslang}wcf.editor.list.outdent{/jslang}',
				
				'wcf.editor.quote.author': '{jslang}wcf.editor.quote.author{/jslang}',
				'wcf.editor.quote.edit': '{jslang}wcf.editor.quote.edit{/jslang}',
				'wcf.editor.quote.title': '{jslang __literal=true}wcf.editor.quote.title{/jslang}',
				'wcf.editor.quote.url': '{jslang}wcf.editor.quote.url{/jslang}',
				'wcf.editor.quote.url.description': '{jslang}wcf.editor.quote.url.description{/jslang}',
				'wcf.editor.quote.url.error.invalid': '{jslang}wcf.editor.quote.url.error.invalid{/jslang}',
				
				'wcf.editor.table.cols': '{jslang}wcf.editor.table.cols{/jslang}',
				'wcf.editor.table.insertTable': '{jslang}wcf.editor.table.insertTable{/jslang}',
				'wcf.editor.table.rows': '{jslang}wcf.editor.table.rows{/jslang}',
				
				'wcf.editor.source.error.active': '{jslang}wcf.editor.source.error.active{/jslang}',
				
				'wcf.editor.spoiler.label': '{jslang}wcf.editor.spoiler.label{/jslang}',
				'wcf.editor.spoiler.label.description': '{jslang}wcf.editor.spoiler.label.description{/jslang}',
				'wcf.editor.spoiler.edit': '{jslang}wcf.editor.spoiler.edit{/jslang}',
				'wcf.editor.spoiler.title': '{jslang __literal=true}wcf.editor.spoiler.title{/jslang}'
			});
			
			var allowedInlineStyles = [], buttons = [], buttonMobile = [], buttonOptions = [], customButtons = [];
			{include file='wysiwygToolbar'}
			
			var highlighters = '{@MESSAGE_PUBLIC_HIGHLIGHTERS|encodeJS}'.split(/\n/).filter(function (item) { return item != ''; });
			
			{include file='mediaJavaScript'}
			
			var element = elById('{if $wysiwygSelector|isset}{$wysiwygSelector|encodeJS}{else}text{/if}');
			
			var autosave = elData(element, 'autosave') || null;
			if (autosave) {
				autosave = new UiRedactorAutosave(element);
				element.value = autosave.getInitialValue();
			}
			
			var disableMedia = elDataBool(element, 'disable-media');
			
			var config = {
				buttons: buttons,
				clipboardImageUpload: {if $__wcf->getBBCodeHandler()->isAvailableBBCode('attach')}true{else}false{/if},
				direction: '{jslang}wcf.global.pageDirection{/jslang}',
				formatting: ['p', 'h2', 'h3', 'h4'],
				imageCaption: false,
				imageUpload: {if $__wcf->getBBCodeHandler()->isAvailableBBCode('attach')}true{else}false{/if},
				lang: 'wsc', // fake language to offload phrases
				langs: {
					wsc: {
						// general
						edit: '{jslang}wcf.global.button.edit{/jslang}',
						
						// formatting dropdown
						heading2: '{jslang}wcf.editor.format.heading2{/jslang}',
						heading3: '{jslang}wcf.editor.format.heading3{/jslang}',
						heading4: '{jslang}wcf.editor.format.heading4{/jslang}',
						paragraph: '{jslang}wcf.editor.format.paragraph{/jslang}',
						
						// links
						'link-edit': '{jslang}wcf.editor.link.edit{/jslang}',
						'link-insert': '{jslang}wcf.editor.link.add{/jslang}',
						unlink: '{jslang}wcf.editor.link.unlink{/jslang}',
						
						// text alignment
						'align-center': '{jslang}wcf.editor.alignment.center{/jslang}',
						'align-justify': '{jslang}wcf.editor.alignment.justify{/jslang}',
						'align-left': '{jslang}wcf.editor.alignment.left{/jslang}',
						'align-right': '{jslang}wcf.editor.alignment.right{/jslang}',
						
						// table plugin
						'add-head': '{jslang}wcf.editor.table.addHead{/jslang}',
						'delete-column': '{jslang}wcf.editor.table.deleteColumn{/jslang}',
						'delete-head': '{jslang}wcf.editor.table.deleteHead{/jslang}',
						'delete-row': '{jslang}wcf.editor.table.deleteRow{/jslang}',
						'delete-table': '{jslang}wcf.editor.table.deleteTable{/jslang}',
						'insert-table': '{jslang}wcf.editor.table.insertTable{/jslang}',
						'insert-column-left': '{jslang}wcf.editor.table.insertColumnLeft{/jslang}',
						'insert-column-right': '{jslang}wcf.editor.table.insertColumnRight{/jslang}',
						'insert-row-above': '{jslang}wcf.editor.table.insertRowAbove{/jslang}',
						'insert-row-below': '{jslang}wcf.editor.table.insertRowBelow{/jslang}',
						
						// size
						'remove-size': '{jslang}wcf.editor.button.size.removeSize{/jslang}',
						
						// color
						'remove-color': '{jslang}wcf.editor.button.color.removeColor{/jslang}',
						
						// font
						'remove-font': '{jslang}wcf.editor.button.font.removeFont{/jslang}'
					}
				},
				linkify: false,
				linkSize: 0xBADC0DED, // some random value to disable truncating
				minHeight: 200,
				pasteImages: {if $__wcf->getBBCodeHandler()->isAvailableBBCode('attach')}true{else}false{/if},
				pastePlainText: {if !$__wcf->user->userID || $__wcf->user->editorPastePreserveFormatting}false{else}true{/if},
				plugins: [
					// Imperavi
					'alignment',
					'source',
					'table',
					
					// WoltLab specials
					'WoltLabBlock',
					'WoltLabEvent',
					'WoltLabKeydown',
					
					// WoltLab core
					'WoltLabAttachment',
					'WoltLabAutosave',
					'WoltLabCaret',
					'WoltLabClean',
					'WoltLabCode',
					{if $__wcf->getBBCodeHandler()->isAvailableBBCode('color')}'WoltLabColor',{/if}
					'WoltLabDragAndDrop',
					{if $__wcf->getBBCodeHandler()->isAvailableBBCode('font')}'WoltLabFont',{/if}
					'WoltLabFullscreen',
					{if $__wcf->getBBCodeHandler()->isAvailableBBCode('html')}'WoltLabHtml',{/if}
					'WoltLabImage',
					'WoltLabIndent',
					//'WoltLabInlineCode',
					'WoltLabInsert',
					'WoltLabKeyup',
					'WoltLabLine',
					{if $__wcf->getBBCodeHandler()->isAvailableBBCode('url')}'WoltLabLink',{/if}
					'WoltLabList',
					'WoltLabModal',
					'WoltLabObserve',
					'WoltLabPaste',
					'WoltLabQuote',
					'WoltLabReply',
					{if $__wcf->getBBCodeHandler()->isAvailableBBCode('size')}'WoltLabSize',{/if}
					'WoltLabSmiley',
					'WoltLabSource',
					'WoltLabSpoiler',
					'WoltLabTable',
					'WoltLabUtils'
				],
				toolbarFixed: false,
				woltlab: {
					allowImages: {if $__wcf->getBBCodeHandler()->isAvailableBBCode('img')}true{else}false{/if},
					attachments: (elDataBool(element, 'disable-attachments') === false),
					attachmentThumbnailWidth: {@ATTACHMENT_THUMBNAIL_WIDTH},
					autosave: autosave,
					allowedInlineStyles: allowedInlineStyles,
					buttons: buttonOptions,
					buttonMobile: buttonMobile,
					customButtons: customButtons,
					forceSecureImages: {if MESSAGE_FORCE_SECURE_IMAGES}true{else}false{/if},
					highlighters: highlighters,
					images: {
						external: {if IMAGE_ALLOW_EXTERNAL_SOURCE}true{else}false{/if},
						secureOnly: {if MESSAGE_FORCE_SECURE_IMAGES}true{else}false{/if},
						whitelist: [
							{implode from=$__wcf->getBBCodeHandler()->getImageExternalSourceWhitelist() item=$hostname}'{$hostname|encodeJS}'{/implode}
						]
					},
					media: {if $__wcf->session->getPermission('admin.content.cms.canUseMedia')}true{else}false{/if},
					mediaUrl: '{link controller='Media' id=-123456789 thumbnail='void' forceFrontend=true}{/link}'
				}
			};
			
			// The caret is misaligned in Safari 13+ when using \u200b. 
			if (Environment.browser() === 'safari') {
				config.emptyHtml = '<p><br></p>';
			}
			
			// user mentions
			if (elDataBool(element, 'support-mention')) {
				config.plugins.push('WoltLabMention');
			}
			
			// media
			{if $__wcf->session->getPermission('admin.content.cms.canUseMedia')}
				if (disableMedia) {
					var index = config.buttons.indexOf('woltlabMedia');
					if (index !== -1) {
						config.buttons.splice(index, 1);
					}
				}
				else {
					config.plugins.push('WoltLabMedia');
				}
			{/if}
			
			{if $__redactorConfig|isset}{@$__redactorConfig}{/if}
			{assign var=__redactorConfig value=''}
			
			{event name='redactorConfig'}
			
			// load the button plugin last to ensure all buttons have been initialized
			// already and we can safely add all icons
			config.plugins.push('WoltLabButton');
			
			var content = element.value;
			element.value = '';
			
			config.callbacks = config.callbacks || { };
			config.callbacks.init = function() {
				// slight delay to allow Redactor to initialize itself
				window.setTimeout(function() {
					if (content === '' && (Environment.platform() === 'ios' || Environment.browser() === 'safari')) {
						content = '<p><br></p>';
					}
					
					content = UiRedactorMetacode.convertFromHtml(element.id, content);
					
					var redactor = $(element).data('redactor');
					
					// set code
					redactor.code.start(content);
					redactor.WoltLabImage.validateImages();
					
					// set value
					redactor.core.textarea().val(redactor.clean.onSync(redactor.$editor.html()));
					redactor.code.html = false;
					
					// work-around for autosave notice being stuck
					window.setTimeout(function() {
						var autosaveNotice = elBySel('.redactorAutosaveRestored.active', element.parentNode);
						if (autosaveNotice) {
							autosaveNotice.style.setProperty('position', 'static', '');
							
							// force layout
							//noinspection BadExpressionStatementJS
							autosaveNotice.offsetTop;
							
							autosaveNotice.style.removeProperty('position');
						}
					}, 10);
				}, 10);
			};
			
			$(function () {
				$(element).redactor(config);
			});
		});
	});
</script>
