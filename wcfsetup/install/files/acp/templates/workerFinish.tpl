{include file='setupWindowHeader'}

<div class="nextButton">
	<input type="button" value="{lang}wcf.global.button.next{/lang}" onclick="parent.location.href='{if $url|isset}{$url}{else}index.php{@SID_ARG_1ST}{/if}'; parent.stopAnimating();" />
</div>

<script type="text/javascript">
	//<![CDATA[
	window.onload = function() {
	changeHeight();	
};
	parent.setProgress(100);
	parent.showWindow(true);
	parent.setCurrentStep('{@$stepTitle}');
	//]]>
</script>

{if $message|isset}
	{@$message}
{/if}

{include file='setupWindowFooter'}

