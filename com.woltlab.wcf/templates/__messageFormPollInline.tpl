{if $__showPoll|isset && $__showPoll}
	<script data-relocate="true">
		require(["WoltLabSuite/Core/Ui/Poll/Editor"], (UiPollEditor) => {
			{jsphrase name='wcf.poll.button.addOption'}
			{jsphrase name='wcf.poll.button.removeOption'}
			{jsphrase name='wcf.poll.endTime.error.invalid'}
			{jsphrase name='wcf.poll.maxVotes.error.invalid'}
			
			new UiPollEditor(
				"pollOptionContainer_{$wysiwygSelector}",
				[ {implode from=$pollOptions item=pollOption}{ optionID: {@$pollOption[optionID]}, optionValue: '{@$pollOption[optionValue]|encodeJS}' }{/implode} ],
				"{$wysiwygSelector}",
				{
					isAjax: true,
					maxOptions: {POLL_MAX_OPTIONS}
				}
			);
		});
	</script>
	
	<div class="jsOnly messageTabMenuContent">
		<dl>
			<dt>
				<label for="{$wysiwygSelector}Poll_question">{lang}wcf.poll.question{/lang}</label>
			</dt>
			<dd>
				<input type="text" name="pollQuestion" id="{$wysiwygSelector}pollQuestion" value="{$pollQuestion}" class="long" maxlength="255">
			</dd>
			<dt>
				<label>{lang}wcf.poll.options{/lang}</label>
			</dt>
			<dd id="pollOptionContainer_{$wysiwygSelector}" class="pollOptionContainer sortableListContainer">
				<ol class="sortableList"></ol>
				<small>{lang}wcf.poll.options.description{/lang}</small>
			</dd>
		</dl>
		<dl>
			<dt>
				<label for="{$wysiwygSelector}Poll_endTime">{lang}wcf.poll.endTime{/lang}</label>
			</dt>
			<dd>
				<input type="datetime" tabindex="-1" name="pollEndTime" id="{$wysiwygSelector}pollEndTime" value="{if $pollEndTime}{@$pollEndTime|date:'c'}{/if}" class="medium">
			</dd>
		</dl>
		<dl>
			<dt>
				<label for="{$wysiwygSelector}Poll_maxVotes">{lang}wcf.poll.maxVotes{/lang}</label>
			</dt>
			<dd>
				<input type="number" name="pollMaxVotes" id="{$wysiwygSelector}pollMaxVotes" value="{$pollMaxVotes}" min="1" class="tiny">
			</dd>
		</dl>
		<dl>
			<dt></dt>
			<dd>
				<label><input type="checkbox" name="pollIsChangeable" id="{$wysiwygSelector}pollIsChangeable" value="1"{if $pollIsChangeable} checked{/if}> {lang}wcf.poll.isChangeable{/lang}</label>
			</dd>
			{if $pollID || $__wcf->getPollManager()->canStartPublicPoll()}
				<dd>
					<label><input type="checkbox" name="pollIsPublic" id="{$wysiwygSelector}pollIsPublic" value="1"{if $pollIsPublic} checked{/if} {if $pollID}disabled{/if}> {lang}wcf.poll.isPublic{/lang}</label>
				</dd>
			{/if}
			<dd>
				<label><input type="checkbox" name="pollResultsRequireVote" id="{$wysiwygSelector}pollResultsRequireVote" value="1"{if $pollResultsRequireVote} checked{/if}> {lang}wcf.poll.resultsRequireVote{/lang}</label>
				<small>{lang}wcf.poll.resultsRequireVote.description{/lang}</small>
			</dd>
			<dd>
				<label><input type="checkbox" name="pollSortByVotes" id="{$wysiwygSelector}pollSortByVotes" value="1"{if $pollSortByVotes} checked{/if}> {lang}wcf.poll.sortByVotes{/lang}</label>
			</dd>
		</dl>
		
		{event name='fields'}
	</div>
{/if}
