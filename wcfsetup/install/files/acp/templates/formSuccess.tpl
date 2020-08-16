{if $success|isset}
	<p class="success" role="status">
		<span class="icon icon16 fa fa-check green"></span>
		<span>{lang}wcf.global.success.{$action}{/lang}</span>
	
		{if $action == 'add' && !$objectEditLink|empty}
			<a href="{$objectEditLink}" class="button buttonPrimary small"><span class="icon icon16 fa-pencil"></span> <span>{lang}wcf.global.success.add.button.editCreatedObject{/lang}</span></a>
		{/if}
	</p>
{/if}
