{include file='setupWindowHeader'}

<h2>Step: {@$stepTitle}</h2>

<form method="post" action="{$url}">
	<input type="submit" value="Go" onclick="parent.stopAnimating()" />
</form>

<script type="text/javascript">
	//<![CDATA[
	{if $progress|isset}parent.setProgress({@$progress});{/if}
	parent.showWindow(false);
	parent.setCurrentStep('{@$stepTitle}');
	
	window.onload = function() {
		document.forms[0].submit();
	}
	//]]>
</script>

{include file='setupWindowFooter'}