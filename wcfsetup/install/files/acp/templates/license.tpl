{include file='header' pageTitle='wcf.acp.license'}

<style>
	.licensed_packages__package__title {
		font-size: var(--wcfFontSizeHeadline);
		font-weight: 600;
	}

	.licensed_packages__package__version {
		color: var(--wcfContentDimmedText);
	}
	
	.licensed_packages__package__description {
		color: var(--wcfContentDimmedText);
		display: block;
	}

	.licensed_packages__package__action {
		white-space: nowrap;
	}

	.license_packages__package__purchase {
		display: flex;
		flex-direction: column;
		font-size: var(--wcfFontSizeSmall);
		row-gap: 5px;
	}

	.license_packages__package__purchase__outdated {
		color: var(--wcfContentDimmedText);
	}
</style>

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.license{/lang}</span></h1>
		{if $licenseData->getLicenseNumber()}
			<p class="contentDescription">{lang licenseNumber=$licenseData->getLicenseNumber()}wcf.acp.license.licenseNo{/lang}</p>
		{/if}
	</div>

	{hascontent}
		<nav class="contentHeaderNavigation">
			<ul>
				{content}
					{if $__wcf->session->getPermission('admin.configuration.package.canEditServer')}
						<li>
							<a href="{link controller='LicenseEdit'}{/link}" class="button">
								{icon name='pencil'}
								<span>{lang}wcf.acp.license.edit{/lang}</span>
							</a>
						</li>
					{/if}

					{event name='contentHeaderNavigation'}
				{/content}
			</ul>
		</nav>
	{/hascontent}
</header>

{if $licenseData->getLicenseType() === 'developer'}
	<woltlab-core-notice type="warning">{lang}wcf.acp.license.developerLicense{/lang}</woltlab-core-notice>
{/if}

{hascontent}
<section class="section">
	<h1 class="sectionTitle">WoltLab®</h1>

	<div class="section tabularBox">
		<table class="table licensed_packages">
			<thead>
				<tr>
					<th colspan="2">{lang}wcf.acp.package.name{/lang}</th>
				</tr>
			</thead>
			<tbody>
				{content}
				{foreach from=$availablePackages[woltlab] key=package item=majorVersion}
					<tr class="licensed_packages__package" data-package="{$package}">
						{if $installedPackages[$package]|isset}
							<td class="columnText">
								<span class="licensed_packages__package__title">{$installedPackages[$package]}</span>
								<span class="licensed_packages__package__version">{$installedPackages[$package]->packageVersion}</span>
								<small class="licensed_packages__package__description">{$installedPackages[$package]->getDescription()}</small>
							</td>
							<td class="columnStatus">
								<small class="green licensed_packages__package__action">
									{icon name='check'}
									{lang}wcf.acp.license.package.installed{/lang}
								</small>
							</td>
						{else}
							<td class="columnText">
								<span class="licensed_packages__package__title">{$packageUpdates[$package]->packageName}</span>
								<span class="licensed_packages__package__version">{$installablePackages[$package]}</span>
								<small class="licensed_packages__package__description">{$packageUpdates[$package]->packageDescription}</small>
							</td>
							<td class="columnStatus">
								{if $requiresLicenseExtension[$package]|isset}
									<span class="license_packages__package__purchase">
										{if $requiresLicenseExtension[$package] !== 'purchase'}
											<span class="license_packages__package__purchase__outdated">
												{lang accessibleVersion=$requiresLicenseExtension[$package]}wcf.acp.license.package.outdated{/lang}
											</span>
										{/if}
										<a href="https://www.woltlab.com/license-extend/{$licenseData->getLicenseNumber()}/" class="externalURL" rel="nofollow noopener" target="_blank">{lang}wcf.acp.license.extend{/lang}</a>
									</span>
								{else}
									<button type="button" class="button small jsInstallPackage" data-package="{$package}" data-package-version="{$installablePackages[$package]}">
										{lang}wcf.acp.license.package.install{/lang}
									</button>
								{/if}
							</td>
						{/if}
					</tr>
				{/foreach}
				{/content}
			</tbody>
		</table>
	</div>
</section>
{/hascontent}

{hascontent}
<section class="section">
	<h1 class="sectionTitle">WoltLab® Plugin-Store</h1>

	<div class="section tabularBox">
		<table class="table licensed_packages">
			<thead>
				<tr>
					<th colspan="2">{lang}wcf.acp.package.name{/lang}</th>
				</tr>
			</thead>
			<tbody>
				{content}
				{foreach from=$availablePackages[pluginstore] key=package item=majorVersion}
					<tr class="licensed_packages__package" data-package="{$package}">
						{if $installedPackages[$package]|isset}
							<td class="columnText">
								<span class="licensed_packages__package__title">{$installedPackages[$package]}</span>
								<span class="licensed_packages__package__version">{$installedPackages[$package]->packageVersion}</span>
								<small class="licensed_packages__package__description">{$installedPackages[$package]->getDescription()}</small>
							</td>
							<td class="columnStatus">
								<small class="green licensed_packages__package__action">
									{icon name='check'}
									{lang}wcf.acp.license.package.installed{/lang}
								</small>
							</td>
						{else}
							<td class="columnText">
								<span class="licensed_packages__package__title">{$packageUpdates[$package]->packageName}</span>
								<span class="licensed_packages__package__version">{$installablePackages[$package]}</span>
								<small class="licensed_packages__package__description">{$packageUpdates[$package]->packageDescription}</small>
							</td>
							<td class="columnStatus">
								<button type="button" class="button small jsInstallPackage" data-package="{$package}" data-package-version="{$installablePackages[$package]}">
									{lang}wcf.acp.license.package.install{/lang}
								</button>
							</td>
						{/if}
					</tr>
				{/foreach}
				{/content}
			</tbody>
		</table>
	</div>
</section>
{/hascontent}

<script data-relocate="true">
	require(["WoltLabSuite/Core/Acp/Component/License"], ({ setup }) => {
		{jsphrase name='wcf.acp.package.error.uniqueAlreadyInstalled'}
		{jsphrase name='wcf.acp.package.install.title'}
		{jsphrase name='wcf.acp.package.quickInstallation.code.error.invalid'}
		{jsphrase name='wcf.acp.package.update.excludedPackages'}
		{jsphrase name='wcf.acp.package.update.title'}
		{jsphrase name='wcf.acp.package.update.unauthorized'}
		
		setup();
	});
</script>

{include file='footer'}
