<ol class="nativeList multifactorBackupCodes">
{foreach from=$codes item='code'}
<li>
	<span class="multifactorBackupCode{if $code[useTime]} used{/if}">{foreach from=$code[chunks] item='chunk'}<span class="chunk">{$chunk}</span>{/foreach}</span>
	{if $code[useTime]}({$code[useTime]|plainTime}){/if}
</li>
{/foreach}
</ol>
