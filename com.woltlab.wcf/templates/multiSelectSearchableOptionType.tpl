<label><input type="checkbox" id="search_{$option->optionName}" name="searchOptions[{$option->optionName}]"{if $searchOption} checked{/if}> {lang}wcf.user.option.searchRadioButtonOption{/lang}</label>
<select id="{$option->optionName}" name="values[{$option->optionName}][]" multiple size="{if $selectOptions|count > 10}10{else}{@$selectOptions|count}{/if}"{if !$searchOption} disabled{/if}>
	{foreach from=$selectOptions key=key item=selectOption}
		<option value="{$key}"{if $key|in_array:$value} selected{/if}>{lang}{@$selectOption}{/lang}</option>
	{/foreach}
</select>

<script data-relocate="true">
//<![CDATA[
$(function() {
	$('#search_{$option->optionName}').change(function(event) {
		if ($(event.currentTarget).prop('checked')) {
			$('#{$option->optionName}').enable();
		}
		else {
			$('#{$option->optionName}').disable();
		}
	});
});
//]]>
</script>
