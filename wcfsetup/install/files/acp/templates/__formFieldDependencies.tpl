{if !$field->getDependencies()|empty}
	<script data-relocate="true">
		{foreach from=$field->getDependencies() item=dependency}
			{@$dependency->getHtml()}
		{/foreach}
	</script>
{/if}
