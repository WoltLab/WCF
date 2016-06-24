<label><input type="checkbox" id="search_{$option->optionName}" name="searchOptions[{$option->optionName}]"{if $searchOption} checked{/if}> {lang}wcf.user.option.searchTextOption{/lang}</label>
<textarea id="{$option->optionName}" name="values[{$option->optionName}]"{if !$searchOption} disabled{/if} cols="40" rows="10">{$value}</textarea>

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
