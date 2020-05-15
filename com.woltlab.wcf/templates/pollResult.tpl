<ol class="pollResultList">
	{foreach from=$poll->getOptions(true) item=option}
		<li class="pollResultItem">
			<div class="pollResultItemCaption">
				<span class="pollOptionName">{$option->optionValue} ({#$option->votes})</span>
				<span class="pollOptionRelativeValue">{@$option->getRelativeVotes($poll)}%</span>
			</div>
			<div class="pollMeter">
				<div class="pollMeterValue" style="width: {if $option->getRelativeVotes($poll)}{@$option->getRelativeVotes($poll)}%{else}0{/if}"></div>
			</div>
		</li>
	{/foreach}
</ol>

{if $poll->endTime && !$poll->isFinished()}
	<p><small>{lang}wcf.poll.endTimeInfo{/lang}</small></p>
{/if}
