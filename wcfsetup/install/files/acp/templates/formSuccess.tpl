{if $success|isset}
	<p class="success" role="status">
		<span>{lang}wcf.global.success.{$action}{/lang}</span>
	
		{if $action == 'add' && !$objectEditLink|empty}
			<span>{lang}wcf.global.success.add.editCreatedObject{/lang}</span>
		{/if}
	</p>
{/if}
