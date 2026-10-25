<div class="userPopover" id="userPopover_{$user->userID}">
	{include file='userCard'}
</div>

{unsafe:$interactionInitialization}

{if $contextMenuButton}
	<script data-relocate="true">
		require(['WoltLabSuite/Core/Component/Popover/Interactions'], ({ setup }) => {
			setup(
				document.getElementById('userPopover_{$user->userID}'),
				'com.woltlab.wcf.user',
				{$user->userID},
				'{unsafe:$providerClassName|encodeJS}'
			);
		});
	</script>
{/if}
