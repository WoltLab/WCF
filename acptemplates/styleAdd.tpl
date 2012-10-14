{include file='header' pageTitle='wcf.acp.style.'|concat:$action}

<script type="text/javascript" src="{@$__wcf->getPath()}acp/js/WCF.ACP.Style.js"></script>
<script type="text/javascript" src="{@$__wcf->getPath()}js/WCF.ColorPicker.js"></script>
<script type="text/javascript">
	//<![CDATA[
	$(function() {
		new WCF.ColorPicker('.jsColorPicker');
		WCF.TabMenu.init();
		
		var $useFluidLayout = $('#useFluidLayout');
		var $fluidLayoutVariables = $('#fluidLayoutVariables');
		var $fixedLayoutVariables = $('#fixedLayoutVariables');
		function useFluidLayout() {
			if ($useFluidLayout.is(':checked')) {
				$fluidLayoutVariables.show();
				$fixedLayoutVariables.hide();
			}
			else {
				$fluidLayoutVariables.hide();
				$fixedLayoutVariables.show();
			}
		}
		$useFluidLayout.change(useFluidLayout);
		useFluidLayout();
		
		new WCF.ACP.Style.ImageUpload(0, '{$tmpHash}');
		
		{if $action == 'edit' && $__wcf->getSession()->getPermission('admin.style.canAddStyle')}
			new WCF.ACP.Style.CopyStyle({@$style->styleID});
		
			WCF.Language.addObject({
				'wcf.acp.style.copyStyle.confirmMessage': '{lang}wcf.acp.style.copyStyle.confirmMessage{/lang}'
			});
		{/if}
	});
	//]]>
</script>
<header class="boxHeadline">
	<hgroup>
		<h1>{lang}wcf.acp.style.{$action}{/lang}</h1>
	</hgroup>
</header>

{if $errorField}
	<p class="error">{lang}wcf.global.form.error{/lang}</p>
{/if}

{if $success|isset}
	<p class="success">{lang}wcf.global.form.{$action}.success{/lang}</p>	
{/if}

<div class="contentNavigation">
	<nav>
		<ul>
			{if $action == 'edit'}
				<li><a href="{link controller='StyleExport' id=$style->styleID}{/link}" class="button"><img src="{@$__wcf->getPath()}icon/download.svg" alt="" class="icon24" /> <span>{lang}wcf.acp.style.exportStyle{/lang}</span></a></li>
				{if $__wcf->getSession()->getPermission('admin.style.canAddStyle')}<li><a class="jsCopyStyle button"><img src="{@$__wcf->getPath()}icon/add.svg" alt="" class="icon24" /> <span>{lang}wcf.acp.style.copyStyle{/lang}</span></a></li>{/if}
			{/if}
			<li><a href="{link controller='StyleList'}{/link}" title="{lang}wcf.acp.menu.link.style.list{/lang}" class="button"><img src="{@$__wcf->getPath()}icon/list.svg" alt="" class="icon24" /> <span>{lang}wcf.acp.menu.link.style.list{/lang}</span></a></li>
		</ul>
	</nav>
</div>

