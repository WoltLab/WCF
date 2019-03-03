{include file='__formFieldHeader'}

<ol class="sortableList"></ol>

{include file='__formFieldFooter'}

{js application='wcf' file='WCF.Poll' bundle='WCF.Combined'}
<script data-relocate="true">
	require(['Dom/Traverse', 'Dom/Util', 'Language'], function(DomTraverse, DomUtil, Language) {
		Language.addObject({
			'wcf.poll.button.addOption': '{lang}wcf.poll.button.addOption{/lang}',
			'wcf.poll.button.removeOption': '{lang}wcf.poll.button.removeOption{/lang}'
		});
		
		new WCF.Poll.Management(
			DomUtil.identify(DomTraverse.childByTag(elById('{@$field->getPrefixedId()}Container'), 'DD')),
			[ {implode from=$field->getValue() item=pollOption}{ optionID: {@$pollOption[optionID]}, optionValue: '{$pollOption[optionValue]|encodeJS}' }{/implode} ],
			{@POLL_MAX_OPTIONS},
			'{if $field->getDocument()->isAjax()}{@$field->getWysiwygId()}{/if}',
			'{@$field->getPrefixedId()}'
		);
	});
</script>
