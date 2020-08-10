{include file='header' pageTitle='wcf.acp.style.'|concat:$action}

<link href="{@$__wcf->getPath()}acp/style/acpStyleEditor.css" type="text/css" rel="stylesheet">

{js application='wcf' acp='true' file='WCF.ACP.Style'}
{js application='wcf' file='WCF.ColorPicker' bundle='WCF.Combined'}
<script data-relocate="true">
	require(['WoltLabSuite/Core/Acp/Ui/Style/Editor'], function(AcpUiStyleEditor) {
		AcpUiStyleEditor.setup({
			isTainted: {if $isTainted}true{else}false{/if},
			styleId: {if $action === 'edit'}{@$style->styleID}{else}0{/if},
			styleRuleMap: styleRuleMap
		});
	});
	
	$(function() {
		new WCF.ColorPicker('.jsColorPicker');
		
		WCF.Language.addObject({
			'wcf.style.colorPicker': '{lang}wcf.style.colorPicker{/lang}',
			'wcf.style.colorPicker.new': '{lang}wcf.style.colorPicker.new{/lang}',
			'wcf.style.colorPicker.current': '{lang}wcf.style.colorPicker.current{/lang}',
			'wcf.style.colorPicker.button.apply': '{lang}wcf.style.colorPicker.button.apply{/lang}',
			'wcf.acp.style.image.error.invalidExtension': '{lang}wcf.acp.style.image.error.invalidExtension{/lang}'
		});
		
		{if $action == 'edit'}
			new WCF.ACP.Style.CopyStyle({@$style->styleID});
			
			WCF.Language.addObject({
				'wcf.acp.style.copyStyle.confirmMessage': '{@"wcf.acp.style.copyStyle.confirmMessage"|language|encodeJS}'
			});
		{/if}
		
		$('.jsUnitSelect').change(function(event) {
			var $target = $(event.currentTarget);
			$target.prev().attr('step', (($target.val() === 'em' || $target.val() === 'rem') ? '0.01' : '1'));
		}).trigger('change');
	});
</script>

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.style.{$action}{/lang}</h1>
		{if $action == 'edit'}<p class="contentHeaderDescription">{$styleName}</p>{/if}
	</div>
	
	<nav class="contentHeaderNavigation">
		<ul>
			{if $action == 'edit'}
				<li><a href="{link controller='StyleExport' id=$style->styleID}{/link}" class="button"><span class="icon icon16 fa-download"></span> <span>{lang}wcf.acp.style.exportStyle{/lang}</span></a></li>
				<li><a class="jsCopyStyle button"><span class="icon icon16 fa-copy"></span> <span>{lang}wcf.acp.style.copyStyle{/lang}</span></a></li>
			{/if}
			
			<li><a href="{link controller='StyleList'}{/link}" class="button"><span class="icon icon16 fa-list"></span> <span>{lang}wcf.acp.menu.link.style.list{/lang}</span></a></li>
			
			{event name='contentHeaderNavigation'}
		</ul>
	</nav>
</header>

{if !$isTainted}
	<p class="info">{lang}wcf.acp.style.protected{/lang}</p>
{/if}

{include file='formError'}

{if $success|isset}
	<p class="success">{lang}wcf.global.success.{$action}{/lang}</p>
{/if}

