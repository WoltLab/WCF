{if !$wysiwygEnableUpload|isset}{assign var=wysiwygEnableUpload value=false}{/if}
{*<link rel="stylesheet" type="text/css" href="{@$__wcf->getPath()}js/3rdParty/redactor/redactor.css" />*}
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
			'wcf.attachment.insertAll': '{lang}wcf.attachment.insertAll{/lang}',
			'wcf.attachment.delete.sure': '{lang}wcf.attachment.delete.sure{/lang}',
			'wcf.attachment.upload.limits': '{'wcf.attachment.upload.limits'|language|encodeJS}'
		});
	{/if}
	
	WCF.System.Dependency.Manager.setup($callbackIdentifier, function() {
		var $textarea = $('#' + $editorName);
		var $buttons = [ ];
		var __wysiwygMessageOptions = (typeof $wysiwygMessageOptions === 'undefined') ? [ ] : $wysiwygMessageOptions;
		
		{include file='wysiwygToolbar'}
		
		var $autosave = $textarea.data('autosave');
		var $config = {
			buttons: $buttons,
			convertImageLinks: false,
			convertLinks: false,
			convertVideoLinks: false,
			direction: '{lang}wcf.global.pageDirection{/lang}',
			lang: '{@$__wcf->getLanguage()->getFixedLanguageCode()}',
			minHeight: 200,
			imageResizable: false,
			plugins: [ 'wutil',  'wmonkeypatch', 'wbutton', 'wbbcode',  'wfontcolor', 'wfontfamily', 'wfontsize', 'woptions' ],
			wautosave: {
				active: ($autosave) ? true : false,
				key: ($autosave) ? '{@$__wcf->getAutosavePrefix()}_' + $autosave : '',
				saveOnInit: {if !$errorField|empty}true{else}false{/if}
			},
			wMessageOptions: [ ]
		};
		
		{if $wysiwygEnableUpload}
			$config.plugins.push('wupload');
			$config.wMessageOptions.push({
				containerID: 'attachments',
				title: '{lang}wcf.attachment.attachments{/lang}',
				items: [ ]
			});
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
		
		if (__wysiwygMessageOptions.length) {
			$config.wMessageOptions.push({
				containerID: 'settings',
				title: '{lang}wcf.message.settings{/lang}',
				items: __wysiwygMessageOptions
			});
		}
		
		if (false && $.getLength(__REDACTOR_SMILIES)) {
			$config.wMessageOptions.push({
				containerID: 'smilies',
				title: '{lang}wcf.message.smilies{/lang}',
				items: [ ]
			});
		}
		
		$textarea.redactor($config);
	});
	
	head.load([
		'{@$__wcf->getPath()}js/3rdParty/redactor/redactor{if !ENABLE_DEBUG_MODE}.min{/if}.js?v={@$__wcfVersion}',
		{if $__wcf->getLanguage()->getFixedLanguageCode() != 'en'}'{@$__wcf->getPath()}js/3rdParty/redactor/languages/{@$__wcf->getLanguage()->getFixedLanguageCode()}.js?v={@$__wcfVersion}',{/if}
		{if !ENABLE_DEBUG_MODE}
			'{@$__wcf->getPath()}js/3rdParty/redactor/plugins/wcombined.min.js?v={@$__wcfVersion}',
		{else}
			'{@$__wcf->getPath()}js/3rdParty/redactor/plugins/wbbcode.js?v={@$__wcfVersion}',
			'{@$__wcf->getPath()}js/3rdParty/redactor/plugins/wbutton.js?v={@$__wcfVersion}',
			'{@$__wcf->getPath()}js/3rdParty/redactor/plugins/wfontcolor.js?v={@$__wcfVersion}',
			'{@$__wcf->getPath()}js/3rdParty/redactor/plugins/wfontfamily.js?v={@$__wcfVersion}',
			'{@$__wcf->getPath()}js/3rdParty/redactor/plugins/wfontsize.js?v={@$__wcfVersion}',
			'{@$__wcf->getPath()}js/3rdParty/redactor/plugins/wmonkeypatch.js?v={@$__wcfVersion}',
			'{@$__wcf->getPath()}js/3rdParty/redactor/plugins/wutil.js?v={@$__wcfVersion}',
			'{@$__wcf->getPath()}js/3rdParty/redactor/plugins/wupload.js?v={@$__wcfVersion}',
			'{@$__wcf->getPath()}js/3rdParty/redactor/plugins/woptions.js?v={@$__wcfVersion}'
		{/if}
		{event name='javascriptFiles'}
	], function() {
		WCF.System.Dependency.Manager.invoke($callbackIdentifier);
	});
});
</script>