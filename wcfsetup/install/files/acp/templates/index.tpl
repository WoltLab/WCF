{include file='header'}

<header class="contentHeader">
	<h1 class="contentTitle">{lang}wcf.global.acp{/lang}</h1>
</header>

{foreach from=$evaluationExpired item=$expiredApp}
	<p class="error">{lang packageName=$expiredApp[packageName] isWoltLab=$expiredApp[isWoltLab] pluginStoreFileID=$expiredApp[pluginStoreFileID]}wcf.acp.package.evaluation.expired{/lang}</p>
{/foreach}
{foreach from=$evaluationPending key=$evaluationEndDate item=$pendingApps}
	<div class="warning">{lang evaluationEndDate=$evaluationEndDate}wcf.acp.package.evaluation.pending{/lang}</div>
{/foreach}

{foreach from=$taintedApplications item=$taintedApplication}
	<div class="error">{lang}wcf.acp.package.application.isTainted{/lang}</div>
{/foreach}

{if TMP_DIR !== WCF_DIR|concat:'tmp/'}
	<p class="error">{lang}wcf.acp.index.tmpBroken{/lang}</p>
{/if}

{if $recaptchaWithoutKey}
	<p class="error">{lang}wcf.acp.index.recaptchaWithoutKey{/lang}</p>
{/if}

{if $nonInnoDbSearch}
	<p class="error">{lang}wcf.acp.index.nonInnoDbSearch{/lang}</p>
{/if}

{if !VISITOR_USE_TINY_BUILD}
	<p class="info">{lang}wcf.acp.index.tinyBuild{/lang}</p>
{/if}

{if $usersAwaitingApproval}
	<p class="info">{lang}wcf.acp.user.usersAwaitingApprovalInfo{/lang}</p>
{/if}

{if $missingLanguageItemsMTime}
	<p class="warning">{lang}wcf.acp.index.missingLanguageItems{/lang}</p>
{/if}

{event name='userNotice'}

<div class="section tabMenuContainer" data-active="{if ENABLE_WOLTLAB_NEWS}news{else}system{/if}" data-store="activeTabMenuItem">
	<nav class="tabMenu">
		<ul>
			{if ENABLE_WOLTLAB_NEWS}<li><a href="{@$__wcf->getAnchor('news')}">{lang}wcf.acp.index.news{/lang}</a></li>{/if}
			<li><a href="{@$__wcf->getAnchor('system')}">{lang}wcf.acp.index.system{/lang}</a></li>
			<li><a href="{@$__wcf->getAnchor('credits')}">{lang}wcf.acp.index.credits{/lang}</a></li>
			
			{event name='tabMenuTabs'}
		</ul>
	</nav>
	
	{if ENABLE_WOLTLAB_NEWS}
		<div id="news" class="hidden tabMenuContent">
			<div class="section">
				<div id="news-twitter-timeline">
					{if $__wcf->language->languageCode == 'de'}
						<a class="twitter-timeline" href="https://twitter.com/woltlab_de" data-chrome="noheader nofooter transparent" data-widget-id="339042086949093376">Tweets von @woltlab_de</a>
						
						<div style="margin-top: 20px">
							<a class="twitter-follow-button" href="https://twitter.com/woltlab_de">Folge @woltlab_de</a>
						</div>
					{else}
						<a class="twitter-timeline" href="https://twitter.com/woltlab" data-chrome="noheader nofooter transparent" data-widget-id="335166618281865217">Tweets by @woltlab</a>
						
						<div style="margin-top: 20px">
							<a class="twitter-follow-button" href="https://twitter.com/woltlab_de">Follow @woltlab</a>
						</div>
					{/if}
					
					{literal}
						<script data-relocate="true">!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+"://platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script>
					{/literal}
				</div>
			</div>
		</div>
	{/if}
	
	<div id="system" class="hidden tabMenuContent">
		<section class="section">
			<h2 class="sectionTitle">{lang}wcf.acp.index.system.software{/lang}</h2>
			
			<dl>
				<dt>{lang}wcf.acp.index.system.software.version{/lang}</dt>
				<dd>{@WCF_VERSION}</dd>
			</dl>
			
			{event name='softwareFields'}
			
			<dl>
				<dt>{lang}wcf.acp.index.system.software.databaseNumber{/lang}</dt>
				<dd>{@WCF_N}</dd>
			</dl>
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
				
				<dl>
					<dt>{lang}wcf.acp.index.system.php.sslSupport{/lang}</dt>
					<dd>
						{if $server[sslSupport]}{lang}wcf.acp.index.system.php.sslSupport.available{/lang}{else}{lang}wcf.acp.index.system.php.sslSupport.notAvailable{/lang}{/if}
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
				<dd><a href="https://www.woltlab.com/{if $__wcf->getLanguage()->getFixedLanguageCode() === 'de'}de/{/if}" class="externalURL">WoltLab&reg; GmbH</a></dd>
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
							<a href="https://github.com/WoltLab/WCF/contributors" class="externalURL">{lang}wcf.acp.index.credits.contributor.more{/lang}</a>
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
