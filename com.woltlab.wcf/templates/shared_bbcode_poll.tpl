<section class="pollWidget" id="{$elementID}" data-interaction-container aria-labelledby="{$elementID}_question"{*
	*} data-poll-id="{$poll->pollID}"{*
	*} data-state="{$initialState}"{*
	*} data-can-vote="{if $poll->canVote()}true{else}false{/if}"{*
	*} data-max-votes="{$poll->maxVotes}"{*
	*} data-required-votes="{$poll->getRequiredVotes()}"{*
	*} data-question="{$poll->getQuestion()}">
	<header class="pollWidget__header">
		<div class="pollWidget__eyebrow">
			{icon name='chart-simple' type='solid'}
			<span class="pollWidget__label">{lang}wcf.poll.widget.label{/lang}</span>
			<span class="pollWidget__separator" aria-hidden="true"></span>
			<span class="pollWidget__totalVotes">{lang}wcf.poll.widget.votes{/lang}</span>
			{unsafe:$interactionButton}
		</div>

		<h3 class="pollWidget__question" id="{$elementID}_question" tabindex="-1">{$poll->getQuestion()}</h3>

		{if $poll->canVote()}
			{if $poll->maxVotes > 1}
				<div class="pollWidget__hint pollWidget__hint--vote">
					{if $poll->getRequiredVotes() == $poll->maxVotes}
						{lang}wcf.poll.widget.hint.exactly{/lang}
					{elseif $poll->getRequiredVotes() > 1}
						{lang}wcf.poll.widget.hint.range{/lang}
					{else}
						{lang}wcf.poll.widget.hint.multiple{/lang}
					{/if}
				</div>
			{/if}
		{elseif !$poll->canSeeResult() && !$poll->resultsRequireEnd}
			<div class="pollWidget__hint pollWidget__hint--vote">{lang}wcf.poll.restrictedResult{/lang}</div>
		{/if}
		{if $poll->isParticipant()}
			{if $poll->canSeeResult()}
				<div class="pollWidget__hint pollWidget__hint--results">{lang}wcf.poll.widget.hint.voted{/lang}</div>
			{else}
				{* Participants remain in the vote state while the results are hidden until the end. *}
				<div class="pollWidget__hint">{lang}wcf.poll.widget.hint.voted{/lang}</div>
			{/if}
		{/if}
		{if $poll->resultsRequireEnd && !$poll->isFinished()}
			<div class="pollWidget__hint">{lang}wcf.poll.widget.hint.resultsAfterEnd{/lang}</div>
		{/if}
	</header>

	<div class="pollWidget__options">
		{foreach from=$options item=option}
			<label class="pollWidget__option{if $poll->canSeeResult() && $leadingVotes && $option->votes == $leadingVotes} pollWidget__option--leading{/if}"{*
				*}{if $poll->canSeeResult()} style="--pollWidget-percent: {$option->getRelativeVotes($poll)}%; --pollWidget-order: {$resultPositions[$option->optionID]}"{/if}>
				<input type="{if $poll->maxVotes > 1}checkbox{else}radio{/if}" class="pollWidget__input visuallyHidden" name="{$elementID}" value="{$option->optionID}"{if $option->voted} checked{/if}{if !$poll->canVote()} disabled{/if}>
				<span class="pollWidget__indicator" aria-hidden="true"></span>
				{if $poll->canSeeResult()}
					<span class="pollWidget__bar" aria-hidden="true"></span>
				{/if}
				<span class="pollWidget__text">{$option->getOptionValue()}</span>
				{if $option->voted}
					<span class="pollWidget__ownVote" title="{lang}wcf.poll.widget.ownVote{/lang}" role="img" aria-label="{lang}wcf.poll.widget.ownVote{/lang}"></span>
				{/if}
				{if $poll->canSeeResult()}
					<span class="pollWidget__value" aria-hidden="true">
						<span class="pollWidget__count">{#$option->votes}</span>
						<span class="pollWidget__percent">{#$option->getRelativeVotes($poll)} %</span>
					</span>
					<span class="pollWidget__resultLabel visuallyHidden">{lang}wcf.poll.widget.optionResult{/lang}</span>
				{/if}
			</label>
		{/foreach}
	</div>

	<footer class="pollWidget__footer">
		{hascontent}
			<div class="pollWidget__meta">
				{content}
					{if $poll->endTime}
						<div class="pollWidget__metaItem">
							{icon name='clock'}
							<span>
								{if $poll->isFinished()}
									{lang}wcf.poll.widget.ended{/lang}
								{else}
									{lang}wcf.poll.widget.endTime{/lang}
								{/if}
							</span>
						</div>
					{/if}
					{if $poll->isPublic}
						<div class="pollWidget__metaItem">
							{icon name='eye'}
							<span>{lang}wcf.poll.isPublic{/lang}</span>
						</div>
					{/if}

					{event name='meta'}
				{/content}
			</div>
		{/hascontent}

		{hascontent}
			<div class="pollWidget__actions pollWidget__actions--vote">
				{content}
					{if $poll->canVote()}
						<button type="button" class="button buttonPrimary pollWidget__vote" disabled>
							{lang}wcf.poll.button.vote{/lang}
						</button>
					{/if}
					{if $poll->canSeeResult()}
						<button type="button" class="pollWidget__link pollWidget__showResults">
							{lang}wcf.poll.button.showResult{/lang}
						</button>
					{/if}
				{/content}
			</div>
		{/hascontent}

		{hascontent}
			<div class="pollWidget__actions pollWidget__actions--results">
				{content}
					{if $poll->canVote()}
						<button type="button" class="button buttonPrimary pollWidget__showVote">
							{if $poll->isParticipant()}
								{lang}wcf.poll.button.changeVote{/lang}
							{else}
								{lang}wcf.poll.button.showVote{/lang}
							{/if}
						</button>
					{/if}
					{if $poll->canViewParticipants()}
						<button type="button" class="pollWidget__link pollWidget__showParticipants">
							{lang}wcf.poll.button.showParticipants{/lang}
						</button>
					{/if}
				{/content}
			</div>
		{/hascontent}

		{event name='footer'}
	</footer>

	{unsafe:$interactionInitialization}
</section>