<form method="post" action="{if $action == 'add'}{link controller='StyleAdd'}{/link}{else}{link controller='StyleEdit' id=$styleID}{/link}{/if}">
	<div class="section tabMenuContainer" data-active="{$activeTabMenuItem}" data-store="activeTabMenuItem" id="styleTabMenuContainer">
		<nav class="tabMenu">
			<ul>
				<li><a href="{@$__wcf->getAnchor('general')}">{lang}wcf.acp.style.general{/lang}</a></li>
				<li><a href="{@$__wcf->getAnchor('globals')}">{lang}wcf.acp.style.globals{/lang}</a></li>
				<li><a href="{@$__wcf->getAnchor('colors')}">{lang}wcf.acp.style.colors{/lang}</a></li>
				<li><a href="{@$__wcf->getAnchor('advanced')}">{lang}wcf.acp.style.advanced{/lang}</a></li>
				
				{event name='tabMenuTabs'}
			</ul>
		</nav>
		
		{* general *}
		<div id="general" class="tabMenuContent">
			<div class="section">
				<dl{if $errorField == 'styleName'} class="formError"{/if}>
					<dt><label for="styleName">{lang}wcf.acp.style.styleName{/lang}</label></dt>
					<dd>
						<input type="text" name="styleName" id="styleName" value="{$styleName}" class="long">
						{if $errorField == 'styleName'}
							<small class="innerError">
								{if $errorType == 'empty'}
									{lang}wcf.global.form.error.empty{/lang}
								{else}
									{lang}wcf.acp.style.styleName.error.{$errorType}{/lang}
								{/if}
							</small>
						{/if}
					</dd>
				</dl>
				<dl{if $errorField == 'authorName'} class="formError"{/if}>
					<dt><label for="authorName">{lang}wcf.acp.style.authorName{/lang}</label></dt>
					<dd>
						<input type="text" name="authorName" id="authorName" value="{$authorName}" class="long"{if !$isTainted} readonly{/if}>
						{if $errorField == 'authorName'}
							<small class="innerError">
								{if $errorType == 'empty'}
									{lang}wcf.global.form.error.empty{/lang}
								{else}
									{lang}wcf.acp.style.authorName.error.{$errorType}{/lang}
								{/if}
							</small>
						{/if}
					</dd>
				</dl>
				<dl{if $errorField == 'copyright'} class="formError"{/if}>
					<dt><label for="copyright">{lang}wcf.acp.style.copyright{/lang}</label></dt>
					<dd>
						<input type="text" name="copyright" id="copyright" value="{$copyright}" class="long"{if !$isTainted} readonly{/if}>
						{if $errorField == 'copyright'}
							<small class="innerError">
								{if $errorType == 'empty'}
									{lang}wcf.global.form.error.empty{/lang}
								{else}
									{lang}wcf.acp.style.copyright.error.{$errorType}{/lang}
								{/if}
							</small>
						{/if}
					</dd>
				</dl>
				<dl{if $errorField == 'styleVersion'} class="formError"{/if}>
					<dt><label for="styleVersion">{lang}wcf.acp.style.styleVersion{/lang}</label></dt>
					<dd>
						<input type="text" name="styleVersion" id="styleVersion" value="{$styleVersion}" class="small"{if !$isTainted} readonly{/if}>
						{if $errorField == 'styleVersion'}
							<small class="innerError">
								{if $errorType == 'empty'}
									{lang}wcf.global.form.error.empty{/lang}
								{else}
									{lang}wcf.acp.style.styleVersion.error.{$errorType}{/lang}
								{/if}
							</small>
						{/if}
					</dd>
				</dl>
				<dl{if $errorField == 'apiVersion'} class="formError"{/if}>
					<dt><label for="apiVersion">{lang}wcf.acp.style.apiVersion{/lang}</label></dt>
					<dd>
						<select name="apiVersion" id="apiVersion"{if !$isTainted} disabled{/if}>
							{foreach from=$supportedApiVersions item=supportedApiVersion}
								<option value="{$supportedApiVersion}"{if $supportedApiVersion === $apiVersion} selected{/if}>{$supportedApiVersion} ({lang}wcf.acp.style.apiVersion.{if $supportedApiVersion === $recommendedApiVersion}recommended{else}deprecated{/if}{/lang})</option>
							{/foreach}
						</select>
						<small>{lang}wcf.acp.style.apiVersion.description{/lang}</small>
					</dd>
				</dl>
				<dl{if $errorField == 'styleDate'} class="formError"{/if}>
					<dt><label for="styleDate">{lang}wcf.acp.style.styleDate{/lang}</label></dt>
					<dd>
						<input type="date" name="styleDate" id="styleDate" value="{$styleDate}" class="small"{if !$isTainted} readonly{/if}>
						{if $errorField == 'styleDate'}
							<small class="innerError">
								{if $errorType == 'empty'}
									{lang}wcf.global.form.error.empty{/lang}
								{else}
									{lang}wcf.acp.style.styleDate.error.{$errorType}{/lang}
								{/if}
							</small>
						{/if}
					</dd>
				</dl>
				<dl{if $errorField == 'license'} class="formError"{/if}>
					<dt><label for="license">{lang}wcf.acp.style.license{/lang}</label></dt>
					<dd>
						<input type="text" name="license" id="license" value="{$license}" class="long"{if !$isTainted} readonly{/if}>
						{if $errorField == 'license'}
							<small class="innerError">
								{if $errorType == 'empty'}
									{lang}wcf.global.form.error.empty{/lang}
								{else}
									{lang}wcf.acp.style.license.error.{$errorType}{/lang}
								{/if}
							</small>
						{/if}
					</dd>
				</dl>
				<dl{if $errorField == 'authorURL'} class="formError"{/if}>
					<dt><label for="authorURL">{lang}wcf.acp.style.authorURL{/lang}</label></dt>
					<dd>
						<input type="text" name="authorURL" id="authorURL" value="{$authorURL}" class="long"{if !$isTainted} readonly{/if}>
						{if $errorField == 'authorURL'}
							<small class="innerError">
								{if $errorType == 'empty'}
									{lang}wcf.global.form.error.empty{/lang}
								{else}
									{lang}wcf.acp.style.authorURL.error.{$errorType}{/lang}
								{/if}
							</small>
						{/if}
					</dd>
				</dl>
				<dl{if $errorField == 'packageName'} class="formError"{/if}>
					<dt><label for="packageName">{lang}wcf.acp.style.packageName{/lang}</label></dt>
					<dd>
						<input type="text" name="packageName" id="packageName" value="{$packageName}" class="long"{if !$isTainted} readonly{/if}>
						{if $errorField == 'packageName'}
							<small class="innerError">{lang}wcf.acp.style.packageName.error.{$errorType}{/lang}</small>
						{/if}
					</dd>
				</dl>
				<dl{if $errorField == 'styleDescription'} class="formError"{/if}>
					<dt><label for="styleDescription">{lang}wcf.acp.style.styleDescription{/lang}</label></dt>
					<dd>
						<textarea name="styleDescription" id="styleDescription">{$i18nPlainValues['styleDescription']}</textarea>
						{if $errorField == 'styleDescription'}
							<small class="innerError">
								{if $errorType == 'empty'}
									{lang}wcf.global.form.error.empty{/lang}
								{else}
									{lang}wcf.acp.style.styleDescription.error.{$errorType}{/lang}
								{/if}
							</small>
						{/if}
						
						{include file='multipleLanguageInputJavascript' elementIdentifier='styleDescription' forceSelection=true}
					</dd>
				</dl>
				
				{event name='dataFields'}
			</div>
			
			<section class="section">
				<h2 class="sectionTitle">{lang}wcf.acp.style.general.files{/lang}</h2>
				
				<dl{if $errorField == 'image'} class="formError"{/if}>
					<dt><label for="image">{lang}wcf.acp.style.image{/lang}</label></dt>
					<dd>
						{@$__wcf->getUploadHandler()->renderField('image')}
						{if $errorField == 'image'}
							<small class="innerError">
								{if $errorType == 'empty'}
									{lang}wcf.global.form.error.empty{/lang}
								{else}
									{lang}wcf.acp.style.image.error.{$errorType}{/lang}
								{/if}
							</small>
						{/if}
						<small>{lang}wcf.acp.style.image.description{/lang}</small>
					</dd>
				</dl>
				<dl{if $errorField == 'image2x'} class="formError"{/if}>
					<dt><label for="image2x">{lang}wcf.acp.style.image2x{/lang}</label></dt>
					<dd>
						{@$__wcf->getUploadHandler()->renderField('image2x')}
						{if $errorField == 'image2x'}
							<small class="innerError">
								{if $errorType == 'empty'}
									{lang}wcf.global.form.error.empty{/lang}
								{else}
									{lang}wcf.acp.style.image2x.error.{$errorType}{/lang}
								{/if}
							</small>
						{/if}
						<small>{lang}wcf.acp.style.image2x.description{/lang}</small>
					</dd>
				</dl>
				{if $availableTemplateGroups|count}
					<dl{if $errorField == 'templateGroupID'} class="formError"{/if}>
						<dt><label for="templateGroupID">{lang}wcf.acp.style.templateGroupID{/lang}</label></dt>
						<dd>
							<select name="templateGroupID" id="templateGroupID">
								<option value="0">{lang}wcf.acp.template.group.default{/lang}</option>
							    	{htmlOptions options=$availableTemplateGroups selected=$templateGroupID disableEncoding=true}
							</select>
							{if $errorField == 'templateGroupID'}
								<small class="innerError">
									{if $errorType == 'empty'}
										{lang}wcf.global.form.error.empty{/lang}
									{else}
										{lang}wcf.acp.style.templateGroupID.error.{$errorType}{/lang}
									{/if}
								</small>
							{/if}
						</dd>
					</dl>
				{/if}
				
				<dl{if $errorField == 'customAssets'} class="formError"{/if}>
					<dt><label for="customAssets">{lang}wcf.acp.style.customAssets{/lang}</label></dt>
					<dd>
						{@$__wcf->getUploadHandler()->renderField('customAssets')}
						{if $errorField == 'customAssets'}
							<small class="innerError">
								{if $errorType == 'empty'}
									{lang}wcf.global.form.error.empty{/lang}
								{else}
									{lang}wcf.acp.style.customAssets.error.{$errorType}{/lang}
								{/if}
							</small>
						{/if}
						<small>{lang}wcf.acp.style.customAssets.description{/lang}</small>
					</dd>
				</dl>
				
				{event name='fileFields'}
			</section>
			
			<section class="section">
				<h2 class="sectionTitle">{lang}wcf.acp.style.general.favicon{/lang}</h2>
				
				<dl{if $errorField == 'image'} class="formError"{/if}>
					<dt><label for="favicon">{lang}wcf.acp.style.favicon{/lang}</label></dt>
					<dd>
						{@$__wcf->getUploadHandler()->renderField('favicon')}
						{if $errorField == 'favicon'}
							<small class="innerError">
								{if $errorType == 'empty'}
									{lang}wcf.global.form.error.empty{/lang}
								{elseif $errorType == 'minWidth' || $errorType == 'minHeight' || $errorType == 'maxWidth' || $errorType == 'maxHeight'}
									{lang}wcf.image.coverPhoto.upload.error.dimensions{/lang}
								{else}
									{lang}wcf.acp.style.favicon.error.{$errorType}{/lang}
								{/if}
							</small>
						{/if}
						<small>{lang}wcf.acp.style.favicon.description{/lang}</small>
					</dd>
				</dl>
				
				{event name='faviconFields'}
			</section>
			
			<section class="section">
				<header class="sectionHeader">
					<h2 class="sectionTitle">{lang}wcf.acp.style.general.coverPhoto{/lang}</h2>
					<p class="sectionDescription">{lang}wcf.acp.style.general.coverPhoto.description{/lang}</p>
				</header>
				
				<dl{if $errorField == 'coverPhoto'} class="formError"{/if}>
					<dt><label for="coverPhoto">{lang}wcf.acp.style.coverPhoto{/lang}</label></dt>
					<dd>
						{@$__wcf->getUploadHandler()->renderField('coverPhoto')}
						{if $errorField == 'coverPhoto'}
							<small class="innerError">
								{if $errorType == 'empty'}
									{lang}wcf.global.form.error.empty{/lang}
								{elseif $errorType == 'minWidth' || $errorType == 'minHeight'}
									{lang}wcf.image.coverPhoto.upload.error.{$errorType}{/lang}
								{else}
									{lang}wcf.acp.style.coverPhoto.error.{$errorType}{/lang}
								{/if}
							</small>
						{/if}
						<small>{lang}wcf.acp.style.coverPhoto.description{/lang}</small>
					</dd>
				</dl>
				
				{event name='coverPhotoFields'}
			</section>
			
			{event name='generalFieldsets'}
		</div>
		
		{* globals *}
		<div id="globals" class="tabMenuContent">
			{* layout *}
			<section class="section">
				<h2 class="sectionTitle">{lang}wcf.acp.style.globals.layout{/lang}</h2>
				
				<dl>
					<dt></dt>
					<dd><label>
						<input type="checkbox" id="useFluidLayout" name="useFluidLayout" value="1"{if $variables[useFluidLayout]} checked{/if}>
						<span>{lang}wcf.acp.style.globals.useFluidLayout{/lang}</span>
					</label></dd>
				</dl>
				
				<dl id="fluidLayoutMinWidth">
					<dt><label for="wcfLayoutMinWidth">{lang}wcf.acp.style.globals.fluidLayoutMinWidth{/lang}</label></dt>
					<dd>
						<input type="number" id="wcfLayoutMinWidth" name="wcfLayoutMinWidth" value="{@$variables[wcfLayoutMinWidth]}" class="tiny">
						<select name="wcfLayoutMinWidth_unit" class="jsUnitSelect">
							{foreach from=$availableUnits item=unit}
								<option value="{@$unit}"{if $variables[wcfLayoutMinWidth_unit] == $unit} selected{/if}>{@$unit}</option>
							{/foreach}
						</select>
					</dd>
				</dl>
				<dl id="fluidLayoutMaxWidth">
					<dt><label for="wcfLayoutMaxWidth">{lang}wcf.acp.style.globals.fluidLayoutMaxWidth{/lang}</label></dt>
					<dd>
						<input type="number" id="wcfLayoutMaxWidth" name="wcfLayoutMaxWidth" value="{@$variables[wcfLayoutMaxWidth]}" class="tiny">
						<select name="wcfLayoutMaxWidth_unit" class="jsUnitSelect">
							{foreach from=$availableUnits item=unit}
								<option value="{@$unit}"{if $variables[wcfLayoutMaxWidth_unit] == $unit} selected{/if}>{@$unit}</option>
							{/foreach}
						</select>
					</dd>
				</dl>
				
				<dl id="fixedLayoutVariables">
					<dt><label for="wcfLayoutFixedWidth">{lang}wcf.acp.style.globals.fixedLayoutWidth{/lang}</label></dt>
					<dd>
						<input type="number" id="wcfLayoutFixedWidth" name="wcfLayoutFixedWidth" value="{@$variables[wcfLayoutFixedWidth]}" class="tiny">
						<select name="wcfLayoutFixedWidth_unit" class="jsUnitSelect">
							{foreach from=$availableUnits item=unit}
								<option value="{@$unit}"{if $variables[wcfLayoutFixedWidth_unit] == $unit} selected{/if}>{@$unit}</option>
							{/foreach}
						</select>
					</dd>
				</dl>
				
				{event name='layoutFields'}
			</section>
			
			{* logo *}
			<section class="section">
				<h2 class="sectionTitle">{lang}wcf.acp.style.globals.pageLogo{/lang}</h2>
				
				<dl>
					<dt><label for="pageLogo">{lang}wcf.acp.style.globals.pageLogo{/lang}</label></dt>
					<dd>
						{@$__wcf->getUploadHandler()->renderField('pageLogo')}
						<small>{lang}wcf.acp.style.globals.pageLogo.description{/lang}</small>
						<script data-relocate="true">
						elBySel('#pageLogouploadFileList').addEventListener('change', function (ev) {
							var img = elBySel('#pageLogouploadFileList img');
							if (!img) return;
							
							function updateSizes() {
								elById('pageLogoWidth').value = img.width;
								elById('pageLogoHeight').value = img.height;
							}
							img.addEventListener('load', updateSizes);
							if (img.complete) {
								updateSizes();
							}
						})
						</script>
					</dd>
				</dl>
				
				<dl>
					<dt><label for="pageLogoWidth">{lang}wcf.acp.style.globals.pageLogo.width{/lang}</label></dt>
					<dd>
						<input type="number" name="pageLogoWidth" id="pageLogoWidth" value="{$variables[pageLogoWidth]}" class="tiny">
					</dd>
				</dl>
				<dl>
					<dt><label for="pageLogoHeight">{lang}wcf.acp.style.globals.pageLogo.height{/lang}</label></dt>
					<dd>
						<input type="number" name="pageLogoHeight" id="pageLogoHeight" value="{$variables[pageLogoHeight]}" class="tiny">
					</dd>
				</dl>
				
				<dl>
					<dt><label for="pageLogoMobile">{lang}wcf.acp.style.globals.pageLogoMobile{/lang}</label></dt>
					<dd>
						{@$__wcf->getUploadHandler()->renderField('pageLogoMobile')}
						<small>{lang}wcf.acp.style.globals.pageLogoMobile.description{/lang}</small>
					</dd>
				</dl>
				
				{event name='logoFields'}
			</section>
			
			{* font *}
			<section class="section">
				<h2 class="sectionTitle">{lang}wcf.acp.style.globals.font{/lang}</h2>
				
				<dl>
					<dt><label for="wcfFontSizeDefault">{lang}wcf.acp.style.globals.fontSizeDefault{/lang}</label></dt>
					<dd>
						<input type="number" id="wcfFontSizeDefault" name="wcfFontSizeDefault" value="{@$variables[wcfFontSizeDefault]}" class="tiny">
						<select name="wcfFontSizeDefault_unit" class="jsUnitSelect">
							{foreach from=$availableUnits item=unit}
								{if $unit == 'px' || $unit == 'pt'}
									<option value="{@$unit}"{if $variables[wcfFontSizeDefault_unit] == $unit} selected{/if}>{@$unit}</option>
								{/if}
							{/foreach}
						</select>
					</dd>
				</dl>
				<dl>
					<dt><label for="wcfFontSizeSmall">{lang}wcf.acp.style.globals.fontSizeSmall{/lang}</label></dt>
					<dd>
						<input type="number" id="wcfFontSizeSmall" name="wcfFontSizeSmall" value="{@$variables[wcfFontSizeSmall]}" class="tiny">
						<select name="wcfFontSizeSmall_unit" class="jsUnitSelect">
							{foreach from=$availableUnits item=unit}
								<option value="{@$unit}"{if $variables[wcfFontSizeSmall_unit] == $unit} selected{/if}>{@$unit}</option>
							{/foreach}
						</select>
					</dd>
				</dl>
				<dl>
					<dt><label for="wcfFontSizeHeadline">{lang}wcf.acp.style.globals.fontSizeHeadline{/lang}</label></dt>
					<dd>
						<input type="number" id="wcfFontSizeHeadline" name="wcfFontSizeHeadline" value="{@$variables[wcfFontSizeHeadline]}" class="tiny">
						<select name="wcfFontSizeHeadline_unit" class="jsUnitSelect">
							{foreach from=$availableUnits item=unit}
								<option value="{@$unit}"{if $variables[wcfFontSizeHeadline_unit] == $unit} selected{/if}>{@$unit}</option>
							{/foreach}
						</select>
					</dd>
				</dl>
				<dl>
					<dt><label for="wcfFontSizeSection">{lang}wcf.acp.style.globals.fontSizeSection{/lang}</label></dt>
					<dd>
						<input type="number" id="wcfFontSizeSection" name="wcfFontSizeSection" value="{@$variables[wcfFontSizeSection]}" class="tiny">
						<select name="wcfFontSizeSection_unit" class="jsUnitSelect">
							{foreach from=$availableUnits item=unit}
								<option value="{@$unit}"{if $variables[wcfFontSizeSection_unit] == $unit} selected{/if}>{@$unit}</option>
							{/foreach}
						</select>
					</dd>
				</dl>
				<dl>
					<dt><label for="wcfFontSizeTitle">{lang}wcf.acp.style.globals.fontSizeTitle{/lang}</label></dt>
					<dd>
						<input type="number" id="wcfFontSizeTitle" name="wcfFontSizeTitle" value="{@$variables[wcfFontSizeTitle]}" class="tiny">
						<select name="wcfFontSizeTitle_unit" class="jsUnitSelect">
							{foreach from=$availableUnits item=unit}
								<option value="{@$unit}"{if $variables[wcfFontSizeTitle_unit] == $unit} selected{/if}>{@$unit}</option>
							{/foreach}
						</select>
					</dd>
				</dl>
				
				<dl id="wcfFontFamilyGoogleContainer"{if $errorField == 'wcfFontFamilyGoogle'} class="formError"{/if}>
					<dt><label for="wcfFontFamilyGoogle">{lang}wcf.acp.style.globals.fontFamilyGoogle{/lang}</label></dt>
					<dd>
						<input type="text" id="wcfFontFamilyGoogle" name="wcfFontFamilyGoogle" value="{$variables[wcfFontFamilyGoogle]}" class="medium">
						<small>{lang}wcf.acp.style.globals.fontFamilyGoogle.description{/lang}</small>
						{if $errorField == 'wcfFontFamilyGoogle'}
							<small class="innerError">
								{if $errorType == 'empty'}
									{lang}wcf.global.form.error.empty{/lang}
								{else}
									{lang}wcf.acp.style.globals.fontFamilyGoogle.error.{$errorType}{/lang}
								{/if}
							</small>
						{/if}
					</dd>
				</dl>
				<dl>
					<dt><label for="wcfFontFamilyFallback">{lang}wcf.acp.style.globals.fontFamilyFallback{/lang}</label></dt>
					<dd>
						<select name="wcfFontFamilyFallback" id="wcfFontFamilyFallback">
							{foreach from=$availableFontFamilies key=fontFamily item=primaryFont}
								<option value='{@$fontFamily}'{if $variables[wcfFontFamilyFallback] == $fontFamily} selected{/if}>{@$primaryFont}</option>
							{/foreach}
						</select>
					</dd>
				</dl>
				
				{event name='fontFields'}
			</section>
			
			{event name='globalFieldsets'}
		</div>
		
		{* colors *}
		<div id="colors" class="tabMenuContent">
			<div class="section">
				<div id="spWrapper">
					<div id="spWindow">
						<div id="spHeaderPanel" data-region="wcfHeaderMenu">
							<div class="spBoundary">
								<ol class="inlineList">
									<li><a>Lorem</a></li>
									<li><a>Ipsum Dolor</a></li>
									<li><a>Sit Amet Lorem</a></li>
									
									<li class="active">
										<a>Sadipscing</a>
										<ol id="spSubMenu" data-region="wcfHeaderMenuDropdown">
											<li><a>Lorem</a></li>
											<li><a>Ipsum</a></li>
											<li class="active"><a>Dolor Sit</a></li>
										</ol>
									</li>
								</ol>
							</div>
						</div>
						
						<div id="spHeader" data-region="wcfHeader">
							<div class="spBoundary">
								<div id="spLogo"><img src="{@$__wcf->getPath()}acp/images/woltlabSuite.png"></div>
								<div id="spSearch"><div class="spInlineWrapper" data-region="wcfHeaderSearchBox"><input type="search" id="spSearchBox" placeholder="{lang}wcf.global.search.enterSearchTerm{/lang}" autocomplete="off"></div></div>
							</div>
						</div>
						
						<div id="spNavigation" data-region="wcfNavigation">
							<div class="spBoundary">
								<ol class="inlineList">
									<li><a>Lorem</a></li>
									<li><a>Ipsum</a></li>
								</ol>
							</div>
						</div>
						
						<div id="spContent">
							<div class="spBoundary">
								<div id="spContentWrapper">
									<div class="spHeadline" data-region="wcfContentHeadline">Lorem Ipsum</div>
									
									<div data-region="wcfContent">
										Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. <a>At vero eos</a> et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.
									
										<div data-region="wcfContentContainer">
											<div class="spContentContainer">
												Duis autem vel eum iriure dolor in hendrerit in vulputate velit esse molestie consequat, vel illum dolore eu feugiat nulla facilisis at vero eros et accumsan et iusto odio dignissim qui blandit praesent luptatum zzril delenit augue duis dolore te feugait nulla facilisi.
												
												<div id="spContentBorderInner"></div>
												
												Stet clita kasd gubergren, no sea <a>takimata</a> sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor <a>invidunt</a> ut labore et dolore magna aliquyam erat, sed diam voluptua.
											</div>
										</div>
										
										<div id="spContentBorder"></div>
										
										<div id="spContentDimmed" data-region="wcfContentDimmed">
											Stet clita kasd gubergren, <a>no sea takimata</a> sanctus est Lorem ipsum dolor sit amet.
										</div>
									</div>
									
									<div class="spHeadline">Tabular Box</div>
									
									<table id="spTable" data-region="wcfTabularBox">
										<thead>
											<tr>
												<th><a>Lorem</a></th>
												<th><a>Ipsum</a></th>
												<th><a>Dolor Sit Amet</a></th>
											</tr>
										</thead>
										
										<tbody>
											<tr>
												<td>Lorem ipsum dolor</td><td>sit amet, consetetur sadipscing elitr</td><td>sed diam nonumy</td>
											</tr>
											<tr>
												<td>eirmod tempor</td><td>invidunt ut labore et dolore</td><td>magna aliquyam erat</td>
											</tr>
											<tr>
												<td>sed diam voluptua</td><td>At vero eos</td><td>et accusam et justo</td>
											</tr>
										</tbody>
									</table>
									
									<div class="spHeadline">Input</div>
									
									<dl data-region="wcfInput">
										<dt><label class="spInputLabel" for="spInput">Lorem Ipsum</label></dt>
										<dd><input type="text" id="spInput" class="long" placeholder="Placeholder" value="Consetetur sadipscing elitr"></dd>
									</dl>
									<dl data-region="wcfInputDisabled">
										<dt><label class="spInputLabel" for="spInputDisabled">Dolor Sit Amet</label></dt>
										<dd><input type="text" id="spInputDisabled" class="long" value="Disabled" disabled></dd>
									</dl>
									
									<div class="spHeadline">Button</div>
									
									<div id="spButton">
										<div class="spInlineWrapper" data-region="wcfButton">
											<ol class="inlineList">
												<li><a class="button">Button</a></li>
												<li><a class="button active">Button (Active)</a></li>
											</ol>
										</div>
										<div class="spInlineWrapper" data-region="wcfButtonDisabled">
											<ol class="inlineList">
												<li><a class="button disabled">Button (Disabled)</a></li>
											</ol>
										</div>
									</div>
									
									<div id="spButtonPrimary">
										<div class="spInlineWrapper" data-region="wcfButtonPrimary">
											<ol class="inlineList">
												<li><a class="button buttonPrimary">Primary Button</a></li>
												<li><a class="button buttonPrimary active">Primary Button (Active)</a></li>
												<li><a class="button disabled">Primary Button (Disabled)</a></li>
											</ol>
										</div>
									</div>
									
									<div class="spHeadline">Editor</div>
									
									<div id="spEditor">
										<div id="spEditorToolbar" data-region="wcfEditorButton">
											<ul class="redactor-toolbar">
												<li><a class="redactor-button-disabled"><span class="icon icon16 fa-file-code-o"></span></a></li>
												<li><a><span class="icon icon16 fa-undo"></span></a></li>
												<li><a><span class="icon icon16 fa-repeat"></span></a></li>
												<li><a><span class="icon icon16 fa-expand"></span></a></li>
												<li class="redactor-toolbar-separator"><a><span class="icon icon16 fa-header"></span></a></li>
												<li class="redactor-toolbar-separator"><a><span class="icon icon16 fa-bold"></span></a></li>
												<li><a class="dropact"><span class="icon icon16 fa-italic"></span></a></li>
												<li><a><span class="icon icon16 fa-underline"></span></a></li>
												<li><a><span class="icon icon16 fa-strikethrough"></span></a></li>
											</ul>
										</div>
										<div id="spEditorContent">
											<table id="spEditorTable" data-region="wcfEditorTable">
												<tr>
													<td>Lorem</td>
													<td>&nbsp;</td>
													<td>&nbsp;</td>
												</tr>
												<tr>
													<td>Ipsum</td>
													<td>&nbsp;</td>
													<td>&nbsp;</td>
												</tr>
											</table>
										</div>
									</div>
									
									<div class="spHeadline">Dropdown</div>
									
									<div style="position: relative">
										<ul class="dropdownMenu" id="spDropdown" data-region="wcfDropdown">
											<li><a>Lorem Ipsum</a></li>
											<li class="active"><a>Dolor Sit Amet</a></li>
											<li><a>Consetetur Sadipscing</a></li>
											<li class="dropdownDivider"></li>
											<li><a>Sed diam nonumy</a></li>
										</ul>
									</div>
									
									<div class="spHeadline">Status</div>
									
									<ol id="spStatus">
										<li>
											<div id="spStatusInfo" data-region="wcfStatusInfo">Lorem ipsum dolor <a>sit amet</a>.</div>
										</li>
										<li>
											<div id="spStatusSuccess" data-region="wcfStatusSuccess"><a>Sed diam nonumy</a> eirmod tempor.</div>
										</li>
										<li>
											<div id="spStatusWarning" data-region="wcfStatusWarning">At vero eos <a>et accusam et justo duo</a>.</div>
										</li>
										<li>
											<div id="spStatusError" data-region="wcfStatusError">Stet clita <a>kasd gubergren</a>, no sea.</div>
										</li>
									</ol>
								</div>
								
								<div id="spContentSidebar">
									<div class="spContentSidebarBox" data-region="wcfSidebar">
										<div class="spContentSidebarHeadline" data-region="wcfSidebarHeadline">Sidebar</div>
										
										<p>
											Lorem ipsum dolor sit amet, consetetur sadipscing elitr, <a>sed diam nonumy eirmod tempor</a> invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam <a>et justo</a> duo dolores et ea rebum.
										</p>
									</div>
									
									<div class="spContentSidebarBox">
										<div class="spContentSidebarHeadline"><a>Dolor Sit Amet</a></div>
										
										<p>
											<a>Stet clita kasd gubergren</a>, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut <a>labore et dolore magna</a> aliquyam erat, sed diam voluptua.
										</p>
										
										<div id="spContentSidebarBoxDimmed" style="margin-top: 10px;" data-region="wcfSidebarDimmed">
											Stet clita kasd gubergren, <a>no sea takimata</a> sanctus est Lorem ipsum dolor sit amet.
										</div>
									</div>
								</div>
							</div>
						</div>
						
						<div id="spFooterBox" data-region="wcfFooterBox">
							<div class="spBoundary">
								<div class="spFooterBoxItem">
									<div class="spFooterBoxHeadline" data-region="wcfFooterBoxHeadline">Lorem Ipsum</div>
									
									<p>
										Lorem ipsum dolor sit amet, consetetur <a>sadipscing elitr</a>, sed diam nonumy eirmod tempor <a>invidunt ut labore</a> et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.
									</p>
								</div>
								
								<div class="spFooterBoxItem">
									<div class="spFooterBoxHeadline"><a>Dolor Sit Amet</a></div>
									
									<p>
										Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, <a>sed diam voluptua</a>.
									</p>
								</div>
							</div>
						</div>
						
						<div id="spFooter" data-region="wcfFooter">
							<div class="spBoundary">
								<div class="spFooterItem">
									<div class="spFooterHeadline" data-region="wcfFooterHeadline">Lorem Ipsum <a>Dolor Sit Amet</a></div>
									
									<p>
										Lorem ipsum dolor sit amet, consetetur <a>sadipscing elitr</a>, sed diam nonumy eirmod tempor <a>invidunt ut labore</a> et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.
									</p>
								</div>
							</div>
						</div>
						
						<div id="spFooterCopyright" data-region="wcfFooterCopyright">
							<div class="spBoundary">
								Copyright &copy; 1970-2038 <a>Example Company</a>
							</div>
						</div>
					</div>
					<div id="spSidebar">
						<div id="spVariablesWrapper">
							<div id="spSidebarButtons">
								<ul>
									<li id="spSelectCategory"><a href="#" class="button jsButtonSelectCategoryByClick">{lang}wcf.acp.style.colors.selectCategoryByClick{/lang}</a></li>
									<li><a href="#" class="button jsButtonToggleColorPalette">{lang}wcf.acp.style.colors.toggleColorPalette{/lang}</a></li>
								</ul>
							</div>
							<div class="spSidebarBox spSidebarBoxCategorySelection">
								<select id="spCategories">
									<option value="none" selected>{lang}wcf.global.noSelection{/lang}</option>
									{foreach from=$colorCategories key=spName item=spCategory}
										<optgroup label="{$spName}">
											{if $spCategory|is_array}
												{foreach from=$spCategory item=spChildCategory}
													<option value="{$spChildCategory}">{$spChildCategory}</option>
												{/foreach}
											{else}
												<option value="{$spCategory}">{$spCategory}</option>
											{/if}
										</optgroup>
									{/foreach}
								</select>
							</div>
							
							<div class="spSidebarBox" data-category="none">
								<p>{lang}wcf.acp.style.colors.description{/lang}</p>
								<p><br></p>
								<p><sup class="spApiVersion">3.1</sup> <small>{lang version='3.1'}wcf.acp.style.colors.description.apiVersion{/lang}</small></p>
								<p><sup class="spApiVersion">5.2</sup> <small>{lang version='5.2'}wcf.acp.style.colors.description.apiVersion{/lang}</small></p>
							</div>
							
							{foreach from=$colors key=spCategory item=spColors}
								<div class="spSidebarBox" data-category="{$spCategory}" style="display: none;">
									<ul>
										{foreach from=$spColors item=spType}
											{capture assign=spColor}{$spCategory}{$spType|ucfirst}{/capture}
											<li class="box24 spColor">
												<div class="spColorBox">
													<span class="styleVariableColor jsColorPicker" style="background-color: {$variables[$spColor]};" data-color="{$variables[$spColor]}" data-store="{$spColor}_value"></span>
													<input type="hidden" id="{$spColor}_value" name="{$spColor}" value="{$variables[$spColor]}">
												</div>
												<div>
													<span class="spVariable">${$spColor}{if $newVariables[$spColor]|isset} <sup class="spApiVersion">{$newVariables[$spColor]}</sup>{/if}</span>
													<span class="spDescription">{$spType}</span>
												</div>
											</li>
										{/foreach}
									</ul>
								</div>
							{/foreach}
						</div>
					</div>
				</div>
			</div>
		</div>
		
		<script>
			var styleRuleMap = {
				'wcfHeaderBackground': '#spHeader { background-color: VALUE; }',
				'wcfHeaderText': '#spHeader { color: VALUE; }',
				'wcfHeaderLink': '#spHeader a { color: VALUE; }',
				'wcfHeaderLinkActive': '#spHeader a:hover { color: VALUE; }',
				'wcfHeaderSearchBoxBackground': '#spSearchBox { background-color: VALUE; }',
				'wcfHeaderSearchBoxText': '#spSearchBox { color: VALUE; }',
				'wcfHeaderSearchBoxPlaceholder': '#spSearchBox::-webkit-input-placeholder { color: VALUE; } __COMBO_RULE__ #spSearchBox::-moz-placeholder { color: VALUE; } __COMBO_RULE__ #spSearchBox:-ms-input-placeholder { color: VALUE; }',
				'wcfHeaderSearchBoxPlaceholderActive': '#spSearchBox:focus::-webkit-input-placeholder, #spSearchBox:hover::-webkit-input-placeholder { color: VALUE; } __COMBO_RULE__ #spSearchBox:focus::-moz-placeholder, #spSearchBox:hover::-moz-placeholder { color: VALUE; } __COMBO_RULE__ #spSearchBox:focus:-ms-input-placeholder, #spSearchBox:hover:-ms-input-placeholder { color: VALUE; }',
				'wcfHeaderSearchBoxBackgroundActive': '#spSearchBox:focus, #spSearchBox:hover { background-color: VALUE; }',
				'wcfHeaderSearchBoxTextActive': '#spSearchBox:focus, #spSearchBox:hover { color: VALUE; }',
				'wcfHeaderMenuBackground': '#spHeaderPanel { background-color: VALUE; }',
				'wcfHeaderMenuLinkBackground': '#spHeaderPanel ol.inlineList > li > a { background-color: VALUE; }',
				'wcfHeaderMenuLinkBackgroundActive': '#spHeaderPanel ol.inlineList > li.active > a, #spHeaderPanel ol.inlineList > li > a:hover { background-color: VALUE; }',
				'wcfHeaderMenuLink': '#spHeaderPanel ol.inlineList > li > a { color: VALUE; }',
				'wcfHeaderMenuLinkActive': '#spHeaderPanel ol.inlineList > li.active > a, #spHeaderPanel ol.inlineList > li > a:hover { color: VALUE; }',
				'wcfHeaderMenuDropdownBackground': '#spSubMenu { background-color: VALUE; }',
				'wcfHeaderMenuDropdownLink': '#spSubMenu li > a { color: VALUE; }',
				'wcfHeaderMenuDropdownBackgroundActive': '#spSubMenu li.active > a, #spSubMenu li > a:hover { background-color: VALUE; }',
				'wcfHeaderMenuDropdownLinkActive': '#spSubMenu li.active > a, #spSubMenu li > a:hover { color: VALUE; }',
				'wcfNavigationBackground': '#spNavigation { background-color: VALUE; }',
				'wcfNavigationText': '#spNavigation { color: VALUE; }',
				'wcfNavigationLink': '#spNavigation a { color: VALUE; }',
				'wcfNavigationLinkActive': '#spNavigation a:hover { color: VALUE; }',
				'wcfContentBackground': '#spContent { background-color: VALUE; }',
				'wcfContentBorder': '#spContentBorder { border-color: VALUE; }',
				'wcfContentBorderInner': '#spContentBorderInner { border-color: VALUE; }',
				'wcfContentContainerBackground': '.spContentContainer { background-color: VALUE; }',
				'wcfContentContainerBorder': '.spContentContainer { border-color: VALUE; }',
				'wcfContentText': '#spContent { color: VALUE; }',
				'wcfContentLink': '#spContent a { color: VALUE; }',
				'wcfContentLinkActive': '#spContent a:hover { color: VALUE; }',
				'wcfContentDimmedText': '#spContentDimmed { color: VALUE; }',
				'wcfContentDimmedLink': '#spContentDimmed a { color: VALUE; }',
				'wcfContentDimmedLinkActive': '#spContentDimmed a:hover { color: VALUE; }',
				'wcfContentHeadlineBorder': '.spHeadline { border-color: VALUE; }',
				'wcfContentHeadlineText': '.spHeadline { color: VALUE; }',
				'wcfContentHeadlineLink': '.spHeadline a { color: VALUE; }',
				'wcfContentHeadlineLinkActive': '.spHeadline a:hover { color: VALUE; }',
				'wcfTabularBoxBorderInner': '#spTable td { border-color: VALUE; }',
				'wcfTabularBoxHeadline': '#spTable { border-color: VALUE; } __COMBO_RULE__ #spTable th, #spTable th a { color: VALUE; }',
				'wcfTabularBoxBackgroundActive': '#spTable tr:hover > td { background-color: VALUE; }',
				'wcfTabularBoxHeadlineActive': '#spTable th a:hover { color: VALUE; }',
				'wcfInputLabel': '.spInputLabel { color: VALUE; }',
				'wcfInputBackground': '#spInput { background-color: VALUE; }',
				'wcfInputBorder': '#spInput { border-color: VALUE; }',
				'wcfInputText': '#spInput { color: VALUE; }',
				'wcfInputPlaceholder': '#spInput::-webkit-input-placeholder { color: VALUE; } __COMBO_RULE__ #spInput::-moz-placeholder { color: VALUE; } __COMBO_RULE__ #spInput:-ms-input-placeholder { color: VALUE; } __COMBO_RULE__ #spInputDisabled::-webkit-input-placeholder { color: VALUE; } __COMBO_RULE__ #spInputDisabled::-moz-placeholder { color: VALUE; } __COMBO_RULE__ #spInputDisabled:-ms-input-placeholder { color: VALUE; }',
				'wcfInputPlaceholderActive': '#spInput:focus::-webkit-input-placeholder, #spInput:hover::-webkit-input-placeholder { color: VALUE; } __COMBO_RULE__ #spInput:focus::-moz-placeholder, #spInput:hover::-moz-placeholder { color: VALUE; } __COMBO_RULE__ #spInput:focus:-ms-input-placeholder, #spInput:hover:-ms-input-placeholder { color: VALUE; } __COMBO_RULE__ #spInputDisabled:focus::-webkit-input-placeholder, #spInputDisabled:hover::-webkit-input-placeholder { color: VALUE; } __COMBO_RULE__ #spInputDisabled:focus::-moz-placeholder, #spInputDisabled:hover::-moz-placeholder { color: VALUE; } __COMBO_RULE__ #spInputDisabled:focus:-ms-input-placeholder, #spInputDisabled:hover:-ms-input-placeholder { color: VALUE; }',
				'wcfInputBackgroundActive': '#spInput:focus, #spInput:hover { background-color: VALUE; }',
				'wcfInputBorderActive': '#spInput:focus, #spInput:hover { border-color: VALUE; }',
				'wcfInputTextActive': '#spInput:focus, #spInput:hover { color: VALUE; }',
				'wcfInputDisabledBackground': '#spInputDisabled { background-color: VALUE; }',
				'wcfInputDisabledBorder': '#spInputDisabled { border-color: VALUE; }',
				'wcfInputDisabledText': '#spInputDisabled { color: VALUE; }',
				'wcfButtonBackground': '#spButton .button { background-color: VALUE; }',
				'wcfButtonText': '#spButton .button { color: VALUE; }',
				'wcfButtonBackgroundActive': '#spButton .button.active, #spButton .button:hover { background-color: VALUE; }',
				'wcfButtonTextActive': '#spButton .button.active, #spButton .button:hover { color: VALUE; }',
				'wcfButtonPrimaryBackground': '#spButtonPrimary .button { background-color: VALUE; }',
				'wcfButtonPrimaryText': '#spButtonPrimary .button { color: VALUE; }',
				'wcfButtonPrimaryBackgroundActive': '#spButtonPrimary .button.active, #spButtonPrimary .button:hover { background-color: VALUE; }',
				'wcfButtonPrimaryTextActive': '#spButtonPrimary .button.active, #spButtonPrimary .button:hover { color: VALUE; }',
				'wcfButtonDisabledBackground': '#spButton .button.disabled, #spButtonPrimary .button.disabled { background-color: VALUE; }',
				'wcfButtonDisabledText': '#spButton .button.disabled, #spButtonPrimary .button.disabled { color: VALUE; }',
				'wcfEditorButtonBackground': '#spEditor .redactor-toolbar, #spEditor .redactor-toolbar a { background-color: VALUE; }',
				'wcfEditorButtonBackgroundActive': '#spEditor .redactor-toolbar a:hover, #spEditor .redactor-toolbar a.dropact { background-color: VALUE; }',
				'wcfEditorButtonText': '#spEditor .redactor-toolbar a { color: VALUE; }',
				'wcfEditorButtonTextActive': '#spEditor .redactor-toolbar a:hover, #spEditor .redactor-toolbar a.dropact { color: VALUE; }',
				'wcfEditorButtonTextDisabled': '#spEditor .redactor-toolbar a.redactor-button-disabled { color: VALUE; }',
				'wcfEditorTableBorder': '#spEditorTable td { border-color: VALUE; }',
				'wcfDropdownBackground': '#spDropdown { background-color: VALUE; } __COMBO_RULE__ #spDropdown::before { border-bottom-color: VALUE; }',
				'wcfDropdownBorderInner': '#spDropdown .dropdownDivider { border-color: VALUE; }',
				'wcfDropdownText': '#spDropdown li { color: VALUE; }',
				'wcfDropdownLink': '#spDropdown li a { color: VALUE; }',
				'wcfDropdownBackgroundActive': '#spDropdown li.active > a, #spDropdown li a:hover { background-color: VALUE; }',
				'wcfDropdownLinkActive': '#spDropdown li.active > a, #spDropdown li a:hover { color: VALUE; }',
				'wcfFooterBoxBackground': '#spFooterBox { background-color: VALUE; }',
				'wcfFooterBoxText': '#spFooterBox { color: VALUE; }',
				'wcfFooterBoxLink': '#spFooterBox a { color: VALUE; }',
				'wcfFooterBoxLinkActive': '#spFooterBox a:hover { color: VALUE; }',
				'wcfFooterBoxHeadlineText': '#spFooterBox .spFooterBoxHeadline { color: VALUE; }',
				'wcfFooterBoxHeadlineLink': '#spFooterBox .spFooterBoxHeadline a { color: VALUE; }',
				'wcfFooterBoxHeadlineLinkActive': '#spFooterBox .spFooterBoxHeadline a:hover { color: VALUE; }',
				'wcfFooterBackground': '#spFooter { background-color: VALUE; }',
				'wcfFooterText': '#spFooter { color: VALUE; }',
				'wcfFooterLink': '#spFooter a { color: VALUE; }',
				'wcfFooterLinkActive': '#spFooter a:hover { color: VALUE; }',
				'wcfFooterHeadlineText': '#spFooter .spFooterHeadline { color: VALUE; }',
				'wcfFooterHeadlineLink': '#spFooter .spFooterHeadline a { color: VALUE; }',
				'wcfFooterHeadlineLinkActive': '#spFooter .spFooterHeadline a:hover { color: VALUE; }',
				'wcfFooterCopyrightBackground': '#spFooterCopyright { background-color: VALUE; }',
				'wcfFooterCopyrightText': '#spFooterCopyright { color: VALUE; }',
				'wcfFooterCopyrightLink': '#spFooterCopyright a { color: VALUE; }',
				'wcfFooterCopyrightLinkActive': '#spFooterCopyright a:hover { color: VALUE; }',
				'wcfSidebarBackground': '#spContentSidebar .spContentSidebarBox { background-color: VALUE; }',
				'wcfSidebarText': '#spContentSidebar .spContentSidebarBox { color: VALUE; }',
				'wcfSidebarLink': '#spContentSidebar .spContentSidebarBox a { color: VALUE; }',
				'wcfSidebarLinkActive': '#spContentSidebar .spContentSidebarBox a:hover { color: VALUE; }',
				'wcfSidebarDimmedText': '#spContentSidebar .spContentSidebarBox #spContentSidebarBoxDimmed { color: VALUE; }',
				'wcfSidebarDimmedLink': '#spContentSidebar .spContentSidebarBox #spContentSidebarBoxDimmed a { color: VALUE; }',
				'wcfSidebarDimmedLinkActive': '#spContentSidebar .spContentSidebarBox #spContentSidebarBoxDimmed a:hover { color: VALUE; }',
				'wcfSidebarHeadlineText': '#spContentSidebar .spContentSidebarBox .spContentSidebarHeadline { color: VALUE; }',
				'wcfSidebarHeadlineLink': '#spContentSidebar .spContentSidebarBox .spContentSidebarHeadline a { color: VALUE; }',
				'wcfSidebarHeadlineLinkActive': '#spContentSidebar .spContentSidebarBox .spContentSidebarHeadline a:hover { color: VALUE; }',
				'wcfStatusInfoBackground': '#spStatusInfo { background-color: VALUE; }',
				'wcfStatusInfoBorder': '#spStatusInfo { border-color: VALUE; }',
				'wcfStatusInfoText': '#spStatusInfo { color: VALUE; }',
				'wcfStatusInfoLink': '#spStatusInfo a { color: VALUE; }',
				'wcfStatusInfoLinkActive': '#spStatusInfo a:hover { color: VALUE; }',
				'wcfStatusSuccessBackground': '#spStatusSuccess { background-color: VALUE; }',
				'wcfStatusSuccessBorder': '#spStatusSuccess { border-color: VALUE; }',
				'wcfStatusSuccessText': '#spStatusSuccess { color: VALUE; }',
				'wcfStatusSuccessLink': '#spStatusSuccess a { color: VALUE; }',
				'wcfStatusSuccessLinkActive': '#spStatusSuccess a:hover { color: VALUE; }',
				'wcfStatusWarningBackground': '#spStatusWarning { background-color: VALUE; }',
				'wcfStatusWarningBorder': '#spStatusWarning { border-color: VALUE; }',
				'wcfStatusWarningText': '#spStatusWarning { color: VALUE; }',
				'wcfStatusWarningLink': '#spStatusWarning a { color: VALUE; }',
				'wcfStatusWarningLinkActive': '#spStatusWarning a:hover { color: VALUE; }',
				'wcfStatusErrorBackground': '#spStatusError { background-color: VALUE; }',
				'wcfStatusErrorBorder': '#spStatusError { border-color: VALUE; }',
				'wcfStatusErrorText': '#spStatusError { color: VALUE; }',
				'wcfStatusErrorLink': '#spStatusError a { color: VALUE; }',
				'wcfStatusErrorLinkActive': '#spStatusError a:hover { color: VALUE; }'
			};
		</script>
		
		{* advanced *}
		<div id="advanced" class="tabMenuContainer tabMenuContent">
			{if !$isTainted}
				<nav class="menu">
					<ul>
						<li data-name="advanced-custom"><a href="{@$__wcf->getAnchor('advanced-custom')}">{lang}wcf.acp.style.advanced.custom{/lang}</a></li>
						<li data-name="advanced-original"><a href="{@$__wcf->getAnchor('advanced-original')}">{lang}wcf.acp.style.advanced.original{/lang}</a></li>
					</ul>
				</nav>
				
				{* custom declarations *}
				<div id="advanced-custom" class="tabMenuContent">
					<section class="section">
						<h2 class="sectionTitle">{lang}wcf.acp.style.advanced.individualScss{/lang}</h2>
						
						<dl class="wide">
							<dt></dt>
							<dd>
								<div dir="ltr">
									<textarea id="individualScssCustom" rows="20" cols="40" name="individualScssCustom">{$variables[individualScssCustom]}</textarea>
									<input class="codeMirrorScrollOffset" name="scrollOffsets[individualScssCustom]" value="{if $scrollOffsets[individualScssCustom]|isset}{$scrollOffsets[individualScssCustom]}{else}0{/if}" type="hidden">
								</div>
								<small>{lang}wcf.acp.style.advanced.individualScss.description{/lang}</small>
							</dd>
						</dl>
					</section>
					
					<section class="section{if $errorField == 'overrideScssCustom'} formError{/if}">
						<h2 class="sectionTitle">{lang}wcf.acp.style.advanced.overrideScss{/lang}</h2>
						
						<dl class="wide">
							<dt></dt>
							<dd>
								<div dir="ltr">
									<textarea id="overrideScssCustom" rows="20" cols="40" name="overrideScssCustom">{$variables[overrideScssCustom]}</textarea>
									<input class="codeMirrorScrollOffset" name="scrollOffsets[overrideScssCustom]" value="{if $scrollOffsets[overrideScssCustom]|isset}{$scrollOffsets[overrideScssCustom]}{else}0{/if}" type="hidden">
								</div>
								{if $errorField == 'overrideScssCustom'}
									<small class="innerError">
										{lang}wcf.acp.style.advanced.overrideScss.error{/lang}
										{implode from=$errorType item=error}{lang}wcf.acp.style.advanced.overrideScss.error.{$error.error}{/lang}{/implode}
									</small>
								{/if}
								<small>{lang}wcf.acp.style.advanced.overrideScss.description{/lang}</small>
							</dd>
						</dl>
					</section>
					{include file='codemirror' codemirrorMode='text/x-less' codemirrorSelector='#individualScssCustom, #overrideScssCustom'}
					
					{event name='syntaxFieldsetsCustom'}
				</div>
				
				{* original declarations / tainted style *}
				<div id="advanced-original" class="tabMenuContent">
			{/if}
			
			<section class="section">
				<h2 class="sectionTitle">{lang}wcf.acp.style.advanced.individualScss{/lang}{if !$isTainted} ({lang}wcf.acp.style.protected.less{/lang}){/if}</h2>
				
				<dl class="wide">
					<dt></dt>
					<dd>
						<div dir="ltr">
							<textarea id="individualScss" rows="20" cols="40" name="individualScss">{$variables[individualScss]}</textarea>
							<input class="codeMirrorScrollOffset" name="scrollOffsets[individualScss]" value="{if $scrollOffsets[individualScss]|isset}{$scrollOffsets[individualScss]}{else}0{/if}" type="hidden">
						</div>
						<small>{lang}wcf.acp.style.advanced.individualScss.description{/lang}</small>
					</dd>
				</dl>
			</section>
			
			<section class="section{if $errorField == 'overrideScss'} formError{/if}">
				<h2 class="sectionTitle">{lang}wcf.acp.style.advanced.overrideScss{/lang}{if !$isTainted} ({lang}wcf.acp.style.protected.less{/lang}){/if}</h2>
				
				<dl class="wide">
					<dt></dt>
					<dd>
						<div dir="ltr">
							<textarea id="overrideScss" rows="20" cols="40" name="overrideScss">{$variables[overrideScss]}</textarea>
							<input class="codeMirrorScrollOffset" name="scrollOffsets[overrideScss]" value="{if $scrollOffsets[overrideScss]|isset}{$scrollOffsets[overrideScss]}{else}0{/if}" type="hidden">
						</div>
						{if $errorField == 'overrideScss'}
							<small class="innerError">
								{lang}wcf.acp.style.advanced.overrideScss.error{/lang}
								{implode from=$errorType item=error}{lang}wcf.acp.style.advanced.overrideScss.error.{$error.error}{/lang}{/implode}
							</small>
						{/if}
						<small>{lang}wcf.acp.style.advanced.overrideScss.description{/lang}</small>
					</dd>
				</dl>
			</section>
			{include file='codemirror' codemirrorMode='text/x-less' codemirrorSelector='#individualScss, #overrideScss' editable=$isTainted}
			
			{event name='syntaxFieldsetsOriginal'}
			
			{if !$isTainted}
				</div>
			{/if}
		</div>
		
		{event name='tabMenuContents'}
	</div>
	
	<div class="formSubmit">
		<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s">
		<input type="hidden" name="tmpHash" value="{$tmpHash}">
		{@SECURITY_TOKEN_INPUT_TAG}
	</div>
</form>

<div id="styleDisableProtection" class="jsStaticDialogContent" data-title="{lang}wcf.acp.style.protected.title{/lang}">
	<p>{lang}wcf.acp.style.protected.description{/lang}</p>
	
	<dl>
		<dt></dt>
		<dd><label for="styleDisableProtectionConfirm"><input type="checkbox" id="styleDisableProtectionConfirm"> {lang}wcf.acp.style.protected.confirm{/lang}</label></dd>
	</dl>
	
	<div class="formSubmit">
		<button id="styleDisableProtectionSubmit" class="buttonPrimary" disabled>{lang}wcf.global.button.submit{/lang}</button>
	</div>
</div>

{include file='footer'}
