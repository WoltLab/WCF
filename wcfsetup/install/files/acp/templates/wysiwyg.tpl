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
			'{@$__wcf->getPath()}js/3rdParty/redactor2/plugins/WoltLabDropdown.js?v={@LAST_UPDATE_TIME}',
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
				'wcf.attachment.dragAndDrop.dropHere': '{lang}wcf.attachment.dragAndDrop.dropHere{/lang}',
				'wcf.attachment.dragAndDrop.dropNow': '{lang}wcf.attachment.dragAndDrop.dropNow{/lang}',
				
				'wcf.editor.autosave.discard': '{lang}wcf.editor.autosave.discard{/lang}',
				'wcf.editor.autosave.keep': '{lang}wcf.editor.autosave.keep{/lang}',
				'wcf.editor.autosave.restored': '{lang}wcf.editor.autosave.restored{/lang}',
				
				'wcf.editor.code.edit': '{lang}wcf.editor.code.edit{/lang}',
				'wcf.editor.code.file': '{lang}wcf.editor.code.file{/lang}',
				'wcf.editor.code.file.description': '{lang}wcf.editor.code.file.description{/lang}',
				'wcf.editor.code.highlighter': '{lang}wcf.editor.code.highlighter{/lang}',
				'wcf.editor.code.highlighter.description': '{lang}wcf.editor.code.highlighter.description{/lang}',
				'wcf.editor.code.highlighter.detect': '{lang}wcf.editor.code.highlighter.detect{/lang}',
				'wcf.editor.code.line': '{lang}wcf.editor.code.line{/lang}',
				'wcf.editor.code.line.description': '{lang}wcf.editor.code.line.description{/lang}',
				'wcf.editor.code.title': '{lang __literal=true}wcf.editor.code.title{/lang}',
				
				'wcf.editor.html.description': '{lang}wcf.editor.html.description{/lang}',
				'wcf.editor.html.title': '{lang}wcf.editor.html.title{/lang}',
				
				'wcf.editor.image.edit': '{lang}wcf.editor.image.edit{/lang}',
				'wcf.editor.image.insert': '{lang}wcf.editor.image.insert{/lang}',
				'wcf.editor.image.link': '{lang}wcf.editor.image.link{/lang}',
				'wcf.editor.image.link.error.invalid': '{lang}wcf.editor.image.link.error.invalid{/lang}',
				'wcf.editor.image.float': '{lang}wcf.editor.image.float{/lang}',
				'wcf.editor.image.float.left': '{lang}wcf.editor.image.float.left{/lang}',
				'wcf.editor.image.float.right': '{lang}wcf.editor.image.float.right{/lang}',
				'wcf.editor.image.source': '{lang}wcf.editor.image.source{/lang}',
				'wcf.editor.image.source.error.insecure': '{lang}wcf.editor.image.source.error.insecure{/lang}',
				'wcf.editor.image.source.error.invalid': '{lang}wcf.editor.image.source.error.invalid{/lang}',
				
				'wcf.editor.link.add': '{lang}wcf.editor.link.add{/lang}',
				'wcf.editor.link.edit': '{lang}wcf.editor.link.edit{/lang}',
				'wcf.editor.link.error.invalid': '{lang}wcf.editor.link.error.invalid{/lang}',
				'wcf.editor.link.url': '{lang}wcf.editor.link.url{/lang}',
				'wcf.editor.link.text': '{lang}wcf.editor.link.text{/lang}',
				
				'wcf.editor.list.indent': '{lang}wcf.editor.list.indent{/lang}',
				'wcf.editor.list.outdent': '{lang}wcf.editor.list.outdent{/lang}',
				
				'wcf.editor.quote.author': '{lang}wcf.editor.quote.author{/lang}',
				'wcf.editor.quote.edit': '{lang}wcf.editor.quote.edit{/lang}',
				'wcf.editor.quote.title': '{lang __literal=true}wcf.editor.quote.title{/lang}',
				'wcf.editor.quote.url': '{lang}wcf.editor.quote.url{/lang}',
				'wcf.editor.quote.url.description': '{lang}wcf.editor.quote.url.description{/lang}',
				'wcf.editor.quote.url.error.invalid': '{lang}wcf.editor.quote.url.error.invalid{/lang}',
				
				'wcf.editor.table.cols': '{lang}wcf.editor.table.cols{/lang}',
				'wcf.editor.table.insertTable': '{lang}wcf.editor.table.insertTable{/lang}',
				'wcf.editor.table.rows': '{lang}wcf.editor.table.rows{/lang}',
				
				'wcf.editor.source.error.active': '{lang}wcf.editor.source.error.active{/lang}',
				
				'wcf.editor.spoiler.label': '{lang}wcf.editor.spoiler.label{/lang}',
				'wcf.editor.spoiler.label.description': '{lang}wcf.editor.spoiler.label.description{/lang}',
				'wcf.editor.spoiler.edit': '{lang}wcf.editor.spoiler.edit{/lang}',
				'wcf.editor.spoiler.title': '{lang __literal=true}wcf.editor.spoiler.title{/lang}'
			});
			
			var allowedInlineStyles = [], buttons = [], buttonMobile = [], buttonOptions = [], customButtons = [];
			{include file='wysiwygToolbar'}
			
			var highlighters = { {implode from=$__wcf->getBBCodeHandler()->getHighlighters() item=__highlighter}'{$__highlighter}': '{lang}wcf.bbcode.code.{@$__highlighter}.title{/lang}'{/implode} };
			
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
				direction: '{lang}wcf.global.pageDirection{/lang}',
				formatting: ['p', 'h2', 'h3', 'h4'],
				imageCaption: false,
				imageUpload: {if $__wcf->getBBCodeHandler()->isAvailableBBCode('attach')}true{else}false{/if},
				lang: 'wsc', // fake language to offload phrases
				langs: {
					wsc: {
						// general
						edit: '{lang}wcf.global.button.edit{/lang}',
						
						// formatting dropdown
						heading2: '{lang}wcf.editor.format.heading2{/lang}',
						heading3: '{lang}wcf.editor.format.heading3{/lang}',
						heading4: '{lang}wcf.editor.format.heading4{/lang}',
						paragraph: '{lang}wcf.editor.format.paragraph{/lang}',
						
						// links
						'link-edit': '{lang}wcf.editor.link.edit{/lang}',
						'link-insert': '{lang}wcf.editor.link.add{/lang}',
						unlink: '{lang}wcf.editor.link.unlink{/lang}',
						
						// text alignment
						'align-center': '{lang}wcf.editor.alignment.center{/lang}',
						'align-justify': '{lang}wcf.editor.alignment.justify{/lang}',
						'align-left': '{lang}wcf.editor.alignment.left{/lang}',
						'align-right': '{lang}wcf.editor.alignment.right{/lang}',
						
						// table plugin
						'add-head': '{lang}wcf.editor.table.addHead{/lang}',
						'delete-column': '{lang}wcf.editor.table.deleteColumn{/lang}',
						'delete-head': '{lang}wcf.editor.table.deleteHead{/lang}',
						'delete-row': '{lang}wcf.editor.table.deleteRow{/lang}',
						'delete-table': '{lang}wcf.editor.table.deleteTable{/lang}',
						'insert-table': '{lang}wcf.editor.table.insertTable{/lang}',
						'insert-column-left': '{lang}wcf.editor.table.insertColumnLeft{/lang}',
						'insert-column-right': '{lang}wcf.editor.table.insertColumnRight{/lang}',
						'insert-row-above': '{lang}wcf.editor.table.insertRowAbove{/lang}',
						'insert-row-below': '{lang}wcf.editor.table.insertRowBelow{/lang}',
						
						// size
						'remove-size': '{lang}wcf.editor.button.size.removeSize{/lang}',
						
						// color
						'remove-color': '{lang}wcf.editor.button.color.removeColor{/lang}',
						
						// font
						'remove-font': '{lang}wcf.editor.button.font.removeFont{/lang}'
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
					'WoltLabDropdown',
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
					autosave: autosave,
					allowedInlineStyles: allowedInlineStyles,
					buttons: buttonOptions,
					buttonMobile: buttonMobile,
					customButtons: customButtons,
					forceSecureImages: {if MESSAGE_FORCE_SECURE_IMAGES}true{else}false{/if},
					highlighters: highlighters,
					media: {if $__wcf->session->getPermission('admin.content.cms.canUseMedia')}true{else}false{/if},
					mediaUrl: '{link controller='Media' id=-123456789 thumbnail='void' forceFrontend=true}{/link}'
				}
			};
			
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
					if (content === '' && Environment.platform() === 'ios') {
						content = '<p><br></p>';
					}
					
					content = UiRedactorMetacode.convertFromHtml(element.id, content);
					
					var redactor = $(element).data('redactor');
					
					// set code
					redactor.code.start(content);
					
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
