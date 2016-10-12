<label><input type="checkbox" id="search_{$option->optionName}" name="searchOptions[{$option->optionName}]"{if $searchOption} checked{/if}> {lang}wcf.user.option.searchRadioButtonOption{/lang}</label>
{foreach from=$selectOptions key=key item=selectOption}
	<label><input type="checkbox" name="values[{$option->optionName}][]" value="{$key}" {if $key|in_array:$value} checked{/if}{if !$searchOption} disabled{/if}> {lang}{@$selectOption}{/lang}</label>
{/foreach}

<script data-relocate="true">
	$(function() {
		$('#search_{$option->optionName}').change(function(event) {
			if ($(event.currentTarget).prop('checked')) {
				$('input[name="values[{$option->optionName}][]"]').enable();
			}
			else {
				$('input[name="values[{$option->optionName}][]"]').disable();
			}
		});
	});
</script>
