{if !$container->getDependencies()|empty}
	<script data-relocate="true">
		{foreach from=$container->getDependencies() item=dependency}
			{@$dependency->getHtml()}
		{/foreach}
	</script>
{/if}
