<ol class="sortableList"></ol>

<script data-relocate="true">
	require(['Dom/Traverse', 'Dom/Util', 'Language', 'WoltLabSuite/Core/Ui/Poll/Editor'], function(DomTraverse, DomUtil, Language, UiPollEditor) {
		Language.addObject({
			'wcf.poll.button.addOption': '{lang}wcf.poll.button.addOption{/lang}',
			'wcf.poll.button.removeOption': '{lang}wcf.poll.button.removeOption{/lang}',
			'wcf.poll.maxVotes.error.invalid': '{lang}wcf.poll.maxVotes.error.invalid{/lang}'
		});
		
		new UiPollEditor(
			DomUtil.identify(DomTraverse.childByTag(elById('{@$field->getPrefixedId()}Container'), 'DD')),
			[ {implode from=$field->getValue() item=pollOption}{ optionID: {@$pollOption[optionID]}, optionValue: '{$pollOption[optionValue]|encodeJS}' }{/implode} ],
			'{@$field->getPrefixedWysiwygId()}',
			{
				isAjax: {if $field->getDocument()->isAjax()}true{else}false{/if},
				maxOptions: {@POLL_MAX_OPTIONS}
			}
		);
	});
</script>
