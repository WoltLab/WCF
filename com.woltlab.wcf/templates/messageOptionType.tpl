<textarea id="{$option->optionName}" name="values[{$option->optionName}]" cols="40" rows="10" class="wysiwygTextarea" data-disable-attachments="true">{$value}</textarea>
{include file='shared_wysiwyg' wysiwygSelector=$option->optionName}

<script data-relocate="true">
	{
		const textarea = document.getElementById('{unsafe:$option->optionName|encodeJS}');
		if (textarea) {
			const dl = textarea.closest('dl');
			if (dl) {
				dl.classList.add('wide');
			}
		}
	}
</script>
