<button
	type="button"
	class="button small{if !$optionID} active{/if}"
	data-filter-option-id="0"
	data-list-view-id="{$view->getID()}"
>
	<span>{lang}wcf.global.button.all{/lang}</span>
	<span class="badge">{#$totalCount}</span>
</button>

{foreach from=$options item='option'}
	<button
		type="button"
		class="button small{if $option->optionID === $optionID} active{/if}"
		data-filter-option-id="{$option->optionID}"
		data-list-view-id="{$view->getID()}"
	>
		<span>{$option->optionValue}</span>
		<span class="badge">{#$voteCounts[$option->optionID]}</span>
	</button>
{/foreach}

<script data-relocate="true">
	require(['WoltLabSuite/Core/Component/Poll/ParticipantFilterButtons'], ({ setup }) => {
		setup('{unsafe:$view->getID()|encodeJS}');
	});
</script>
