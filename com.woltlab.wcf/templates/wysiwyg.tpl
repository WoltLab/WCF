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
	
	WCF.System.Dependency.Manager.setup($callbackIdentifier, function() {
		var $textarea = $('#' + $editorName);
		var $buttons = [ ];
		
		{include file='wysiwygToolbar'}
		
		var $autosave = $textarea.data('autosave');
		var $config = {
			buttons: $buttons,
			linebreaks: true,
			minHeight: 200,
			plugins: [ 'wutil',  'wmonkeypatch', 'wbutton', 'wbbcode',  'wfontcolor' ],
			wautosave: {
				active: ($autosave) ? true : false,
				key: ($autosave) ? $autosave : '',
				saveOnInit: {if !$errorField|empty}true{else}false{/if}
			}
		};
		
		{event name='javascriptInit'}
		
		$textarea.redactor($config);
	});
	
	head.load([
		'{@$__wcf->getPath()}js/3rdParty/redactor/redactor.js',
		'{@$__wcf->getPath()}js/3rdParty/redactor/plugins/wbbcode.js',
		'{@$__wcf->getPath()}js/3rdParty/redactor/plugins/wbutton.js',
		'{@$__wcf->getPath()}js/3rdParty/redactor/plugins/wfontcolor.js',
		'{@$__wcf->getPath()}js/3rdParty/redactor/plugins/wmonkeypatch.js',
		'{@$__wcf->getPath()}js/3rdParty/redactor/plugins/wutil.js'
		{event name='javascriptFiles'}
	], function() {
		WCF.System.Dependency.Manager.invoke($callbackIdentifier);
	});
});
</script>