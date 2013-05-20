<textarea id="{$option->optionName}" name="values[{$option->optionName}]" cols="40" rows="10">{$value}</textarea>
{include file='wysiwyg' wysiwygSelector=$option->optionName}

<script type="text/javascript">
//<![CDATA[
$(function() {
	$('#{$option->optionName}').parents('dl:eq(0)').addClass('wide');
});
//]]>
</script>