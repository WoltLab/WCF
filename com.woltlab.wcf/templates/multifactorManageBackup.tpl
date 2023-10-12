<div class="multifactorBackupCodesPrintContainer">
	{if $isUnveiled}
		{lang}wcf.user.security.multifactor.backup.printMetadata{/lang}
	{else}
		{lang}wcf.user.security.multifactor.backup.existingCodes.description{/lang}
	{/if}

	<ol class="nativeList multifactorBackupCodes">
		{foreach from=$codes item='code'}
			<li>
				<span class="multifactorBackupCode{if $code[useTime]} used{/if}">{foreach from=$code[chunks] item='chunk'}<span class="chunk">{$chunk}</span>{/foreach}</span>
				{if $code[useTime]}({$code[useTime]|plainTime}){/if}
			</li>
		{/foreach}
	</ol>

	{if $isUnveiled}
		<button type="button" class="button multifactorBackupCodesPrintButton">
			{lang}wcf.user.security.multifactor.backup.print{/lang}
			<script>
			document.currentScript.closest('button').addEventListener('click', () => {
				try {
					// Safari refuses to execute `window.print()` if there are
					// any in-flight requests.
					document.execCommand("print", false, null);
				} catch {
					window.print();
				}
			});
			</script>
		</button>
	{/if}
</div>
