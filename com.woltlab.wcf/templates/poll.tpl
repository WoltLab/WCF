{if ($__wcf->getUser()->userID || $poll->canSeeResult() || $poll->canViewParticipants()) && !$__pollLoadedJavaScript|isset}
	{assign var=__pollLoadedJavaScript value=true}
	<script data-relocate="true" src="{@$__wcf->getPath()}js/WCF.Poll{if !ENABLE_DEBUG_MODE}.min{/if}.js?v={@$__wcfVersion}"></script>
	<script data-relocate="true">
		//<![CDATA[
		$(function() {
			new WCF.Poll.Manager('.pollContainer');
		});
		//]]>
	</script>
{/if}

<div class="container containerPadding pollContainer" data-poll-id="{@$poll->pollID}" data-can-vote="{if $poll->canVote()}1{else}0{/if}" data-can-view-result="{if $poll->canSeeResult()}1{else}0{/if}" data-can-view-participants="{if $poll->canViewParticipants()}true{else}false{/if}" data-in-vote="{if $poll->canVote() && !$poll->isParticipant()}1{else}0{/if}" data-question="{$poll->question}" data-max-votes="{@$poll->maxVotes}">
	<fieldset>
		<legend>{$poll->question} <span class="badge jsTooltip" title="{lang}wcf.poll.totalVotes{/lang}">{#$poll->votes}</span></legend>
		
		<div class="pollInnerContainer">
			{if !$__wcf->getUser()->userID}
				{if $poll->canSeeResult()}
					{include file='pollResult'}
				{else}
					{include file='pollVote'}
				{/if}
			{else}
				{if $poll->canVote() && !$poll->isParticipant()}
					{include file='pollVote'}
				{else}
					{include file='pollResult'}
				{/if}
			{/if}
			
			{event name='pollData'}
		</div>
	</fieldset>
	
	{hascontent}
		<div class="formSubmit jsOnly">
			{content}
				{if $__wcf->getUser()->userID}
					<button class="small jsButtonPollVote">{lang}wcf.poll.button.vote{/lang}</button>
					<button class="small jsButtonPollShowVote">{lang}wcf.poll.button.showVote{/lang}</button>
					<button class="small jsButtonPollShowResult">{lang}wcf.poll.button.showResult{/lang}</button>
				{/if}
				{if $poll->canViewParticipants()}
					<button class="small jsButtonPollShowParticipants">{lang}wcf.poll.button.showParticipants{/lang}</button>
				{/if}
				
				{event name='pollButtons'}
			{/content}
		</div>
	{/hascontent}
</div>