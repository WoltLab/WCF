{hascontent}
	<div id="{$option->optionName}" class="htmlContent">
		{content}
			{unsafe:$value}
		{/content}
	</div>
	
	<script data-relocate="true">
		const element = document.getElementById('{unsafe:$option->optionName|encodeJS}');
		if (element) {
			const dl = element.closest('dl');
			if (dl) {
				dl.classList.add('wide');
			}
		}
	</script>
{/hascontent}
