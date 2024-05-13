<textarea id="{$option->optionName}" name="values[{$option->optionName}]" cols="40" rows="10" class="wysiwygTextarea" data-disable-attachments="true">{$value}</textarea>
{include file='shared_wysiwyg' wysiwygSelector=$option->optionName}

<script data-relocate="true">
$(function() {
	$('#{$option->optionName}').parents('dl:eq(0)').addClass('wide');
});
</script>
