<textarea id="{$option->optionName}" name="values[{$option->optionName}]" cols="40" rows="10"{if $option->required} required{/if}>{$value}</textarea>
{include file='wysiwyg' wysiwygSelector=$option->optionName}

<script data-relocate="true">
//<![CDATA[
$(function() {
	$('#{$option->optionName}').parents('dl:eq(0)').addClass('wide');
});
//]]>
</script>