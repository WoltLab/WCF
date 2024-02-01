<label><input type="checkbox" id="search_{$option->optionName}" name="searchOptions[{$option->optionName}]"{if $searchOption} checked{/if}> {lang}wcf.user.option.searchRadioButtonOption{/lang}</label>
{foreach from=$selectOptions key=key item=selectOption}
	<label><input type="radio" name="values[{$option->optionName}]" value="{$key}" {if $value == $key} checked{/if} {if $disableOptions[$key]|isset || $enableOptions[$key]|isset}class="jsEnablesOptions" data-disable-options="[ {@$disableOptions[$key]}]" data-enable-options="[ {@$enableOptions[$key]}]"{/if}{if !$searchOption} disabled{/if}> {lang}{@$selectOption}{/lang}</label>
{/foreach}

<script data-relocate="true">
$(function() {
	$('#search_{$option->optionName}').change(function(event) {
		if ($(event.currentTarget).prop('checked')) {
			$('input[name="values[{$option->optionName}]"]').enable();
		}
		else {
			$('input[name="values[{$option->optionName}]"]').disable();
		}
	});
});
</script>
