{include file='header'}

<header class="boxHeadline">
	<h1>{lang}wcf.acp.user.option.{$action}{/lang}</h1>
</header>

{if $errorField}
	<p class="error">{lang}wcf.global.form.error{/lang}</p>
{/if}

{if $success|isset}
	<p class="success">{lang}wcf.global.success.{$action}{/lang}</p>
{/if}

<div class="contentNavigation">
	<nav>
		<ul>
			<li><a href="{link controller='UserOptionList'}{/link}" class="button"><span class="icon icon16 icon-list"></span> <span>{lang}wcf.acp.menu.link.user.option.list{/lang}</span></a></li>
			
			{event name='contentNavigationButtons'}
		</ul>
	</nav>
</div>

{if !$availableCategories|empty}
	<form method="post" action="{if $action == 'add'}{link controller='UserOptionAdd'}{/link}{else}{link controller='UserOptionEdit' id=$optionID}{/link}{/if}">
		<div class="container containerPadding marginTop">
			<fieldset>
				<legend>{lang}wcf.global.form.data{/lang}</legend>
				
				<dl{if $errorField == 'optionName'} class="formError"{/if}>
					<dt><label for="optionName">{lang}wcf.global.name{/lang}</label></dt>
					<dd>
						<input type="text" id="optionName" name="optionName" value="{$optionName}" required="required" autofocus="autofocus" class="long" />
						{if $errorField == 'optionName'}
							<small class="innerError">
								{if $errorType == 'empty'}
									{lang}wcf.global.form.error.empty{/lang}
								{else}
									{lang}wcf.acp.user.option.name.error.{@$errorType}{/lang}
								{/if}
							</small>
						{/if}
					</dd>
				</dl>
				{include file='multipleLanguageInputJavascript' elementIdentifier='optionName' forceSelection=true}
				
				<dl{if $errorField == 'optionDescription'} class="formError"{/if}>
					<dt><label for="optionDescription">{lang}wcf.acp.user.option.description{/lang}</label></dt>
					<dd>
						<textarea name="optionDescription" id="optionDescription" cols="40" rows="10">{$optionDescription}</textarea>
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
				{include file='multipleLanguageInputJavascript' elementIdentifier='optionDescription' forceSelection=true}
				
				<dl{if $errorField == 'categoryName'} class="formError"{/if}>
					<dt><label for="categoryName">{lang}wcf.acp.user.option.categoryName{/lang}</label></dt>
					<dd>
						<select name="categoryName" id="categoryName">
							{foreach from=$availableCategories item=availableCategory}
								<option value="{$availableCategory->categoryName}"{if $availableCategory->categoryName == $categoryName} selected="selected"{/if}>{lang}wcf.user.option.category.{$availableCategory->categoryName}{/lang}</option>
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
					<dt><label for="showOrder">{lang}wcf.acp.user.option.showOrder{/lang}</label></dt>
					<dd>
						<input type="number" id="showOrder" name="showOrder" value="{@$showOrder}" class="short" />
					</dd>
				</dl>
				
				{event name='dataFields'}
			</fieldset>
			
			<fieldset>
				<legend>{lang}wcf.acp.user.option.typeData{/lang}</legend>
				
				<dl{if $errorField == 'optionType'} class="formError"{/if}>
					<dt><label for="optionType">{lang}wcf.acp.user.option.optionType{/lang}</label></dt>
					<dd>
						<select name="optionType" id="optionType">
							{foreach from=$availableOptionTypes item=availableOptionType}
								<option value="{$availableOptionType}"{if $availableOptionType == $optionType} selected="selected"{/if}>{$availableOptionType}</option>
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
						<input type="text" id="defaultValue" name="defaultValue" value="{$defaultValue}" class="long" />
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
				
				<dl{if $errorField == 'outputClass'} class="formError"{/if}>
					<dt><label for="outputClass">{lang}wcf.acp.user.option.outputClass{/lang}</label></dt>
					<dd>
						<input type="text" id="outputClass" name="outputClass" value="{$outputClass}" class="long" />
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
			</fieldset>
			
			<fieldset>
				<legend>{lang}wcf.acp.user.option.access{/lang}</legend>
				
				<dl>
					<dt><label for="editable">{lang}wcf.acp.user.option.editable{/lang}</label></dt>
					<dd>
						<select name="editable" id="editable">
							<option value="1"{if $editable == 1} selected="selected"{/if}>{lang}wcf.acp.user.option.editable.1{/lang}</option>
							<option value="2"{if $editable == 2} selected="selected"{/if}>{lang}wcf.acp.user.option.editable.2{/lang}</option>
							<option value="3"{if $editable == 3} selected="selected"{/if}>{lang}wcf.acp.user.option.editable.3{/lang}</option>
						</select>
					</dd>
				</dl>
				
				<dl>
					<dt><label for="visible">{lang}wcf.acp.user.option.visible{/lang}</label></dt>
					<dd>
						<select name="visible" id="visible">
							<option value="1"{if $visible == 1} selected="selected"{/if}>{lang}wcf.acp.user.option.visible.1{/lang}</option>
							<option value="2"{if $visible == 2} selected="selected"{/if}>{lang}wcf.acp.user.option.visible.2{/lang}</option>
							<option value="3"{if $visible == 3} selected="selected"{/if}>{lang}wcf.acp.user.option.visible.3{/lang}</option>
							<option value="7"{if $visible == 7} selected="selected"{/if}>{lang}wcf.acp.user.option.visible.7{/lang}</option>
							<option value="15"{if $visible == 15} selected="selected"{/if}>{lang}wcf.acp.user.option.visible.15{/lang}</option>
						</select>
					</dd>
				</dl>
				
				<dl{if $errorField == 'validationPattern'} class="formError"{/if}>
					<dt><label for="validationPattern">{lang}wcf.acp.user.option.validationPattern{/lang}</label></dt>
					<dd>
						<input type="text" id="validationPattern" name="validationPattern" value="{$validationPattern}" class="long" />
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
						<label><input type="checkbox" name="required" id="required" value="1" {if $required == 1}checked="checked" {/if}/> {lang}wcf.acp.user.option.required{/lang}</label>
						<label><input type="checkbox" name="askDuringRegistration" id="askDuringRegistration" value="1" {if $askDuringRegistration == 1}checked="checked" {/if}/> {lang}wcf.acp.user.option.askDuringRegistration{/lang}</label>
						<label><input type="checkbox" name="searchable" id="searchable" value="1" {if $searchable == 1}checked="checked" {/if}/> {lang}wcf.acp.user.option.searchable{/lang}</label>
					</dd>
				</dl>
					
				{event name='accessFields'}
			</fieldset>
			
			{event name='fieldsets'}
		</div>
		
		<div class="formSubmit">
			<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s" />
		</div>
	</form>
{else}
	<p class="info">{lang}wcf.acp.user.option.add.noCategories{/lang}</p>
{/if}

{include file='footer'}
