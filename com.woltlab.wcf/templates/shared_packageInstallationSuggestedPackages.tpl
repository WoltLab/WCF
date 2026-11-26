<section class="section" id="packageInstallationSuggestedPackages">
	<header class="sectionHeader">
		<h2 class="sectionTitle">{lang}wcf.acp.package.suggestedPackages{/lang}</h2>
		<p class="sectionDescription">{lang}wcf.acp.package.suggestedPackages.description{/lang}</p>
	</header>

	<dl class="wide">
		{foreach from=$packages item=package}
			<dd>
				<label><input type="checkbox" name="suggestedPackages[]" value="{$package->package}" data-package-version="{$package->getAccessibleVersion()->packageVersion}"> {$package->packageName} <span class="packageSearchVersion">{$package->getAccessibleVersion()->packageVersion}</span></label>
				<small>{$package->packageDescription}</small>
			</dd>
		{/foreach}
	</dl>
</section>

<div class="formSubmit">
	<button type="button" class="button buttonPrimary" data-type="submit" disabled>{lang}wcf.acp.package.suggestedPackages.button.install{/lang}</button>
	<button type="button" class="button" data-type="skip">{lang}wcf.acp.package.suggestedPackages.button.skip{/lang}</button>
</div>
