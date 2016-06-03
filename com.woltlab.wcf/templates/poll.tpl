{if ($__wcf->getUser()->userID || $poll->canSeeResult() || $poll->canViewParticipants()) && !$__pollLoadedJavaScript|isset}
	{assign var=__pollLoadedJavaScript value=true}
	<script data-relocate="true">
		//<![CDATA[
		$(function() {
			new WCF.Poll.Manager('.pollContainer');
		});
		//]]>
	</script>
{/if}

<div class="pollContainer" data-poll-id="{@$poll->pollID}" data-can-vote="{if $poll->canVote()}1{else}0{/if}" data-can-view-result="{if $poll->canSeeResult()}1{else}0{/if}" data-can-view-participants="{if $poll->canViewParticipants()}true{else}false{/if}" data-in-vote="{if $poll->canVote() && !$poll->isParticipant()}1{else}0{/if}" data-question="{$poll->question}" data-max-votes="{@$poll->maxVotes}">
	<section>
		<h2>{$poll->question} <span class="badge jsTooltip" title="{lang}wcf.poll.totalVotes{/lang}">{#$poll->votes}</span></h2>
		
		<div class="pollInnerContainer">
			{if !$__wcf->getUser()->userID}
				{if $poll->canSeeResult()}
					{assign var='__pollView' value='result'}
					{include file='pollResult'}
				{else}
					{assign var='__pollView' value='vote'}
					{include file='pollVote'}
				{/if}
			{else}
				{if $poll->canVote() && !$poll->isParticipant()}
					{assign var='__pollView' value='vote'}
					{include file='pollVote'}
				{else}
					{assign var='__pollView' value='result'}
					{include file='pollResult'}
				{/if}
			{/if}
			
			{event name='pollData'}
		</div>
	</section>
	
	{hascontent}
		<div class="formSubmit jsOnly"{if !$poll->canVote() && $__pollView === 'result'} style="display: none"{/if}>
			{content}
				{if $__wcf->getUser()->userID}
					<button class="small jsButtonPollVote"{if $poll->canVote()} disabled{else} style="display: none;"{/if}>{lang}wcf.poll.button.vote{/lang}</button>
					<button class="small jsButtonPollShowVote"{if $__pollView === 'vote'} style="display: none;"{/if}>{lang}wcf.poll.button.showVote{/lang}</button>
					<button class="small jsButtonPollShowResult"{if $__pollView === 'result'} style="display: none;"{/if}>{lang}wcf.poll.button.showResult{/lang}</button>
				{/if}
				{if $poll->canViewParticipants()}
					<button class="small jsButtonPollShowParticipants"{if $__pollView === 'vote' || !$poll->canVote()} style="display: none"{/if}>{lang}wcf.poll.button.showParticipants{/lang}</button>
				{/if}
				
				{event name='pollButtons'}
			{/content}
		</div>
	{/hascontent}
</div>