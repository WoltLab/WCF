<script type="text/javascript">
//<![CDATA[
	var CKEDITOR_BASEPATH = '{@$__wcf->getPath()}js/3rdParty/ckeditor/';
	var __CKEDITOR_BUTTONS = [ {implode from=$__wcf->getBBCodeHandler()->getButtonBBCodes() item=__bbcode}{ icon: '{$__bbcode->wysiwygIcon}', label: '{$__bbcode->buttonLabel|language}', name: '{$__bbcode->bbcodeTag}' }{/implode} ];
//]]>
</script>
<script type="text/javascript" src="{@$__wcf->getPath()}js/3rdParty/ckeditor/ckeditor.js"></script>
<script type="text/javascript" src="{@$__wcf->getPath()}js/3rdParty/ckeditor/adapters/jquery.js"></script>
{event name='javascriptIncludes'}

<script type="text/javascript">
//<![CDATA[
$(function() {
	if ($.browser.mobile) {
		return;
	}
	
	var __CKEDITOR_TOOLBAR = [
		['Source', '-', 'Undo', 'Redo'],
		['Bold', 'Italic', 'Underline', '-', 'Strike', 'Subscript','Superscript'],
		['NumberedList', 'BulletedList', '-', 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock'],
		'/',
		['Font', 'FontSize', 'TextColor'],
		['Link', 'Unlink', 'Image', 'Table', 'Smiley'],
		['Maximize']
	];
	if (__CKEDITOR_BUTTONS.length) {
		var $buttons = [ ];
		
		for (var $i = 0, $length = __CKEDITOR_BUTTONS.length; $i < $length; $i++) {
			$buttons.push('__wcf_' + __CKEDITOR_BUTTONS[$i].name);
		}
		
		__CKEDITOR_TOOLBAR.push($buttons);
	}
	
	var $config = {
		smiley_path: '{@$__wcf->getPath()|encodeJS}',
		extraPlugins: 'wbbcode,wbutton',
		removePlugins: 'contextmenu,tabletools,liststyle,elementspath,menubutton,forms,scayt',
		language: '{@$__wcf->language->getFixedLanguageCode()}',
		fontSize_sizes: '8/8pt;10/10pt;12/12pt;14/14pt;18/18pt;24/24pt;36/36pt;',
		disableObjectResizing: true,
		disableNativeSpellChecker: false,
		toolbarCanCollapse: false,
		enterMode: CKEDITOR.ENTER_BR,
		minHeight: 200,
		toolbar: __CKEDITOR_TOOLBAR,
		smiley_images: [
			{implode from=$defaultSmilies item=smiley}'{@$smiley->smileyPath|encodeJS}'{/implode}
		],
		smiley_descriptions: [
			{implode from=$defaultSmilies item=smiley}'{@$smiley->smileyCode|encodeJS}'{/implode}
		]
	};
	
	{event name='javascriptInit'}
	
	var $editor = CKEDITOR.instances['{if $wysiwygSelector|isset}{$wysiwygSelector|encodeJS}{else}text{/if}'];
	if ($editor) $editor.destroy(true);
	
	$('{if $wysiwygSelector|isset}#{$wysiwygSelector|encodeJS}{else}#text{/if}').ckeditor($config);
});
//]]>
</script>
