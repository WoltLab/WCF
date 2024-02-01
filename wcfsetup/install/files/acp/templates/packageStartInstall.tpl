{if $package === null}
	{assign var='pageTitle' value='wcf.acp.package.startInstall'}
{else}
	{assign var='pageTitle' value='wcf.acp.package.startUpdate'}
{/if}
{include file='header'}

<script data-relocate="true">
	require([
		"WoltLabSuite/Core/Acp/Ui/Package/QuickInstallation", "WoltLabSuite/Core/Acp/Ui/Package/Search"],
		(AcpUiPackageQuickInstallation, AcpUiPackageSearch) => {
		{jsphrase name='wcf.acp.package.error.uniqueAlreadyInstalled'}
		{jsphrase name='wcf.acp.package.install.title'}
		{jsphrase name='wcf.acp.package.quickInstallation.code.error.invalid'}
		{jsphrase name='wcf.acp.package.update.excludedPackages'}
		{jsphrase name='wcf.acp.package.update.title'}
		{jsphrase name='wcf.acp.package.update.unauthorized'}
		
		AcpUiPackageQuickInstallation.setup();
		new AcpUiPackageSearch();
		
		{if $errorField === 'uploadPackage'}
			document.querySelector('.jsButtonUploadPackage').click();
		{/if}
	});
</script>

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}{@$pageTitle}{/lang}</h1>
	</div>
	
	<nav class="contentHeaderNavigation">
		<ul>
			{if !ENABLE_ENTERPRISE_MODE || $__wcf->getUser()->hasOwnerAccess()}
				<li><a href="#" class="button jsButtonUploadPackage jsStaticDialog" data-dialog-id="packageUploadDialog">{icon name='upload'} <span>{lang}wcf.acp.package.upload{/lang}</span></a></li>
			{/if}
			<li><a href="{link controller='PackageList'}{/link}" class="button">{icon name='list'} <span>{lang}wcf.acp.menu.link.package.list{/lang}</span></a></li>
			
			{event name='contentHeaderNavigation'}
		</ul>
	</nav>
</header>

{if $errorField && $installingImportedStyle}
	<woltlab-core-notice type="info">{lang}wcf.acp.package.install.installingImportedStyle{/lang}</woltlab-core-notice>
{/if}

{include file='shared_formError'}

<div class="section">
	<section class="section">
		<h2 class="sectionTitle">{lang}wcf.acp.package.quickInstallation{/lang}</h2>

		<dl>
			<dt><label for="quickInstallationCode">{lang}wcf.acp.package.quickInstallation.code{/lang}</label></dt>
			<dd>
				<input type="text" id="quickInstallationCode" value="" class="long" autocomplete="off">
				<small>{lang}wcf.acp.package.quickInstallation.code.description{/lang}</small>
			</dd>
		</dl>
	</section>

	<section class="section" id="packageSearch">
		<h2 class="sectionTitle">{lang}wcf.acp.package.search{/lang}</h2>
		
		<dl>
			<dt><label for="packageSearchInput">{lang}wcf.acp.package.search.input{/lang}</label></dt>
			<dd>
				<input type="text" id="packageSearchInput" value="" class="long" autocomplete="off">
				<small>{lang}wcf.acp.package.search.input.description{/lang}</small>
			</dd>
		</dl>
	</section>
	
	<section class="section tabularBox" id="packageSearchResultContainer" data-status="idle">
		<h2 class="sectionTitle">{lang}wcf.acp.package.search.resultList{/lang} <span class="badge" id="packageSearchResultCounter">0</span></h2>
		
		<div class="packageSearchStatus packageSearchStatusIdle">
			<span class="packageSearchStatusLabel">{lang}wcf.acp.package.search.status.idle{/lang}</span>
		</div>
		
		<div class="packageSearchStatus packageSearchStatusRefreshDatabase">
			{icon size=64 name='spinner'}
			<span class="packageSearchStatusLabel">{lang}wcf.acp.package.search.status.refreshDatabase{/lang}</span>
		</div>
		
		<div class="packageSearchStatus packageSearchStatusLoading">
			{icon size=64 name='spinner'}
			<span class="packageSearchStatusLabel">{lang}wcf.acp.package.search.status.loading{/lang}</span>
		</div>
		
		<div class="packageSearchStatus packageSearchStatusNoResults">
			<span class="packageSearchStatusLabel">{lang}wcf.acp.package.search.status.noResults{/lang}</span>
		</div>
		
		<div id="packageSearchResultList"></div>
	</section>
</div>

{if !ENABLE_ENTERPRISE_MODE || $__wcf->getUser()->hasOwnerAccess()}
	<div id="packageUploadDialog" class="jsStaticDialogContent" data-title="{lang}wcf.acp.package.upload{/lang}">
		<form method="post" action="{link controller='PackageStartInstall'}{/link}" enctype="multipart/form-data">
			<div class="section">
				<dl{if $errorField == 'uploadPackage'} class="formError"{/if}>
					<dt><label for="uploadPackage">{lang}wcf.acp.package.source.upload{/lang}</label></dt>
					<dd>
						<input type="file" id="uploadPackage" name="uploadPackage" value="" accept="application/x-tar,application/gzip,application/tar+gzip">
						{if $errorField == 'uploadPackage'}
							<small class="innerError">
								{if $errorType == 'empty'}
									{lang}wcf.global.form.error.empty{/lang}
								{else}
									{lang}wcf.acp.package.error.{@$errorType}{/lang}
								{/if}
							</small>
						{/if}
						<small>{lang}wcf.acp.package.source.upload.description{/lang}</small>
					</dd>
				</dl>
			</div>
			
			<div class="formSubmit">
				<input type="submit" name="submitButton" value="{lang}wcf.global.button.submit{/lang}" accesskey="s">
				<input type="hidden" name="action" value="{$action}">
				{csrfToken}
			</div>
		</form>
	</div>
{/if}

{include file='footer'}
