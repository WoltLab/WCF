<div id="reactionList" class="recentActivityList recentActivityList--userProfileContent userProfileReactionList"
	data-last-like-time="{$lastLikeTime}"
	data-user-id="{$userID}"
	data-target-type="received"
	data-reaction-type-id="0"
>
	<div class="userProfileReactionList__typeSelection">
		<ul class="buttonGroup">
			<li>
				<button type="button" class="button small active" data-target-type="received">
					{lang}wcf.like.reactionsReceived{/lang}
				</button>
			</li>
			<li>
				<button type="button" class="button small" data-target-type="given">
					{lang}wcf.like.reactionsGiven{/lang}
				</button>
			</li>
		</ul>

		{if $__wcf->getReactionHandler()->getReactionTypes()|count > 1}
			<ul class="buttonGroup">
				{foreach from=$__wcf->getReactionHandler()->getReactionTypes() item=reactionType name=reactionTypeLoop}
					<li>
						<button
							type="button"
							class="button small jsTooltip"
							data-reaction-type-id="{$reactionType->reactionTypeID}"
							title="{$reactionType->getTitle()}"
							data-is-assignable="{if $reactionType->isAssignable}1{else}0{/if}"
						>
							{unsafe:$reactionType->renderIcon()}
						</button>
					</li>
				{/foreach}
			</ul>
		{/if}
	</div>

	{include file='userProfileLikeItem'}
</div>

<script data-relocate="true">
	require(['WoltLabSuite/Core/Component/User/Reaction/Loader'], ({ setup }) => {
		{jsphrase name='wcf.like.reaction.noMoreEntries'}
		{jsphrase name='wcf.like.reaction.more'}

		setup(document.getElementById('reactionList'));
	});
</script>
