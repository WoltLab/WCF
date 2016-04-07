{include file='header' pageTitle='wcf.acp.bbcode.'|concat:$action}

{capture assign='attributeTemplate'}
	<section class="section">
		<h2 class="sectionTitle"><span class="icon icon16 fa-times pointer jsDeleteButton jsTooltip" title="{lang}wcf.global.button.delete{/lang}"></span> <span>{lang}wcf.acp.bbcode.attribute{/lang} {ldelim}#$attributeNo}</span></h2>
		
		<dl>
			<dt><label for="attributes[{ldelim}@$attributeNo}][attributeHtml]">{lang}wcf.acp.bbcode.attribute.attributeHtml{/lang}</label></dt>
			<dd>
				<input type="text" id="attributes[{ldelim}@$attributeNo}][attributeHtml]" name="attributes[{ldelim}@$attributeNo}][attributeHtml]" value="" class="long" />
			</dd>
		</dl>
		
		<dl>
			<dt><label for="attributes[{ldelim}@$attributeNo}][validationPattern]">{lang}wcf.acp.bbcode.attribute.validationPattern{/lang}</label></dt>
			<dd>
				<input type="text" id="attributes[{ldelim}@$attributeNo}][validationPattern]" name="attributes[{ldelim}@$attributeNo}][validationPattern]" value="" class="long" />
			</dd>
		</dl>
		
		<dl>
			<dt></dt>
			<dd>
				<label for="attributes[{ldelim}@$attributeNo}][required]"><input type="checkbox" id="attributes[{ldelim}@$attributeNo}][required]" name="attributes[{ldelim}@$attributeNo}][required]" value="1" /> {lang}wcf.acp.bbcode.attribute.required{/lang}</label>
			</dd>
		</dl>
		
		<dl>
			<dt></dt>
			<dd>
				<label for="attributes[{ldelim}@$attributeNo}][useText]"><input type="checkbox" id="attributes[{ldelim}@$attributeNo}][useText]" name="attributes[{ldelim}@$attributeNo}][useText]" value="1" /> {lang}wcf.acp.bbcode.attribute.useText{/lang}</label>
				<small>{lang}wcf.acp.bbcode.attribute.useText.description{/lang}</small>
			</dd>
		</dl>
		
		{event name='attributeFields'}
	</section>
{/capture}

<script data-relocate="true">
//<![CDATA[
	$(function() {
		$('.jsDeleteButton').click(function (event) {
			$(event.target).parent().parent().remove();
		});
		
		var attributeNo = {if !$attributes|count}0{else}{assign var='lastAttribute' value=$attributes|end}{$lastAttribute->attributeNo+1}{/if};
		var attributeTemplate = new WCF.Template('{@$attributeTemplate|encodeJS}');
		
		$('.jsAddButton').click(function (event) {
			var $html = $($.parseHTML(attributeTemplate.fetch({ attributeNo: attributeNo++ })));
			$html.find('.jsDeleteButton').click(function (event) {
				$(event.target).parent().parent().remove();
			});
			$('#attributeFieldset').append($html);
		});
		
		var $buttonSettings = $('.jsButtonSetting');
		var $showButton = $('#showButton');
		function toggleButtonSettings() {
			if ($showButton.is(':checked')) {
				$buttonSettings.show();
			}
			else {
				$buttonSettings.hide();
			}
		}
		
		$showButton.change(toggleButtonSettings);
		toggleButtonSettings();
	});
//]]>
</script>

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.bbcode.{$action}{/lang}</h1>
	</div>
	
	<nav class="contentHeaderNavigation">
		<ul>
			<li><a href="{link controller='BBCodeList'}{/link}" class="button"><span class="icon icon16 fa-list"></span> <span>{lang}wcf.acp.menu.link.bbcode.list{/lang}</span></a></li>
			
			{event name='contentHeaderNavigation'}
		</ul>
	</nav>
</header>

{if $action == 'add'}
	<p class="info">{lang}wcf.acp.bbcode.add.userGroupOptionInfo{/lang}</p>
{/if}

{include file='formError'}

{if $success|isset}
	<p class="success">{lang}wcf.global.success.{$action}{/lang}</p>
{/if}

