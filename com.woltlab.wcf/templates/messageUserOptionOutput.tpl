{hascontent}
	<div id="{$option->optionName}" class="htmlContent">
		{content}
			{unsafe:$value}
		{/content}
	</div>
	
	<script data-relocate="true">
		document.getElementById('{unsafe:$option->optionName|encodeJS}')?.closest('dl')?.classList.add('wide');
	</script>
{/hascontent}
