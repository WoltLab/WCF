{if !$field->getDependencies()|empty}
	<script data-relocate="true">
		{foreach from=$field->getDependencies() item=dependency}
			{unsafe:$dependency->getHtml()}
		{/foreach}
	</script>
{/if}
