{if !$button->getDependencies()|empty}
	<script data-relocate="true">
		{foreach from=$button->getDependencies() item=dependency}
			{@$dependency->getHtml()}
		{/foreach}
	</script>
{/if}