<form method="post" action="{if $action == 'add'}{link controller='StyleAdd'}{/link}{else}{link controller='StyleEdit'}{/link}{/if}">
	<div class="tabMenuContainer" data-active="" data-store="">
		<nav class="tabMenu">
			<ul>
				<li><a href="#general">{lang}wcf.acp.style.general{/lang}</a></li>
				<li><a href="#globals">{lang}wcf.acp.style.globals{/lang}</a></li>
				<li><a href="#colors">{lang}wcf.acp.style.colors{/lang}</a></li>
			</ul>
		</nav>
		
		{* general *}
		<div id="general" class="container containerPadding tabMenuContainer tabMenuContent">
			<fieldset>
				<legend>{lang}wcf.acp.style.general.data{/lang}</legend>
				
				<dl{if $errorField == 'styleName'} class="formError"{/if}>
					<dt><label for="styleName">{lang}wcf.acp.style.styleName{/lang}</label></dt>
					<dd>
						<input type="text" name="styleName" id="styleName" value="{$styleName}" class="long" />
						{if $errorField == 'styleName'}
							<small class="innerError">
								{if $errorType == 'empty'}
									{lang}wcf.global.form.error.empty{/lang}
								{else}
									{lang}wcf.acp.style.error.styleName.{$errorType}{/lang}
								{/if}
							</small>
						{/if}
					</dd>
				</dl>
				<dl{if $errorField == 'authorName'} class="formError"{/if}>
					<dt><label for="authorName">{lang}wcf.acp.style.authorName{/lang}</label></dt>
					<dd>
						<input type="text" name="authorName" id="authorName" value="{$authorName}" class="long" />
						{if $errorField == 'authorName'}
							<small class="innerError">
								{if $errorType == 'empty'}
									{lang}wcf.global.form.error.empty{/lang}
								{else}
									{lang}wcf.acp.style.error.authorName.{$errorType}{/lang}
								{/if}
							</small>
						{/if}
					</dd>
				</dl>
				<dl{if $errorField == 'copyright'} class="formError"{/if}>
					<dt><label for="copyright">{lang}wcf.acp.style.copyright{/lang}</label></dt>
					<dd>
						<input type="text" name="copyright" id="copyright" value="{$copyright}" class="long" />
						{if $errorField == 'copyright'}
							<small class="innerError">
								{if $errorType == 'empty'}
									{lang}wcf.global.form.error.empty{/lang}
								{else}
									{lang}wcf.acp.style.error.copyright.{$errorType}{/lang}
								{/if}
							</small>
						{/if}
					</dd>
				</dl>
				<dl{if $errorField == 'styleVersion'} class="formError"{/if}>
					<dt><label for="styleVersion">{lang}wcf.acp.style.styleVersion{/lang}</label></dt>
					<dd>
						<input type="text" name="styleVersion" id="styleVersion" value="{$styleVersion}" class="small" />
						{if $errorField == 'styleVersion'}
							<small class="innerError">
								{if $errorType == 'empty'}
									{lang}wcf.global.form.error.empty{/lang}
								{else}
									{lang}wcf.acp.style.error.styleVersion.{$errorType}{/lang}
								{/if}
							</small>
						{/if}
					</dd>
				</dl>
				<dl{if $errorField == 'styleDate'} class="formError"{/if}>
					<dt><label for="styleDate">{lang}wcf.acp.style.styleDate{/lang}</label></dt>
					<dd>
						<input type="date" name="styleDate" id="styleDate" value="{$styleDate}" class="small" />
						{if $errorField == 'styleDate'}
							<small class="innerError">
								{if $errorType == 'empty'}
									{lang}wcf.global.form.error.empty{/lang}
								{else}
									{lang}wcf.acp.style.error.styleDate.{$errorType}{/lang}
								{/if}
							</small>
						{/if}
					</dd>
				</dl>
				<dl{if $errorField == 'license'} class="formError"{/if}>
					<dt><label for="license">{lang}wcf.acp.style.license{/lang}</label></dt>
					<dd>
						<input type="text" name="license" id="license" value="{$license}" class="long" />
						{if $errorField == 'license'}
							<small class="innerError">
								{if $errorType == 'empty'}
									{lang}wcf.global.form.error.empty{/lang}
								{else}
									{lang}wcf.acp.style.error.license.{$errorType}{/lang}
								{/if}
							</small>
						{/if}
					</dd>
				</dl>
				<dl{if $errorField == 'authorURL'} class="formError"{/if}>
					<dt><label for="authorURL">{lang}wcf.acp.style.authorURL{/lang}</label></dt>
					<dd>
						<input type="text" name="authorURL" id="authorURL" value="{$authorURL}" class="long" />
						{if $errorField == 'authorURL'}
							<small class="innerError">
								{if $errorType == 'empty'}
									{lang}wcf.global.form.error.empty{/lang}
								{else}
									{lang}wcf.acp.style.error.authorURL.{$errorType}{/lang}
								{/if}
							</small>
						{/if}
					</dd>
				</dl>
				<dl{if $errorField == 'styleDescription'} class="formError"{/if}>
					<dt><label for="styleDescription">{lang}wcf.acp.style.styleDescription{/lang}</label></dt>
					<dd>
						<textarea name="styleDescription" id="styleDescription">{$styleDescription}</textarea>
						{if $errorField == 'styleDescription'}
							<small class="innerError">
								{if $errorType == 'empty'}
									{lang}wcf.global.form.error.empty{/lang}
								{else}
									{lang}wcf.acp.style.error.styleDescription.{$errorType}{/lang}
								{/if}
							</small>
						{/if}
					</dd>
				</dl>
			</fieldset>
			
			<fieldset>
				<legend>{lang}wcf.acp.style.general.files{/lang}</legend>
				
				<dl{if $errorField == 'image'} class="formError"{/if}>
					<dt><label for="image">{lang}wcf.acp.style.image{/lang}</label></dt>
					<dd class="framed">
						<img src="{if $action == 'add'}{@$__wcf->getPath()}images/stylePreview.png{else}{@$style->getPreviewImage()}{/if}" alt="" id="styleImage" />
						<div id="uploadImage" class="marginTop"></div>
						{*<input type="hidden" name="image" value="{$image}" readonly="readonly" class="long" /> TODO: Add upload here!*}
						{if $errorField == 'image'}
							<small class="innerError">
								{if $errorType == 'empty'}
									{lang}wcf.global.form.error.empty{/lang}
								{else}
									{lang}wcf.acp.style.error.image.{$errorType}{/lang}
								{/if}
							</small>
						{/if}
						<small>{lang}wcf.acp.style.image.description{/lang}</small>
					</dd>
				</dl>
				{hascontent}
					<dl{if $errorField == 'templateGroupID'} class="formError"{/if}>
						<dt><label for="templateGroupID">{lang}wcf.acp.style.templateGroupID{/lang}</label></dt>
						<dd>
							<select name="templateGroupID" id="templateGroupID">
								<option value="0"></option>
								{content}
									{foreach from=$availableTemplateGroups item=templateGroup}
										<option value="{@$templateGroup->templateGroupID}">{$templateGroup->templateGroupName}</option>
									{/foreach}
								{/content}
							</select>
							{if $errorField == 'templateGroupID'}
								<small class="innerError">
									{if $errorType == 'empty'}
										{lang}wcf.global.form.error.empty{/lang}
									{else}
										{lang}wcf.acp.style.error.templateGroupID.{$errorType}{/lang}
									{/if}
								</small>
							{/if}
						</dd>
					</dl>
				{/hascontent}
				<dl{if $errorField == 'iconPath'} class="formError"{/if}>
					<dt><label for="iconPath">{lang}wcf.acp.style.iconPath{/lang}</label></dt>
					<dd>
						<input type="text" name="iconPath" id="iconPath" value="{$iconPath}" class="long" />
						{if $errorField == 'iconPath'}
							<small class="innerError">
								{if $errorType == 'empty'}
									{lang}wcf.global.form.error.empty{/lang}
								{else}
									{lang}wcf.acp.style.error.iconPath.{$errorType}{/lang}
								{/if}
							</small>
						{/if}
						<small>{lang}wcf.acp.style.iconPath.description{/lang}</small>
					</dd>
				</dl>
				<dl{if $errorField == 'imagePath'} class="formError"{/if}>
					<dt><label for="imagePath">{lang}wcf.acp.style.imagePath{/lang}</label></dt>
					<dd>
						<input type="text" name="imagePath" id="imagePath" value="{$imagePath}" class="long" />
						{if $errorField == 'imagePath'}
							<small class="innerError">
								{if $errorType == 'empty'}
									{lang}wcf.global.form.error.empty{/lang}
								{else}
									{lang}wcf.acp.style.error.imagePath.{$errorType}{/lang}
								{/if}
							</small>
						{/if}
						<small>{lang}wcf.acp.style.imagePath.description{/lang}</small>
					</dd>
				</dl>
			</fieldset>
		</div>
		
		{* globals *}
		<div id="globals" class="container containerPadding tabMenuContainer tabMenuContent">
			{* layout *}
			<fieldset>
				<legend>{lang}wcf.acp.style.globals.layout{/lang}</legend>
				
				<dl>
					<dd><label>
						<input type="checkbox" id="useFluidLayout" name="useFluidLayout" value="1"{if $useFluidLayout} checked="checked"{/if} />
						<span>{lang}wcf.acp.style.globals.useFluidLayout{/lang}</span>
					</label></dd>
				</dl>
				<div id="fluidLayoutVariables">
					<dl>
						<dt><label for="wcfLayoutFluidGap">{lang}wcf.acp.style.globals.fluidLayoutGap{/lang}</label></dt>
						<dd>
							<input type="number" id="wcfLayoutFluidGap" name="wcfLayoutFluidGap" value="{@$variables[wcfLayoutFluidGap]}" class="tiny" />
							<select name="wcfLayoutFluidGap_unit">
								{foreach from=$availableUnits item=unit}
									<option value="{@$unit}"{if $variables[wcfLayoutFluidGap_unit] == $unit} selected="selected"{/if}>{@$unit}</option>
								{/foreach}
							</select>
						</dd>
					</dl>
				</div>
				<div id="fixedLayoutVariables">
					<dl>
						<dt><label for="wcfLayoutFixedWidth">{lang}wcf.acp.style.globals.fixedLayoutWidth{/lang}</label></dt>
						<dd>
							<input type="number" id="wcfLayoutFixedWidth" name="wcfLayoutFixedWidth" value="{@$variables[wcfLayoutFixedWidth]}" class="tiny" />
							<select name="wcfLayoutFixedWidth_unit">
								{foreach from=$availableUnits item=unit}
									<option value="{@$unit}"{if $variables[wcfLayoutFixedWidth_unit] == $unit} selected="selected"{/if}>{@$unit}</option>
								{/foreach}
							</select>
						</dd>
					</dl>
				</div>
			</fieldset>
			
			{* font *}
			<fieldset>
				<legend>{lang}wcf.acp.style.globals.font{/lang}</legend>
				
				<dl>
					<dt><label for="wcfBaseFontSize">{lang}wcf.acp.style.globals.fontSize{/lang}</label></dt>
					<dd>
						<input type="number" id="wcfBaseFontSize" name="wcfBaseFontSize" value="{@$variables[wcfBaseFontSize]}" class="tiny" />
						<select name="wcfBaseFontSize_unit">
							{foreach from=$availableUnits item=unit}
								<option value="{@$unit}"{if $variables[wcfBaseFontSize_unit] == $unit} selected="selected"{/if}>{@$unit}</option>
							{/foreach}
						</select>
					</dd>
				</dl>
				<dl>
					<dt><label for="wcfBaseFontFamily">{lang}wcf.acp.style.globals.fontFamily{/lang}</label></dt>
					<dd>
						<select name="wcfBaseFontFamily" id="wcfBaseFontFamily">
							{foreach from=$availableFontFamilies key=fontFamily item=primaryFont}
								<option value="{@$fontFamily}"{if $variables[wcfBaseFontFamily] == $fontFamily} selected="selected"{/if}>{@$primaryFont}</option>
							{/foreach}
						</select>
					</dd>
				</dl>
			</fieldset>
		</div>
		
		{* colors *}
		<div id="colors" class="container containerPadding tabMenuContainer tabMenuContent">
			<fieldset>
				<legend>{lang}wcf.acp.style.colors.content{/lang}</legend>
				
				{* content *}
				<ul class="colorList">
					<li>{include file='styleVariableColor' variableName='wcfContentBackgroundColor' languageVariable='backgroundColor'}</li>
					<li>{include file='styleVariableColor' variableName='wcfColor' languageVariable='color'}</li>
					<li>{include file='styleVariableColor' variableName='wcfDimmedColor' languageVariable='dimmedColor'}</li>
					<li>{include file='styleVariableColor' variableName='wcfLinkColor' languageVariable='linkColor'}</li>
					<li>{include file='styleVariableColor' variableName='wcfLinkHoverColor' languageVariable='linkHoverColor'}</li>
				</ul>
			</fieldset>
			
			<fieldset>
				<legend>{lang}wcf.acp.style.colors.container{/lang}</legend>
				
				{* general *}
				<ul class="colorList">
					<li>{include file='styleVariableColor' variableName='wcfContainerBackgroundColor' languageVariable='backgroundColor'}</li>
					<li>{include file='styleVariableColor' variableName='wcfContainerAccentBackgroundColor' languageVariable='accentBackgroundColor'}</li>
					<li>{include file='styleVariableColor' variableName='wcfContainerBorderColor' languageVariable='borderColor'}</li>
					<li>{include file='styleVariableColor' variableName='wcfContainerHoverBackgroundColor' languageVariable='hoverBackgroundColor'}</li>
				</ul>
			</fieldset>
			
			<fieldset>
				<legend>{lang}wcf.acp.style.colors.userPanel{/lang}</legend>
				
				{* user panel *}
				<ul class="colorList">
					<li>{include file='styleVariableColor' variableName='wcfUserPanelBackgroundColor' languageVariable='backgroundColor'}</li>
					<li>{include file='styleVariableColor' variableName='wcfUserPanelColor' languageVariable='color'}</li>
					<li>{include file='styleVariableColor' variableName='wcfUserPanelHoverBackgroundColor' languageVariable='hoverBackgroundColor'}</li>
					<li>{include file='styleVariableColor' variableName='wcfUserPanelHoverColor' languageVariable='hoverColor'}</li>
				</ul>
			</fieldset>
			
			<fieldset>
				<legend>{lang}wcf.acp.style.colors.tabular{/lang}</legend>
				
				{* general *}
				<ul class="colorList">
					<li>{include file='styleVariableColor' variableName='wcfTabularBoxBackgroundColor' languageVariable='backgroundColor'}</li>
					<li>{include file='styleVariableColor' variableName='wcfTabularBoxColor' languageVariable='color'}</li>
					<li>{include file='styleVariableColor' variableName='wcfTabularBoxHoverColor' languageVariable='hoverColor'}</li>
				</ul>
			</fieldset>
			
			<fieldset>
				<legend>{lang}wcf.acp.style.colors.buttons{/lang}</legend>
				
				{* default button *}
				<ul class="colorList">
					<li>{include file='styleVariableColor' variableName='wcfButtonBackgroundColor' languageVariable='backgroundColor'}</li>
					<li>{include file='styleVariableColor' variableName='wcfButtonBorderColor' languageVariable='borderColor'}</li>
					<li>{include file='styleVariableColor' variableName='wcfButtonColor' languageVariable='color'}</li>
				</ul>
				
				{* button:hover *}
				<ul class="colorList">
					<li>{include file='styleVariableColor' variableName='wcfButtonHoverBackgroundColor' languageVariable='hoverBackgroundColor'}</li>
					<li>{include file='styleVariableColor' variableName='wcfButtonHoverBorderColor' languageVariable='hoverBorderColor'}</li>
					<li>{include file='styleVariableColor' variableName='wcfButtonHoverColor' languageVariable='hoverColor'}</li>
				</ul>
				
				{* primary button *}
				<ul class="colorList">
					<li>{include file='styleVariableColor' variableName='wcfButtonPrimaryBackgroundColor' languageVariable='primaryBackgroundColor'}</li>
					<li>{include file='styleVariableColor' variableName='wcfButtonPrimaryBorderColor' languageVariable='primaryBorderColor'}</li>
					<li>{include file='styleVariableColor' variableName='wcfButtonPrimaryColor' languageVariable='primaryColor'}</li>
				</ul>
			</fieldset>
			
			<fieldset>
				<legend>{lang}wcf.acp.style.colors.formInput{/lang}</legend>
				
				{* default button *}
				<ul class="colorList">
					<li>{include file='styleVariableColor' variableName='wcfInputBackgroundColor' languageVariable='backgroundColor'}</li>
					<li>{include file='styleVariableColor' variableName='wcfInputBorderColor' languageVariable='borderColor'}</li>
					<li>{include file='styleVariableColor' variableName='wcfInputColor' languageVariable='color'}</li>
					<li>{include file='styleVariableColor' variableName='wcfInputHoverBackgroundColor' languageVariable='hoverBackgroundColor'}</li>
					<li>{include file='styleVariableColor' variableName='wcfInputHoverBorderColor' languageVariable='hoverBorderColor'}</li>
				</ul>
			</fieldset>
		</div>
	</div>
	
	<div class="formSubmit">
		<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s" />
		<input type="hidden" name="tmpHash" value="{$tmpHash}" />
		{if $styleID|isset}<input type="hidden" name="id" value="{@$styleID}" />{/if}
	</div>
</form>

{include file='footer'}