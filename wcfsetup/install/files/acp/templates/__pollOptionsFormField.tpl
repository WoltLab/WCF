<ol class="sortableList"></ol>

<script data-relocate="true">
	require([
		'Dom/Traverse',
		'Dom/Util',
		'EventHandler',
		'Language',
		'WoltLabSuite/Core/Form/Builder/Manager',
		'WoltLabSuite/Core/Ui/Poll/Editor'
	], function(DomTraverse, DomUtil, EventHandler, Language, FormBuilderManager, UiPollEditor) {
		Language.addObject({
			'wcf.poll.button.addOption': '{jslang}wcf.poll.button.addOption{/jslang}',
			'wcf.poll.button.removeOption': '{jslang}wcf.poll.button.removeOption{/jslang}',
			'wcf.poll.maxVotes.error.invalid': '{jslang}wcf.poll.maxVotes.error.invalid{/jslang}'
		});
		
		var pollEditor = new UiPollEditor(
			DomUtil.identify(DomTraverse.childByTag(elById('{@$field->getPrefixedId()}Container'), 'DD')),
			[ {implode from=$field->getValue() item=pollOption}{ optionID: {@$pollOption[optionID]}, optionValue: '{$pollOption[optionValue]|encodeJS}' }{/implode} ],
			'{@$field->getPrefixedWysiwygId()}',
			{
				isAjax: {if $field->getDocument()->isAjax()}true{else}false{/if},
				maxOptions: {@POLL_MAX_OPTIONS}
			}
		);
		
		EventHandler.add('WoltLabSuite/Core/Form/Builder/Manager', 'registerField', function(data) {
			if (data.formId === '{@$field->getDocument()->getId()}' && data.field.getId() === '{@$field->getPrefixedId()}') {
				data.field.setPollEditor(pollEditor);
			}
		});
	});
</script>
