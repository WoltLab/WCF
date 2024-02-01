<script data-relocate="true">
	$(function() {
		var $optionTypesUsingSelectOptions = [{implode from=$optionTypesUsingSelectOptions item=optionTypeUsingSelectOptions}'{@$optionTypeUsingSelectOptions}'{/implode}];
		
		$('#optionType').change(function(event) {
			var $value = $(event.currentTarget).val();
			if (WCF.inArray($value, $optionTypesUsingSelectOptions)) {
				$('#selectOptionsDL').show();
			}
			else {
				$('#selectOptionsDL').hide();
			}
			
			window[($value === 'boolean' ? 'elHide' : 'elShow')](elById('validationPatternDL'));
		});
		$('#optionType').trigger('change');
	});
</script>

<div class="section">
	<dl{if $errorField == 'optionTitle'} class="formError"{/if}>
		<dt><label for="optionTitle">{lang}wcf.global.name{/lang}</label></dt>
		<dd>
			<input type="text" id="optionTitle" name="optionTitle" value="{$i18nPlainValues['optionTitle']}" required autofocus maxlength="255" class="long">
			{if $errorField == 'optionTitle'}
				<small class="innerError">
					{if $errorType == 'multilingual'}
						{lang}wcf.global.form.error.multilingual{/lang}
					{else}
						{lang}wcf.acp.customOption.name.error.{@$errorType}{/lang}
					{/if}
				</small>
			{/if}
		</dd>
	</dl>
	{include file='shared_multipleLanguageInputJavascript' elementIdentifier='optionTitle' forceSelection=false}
	
	<dl{if $errorField == 'optionDescription'} class="formError"{/if}>
		<dt><label for="optionDescription">{lang}wcf.global.description{/lang}</label></dt>
		<dd>
			<textarea name="optionDescription" id="optionDescription" cols="40" rows="10">{$i18nPlainValues[optionDescription]}</textarea>
			{if $errorField == 'optionDescription'}
				<small class="innerError">
					{if $errorType == 'empty'}
						{lang}wcf.global.form.error.empty{/lang}
					{else}
						{lang}wcf.acp.customOption.description.error.{@$errorType}{/lang}
					{/if}
				</small>
			{/if}
		</dd>
	</dl>
	{include file='shared_multipleLanguageInputJavascript' elementIdentifier='optionDescription' forceSelection=false}
	
	<dl>
		<dt><label for="showOrder">{lang}wcf.acp.customOption.showOrder{/lang}</label></dt>
		<dd>
			<input type="number" id="showOrder" name="showOrder" value="{$showOrder}" class="short">
		</dd>
	</dl>
	
	<dl>
		<dt></dt>
		<dd>
			<label><input type="checkbox" id="isDisabled" name="isDisabled" value="1"{if $isDisabled} checked{/if}> {lang}wcf.acp.customOption.isDisabled{/lang}</label>
		</dd>
	</dl>
	
	{event name='dataFields'}
</div>

<section class="section">
	<h2 class="sectionTitle">{lang}wcf.acp.customOption.typeData{/lang}</h2>
	
	<dl{if $errorField == 'optionType'} class="formError"{/if}>
		<dt><label for="optionType">{lang}wcf.acp.customOption.optionType{/lang}</label></dt>
		<dd>
			<select name="optionType" id="optionType">
				{foreach from=$availableOptionTypes item=availableOptionType}
					<option value="{$availableOptionType}"{if $availableOptionType == $optionType} selected{/if}>{lang}wcf.acp.customOption.optionType.{$availableOptionType}{/lang}</option>
				{/foreach}
			</select>
			{if $errorField == 'optionType'}
				<small class="innerError">
					{if $errorType == 'empty'}
						{lang}wcf.global.form.error.empty{/lang}
					{else}
						{lang}wcf.acp.customOption.optionType.error.{@$errorType}{/lang}
					{/if}
				</small>
			{/if}
		</dd>
	</dl>
	
	<dl>
		<dt><label for="defaultValue">{lang}wcf.acp.customOption.defaultValue{/lang}</label></dt>
		<dd>
			<input type="text" id="defaultValue" name="defaultValue" value="{$defaultValue}" class="long">
			<small>{lang}wcf.acp.customOption.defaultValue.description{/lang}</small>
		</dd>
	</dl>
	
	<dl id="selectOptionsDL"{if $errorField == 'selectOptions'} class="formError"{/if}>
		<dt><label for="selectOptions">{lang}wcf.acp.customOption.selectOptions{/lang}</label></dt>
		<dd>
			<textarea name="selectOptions" id="selectOptions" cols="40" rows="10">{$selectOptions}</textarea>
			{if $errorField == 'selectOptions'}
				<small class="innerError">
					{if $errorType == 'empty'}
						{lang}wcf.global.form.error.empty{/lang}
					{else}
						{lang}wcf.acp.customOption.selectOptions.error.{@$errorType}{/lang}
					{/if}
				</small>
			{/if}
			<small>{lang}wcf.acp.customOption.selectOptions.description{/lang}</small>
		</dd>
	</dl>
	
	<dl id="validationPatternDL"{if $errorField == 'validationPattern'} class="formError"{/if}>
		<dt><label for="validationPattern">{lang}wcf.acp.customOption.validationPattern{/lang}</label></dt>
		<dd>
			<input type="text" id="validationPattern" name="validationPattern" value="{$validationPattern}" class="long">
			{if $errorField == 'validationPattern'}
				<small class="innerError">
					{if $errorType == 'empty'}
						{lang}wcf.global.form.error.empty{/lang}
					{else}
						{lang}wcf.acp.customOption.validationPattern.error.{@$errorType}{/lang}
					{/if}
				</small>
			{/if}
			<small>{lang}wcf.acp.customOption.validationPattern.description{/lang}</small>
		</dd>
	</dl>
	
	<dl>
		<dt></dt>
		<dd>
			<label><input type="checkbox" name="required" id="required" value="1"{if $required == 1} checked{/if}> {lang}wcf.acp.customOption.required{/lang}</label>
		</dd>
	</dl>
	
	{event name='typeDataFields'}
</section>
