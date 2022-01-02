{include file='header' pageTitle='wcf.acp.package.update.title'}

<header class="contentHeader">
	<h1 class="contentTitle">{lang}wcf.acp.package.update.title{/lang}</h1>
</header>

{foreach from=$availableUpdates item=update}
	<section class="section jsPackageUpdate" data-package="{$update[package]}" data-version="{$update[newVersion]}">
		<header class="sectionHeader">
			<h2 class="sectionTitle"><label>
				<input type="checkbox" value="1" checked>
				{$update[packageName]|language}
			</label></h2>
			{if $update[packageDescription]}<p class="sectionDescription">{$update[packageDescription]|language}</p>{/if}
		</header>
		
		<dl>
			<dt>
				{lang}wcf.acp.package.installedVersion{/lang}
			</dt>
			<dd>
				{$update[packageVersion]} ({$update[packageDate]|date})
			</dd>
		</dl>
		<dl>
			<dt>
				{lang}wcf.acp.package.availableVersions{/lang}
			</dt>
			<dd>
				{$update[newVersion]}
			</dd>
		</dl>
	</section>
{/foreach}

<div class="formSubmit">
	<button class="buttonPrimary" id="packageUpdateSubmitButton">{lang}wcf.global.button.submit{/lang}</button>
</div>

<script data-relocate="true">
	require(["WoltLabSuite/Core/Language", "WoltLabSuite/Core/Acp/Ui/Package/Update/Manager"], (Language, { setup }) => {
		Language.addObject({
			'wcf.acp.package.update.excludedPackages': '{jslang}wcf.acp.package.update.excludedPackages{/jslang}',
			'wcf.acp.package.update.title': '{jslang}wcf.acp.package.update.title{/jslang}',
			'wcf.acp.package.update.unauthorized': '{jslang}wcf.acp.package.update.unauthorized{/jslang}'
		});

		setup();
	});
</script>

{include file='footer'}
