{include file='header'}

<section class="section">
	<header class="sectionHeader">
		<h2 class="sectionTitle">{lang}wcf.global.next{/lang}</h2>
		<p class="sectionDescription">{lang}wcf.global.next.description{/lang}</p>
	</header>
</section>	

<form method="post" action="install.php?step={@$nextStep}">
	<div class="formSubmit">
		<input type="hidden" name="tmpFilePrefix" value="{@$tmpFilePrefix}" />
		<input type="hidden" name="languageCode" value="{@$languageCode}" />
		<input type="hidden" name="dev" value="{@$developerMode}" />
		{foreach from=$directories key=application item=directory}
			<input type="hidden" name="directories[{$application}]" value="{$directory}">
		{/foreach}
		{foreach from=$selectedLanguages item=language}
			<input type="hidden" name="selectedLanguages[]" value="{$language}" />
		{/foreach}
		{if $__additionalParameters|isset}
			{foreach from=$__additionalParameters key=__name item=__value}
				<input type="hidden" name="{@$__name}" value="{@$__value}" />
			{/foreach}
		{/if}
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
