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
			'{@$__wcf->getPath()}js/3rdParty/redactor2/plugins/WoltLabAttachment.js?v={@LAST_UPDATE_TIME}',
			'{@$__wcf->getPath()}js/3rdParty/redactor2/plugins/WoltLabButton.js?v={@LAST_UPDATE_TIME}',
			'{@$__wcf->getPath()}js/3rdParty/redactor2/plugins/WoltLabColor.js?v={@LAST_UPDATE_TIME}',
			'{@$__wcf->getPath()}js/3rdParty/redactor2/plugins/WoltLabDropdown.js?v={@LAST_UPDATE_TIME}',
			'{@$__wcf->getPath()}js/3rdParty/redactor2/plugins/WoltLabEvent.js?v={@LAST_UPDATE_TIME}',
			'{@$__wcf->getPath()}js/3rdParty/redactor2/plugins/WoltLabImage.js?v={@LAST_UPDATE_TIME}',
			'{@$__wcf->getPath()}js/3rdParty/redactor2/plugins/WoltLabLink.js?v={@LAST_UPDATE_TIME}',
			'{@$__wcf->getPath()}js/3rdParty/redactor2/plugins/WoltLabMedia.js?v={@LAST_UPDATE_TIME}',
			'{@$__wcf->getPath()}js/3rdParty/redactor2/plugins/WoltLabMention.js?v={@LAST_UPDATE_TIME}',
			'{@$__wcf->getPath()}js/3rdParty/redactor2/plugins/WoltLabModal.js?v={@LAST_UPDATE_TIME}',
			'{@$__wcf->getPath()}js/3rdParty/redactor2/plugins/WoltLabQuote.js?v={@LAST_UPDATE_TIME}',
			'{@$__wcf->getPath()}js/3rdParty/redactor2/plugins/WoltLabSize.js?v={@LAST_UPDATE_TIME}',
			'{@$__wcf->getPath()}js/3rdParty/redactor2/plugins/WoltLabSmiley.js?v={@LAST_UPDATE_TIME}'
		{else}
			'{@$__wcf->getPath()}js/3rdParty/redactor2/redactor.min.js?v={@LAST_UPDATE_TIME}',
			'{@$__wcf->getPath()}js/3rdParty/redactor2/plugins/combined.min.js?v={@LAST_UPDATE_TIME}'
		{/if}
	], function () {
		require(['WoltLab/WCF/Ui/Redactor/Metacode'], function(UiRedactorMetacode) {
			var buttons = [], buttonOptions = [];
			{include file='wysiwygToolbar'}
			
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
				imageCaption: false,
				minHeight: 200,
				plugins: [
					'alignment',
					'source',
					'table',
					'WoltLabAttachment',
					'WoltLabColor',
					'WoltLabDropdown',
					'WoltLabEvent',
					'WoltLabImage',
					'WoltLabLink',
					'WoltLabModal',
					'WoltLabQuote',
					'WoltLabSize',
					'WoltLabSmiley'
				],
				toolbarFixed: false,
				woltlab: {
					autosave: autosave,
					buttons: buttonOptions
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
