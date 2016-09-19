{hascontent}
	<div id="{$option->optionName}" class="htmlContent">
		{content}
			{@$value}
		{/content}
	</div>
	
	<script data-relocate="true">
	$(function() {
		$('#{$option->optionName}').parents('dl:eq(0)').addClass('wide');
	});
	</script>
{/hascontent}