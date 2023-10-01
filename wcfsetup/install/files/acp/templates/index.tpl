{include file='header'}

<header class="contentHeader">
	<h1 class="contentTitle">{lang}wcf.global.acp{/lang}</h1>
</header>

{if !(80100 <= PHP_VERSION_ID && PHP_VERSION_ID <= 80399)}
	<woltlab-core-notice type="error">{lang}wcf.global.incompatiblePhpVersion{/lang}</woltlab-core-notice>
{/if}
{foreach from=$evaluationExpired item=$expiredApp}
	<woltlab-core-notice type="error">{lang packageName=$expiredApp[packageName] isWoltLab=$expiredApp[isWoltLab] pluginStoreFileID=$expiredApp[pluginStoreFileID]}wcf.acp.package.evaluation.expired{/lang}</woltlab-core-notice>
{/foreach}
{foreach from=$evaluationPending key=$evaluationEndDate item=$pendingApps}
	<woltlab-core-notice type="warning">{lang evaluationEndDate=$evaluationEndDate}wcf.acp.package.evaluation.pending{/lang}</woltlab-core-notice>
{/foreach}

{foreach from=$taintedApplications item=$taintedApplication}
	<woltlab-core-notice type="error">{lang}wcf.acp.package.application.isTainted{/lang}</woltlab-core-notice>
{/foreach}

{if $systemIdMismatch}
	{if $__wcf->session->getPermission('admin.configuration.package.canInstallPackage') && (!ENABLE_ENTERPRISE_MODE || $__wcf->user->hasOwnerAccess())}
		<woltlab-core-notice type="info">{lang}wcf.acp.index.systemIdMismatch{/lang}</woltlab-core-notice>
	{/if}
{/if}

{if $recaptchaWithoutKey}
	<woltlab-core-notice type="error">{lang}wcf.acp.index.recaptchaWithoutKey{/lang}</woltlab-core-notice>
{/if}

{if !VISITOR_USE_TINY_BUILD}
	<woltlab-core-notice type="info">{lang}wcf.acp.index.tinyBuild{/lang}</woltlab-core-notice>
{/if}

{if $usersAwaitingApproval}
	<woltlab-core-notice type="info">{lang}wcf.acp.user.usersAwaitingApprovalInfo{/lang}</woltlab-core-notice>
{/if}

{if $missingLanguageItemsMTime}
	<woltlab-core-notice type="warning">{lang}wcf.acp.index.missingLanguageItems{/lang}</woltlab-core-notice>
{/if}

{event name='userNotice'}

<div class="acpDashboard">
	{foreach from=$dashboard->getVisibleBoxes() item='box'}
		<div class="acpDashboardBox">
			<h2 class="acpDashboardBox__title">{$box->getTitle()}</h2>
			<div class="acpDashboardBox__content">
				{@$box->getContent()}
			</div>
		</div>
	{/foreach}
</div>

