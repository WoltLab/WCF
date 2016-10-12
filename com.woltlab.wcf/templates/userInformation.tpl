{include file='userInformationHeadline'}

{if !$disableUserInformationButtons|isset || $disableUserInformationButtons != true}{include file='userInformationButtons'}{/if}

<dl class="plain inlineDataList small">
	{include file='userInformationStatistics'}
</dl>
