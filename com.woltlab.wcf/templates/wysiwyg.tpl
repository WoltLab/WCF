<script data-relocate="true">
//<![CDATA[
	var CKEDITOR_BASEPATH = '{@$__wcf->getPath()}js/3rdParty/ckeditor/';
	var __CKEDITOR_BUTTONS = [ {implode from=$__wcf->getBBCodeHandler()->getButtonBBCodes() item=__bbcode}{ icon: '../../../icon/{$__bbcode->wysiwygIcon}', label: '{$__bbcode->buttonLabel|language}', name: '{$__bbcode->bbcodeTag}' }{/implode} ];
//]]>
</script>
<script data-relocate="true" src="{@$__wcf->getPath()}js/3rdParty/ckeditor/ckeditor.js"></script>
<script data-relocate="true" src="{@$__wcf->getPath()}js/3rdParty/ckeditor/adapters/jquery.js"></script>
{event name='javascriptIncludes'}

<script data-relocate="true">
//<![CDATA[
$(function() {
	if ($.browser.mobile) {
		return;
	}
	
	{include file='wysiwygToolbar'}
	
	if (__CKEDITOR_BUTTONS.length) {
		var $buttons = [ ];
		
		for (var $i = 0, $length = __CKEDITOR_BUTTONS.length; $i < $length; $i++) {
			$buttons.push('__wcf_' + __CKEDITOR_BUTTONS[$i].name);
		}
		
		__CKEDITOR_TOOLBAR.push($buttons);
	}
	
	var $config = {
		smiley_path: '{@$__wcf->getPath()|encodeJS}',
		extraPlugins: 'wbbcode,wbutton,divarea',
		removePlugins: 'contextmenu,tabletools,liststyle,elementspath,menubutton,forms,scayt',
		language: '{@$__wcf->language->getFixedLanguageCode()}',
		fontSize_sizes: '8/8pt;10/10pt;12/12pt;14/14pt;18/18pt;24/24pt;36/36pt;',
		disableObjectResizing: true,
		disableNativeSpellChecker: false,
		toolbarCanCollapse: false,
		enterMode: CKEDITOR.ENTER_BR,
		minHeight: 200,
		toolbar: __CKEDITOR_TOOLBAR
		{if $defaultSmilies|isset}
			,smiley_images: [
				{implode from=$defaultSmilies item=smiley}'{@$smiley->smileyPath|encodeJS}'{/implode}
			],
			smiley_descriptions: [
				{implode from=$defaultSmilies item=smiley}'{@$smiley->smileyCode|encodeJS}'{/implode}
			]
		{/if}
	};
	
	{event name='javascriptInit'}
	
	if ($config.extraPlugins.indexOf('divarea') != -1) {
		CKEDITOR.dom.element.prototype.disableContextMenu = function() { };
	}
	
	var $editor = CKEDITOR.instances['{if $wysiwygSelector|isset}{$wysiwygSelector|encodeJS}{else}text{/if}'];
	if ($editor) $editor.destroy(true);
	
	$('{if $wysiwygSelector|isset}#{$wysiwygSelector|encodeJS}{else}#text{/if}').ckeditor($config);
});
//]]>
</script>
