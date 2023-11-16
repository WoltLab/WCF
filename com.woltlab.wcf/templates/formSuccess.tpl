{if $success|isset}
	<woltlab-core-notice type="success">
		<span>{lang}wcf.global.success.{$action}{/lang}</span>
	
		{if $action == 'add' && !$objectEditLink|empty}
			<span>{lang}wcf.global.success.add.editCreatedObject{/lang}</span>
		{/if}
	</woltlab-core-notice>
{/if}
