{include file='header' pageTitle='wcf.acp.package.update.availableUpdates'}

<header class="contentHeader">
	<h1 class="contentTitle">{lang}wcf.acp.package.update.availableUpdates{/lang} <span class="badge badgeInverse">{#$items}</span></h1>
</header>

<div class="section">
	<div class="contentItemList packageUpdateList">
		{foreach from=$availableUpdates item=update}
		<div class="contentItem packageUpdate" data-package="{$update[package]}" data-version="{$update[newVersion][packageVersion]}">
			<div class="contentItemContent">
				<div class="contentItemTitle">{$update[packageName]|language}</div>
				<div class="contentItemDescription">
					<small
						class="jsTooltip{if $update[newVersion][servers][0][packageUpdateServerID] === $woltlabUpdateServer->packageUpdateServerID} packageSearchAuthorWoltlab{/if}"
						title="{lang}wcf.acp.package.author{/lang}"
					>{*
						*}{$update[author]}
					</small>
					<p class="packageUpdateAvailable">
						{lang currentVersion=$update[packageVersion] newVersion=$update[newVersion][packageVersion]}wcf.acp.package.update.newVersion{/lang}
					</p>
				</div>
			</div>
			<div class="contentItemMeta">
				{if $upgradeOverrideEnabled}
					{lang}wcf.acp.package.update.upgrade{/lang}
				{else}
					<label><input type="checkbox" value="1" checked> {lang}wcf.acp.package.update.installUpdate{/lang}</label>
				{/if}
			</div>
		</div>
		{/foreach}
	</div>
</div>

<div class="formSubmit">
	<button class="buttonPrimary" id="packageUpdateSubmitButton">{lang}wcf.global.button.submit{/lang}</button>
</div>

<script data-relocate="true">
	require(["WoltLabSuite/Core/Language", "WoltLabSuite/Core/Acp/Ui/Package/Update/Manager"], (Language, { setup }) => {
		Language.addObject({
			'wcf.acp.package.update.excludedPackages': '{jslang}wcf.acp.package.update.excludedPackages{/jslang}',
			'wcf.acp.package.update.title': '{jslang}wcf.acp.package.update.title{/jslang}',
			'wcf.acp.package.update.unauthorized': '{jslang}wcf.acp.package.update.unauthorized{/jslang}',
		});

		setup();
	});
</script>

{include file='footer'}
