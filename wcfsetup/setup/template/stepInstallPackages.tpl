{include file='header'}

<section class="section">
	<header class="sectionHeader">
		<h2 class="sectionTitle">{lang}wcf.global.next{/lang}</h2>
	</header>
	
	<p>{lang}wcf.global.next.description{/lang}</p>
	
	<form method="get" action="{$wcfAcp}">
		<div class="formSubmit">
			<input type="hidden" name="action" value="WCFSetup">
		</div>
	</form>
</section>
<script>
if (typeof window._trackWcfSetupStep === 'function') window._trackWcfSetupStep('installPackages');
</script>
<script>
window.addEventListener('DOMContentLoaded', (event) => {
	document.forms[0].submit();
});
</script>

{include file='footer'}
