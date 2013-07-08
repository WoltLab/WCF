{hascontent}
	<div id="{$option->optionName}">
		{content}
			{@$value}
		{/content}
	</div>
	
	<script>
	//<![CDATA[
	$(function() {
		$('#{$option->optionName}').parents('dl:eq(0)').addClass('wide');
	});
	//]]>
	</script>
{/hascontent}