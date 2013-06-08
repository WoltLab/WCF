<dl class="wide jsPollVote" data-max-votes="{@$poll->maxVotes}">
	{foreach from=$poll->getOptions() item=option}
		<dd>
			<label>
				{if $poll->canVote()}<input type="{if $poll->maxVotes > 1}checkbox{else}radio{/if}" name="pollOptions{@$poll->pollID}[]" value="{$option->optionValue}" data-option-id="{@$option->optionID}"{if $option->voted} checked="checked"{/if} />{/if}
				{$option->optionValue}
			</label>
		</dd>
	{/foreach}
</dl>
{if $poll->canVote()}
	{if $poll->maxVotes > 1}<small>{lang}wcf.poll.multipleVotes{/lang}</small>{/if}
{else}
	<p class="info">{lang}wcf.poll.restrictedResult{/lang}</p>
{/if}