{if !$errorField|empty || !$errorType|empty}
	<!--
		DEBUG: FORM_VALIDATION_FAILED
		
		errorField: {if $errorField|empty}(empty){else}{$errorField|print_r:true}{/if}
		
		errorType: {if $errorType|empty}(empty){else}{$errorType|print_r:true}{/if}
	
	-->
	{if ENABLE_DEBUG_MODE}
		<script>
			console.debug('FORM_VALIDATION_FAILED');
			console.debug('errorField:', '{if $errorField|empty}(empty){else}{$errorField|json}{/if}');
			console.debug('errorType:', '{if $errorType|empty}(empty){else}{$errorType|json}{/if}');
		</script>
	{/if}
{/if}

<footer id="pageFooter" class="pageFooter pageFooter--compact">
	{include file='pageFooterCopyright'}
</footer>
