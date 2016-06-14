{assign var=lastStep value=true}
{include file='header'}

<header class="boxHeadline boxSubHeadline">
	<h2>{lang}wcf.global.next{/lang}</h2>
	<p>{lang}wcf.global.next.description{/lang}</p>
</header>

<form method="get" action="{@RELATIVE_WCF_DIR}acp/index.php">
	<div class="formSubmit">
		<input type="hidden" name="action" value="WCFSetup">
	</div>
</form>

<script data-relocate="true">
	//<![CDATA[
	window.onload = function() {
		document.forms[0].submit();
	}
	//]]>
</script>

{include file='footer'}
