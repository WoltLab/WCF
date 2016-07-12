<textarea id="{$option->optionName}" name="values[{$option->optionName}]" {if $option->required}required="required"{/if} cols="40" rows="10">{$value}</textarea>
{include file='wysiwyg' wysiwygSelector=$option->optionName}

<script data-relocate="true">
//<![CDATA[
$(function() {
	$('#{$option->optionName}').parents('dl:eq(0)').addClass('wide');
});
//]]>
</script>