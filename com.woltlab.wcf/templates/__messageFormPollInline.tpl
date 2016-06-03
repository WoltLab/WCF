{if $__showPoll|isset && $__showPoll}
	<script data-relocate="true">
		//<![CDATA[
		$(function() {
			WCF.Language.addObject({
				'wcf.poll.button.addOption': '{lang}wcf.poll.button.addOption{/lang}',
				'wcf.poll.button.removeOption': '{lang}wcf.poll.button.removeOption{/lang}'
			});
			
			new WCF.Poll.Management(
				'pollOptionContainer_{$wysiwygSelector}',
				[ {implode from=$pollOptions item=pollOption}{ optionID: {@$pollOption[optionID]}, optionValue: '{$pollOption[optionValue]|encodeJS}' }{/implode} ],
				{@POLL_MAX_OPTIONS},
				'{$wysiwygSelector}'
			);
		});
		//]]>
	</script>
	
	<div class="jsOnly messageTabMenuContent">
		<dl>
			<dt>
				<label for="pollQuestion_{$wysiwygSelector}">{lang}wcf.poll.question{/lang}</label>
			</dt>
			<dd>
				<input type="text" name="pollQuestion" id="pollQuestion_{$wysiwygSelector}" value="{$pollQuestion}" class="long" maxlength="255">
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
				<label for="pollEndTime_{$wysiwygSelector}">{lang}wcf.poll.endTime{/lang}</label>
			</dt>
			<dd>
				<input type="datetime" name="pollEndTime" id="pollEndTime_{$wysiwygSelector}" value="{if $pollEndTime}{@$pollEndTime|date:'c'}{/if}" class="medium" data-ignore-timezone="true">
			</dd>
		</dl>
		<dl>
			<dt>
				<label for="pollMaxVotes_{$wysiwygSelector}">{lang}wcf.poll.maxVotes{/lang}</label>
			</dt>
			<dd>
				<input type="number" name="pollMaxVotes" id="pollMaxVotes_{$wysiwygSelector}" value="{@$pollMaxVotes}" min="1" class="tiny">
			</dd>
		</dl>
		<dl>
			<dt></dt>
			<dd>
				<label><input type="checkbox" name="pollIsChangeable" value="1"{if $pollIsChangeable} checked{/if}> {lang}wcf.poll.isChangeable{/lang}</label>
			</dd>
			{if !$pollID && $__wcf->getPollManager()->canStartPublicPoll()}
				<dd>
					<label><input type="checkbox" name="pollIsPublic" value="1"{if $pollIsPublic} checked{/if}> {lang}wcf.poll.isPublic{/lang}</label>
				</dd>
			{/if}
			<dd>
				<label><input type="checkbox" name="pollResultsRequireVote" value="1"{if $pollResultsRequireVote} checked{/if}> {lang}wcf.poll.resultsRequireVote{/lang}</label>
				<small>{lang}wcf.poll.resultsRequireVote.description{/lang}</small>
			</dd>
			<dd>
				<label><input type="checkbox" name="pollSortByVotes" value="1"{if $pollSortByVotes} checked{/if}> {lang}wcf.poll.sortByVotes{/lang}</label>
			</dd>
		</dl>
		
		{event name='fields'}
	</div>
{/if}