<form method="post" action="{if $action == 'add'}{link controller='BBCodeAdd'}{/link}{else}{link controller='BBCodeEdit' object=$bbcode}{/link}{/if}">
	<div class="section">
		<dl{if $errorField == 'bbcodeTag'} class="formError"{/if}>
			<dt><label for="bbcodeTag">{lang}wcf.acp.bbcode.bbcodeTag{/lang}</label></dt>
			<dd>
				<input type="text" id="bbcodeTag" name="bbcodeTag" value="{$bbcodeTag}" required="required" autofocus="autofocus" pattern="^[a-zA-Z0-9]+$" class="medium" />
				{if $errorField == 'bbcodeTag'}
					<small class="innerError">
						{if $errorType == 'empty'}
							{lang}wcf.global.form.error.empty{/lang}
						{else}
							{lang}wcf.acp.bbcode.bbcodeTag.error.{$errorType}{/lang}
						{/if}
					</small>
				{/if}
			</dd>
		</dl>
		
		<dl{if $errorField == 'htmlOpen'} class="formError"{/if}>
			<dt><label for="htmlOpen">{lang}wcf.acp.bbcode.htmlOpen{/lang}</label></dt>
			<dd>
				<input type="text" id="htmlOpen" name="htmlOpen" value="{$htmlOpen}" class="long" />
				{if $errorField == 'htmlOpen'}
					<small class="innerError">{lang}wcf.acp.bbcode.htmlOpen.error.{$errorType}{/lang}</small>
				{/if}
			</dd>
		</dl>
		
		<dl{if $errorField == 'htmlClose'} class="formError"{/if}>
			<dt><label for="htmlClose">{lang}wcf.acp.bbcode.htmlClose{/lang}</label></dt>
			<dd>
				<input type="text" id="htmlClose" name="htmlClose" value="{$htmlClose}" class="long" />
				{if $errorField == 'htmlClose'}
					<small class="innerError">{lang}wcf.acp.bbcode.htmlClose.error.{$errorType}{/lang}</small>
				{/if}
			</dd>
		</dl>
		
		<dl{if $errorField == 'allowedChildren'} class="formError"{/if}>
			<dt><label for="allowedChildren">{lang}wcf.acp.bbcode.allowedChildren{/lang}</label></dt>
			<dd>
				<input type="text" id="allowedChildren" name="allowedChildren" value="{$allowedChildren}" class="long" required="required" pattern="^((all|none)\^)?([a-zA-Z0-9]+,)*[a-zA-Z0-9]+$" />
				{if $errorField == 'allowedChildren'}
					<small class="innerError">
						{if $errorType == 'empty'}
							{lang}wcf.global.form.error.empty{/lang}
						{else}
							{lang}wcf.acp.bbcode.allowedChildren.error.{$errorType}{/lang}
						{/if}
					</small>
				{/if}
			</dd>
		</dl>
		
		<dl>
			<dt></dt>
			<dd>
				<label for="isSourceCode"><input type="checkbox" id="isSourceCode" name="isSourceCode" value="1"{if $isSourceCode} checked="checked"{/if} /> {lang}wcf.acp.bbcode.isSourceCode{/lang}</label>
				<small>{lang}wcf.acp.bbcode.isSourceCode.description{/lang}</small>
			</dd>
		</dl>
		
		<dl{if $errorField == 'className'} class="formError"{/if}>
			<dt><label for="className">{lang}wcf.acp.bbcode.className{/lang}</label></dt>
			<dd>
				<input type="text" id="className" name="className" value="{$className}" class="long" pattern="^\\?([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*\\)*[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$" />
				{if $errorField == 'className'}
					<small class="innerError">{lang}wcf.acp.bbcode.className.error.{$errorType}{/lang}</small>
				{/if}
			</dd>
		</dl>
		
		{if $nativeBBCode|empty}
			<dl>
				<dt></dt>
				<dd>
					<label><input type="checkbox" id="showButton" name="showButton" value="1"{if $showButton} checked="checked"{/if} /> {lang}wcf.acp.bbcode.showButton{/lang}</label>
				</dd>
			</dl>
			
			<dl class="jsButtonSetting{if $errorField == 'buttonLabel'} formError{/if}">
				<dt><label for="buttonLabel">{lang}wcf.acp.bbcode.buttonLabel{/lang}</label></dt>
				<dd>
					<input type="text" id="buttonLabel" name="buttonLabel" value="{$i18nPlainValues['buttonLabel']}" class="long" />
					{if $errorField == 'buttonLabel'}
						<small class="innerError">
							{if $errorType == 'empty'}
								{lang}wcf.global.form.error.empty{/lang}
							{elseif $errorType == 'multilingual'}
								{lang}wcf.global.form.error.multilingual{/lang}
							{else}
								{lang}wcf.acp.bbcode.buttonLabel.error.{@$errorType}{/lang}
							{/if}
						</small>
					{/if}
					
					{include file='multipleLanguageInputJavascript' elementIdentifier='buttonLabel' forceSelection=false}
				</dd>
			</dl>
			
			<dl class="jsButtonSetting{if $errorField == 'wysiwygIcon'} formError{/if}">
				<dt><label for="wysiwygIcon">{lang}wcf.acp.bbcode.wysiwygIcon{/lang}</label></dt>
				<dd>
					<input type="text" id="wysiwygIcon" name="wysiwygIcon" value="{$wysiwygIcon}" class="long" />
					{if $errorField == 'wysiwygIcon'}
						<small class="innerError">
							{if $errorType == 'empty'}
								{lang}wcf.global.form.error.empty{/lang}
							{else}
								{lang}wcf.acp.bbcode.wysiwygIcon.error.{@$errorType}{/lang}
							{/if}
						</small>
					{/if}
					<small>{lang}wcf.acp.bbcode.wysiwygIcon.description{/lang}</small>
				</dd>
			</dl>
		{/if}
		
		{event name='dataFields'}
	</div>
		
	<section class="section" id="attributeFieldset">
		<h2 class="sectionTitle"><span class="icon icon16 fa-plus pointer jsAddButton jsTooltip" title="{lang}wcf.global.button.add{/lang}"></span> {lang}wcf.acp.bbcode.attributes{/lang}</h2>
		
		{foreach from=$attributes item='attribute'}
			<section class="section">
				<h2 class="sectionTitle"><span class="icon icon16 fa-times pointer jsDeleteButton jsTooltip" title="{lang}wcf.global.button.delete{/lang}"></span> <span>{lang}wcf.acp.bbcode.attribute{/lang} {#$attribute->attributeNo}</span></h2>
				
				<dl{if $errorField == 'attributeHtml'|concat:$attribute->attributeNo} class="formError"{/if}>
					<dt><label for="attributes[{@$attribute->attributeNo}][attributeHtml]">{lang}wcf.acp.bbcode.attribute.attributeHtml{/lang}</label></dt>
					<dd>
						<input type="text" id="attributes[{@$attribute->attributeNo}][attributeHtml]" name="attributes[{@$attribute->attributeNo}][attributeHtml]" value="{$attribute->attributeHtml}" class="long" />
					</dd>
				</dl>
				
				<dl{if $errorField == 'attributeValidationPattern'|concat:$attribute->attributeNo} class="formError"{/if}>
					<dt><label for="attributes[{@$attribute->attributeNo}][validationPattern]">{lang}wcf.acp.bbcode.attribute.validationPattern{/lang}</label></dt>
					<dd>
						<input type="text" id="attributes[{@$attribute->attributeNo}][validationPattern]" name="attributes[{@$attribute->attributeNo}][validationPattern]" value="{$attribute->validationPattern}" class="long" />
						{if $errorField == 'attributeValidationPattern'|concat:$attribute->attributeNo}
							<small class="innerError">
								{if $errorType == 'notValid'}
									{lang}wcf.acp.bbcode.attribute.validationPattern.error.notValid{/lang}
								{/if}
							</small>
						{/if}
					</dd>
				</dl>
				
				<dl{if $errorField == 'attributeRequired'|concat:$attribute->attributeNo} class="formError"{/if}>
					<dd>
						<label for="attributes[{@$attribute->attributeNo}][required]"><input type="checkbox" id="attributes[{@$attribute->attributeNo}][required]" name="attributes[{@$attribute->attributeNo}][required]" value="1"{if $attribute->required} checked="checked"{/if} /> {lang}wcf.acp.bbcode.attribute.required{/lang}</label>
					</dd>
				</dl>
				
				<dl{if $errorField == 'attributeUseText'|concat:$attribute->attributeNo} class="formError"{/if}>
					<dd>
						<label for="attributes[{@$attribute->attributeNo}][useText]"><input type="checkbox" id="attributes[{@$attribute->attributeNo}][useText]" name="attributes[{@$attribute->attributeNo}][useText]" value="1"{if $attribute->useText} checked="checked"{/if} /> {lang}wcf.acp.bbcode.attribute.useText{/lang}</label>
						<small>{lang}wcf.acp.bbcode.attribute.useText.description{/lang}</small>
					</dd>
				</dl>
				
				{event name='attributeFields'}
			</section>
		{/foreach}
	</section>
	
	{event name='sections'}
	
	<div class="formSubmit">
		<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s" />
		{@SECURITY_TOKEN_INPUT_TAG}
	</div>
</form>

{include file='footer'}