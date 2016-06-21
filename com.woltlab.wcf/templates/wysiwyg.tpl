<style>
	woltlab-mention {
		background-color: rgb(240, 248, 255);
		border: 1px solid rgb(52, 152, 219);
		display: inline-block;
		margin: 0 3px;
		padding: 0 2px;
	}
</style>

<script data-relocate="true">
	head.load([
		{if ENABLE_DEBUG_MODE}
			{* Imperavi *}
			'{@$__wcf->getPath()}js/3rdParty/redactor2/redactor.js?v={@LAST_UPDATE_TIME}',
			'{@$__wcf->getPath()}js/3rdParty/redactor2/plugins/alignment.js?v={@LAST_UPDATE_TIME}',
			'{@$__wcf->getPath()}js/3rdParty/redactor2/plugins/source.js?v={@LAST_UPDATE_TIME}',
			'{@$__wcf->getPath()}js/3rdParty/redactor2/plugins/table.js?v={@LAST_UPDATE_TIME}',
			
			{* WoltLab *}
			'{@$__wcf->getPath()}js/3rdParty/redactor2/plugins/WoltLabAlignment.js?v={@LAST_UPDATE_TIME}',
			'{@$__wcf->getPath()}js/3rdParty/redactor2/plugins/WoltLabAttachment.js?v={@LAST_UPDATE_TIME}',
			'{@$__wcf->getPath()}js/3rdParty/redactor2/plugins/WoltLabBlock.js?v={@LAST_UPDATE_TIME}',
			'{@$__wcf->getPath()}js/3rdParty/redactor2/plugins/WoltLabButton.js?v={@LAST_UPDATE_TIME}',
			'{@$__wcf->getPath()}js/3rdParty/redactor2/plugins/WoltLabCode.js?v={@LAST_UPDATE_TIME}',
			'{@$__wcf->getPath()}js/3rdParty/redactor2/plugins/WoltLabColor.js?v={@LAST_UPDATE_TIME}',
			'{@$__wcf->getPath()}js/3rdParty/redactor2/plugins/WoltLabDropdown.js?v={@LAST_UPDATE_TIME}',
			'{@$__wcf->getPath()}js/3rdParty/redactor2/plugins/WoltLabEvent.js?v={@LAST_UPDATE_TIME}',
			'{@$__wcf->getPath()}js/3rdParty/redactor2/plugins/WoltLabImage.js?v={@LAST_UPDATE_TIME}',
			'{@$__wcf->getPath()}js/3rdParty/redactor2/plugins/WoltLabInlineCode.js?v={@LAST_UPDATE_TIME}',
			'{@$__wcf->getPath()}js/3rdParty/redactor2/plugins/WoltLabLink.js?v={@LAST_UPDATE_TIME}',
			'{@$__wcf->getPath()}js/3rdParty/redactor2/plugins/WoltLabMedia.js?v={@LAST_UPDATE_TIME}',
			'{@$__wcf->getPath()}js/3rdParty/redactor2/plugins/WoltLabMention.js?v={@LAST_UPDATE_TIME}',
			'{@$__wcf->getPath()}js/3rdParty/redactor2/plugins/WoltLabModal.js?v={@LAST_UPDATE_TIME}',
			'{@$__wcf->getPath()}js/3rdParty/redactor2/plugins/WoltLabQuote.js?v={@LAST_UPDATE_TIME}',
			'{@$__wcf->getPath()}js/3rdParty/redactor2/plugins/WoltLabSize.js?v={@LAST_UPDATE_TIME}',
			'{@$__wcf->getPath()}js/3rdParty/redactor2/plugins/WoltLabSmiley.js?v={@LAST_UPDATE_TIME}',
			'{@$__wcf->getPath()}js/3rdParty/redactor2/plugins/WoltLabSpoiler.js?v={@LAST_UPDATE_TIME}'
		{else}
			'{@$__wcf->getPath()}js/3rdParty/redactor2/redactor.min.js?v={@LAST_UPDATE_TIME}',
			'{@$__wcf->getPath()}js/3rdParty/redactor2/plugins/combined.min.js?v={@LAST_UPDATE_TIME}'
		{/if}
	], function () {
		require(['Language', 'WoltLab/WCF/Ui/Redactor/Metacode'], function(Language, UiRedactorMetacode) {
			Language.addObject({
				'wcf.editor.code.edit': '{lang}wcf.editor.code.edit{/lang}',
				'wcf.editor.code.file': '{lang}wcf.editor.code.file{/lang}',
				'wcf.editor.code.file.description': '{lang}wcf.editor.code.file.description{/lang}',
				'wcf.editor.code.highlighter': '{lang}wcf.editor.code.highlighter{/lang}',
				'wcf.editor.code.highlighter.description': '{lang}wcf.editor.code.highlighter.description{/lang}',
				'wcf.editor.code.highlighter.detect': '{lang}wcf.editor.code.highlighter.detect{/lang}',
				'wcf.editor.code.line': '{lang}wcf.editor.code.line{/lang}',
				'wcf.editor.code.line.description': '{lang}wcf.editor.code.line.description{/lang}',
				'wcf.editor.code.title': '{lang __literal=true}wcf.editor.code.title{/lang}',
				
				'wcf.editor.image.edit': '{lang}wcf.editor.image.edit{/lang}',
				'wcf.editor.image.insert': '{lang}wcf.editor.image.insert{/lang}',
				'wcf.editor.image.link': '{lang}wcf.editor.image.link{/lang}',
				'wcf.editor.image.link.error.invalid': '{lang}wcf.editor.image.link.error.invalid{/lang}',
				'wcf.editor.image.float': '{lang}wcf.editor.image.float{/lang}',
				'wcf.editor.image.float.left': '{lang}wcf.editor.image.float.left{/lang}',
				'wcf.editor.image.float.right': '{lang}wcf.editor.image.float.right{/lang}',
				'wcf.editor.image.source': '{lang}wcf.editor.image.source{/lang}',
				'wcf.editor.image.source.error.invalid': '{lang}wcf.editor.image.source.error.invalid{/lang}',
				
				'wcf.editor.link.add': '{lang}wcf.editor.link.add{/lang}',
				'wcf.editor.link.edit': '{lang}wcf.editor.link.edit{/lang}',
				'wcf.editor.link.url': '{lang}wcf.editor.link.url{/lang}',
				'wcf.editor.link.text': '{lang}wcf.editor.link.text{/lang}',
				
				'wcf.editor.quote.author': '{lang}wcf.editor.quote.author{/lang}',
				'wcf.editor.quote.edit': '{lang}wcf.editor.quote.edit{/lang}',
				'wcf.editor.quote.title': '{lang __literal=true}wcf.editor.quote.title{/lang}',
				'wcf.editor.quote.url': '{lang}wcf.editor.quote.url{/lang}',
				'wcf.editor.quote.url.description': '{lang}wcf.editor.quote.url.description{/lang}',
				
				'wcf.editor.spoiler.label': '{lang}wcf.editor.spoiler.label{/lang}',
				'wcf.editor.spoiler.label.description': '{lang}wcf.editor.spoiler.label.description{/lang}',
				'wcf.editor.spoiler.edit': '{lang}wcf.editor.spoiler.edit{/lang}',
				'wcf.editor.spoiler.title': '{lang __literal=true}wcf.editor.spoiler.title{/lang}'
			});
			
			var buttons = [], buttonOptions = [], customButtons = [];
			{include file='wysiwygToolbar'}
			
			var highlighters = { {implode from=$__wcf->getBBCodeHandler()->getHighlighters() item=__highlighter}'{$__highlighter}': '{lang}wcf.bbcode.code.{@$__highlighter}.title{/lang}'{/implode} };
			
			// TODO: Should the media stuff be here?
			{include file='mediaJavaScript'}
			
			var element = elById('{if $wysiwygSelector|isset}{$wysiwygSelector|encodeJS}{else}text{/if}');
			UiRedactorMetacode.convert(element);
			
			var autosave = elData(element, 'autosave') || '';
			if (autosave) {
				element.removeAttribute('data-autosave');
			}
			
			var config = {
				buttons: buttons,
				formatting: ['p', 'h2', 'h3', 'h4'],
				imageCaption: false,
				lang: 'wsc', // fake language to offload phrases
				langs: {
					wsc: {
						// formatting dropdown
						heading2: '{lang}wcf.editor.format.heading2{/lang}',
						heading3: '{lang}wcf.editor.format.heading3{/lang}',
						heading4: '{lang}wcf.editor.format.heading4{/lang}',
						paragraph: '{lang}wcf.editor.format.paragraph{/lang}',
						
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
						'insert-row-below': '{lang}wcf.editor.table.insertRowBelow{/lang}'
					}
				},
				minHeight: 200,
				plugins: [
					'alignment',
					'source',
					'table',
					'WoltLabAlignment',
					'WoltLabAttachment',
					'WoltLabBlock',
					'WoltLabCode',
					'WoltLabColor',
					'WoltLabDropdown',
					'WoltLabEvent',
					'WoltLabImage',
					'WoltLabInlineCode',
					'WoltLabLink',
					'WoltLabModal',
					'WoltLabQuote',
					'WoltLabSize',
					'WoltLabSmiley',
					'WoltLabSpoiler'
				],
				toolbarFixed: false,
				woltlab: {
					autosave: autosave,
					buttons: buttonOptions,
					customButtons: customButtons,
					highlighters: highlighters
				}
			};
			
			// user mentions
			if (elDataBool(element, 'support-mention')) {
				config.plugins.push('WoltLabMention');
			}
			
			// media
			{if $__wcf->session->getPermission('admin.content.cms.canUseMedia')}
				config.plugins.push('WoltLabMedia');
			{/if}
			
			// load the button plugin last to ensure all buttons have been initialized
			// already and we can safely add all icons
			config.plugins.push('WoltLabButton');
			
			$(element).redactor(config);
		});
	});
</script>
