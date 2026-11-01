
{assign var='topReactionsSummary' value=$__wcf->getReactionHandler()->getTopReactionsSummary($cachedReactions)}
{if $topReactionsSummary}
	<div class="topReactionsSummary" role="img" aria-label="{lang count=$topReactionsSummary[count] reactions=$topReactionsSummary[reactions]}wcf.reactions.topReactionsSummary{/lang}">
		<div class="topReactionsSummary__reactions">
			{foreach from=$topReactionsSummary[reactions] item='topReaction'}
				{unsafe:$topReaction->renderIcon()}
			{/foreach}
		</div>
		<span class="topReactionsSummary__count" aria-hidden="true">{$topReactionsSummary[count]|shortUnit}</span>
	</div>
{/if}
