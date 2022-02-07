{if ($__wcf->getUser()->userID || $poll->canSeeResult() || $poll->canViewParticipants()) && !$__pollLoadedJavaScript|isset}
<script data-relocate="true">
	require(['WoltLabSuite/Core/Ui/Poll/Manager/Manager'], function({ Manager }) {
		new Manager(
			{@$poll->pollID},
			{if $poll->canSeeResult()}true{else}false{/if},
			{if $poll->canVote()}true{else}false{/if},
			{if $poll->isPublic}true{else}false{/if},
			{@$poll->maxVotes},
			"{$poll->question}"
		);
	});
	</script>
{/if}

<div id="poll{@$poll->pollID}" class="pollContainer{if POLL_FULL_WIDTH} pollContainerFullWidth{/if}" data-poll-id="{@$poll->pollID}" data-can-vote="{if $poll->canVote()}1{else}0{/if}" data-can-view-result="{if $poll->canSeeResult()}1{else}0{/if}" data-can-view-participants="{if $poll->canViewParticipants()}true{else}false{/if}" data-in-vote="{if $poll->canVote() && !$poll->isParticipant()}1{else}0{/if}" data-question="{$poll->question}" data-max-votes="{@$poll->maxVotes}" data-is-public="{if $poll->isPublic}true{else}false{/if}">
	<section>
		<h2>{$poll->question} <span class="badge jsTooltip jsPollTotalVotes" title="{lang}wcf.poll.totalVotes{/lang}">{#$poll->votes}</span></h2>

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
		<div class="formSubmit jsOnly"{if !$poll->canVote() && $__pollView === 'result' && !$poll->canSeeResult()} style="display: none"{/if}>
			{content}
				{if $__wcf->getUser()->userID}
					<button class="small votePollButton"{if $poll->canVote() && $__pollView === 'vote'} disabled{else} hidden{/if}>{lang}wcf.poll.button.vote{/lang}</button>
					<button class="small showVoteFormButton"{if $__pollView === 'vote' || !$poll->canVote()} hidden{/if}>{lang}wcf.poll.button.showVote{/lang}</button>
					<button class="small showResultsButton"{if $__pollView === 'result' || !$poll->canSeeResult()} hidden{/if}>{lang}wcf.poll.button.showResult{/lang}</button>
				{/if}
				{if $poll->canViewParticipants() || ($poll->canVote() && $poll->isPublic)}
					<button class="small showPollParticipantsButton"{if $__pollView === 'vote' || !$poll->canSeeResult()} hidden{/if}>{lang}wcf.poll.button.showParticipants{/lang}</button>
				{/if}

				{event name='pollButtons'}
			{/content}
		</div>
	{/hascontent}
</div>
