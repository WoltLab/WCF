{assign var='topReaction' value=$__wcf->getReactionHandler()->getTopReaction($cachedReactions)}
{if $topReaction}
	{if $render === 'tiny'}
		<span class="topReactionTiny jsTooltip" role="img" title="{lang reaction=$topReaction[reaction] count=$topReaction[count] other=$topReaction[other]}wcf.like.reaction.topReaction{/lang}">
			{unsafe:$topReaction[reaction]->renderIcon()}
			<span class="reactionCount" aria-hidden="true">{$topReaction[count]|shortUnit}</span>
		</span>
	{elseif $render === 'short'}
		<span class="topReactionShort jsTooltip" role="img" title="{lang reaction=$topReaction[reaction] count=$topReaction[count] other=$topReaction[other]}wcf.like.reaction.topReaction{/lang}">
			{unsafe:$topReaction[reaction]->renderIcon()}
			<span class="reactionCount" aria-hidden="true">{$topReaction[count]|shortUnit}</span>
		</span>
	{elseif $render === 'full'}
		<span class="topReactionFull">
			{unsafe:$topReaction[reaction]->renderIcon()} {lang reaction=$topReaction[reaction] count=$topReaction[count] other=$topReaction[other]}wcf.like.reaction.topReaction{/lang}
		</span>
	{/if}
{/if}
