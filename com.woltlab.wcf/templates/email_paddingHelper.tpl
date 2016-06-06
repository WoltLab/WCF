{* This construct is needed for Microsoft Outlook: https://litmus.com/help/email-clients/outlookcom-margins/ *}
<table cellpadding="0" cellspacing="0" border="0" class="paddingHelper{if $block|isset && $block} block{/if}">
	<tr>
		<td class="{$class}">
			{@$content}
		</td>
	</tr>
</table>
