{if !$wysiwygEnableUpload|isset}{assign var=wysiwygEnableUpload value=false}{/if}
<link rel="stylesheet" type="text/css" href="{@$__wcf->getPath()}js/3rdParty/redactor/redactor.css" />
<script data-relocate="true">
var __REDACTOR_ICON_PATH = '{@$__wcf->getPath()}icon/';
var __REDACTOR_BUTTONS = [ {implode from=$__wcf->getBBCodeHandler()->getButtonBBCodes() item=__bbcode}{ icon: '{$__bbcode->wysiwygIcon}', label: '{$__bbcode->buttonLabel|language}', name: '{$__bbcode->bbcodeTag}' }{/implode} ];
var __REDACTOR_SMILIES = { {implode from=$defaultSmilies item=smiley}'{@$smiley->smileyCode|encodeJS}': '{@$smiley->getURL()|encodeJS}'{/implode} };
var __REDACTOR_SOURCE_BBCODES = [ {implode from=$__wcf->getBBCodeHandler()->getSourceBBCodes() item=__bbcode}'{@$__bbcode->bbcodeTag}'{/implode} ];
</script>
<script data-relocate="true">
$(function() {
	var $editorName = '{if $wysiwygSelector|isset}{$wysiwygSelector|encodeJS}{else}text{/if}';
	var $callbackIdentifier = 'Redactor_' + $editorName;
	
	{if $wysiwygEnableUpload}
		WCF.Language.addObject({
			'wcf.attachment.upload.error.invalidExtension': '{lang}wcf.attachment.upload.error.invalidExtension{/lang}',
			'wcf.attachment.upload.error.tooLarge': '{lang}wcf.attachment.upload.error.tooLarge{/lang}',
			'wcf.attachment.upload.error.reachedLimit': '{lang}wcf.attachment.upload.error.reachedLimit{/lang}',
			'wcf.attachment.upload.error.reachedRemainingLimit': '{lang}wcf.attachment.upload.error.reachedRemainingLimit{/lang}',
			'wcf.attachment.upload.error.uploadFailed': '{lang}wcf.attachment.upload.error.uploadFailed{/lang}',
			'wcf.global.button.upload': '{lang}wcf.global.button.upload{/lang}',
			'wcf.attachment.insert': '{lang}wcf.attachment.insert{/lang}',
			'wcf.attachment.delete.sure': '{lang}wcf.attachment.delete.sure{/lang}',
			'wcf.attachment.upload.limits': '{'wcf.attachment.upload.limits'|language|encodeJS}'
		});
	{/if}
	
	WCF.System.Dependency.Manager.setup($callbackIdentifier, function() {
		var $textarea = $('#' + $editorName);
		var $buttons = [ ];
		
		{include file='wysiwygToolbar'}
		
		var $autosave = $textarea.data('autosave');
		var $config = {
			buttons: $buttons,
			minHeight: 200,
			plugins: [ 'wutil',  'wmonkeypatch', 'wbutton', 'wbbcode',  'wfontcolor', 'wfontfamily', 'wfontsize' ],
			wautosave: {
				active: ($autosave) ? true : false,
				key: ($autosave) ? $autosave : '',
				saveOnInit: {if !$errorField|empty}true{else}false{/if}
			}
		};
		
		{if $wysiwygEnableUpload}
			$config.plugins.push('wupload');
			$config.wattachment = {
				attachments: [ ],
				maxCount: {@$attachmentHandler->getMaxCount()},
				objectType: '{@$attachmentObjectType}',
				objectID: '{@$attachmentObjectID}',
				parentObjectID: '{@$attachmentParentObjectID}',
				tmpHash: '{$tmpHash|encodeJS}'
			};
			
			{if $attachmentList|isset && !$attachmentList|empty}
				{foreach from=$attachmentList item=attachment}
					$config.wattachment.attachments.push({
						attachmentID: {@$attachment->attachmentID},
						filename: '{$attachment->filename|encodeJs}',
						isImage: {if $attachment->isImage}true{else}false{/if},
						tinyThumbnailUrl: '{if $attachment->tinyThumbnailType}{link controller='Attachment' object=$attachment}tiny=1{/link}{/if}',
						url: '{link controller='Attachment' object=$attachment}{/link}'
					});
				{/foreach}
			{/if}
		{/if}
		
		{event name='javascriptInit'}
		
		$textarea.redactor($config);
	});
	
	head.load([
		'{@$__wcf->getPath()}js/WCF.Attachment{if !ENABLE_DEBUG_MODE}.min{/if}.js?v={@$__wcfVersion}',
		'{@$__wcf->getPath()}js/3rdParty/redactor/redactor.js',
		'{@$__wcf->getPath()}js/3rdParty/redactor/plugins/wbbcode.js',
		'{@$__wcf->getPath()}js/3rdParty/redactor/plugins/wbutton.js',
		'{@$__wcf->getPath()}js/3rdParty/redactor/plugins/wfontcolor.js',
		'{@$__wcf->getPath()}js/3rdParty/redactor/plugins/wfontfamily.js',
		'{@$__wcf->getPath()}js/3rdParty/redactor/plugins/wfontsize.js',
		'{@$__wcf->getPath()}js/3rdParty/redactor/plugins/wmonkeypatch.js',
		'{@$__wcf->getPath()}js/3rdParty/redactor/plugins/wutil.js',
		'{@$__wcf->getPath()}js/3rdParty/redactor/plugins/wupload.js'
		{event name='javascriptFiles'}
	], function() {
		WCF.System.Dependency.Manager.invoke($callbackIdentifier);
	});
});
</script>