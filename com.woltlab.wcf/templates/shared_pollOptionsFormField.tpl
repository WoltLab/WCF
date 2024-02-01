<ol class="sortableList"></ol>

<script data-relocate="true">
	require([
		'Dom/Traverse',
		'Dom/Util',
		'EventHandler',
		'WoltLabSuite/Core/Form/Builder/Manager',
		'WoltLabSuite/Core/Ui/Poll/Editor'
	], (DomTraverse, DomUtil, EventHandler, FormBuilderManager, UiPollEditor) => {
		{jsphrase name='wcf.poll.button.addOption'}
		{jsphrase name='wcf.poll.button.removeOption'}
		{jsphrase name='wcf.poll.endTime.error.invalid'}
		{jsphrase name='wcf.poll.maxVotes.error.invalid'}
		
		var pollEditor = new UiPollEditor(
			DomUtil.identify(DomTraverse.childByTag(elById('{@$field->getPrefixedId()|encodeJS}Container'), 'DD')),
			[ {implode from=$field->getValue() item=pollOption}{ optionID: {@$pollOption[optionID]}, optionValue: '{$pollOption[optionValue]|encodeJS}' }{/implode} ],
			'{@$field->getPrefixedWysiwygId()}',
			{
				isAjax: {if $field->getDocument()->isAjax()}true{else}false{/if},
				maxOptions: {POLL_MAX_OPTIONS}
			}
		);
		
		EventHandler.add('WoltLabSuite/Core/Form/Builder/Manager', 'registerField', function(data) {
			if (data.formId === '{@$field->getDocument()->getId()|encodeJS}' && data.field.getId() === '{@$field->getPrefixedId()|encodeJS}') {
				data.field.setPollEditor(pollEditor);
			}
		});
	});
</script>
