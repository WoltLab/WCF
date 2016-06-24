{include file='header' pageTitle='wcf.acp.package.update.title'}

<script data-relocate="true">
	//<![CDATA[
	$(function() {
		WCF.Language.addObject({
			'wcf.acp.package.update.excludedPackages': '{lang}wcf.acp.package.update.excludedPackages{/lang}',
			'wcf.acp.package.update.title': '{lang}wcf.acp.package.update.title{/lang}',
			'wcf.acp.package.update.unauthorized': '{lang}wcf.acp.package.update.unauthorized{/lang}'
		})
		
		new WCF.ACP.Package.Update.Manager();
	});
	//]]>
</script>

<header class="contentHeader">
	<h1 class="contentTitle">{lang}wcf.acp.package.update.title{/lang}</h1>
</header>

{foreach from=$availableUpdates item=update}
	<section class="section" class="jsPackageUpdate" data-package="{$update[package]}">
		<header class="sectionHeader">
			<h2 class="sectionTitle"><label>
				<input type="checkbox" value="1" checked>
				{$update[packageName]|language}
			</label></h2>
			{if $update[packageDescription]}<small class="sectionDescription">{$update[packageDescription]|language}</small>{/if}
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
				<select>
					{foreach from=$update[versions] item=version}
						<option value="{@$version[packageVersion]}"{if $version[packageVersion] == $update[version][packageVersion]} selected{/if}>{$version[packageVersion]}</option>
					{/foreach}
				</select>
			</dd>
		</dl>
	</section>
{/foreach}

<div class="formSubmit">
	<button>{lang}wcf.global.button.submit{/lang}</button>
</div>

{include file='footer'}
