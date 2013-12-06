<script data-relocate="true">
//<![CDATA[
	var CKEDITOR_BASEPATH = '{@$__wcf->getPath()}js/3rdParty/ckeditor/';
	var __CKEDITOR_BUTTONS = [ {implode from=$__wcf->getBBCodeHandler()->getButtonBBCodes() item=__bbcode}{ icon: '../../../icon/{$__bbcode->wysiwygIcon}', label: '{$__bbcode->buttonLabel|language}', name: '{$__bbcode->bbcodeTag}' }{/implode} ];
//]]>
</script>

<script data-relocate="true">
//<![CDATA[
$(function() {
	if (!$.browser.ckeditor) {
		return;
	}

	var $editorName = '{if $wysiwygSelector|isset}{$wysiwygSelector|encodeJS}{else}text{/if}';
	var $callbackIdentifier = 'CKEditor';
	if ($editorName != 'text') {
		$callbackIdentifier += '_' + $editorName;
	}
	
	WCF.System.Dependency.Manager.setup($callbackIdentifier, function() {
		{include file='wysiwygToolbar'}
		
		if (__CKEDITOR_BUTTONS.length) {
			var $buttons = [ ];
			
			for (var $i = 0, $length = __CKEDITOR_BUTTONS.length; $i < $length; $i++) {
				$buttons.push('__wcf_' + __CKEDITOR_BUTTONS[$i].name);
			}
			
			__CKEDITOR_TOOLBAR.push($buttons);
		}
		
		var $config = {
			customConfig: '', /* disable loading of config.js */
			title: '', /* remove title attribute */
			smiley_path: '{@$__wcf->getPath()|encodeJS}',
			extraPlugins: 'wbbcode,wbutton,divarea',
			removePlugins: 'contextmenu,tabletools,liststyle,elementspath,menubutton,forms,scayt,language',
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

		// collapse toolbar on smartphones
		if ($.browser.mobile && !navigator.userAgent.match(/iPad/)) {
			$config.toolbarCanCollapse = true;
			$config.toolbarStartupExpanded = false;
		}
		
		{event name='javascriptInit'}
		
		if ($config.extraPlugins.indexOf('divarea') != -1) {
			CKEDITOR.dom.element.prototype.disableContextMenu = function() { };
		}
		
		var $editor = CKEDITOR.instances[$editorName];
		if ($editor) $editor.destroy(true);
		
		$('#' + $editorName).ckeditor($config);
	});

	head.load([
		{ CKEditorCore: '{@$__wcf->getPath()}js/3rdParty/ckeditor/ckeditor.js' },
		{ CKEditor: '{@$__wcf->getPath()}js/3rdParty/ckeditor/adapters/jquery.js' }
		{event name='javascriptFiles'}
	], function() {
		WCF.System.Dependency.Manager.invoke($callbackIdentifier);
	});
	
	head.ready('CKEditorCore', function() {
		// prevent double editor initialization if used in combination with divarea-plugin
		CKEDITOR.disableAutoInline = true;
	});
});
//]]>
</script>