<div class="section tabMenuContainer" data-active="{if ENABLE_WOLTLAB_NEWS}news{else}system{/if}" data-store="activeTabMenuItem">
	<nav class="tabMenu">
		<ul>
			{if ENABLE_WOLTLAB_NEWS}<li><a href="#news">{lang}wcf.acp.index.news{/lang}</a></li>{/if}
			<li><a href="#system">{lang}wcf.acp.index.system{/lang}</a></li>
			<li><a href="#credits">{lang}wcf.acp.index.credits{/lang}</a></li>
			
			{event name='tabMenuTabs'}
		</ul>
	</nav>
	
	{if ENABLE_WOLTLAB_NEWS}
		<div id="news" class="hidden tabMenuContent">
			<div class="section">
				<div class="woltlabNewsfeed woltlabNewsfeed--loading">
					<woltlab-core-loading-indicator size="48"></woltlab-core-loading-indicator>
					<iframe
						class="woltlabNewsfeed__iframe"
						referrerpolicy="no-referrer"
						sandbox="allow-popups allow-popups-to-escape-sandbox"
					></iframe>
				</div>

				<script data-eager="true">
				{
					const languageCode = "{if $__wcf->language->languageCode === 'de'}de{else}en{/if}";
					let colorScheme = document.documentElement.dataset.colorScheme;
					const container = document.querySelector(".woltlabNewsfeed");
					const iframe = container.querySelector(".woltlabNewsfeed__iframe");

					const updateColorScheme = () => {
						container.classList.add("woltlabNewsfeed--loading");
						iframe.addEventListener(
							"load",
							() => container.classList.remove("woltlabNewsfeed--loading"),
							{ once: true }
						);
						iframe.src = `https://newsfeed.woltlab.com/${ languageCode }_${ colorScheme }.html`;
					};

					const observer = new MutationObserver(() => {
						const newScheme = document.documentElement.dataset.colorScheme;
						if (newScheme === "light" || newScheme === "dark") {
							colorScheme = newScheme;
							updateColorScheme();
						}
					});
					observer.observe(document.documentElement, {
						attributeFilter: ["data-color-scheme"]
					});

					updateColorScheme();
				}
				</script>
			</div>
		</div>
	{/if}
	
	<div id="system" class="hidden tabMenuContent">
		<section class="section">
			<h2 class="sectionTitle">{lang}wcf.acp.index.system.software{/lang}</h2>
			
			<dl>
				<dt>{lang}wcf.acp.index.system.software.version{/lang}</dt>
				<dd>{WCF_VERSION}</dd>
			</dl>
			
			{event name='softwareFields'}
			
			<dl>
				<dt>{lang}wcf.acp.index.system.software.databaseName{/lang}</dt>
				<dd>{$databaseName}</dd>
			</dl>

			{if WCF_N != 1}
				<dl>
					<dt>{lang}wcf.acp.index.system.software.databaseNumber{/lang}</dt>
					<dd>{WCF_N}</dd>
				</dl>
			{/if}
		</section>
		
		{if !ENABLE_ENTERPRISE_MODE || $__wcf->getUser()->hasOwnerAccess()}
			<section class="section">
				<h2 class="sectionTitle">{lang}wcf.acp.index.system.server{/lang}</h2>
				
				<dl>
					<dt>{lang}wcf.acp.index.system.os{/lang}</dt>
					<dd>{$server[os]}</dd>
				</dl>
				
				<dl>
					<dt>{lang}wcf.acp.index.system.webserver{/lang}</dt>
					<dd>{$server[webserver]}</dd>
				</dl>
				
				<dl>
					<dt>{lang}wcf.acp.index.system.mySQLVersion{/lang}</dt>
					<dd>{$server[mySQLVersion]}</dd>
				</dl>
				
				{if $server[load]}
					<dl>
						<dt>{lang}wcf.acp.index.system.load{/lang}</dt>
						<dd>{$server[load]}</dd>
					</dl>
				{/if}
				
				{if $server[innodbFlushLogAtTrxCommit] !== false}
					<dl>
						<dt>innodb_flush_log_at_trx_commit</dt>
						<dd>{$server[innodbFlushLogAtTrxCommit]}</dd>
					</dl>
				{/if}
				
				{event name='serverFields'}
			</section>
		
			<section class="section">
				<h2 class="sectionTitle">{lang}wcf.acp.index.system.php{/lang}</h2>
				
				<dl>
					<dt>{lang}wcf.acp.index.system.php.version{/lang}</dt>
					<dd>
						{if $__wcf->session->getPermission('admin.configuration.package.canInstallPackage') && $__wcf->session->getPermission('admin.configuration.package.canUpdatePackage')}
							<a href="{link controller='PHPInfo'}{/link}">{PHP_VERSION}</a>
						{else}
							{PHP_VERSION}
						{/if}
					</dd>
				</dl>
				
				<dl>
					<dt>memory_limit</dt>
					<dd>
						{$server[memoryLimit]}
					</dd>
				</dl>
				
				<dl>
					<dt>post_max_size</dt>
					<dd>
						{$server[postMaxSize]}
					</dd>
				</dl>
				
				<dl>
					<dt>upload_max_filesize</dt>
					<dd>
						{$server[upload_max_filesize]}
					</dd>
				</dl>
				
				{event name='phpFields'}
			</section>
		{/if}
		
		{event name='systemFieldsets'}
	</div>
	
	<div id="credits" class="hidden tabMenuContent">
		<section class="section">
			<dl>
				<dt>{lang}wcf.acp.index.credits.developedBy{/lang}</dt>
				<dd><a href="https://www.woltlab.com/{if $__wcf->getLanguage()->getFixedLanguageCode() === 'de'}de/{/if}" class="externalURL"{if EXTERNAL_LINK_TARGET_BLANK} target="_blank" rel="noopener"{/if}>WoltLab&reg; GmbH</a></dd>
			</dl>
			
			<dl>
				<dt>{lang}wcf.acp.index.credits.productManager{/lang}</dt>
				<dd>
					<ul class="inlineList commaSeparated">
						<li>Marcel Werk</li>
					</ul>
				</dd>
			</dl>
			
			<dl>
				<dt>{lang}wcf.acp.index.credits.developer{/lang}</dt>
				<dd>
					<ul class="inlineList commaSeparated">
						<li>Tim D&uuml;sterhus</li>
						<li>Alexander Ebert</li>
						<li>Joshua R&uuml;sweg</li>
						<li>Matthias Schmidt</li>
						<li>Marcel Werk</li>
					</ul>
				</dd>
			</dl>
			
			<dl>
				<dt>{lang}wcf.acp.index.credits.designer{/lang}</dt>
				<dd>
					<ul class="inlineList commaSeparated">
						<li>Alexander Ebert</li>
						<li>Marcel Werk</li>
					</ul>
				</dd>
			</dl>
			
			<dl>
				<dt>{lang}wcf.acp.index.credits.contributor{/lang}</dt>
				<dd>
					<ul class="inlineList commaSeparated">
						<li>Andrea Berg</li>
						<li>Thorsten Buitkamp</li>
						<li>
							<a href="https://github.com/WoltLab/WCF/contributors" class="externalURL"{if EXTERNAL_LINK_TARGET_BLANK} target="_blank" rel="noopener"{/if}>{lang}wcf.acp.index.credits.contributor.more{/lang}</a>
						</li>
					</ul>
				</dd>
			</dl>
			
			<dl>
				<dt></dt>
				<dd>Copyright &copy; 2001-{TIME_NOW|date:'Y'} WoltLab&reg; GmbH. All rights reserved.</dd>
			</dl>
			
			<dl>
				<dt></dt>
				<dd>{lang}wcf.acp.index.credits.trademarks{/lang}</dd>
			</dl>
		</section>
	</div>
	
	{event name='tabMenuContents'}
</div>

{include file='footer'}
