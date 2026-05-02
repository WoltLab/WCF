<button
	type="button"
	class="button small{if !$reactionTypeID} active{/if}"
	data-filter-reaction-type-id="0"
	data-list-view-id="{$view->getID()}"
>
	<span>{lang}wcf.like.reaction.all{/lang}</span>
	<span class="badge">{#$totalCount}</span>
</button>

{foreach from=$reactionTypes item='reactionType'}
	<button
		type="button"
		class="button small{if $reactionType->reactionTypeID === $reactionTypeID} active{/if}"
		data-filter-reaction-type-id="{$reactionType->reactionTypeID}"
		data-list-view-id="{$view->getID()}"
	>
		{unsafe:$reactionType->renderIcon()}
		<span>{$reactionType->getTitle()}</span>
		<span class="badge">{#$reactionCounts[$reactionType->reactionTypeID]}</span>
	</button>
{/foreach}

<script data-relocate="true">
	require(['WoltLabSuite/Core/Component/Reaction/SummaryDetailsFilterButtons'], ({ setup }) => {
		setup('{unsafe:$view->getID()|encodeJS}');
	});
</script>
