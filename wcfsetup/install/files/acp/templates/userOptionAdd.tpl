{include file='header' pageTitle='wcf.acp.user.option.'|concat:$action}

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.user.option.{$action}{/lang}</h1>
	</div>
	
	<nav class="contentHeaderNavigation">
		<ul>
			<li><a href="{link controller='UserOptionList'}{/link}" class="button">{icon name='list'} <span>{lang}wcf.acp.menu.link.user.option.list{/lang}</span></a></li>
			
			{event name='contentHeaderNavigation'}
		</ul>
	</nav>

</header>

{include file='shared_formNotice'}

{if !$availableCategories|empty}
	<form method="post" action="{if $action == 'add'}{link controller='UserOptionAdd'}{/link}{else}{link controller='UserOptionEdit' id=$optionID}{/link}{/if}">
		<div class="section">
			<dl{if $errorField == 'optionName'} class="formError"{/if}>
				<dt><label for="optionName">{lang}wcf.global.name{/lang}</label></dt>
				<dd>
					<input type="text" id="optionName" name="optionName" value="{$i18nPlainValues['optionName']}" required autofocus class="long">
					{if $errorField == 'optionName'}
						<small class="innerError">
							{if $errorType == 'multilingual'}
								{lang}wcf.global.form.error.multilingual{/lang}
							{else}
								{lang}wcf.acp.user.option.name.error.{@$errorType}{/lang}
							{/if}
						</small>
					{/if}
				</dd>
			</dl>
			{include file='shared_multipleLanguageInputJavascript' elementIdentifier='optionName' forceSelection=true}
			
			<dl{if $errorField == 'optionDescription'} class="formError"{/if}>
				<dt><label for="optionDescription">{lang}wcf.acp.user.option.description{/lang}</label></dt>
				<dd>
					{* dirty work-around for non-i18n environments *}
					{capture assign=__optionDescription}{lang __optional=true}{$i18nPlainValues['optionDescription']}{/lang}{/capture}
					{if !$__optionDescription && !"~^[a-zA-Z0-9\-\_\.]+$~"|preg_match:$i18nPlainValues['optionDescription']}{capture assign=__optionDescription}{$i18nPlainValues['optionDescription']}{/capture}{/if}
					
					{* value is already encoded inside the capture calls above *}
					<textarea name="optionDescription" id="optionDescription" cols="40" rows="10">{@$__optionDescription}</textarea>
					{if $errorField == 'optionDescription'}
						<small class="innerError">
							{if $errorType == 'empty'}
								{lang}wcf.global.form.error.empty{/lang}
							{else}
								{lang}wcf.acp.user.option.description.error.{@$errorType}{/lang}
							{/if}
						</small>
					{/if}
				</dd>
			</dl>
			{include file='shared_multipleLanguageInputJavascript' elementIdentifier='optionDescription' forceSelection=true}
			
			<dl{if $errorField == 'categoryName'} class="formError"{/if}>
				<dt><label for="categoryName">{lang}wcf.global.category{/lang}</label></dt>
				<dd>
					<select name="categoryName" id="categoryName">
						{foreach from=$availableCategories item=availableCategory}
							<option value="{$availableCategory->categoryName}"{if $availableCategory->categoryName == $categoryName} selected{/if}>{lang}wcf.user.option.category.{$availableCategory->categoryName}{/lang}</option>
						{/foreach}
					</select>
					
					{if $errorField == 'categoryName'}
						<small class="innerError">
							{if $errorType == 'empty'}
								{lang}wcf.global.form.error.empty{/lang}
							{else}
								{lang}wcf.acp.user.option.categoryName.error.{@$errorType}{/lang}
							{/if}
						</small>
					{/if}
				</dd>
			</dl>
			
			<dl>
				<dt><label for="showOrder">{lang}wcf.global.showOrder{/lang}</label></dt>
				<dd>
					<input type="number" id="showOrder" name="showOrder" value="{$showOrder}" class="short">
				</dd>
			</dl>
			
			{event name='dataFields'}
		</div>
		
		<section class="section">
			<h2 class="sectionTitle">{lang}wcf.acp.user.option.typeData{/lang}</h2>
			
			<dl{if $errorField == 'optionType'} class="formError"{/if}>
				<dt><label for="optionType">{lang}wcf.acp.user.option.optionType{/lang}</label></dt>
				<dd>
					<select name="optionType" id="optionType"{if $action === 'edit'} disabled{/if}>
						{foreach from=$availableOptionTypes item=availableOptionType}
							<option value="{$availableOptionType}"{if $availableOptionType == $optionType} selected{/if}>{$availableOptionType}</option>
						{/foreach}
					</select>
					{if $errorField == 'optionType'}
						<small class="innerError">
							{if $errorType == 'empty'}
								{lang}wcf.global.form.error.empty{/lang}
							{else}
								{lang}wcf.acp.user.option.optionType.error.{@$errorType}{/lang}
							{/if}
						</small>
					{/if}
					<small>{lang}wcf.acp.user.option.optionType.description{/lang}</small>
				</dd>
			</dl>
			
			<dl>
				<dt><label for="defaultValue">{lang}wcf.acp.user.option.defaultValue{/lang}</label></dt>
				<dd>
					<input type="text" id="defaultValue" name="defaultValue" value="{$defaultValue}" class="long">
					<small>{lang}wcf.acp.user.option.defaultValue.description{/lang}</small>
				</dd>
			</dl>
			
			<dl{if $errorField == 'selectOptions'} class="formError"{/if}>
				<dt><label for="selectOptions">{lang}wcf.acp.user.option.selectOptions{/lang}</label></dt>
				<dd>
					<textarea name="selectOptions" id="selectOptions" cols="40" rows="10">{$selectOptions}</textarea>
					{if $errorField == 'selectOptions'}
						<small class="innerError">
							{if $errorType == 'empty'}
								{lang}wcf.global.form.error.empty{/lang}
							{else}
								{lang}wcf.acp.user.option.selectOptions.error.{@$errorType}{/lang}
							{/if}
						</small>
					{/if}
					<small>{lang}wcf.acp.user.option.selectOptions.description{/lang}</small>
				</dd>
			</dl>
			
			<dl{if $errorField == 'labeledUrl'} class="formError"{/if}>
				<dt><label for="labeledUrl">{lang}wcf.acp.user.option.labeledUrl{/lang}</label></dt>
				<dd>
					<input type="text" id="labeledUrl" name="labeledUrl" value="{$labeledUrl}" class="long">
					{if $errorField == 'labeledUrl'}
						<small class="innerError">
							{if $errorType == 'empty'}
								{lang}wcf.global.form.error.empty{/lang}
							{else}
								{lang}wcf.acp.user.option.labeledUrl.error.{@$errorType}{/lang}
							{/if}
						</small>
					{/if}
					<small>{lang}wcf.acp.user.option.labeledUrl.description{/lang}</small>
				</dd>
			</dl>
			
			<dl{if $errorField == 'outputClass'} class="formError"{/if}>
				<dt><label for="outputClass">{lang}wcf.acp.user.option.outputClass{/lang}</label></dt>
				<dd>
					<input type="text" id="outputClass" name="outputClass" value="{$outputClass}" class="long">
					{if $errorField == 'outputClass'}
						<small class="innerError">
							{if $errorType == 'empty'}
								{lang}wcf.global.form.error.empty{/lang}
							{else}
								{lang}wcf.acp.user.option.outputClass.error.{@$errorType}{/lang}
							{/if}
						</small>
					{/if}
					<small>{lang}wcf.acp.user.option.outputClass.description{/lang}</small>
				</dd>
			</dl>
			
			{event name='typeDataFields'}
		</section>
		
		<section class="section">
			<h2 class="sectionTitle">{lang}wcf.acp.user.option.access{/lang}</h2>
			
			<dl>
				<dt><label for="editable">{lang}wcf.acp.user.option.editable{/lang}</label></dt>
				<dd>
					<select name="editable" id="editable">
						<option value="1"{if $editable == 1} selected{/if}>{lang}wcf.acp.user.option.editable.1{/lang}</option>
						<option value="2"{if $editable == 2} selected{/if}>{lang}wcf.acp.user.option.editable.2{/lang}</option>
						<option value="3"{if $editable == 3} selected{/if}>{lang}wcf.acp.user.option.editable.3{/lang}</option>
						<option value="6"{if $editable == 6} selected{/if}>{lang}wcf.acp.user.option.editable.6{/lang}</option>
					</select>
				</dd>
			</dl>
			
			<dl>
				<dt><label for="visible">{lang}wcf.acp.user.option.visible{/lang}</label></dt>
				<dd>
					<select name="visible" id="visible">
						<option value="0"{if $visible == 0} selected{/if}>{lang}wcf.acp.user.option.visible.0{/lang}</option>
						<option value="1"{if $visible == 1} selected{/if}>{lang}wcf.acp.user.option.visible.1{/lang}</option>
						<option value="2"{if $visible == 2} selected{/if}>{lang}wcf.acp.user.option.visible.2{/lang}</option>
						<option value="3"{if $visible == 3} selected{/if}>{lang}wcf.acp.user.option.visible.3{/lang}</option>
						<option value="7"{if $visible == 7} selected{/if}>{lang}wcf.acp.user.option.visible.7{/lang}</option>
						<option value="15"{if $visible == 15} selected{/if}>{lang}wcf.acp.user.option.visible.15{/lang}</option>
					</select>
				</dd>
			</dl>
			
			<dl{if $errorField == 'validationPattern'} class="formError"{/if}>
				<dt><label for="validationPattern">{lang}wcf.acp.user.option.validationPattern{/lang}</label></dt>
				<dd>
					<input type="text" id="validationPattern" name="validationPattern" value="{$validationPattern}" class="long">
					{if $errorField == 'validationPattern'}
						<small class="innerError">
							{if $errorType == 'empty'}
								{lang}wcf.global.form.error.empty{/lang}
							{else}
								{lang}wcf.acp.user.option.validationPattern.error.{@$errorType}{/lang}
							{/if}
						</small>
					{/if}
					<small>{lang}wcf.acp.user.option.validationPattern.description{/lang}</small>
				</dd>
			</dl>
			
			<dl>
				<dt></dt>
				<dd>
					<label><input type="checkbox" name="required" id="required" value="1"{if $required == 1} checked{/if}> {lang}wcf.acp.user.option.required{/lang}</label>
					<label><input type="checkbox" name="askDuringRegistration" id="askDuringRegistration" value="1"{if $askDuringRegistration == 1} checked{/if}> {lang}wcf.acp.user.option.askDuringRegistration{/lang}</label>
					<label><input type="checkbox" name="searchable" id="searchable" value="1"{if $searchable == 1} checked{/if}> {lang}wcf.acp.user.option.searchable{/lang}</label>
				</dd>
			</dl>
			
			{event name='accessFields'}
		</section>
		
		{event name='sections'}
		
		<div class="formSubmit">
			<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s">
			{csrfToken}
		</div>
	</form>
{else}
	<woltlab-core-notice type="error">{lang}wcf.acp.user.option.error.noCategories{/lang}</woltlab-core-notice>
{/if}

{include file='footer'}
