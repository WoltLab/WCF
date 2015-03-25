{include file='header'}

<script data-relocate="true">
	//<![CDATA[
	$(function() {
		WCF.TabMenu.init();
	});
	//]]>
</script>

<header class="boxHeadline">
	<h1>{lang}wcf.global.acp{/lang}</h1>
</header>

{if $inRescueMode}
	<p style="background-color: rgb(255, 153, 153); border: 2px solid rgb(255, 0, 0); padding: 1em;">{lang}wcf.acp.index.inRescueMode{/lang}</p>
{/if}

{if TMP_DIR !== WCF_DIR|concat:'tmp/'}
	<p class="error">{lang}wcf.acp.index.tmpBroken{/lang}</p>
{/if}

{if $usersAwaitingApproval}
	<p class="info">{lang}wcf.acp.user.usersAwaitingApprovalInfo{/lang}</p>
{/if}

{event name='userNotice'}

{if ENABLE_PLUGINSTORE_WIDGET}
	<div id="pluginstore"></div>
	<script data-relocate="true" src="https://assets.woltlab.com/widget/pluginstore/featuredFiles.min.js"></script>
{/if}

<div class="tabMenuContainer" data-active="{if ENABLE_WOLTLAB_NEWS}news{else}system{/if}" data-store="activeTabMenuItem">
	<nav class="tabMenu">
		<ul>
			{if ENABLE_WOLTLAB_NEWS}<li><a href="{@$__wcf->getAnchor('news')}">{lang}wcf.acp.index.news{/lang}</a></li>{/if}
			<li><a href="{@$__wcf->getAnchor('system')}">{lang}wcf.acp.index.system{/lang}</a></li>
			<li><a href="{@$__wcf->getAnchor('credits')}">{lang}wcf.acp.index.credits{/lang}</a></li>
			
			{event name='tabMenuTabs'}
		</ul>
	</nav>
	
	{if ENABLE_WOLTLAB_NEWS}
		<div id="news" class="container containerPadding hidden tabMenuContent">
			<div id="news-twitter-timeline">
				{if $__wcf->language->languageCode == 'de'}
					<a class="twitter-timeline" href="https://twitter.com/woltlab_de" data-chrome="nofooter transparent" data-widget-id="339042086949093376">Tweets von @woltlab_de</a>
				{else}
					<a class="twitter-timeline" href="https://twitter.com/woltlab" data-chrome="nofooter transparent" data-widget-id="335166618281865217">Tweets by @woltlab</a>
				{/if}
				{literal}
					<script data-relocate="true">!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+"://platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script>
				{/literal}
			</div>
		</div>
	{/if}
	
	<div id="system" class="container containerPadding hidden tabMenuContent">
		<fieldset>
			<legend>{lang}wcf.acp.index.system.software{/lang}</legend>
			
			{event name='softwareVersions'}
			
			<dl>
				<dt>{lang}wcf.acp.index.system.software.wcfVersion{/lang}</dt>
				<dd>{@WCF_VERSION}</dd>
			</dl>
			
			{event name='softwareFields'}
		</fieldset>
		
		<fieldset>
			<legend>{lang}wcf.acp.index.system.server{/lang}</legend>
			
			<dl>
				<dt>{lang}wcf.acp.index.system.os{/lang}</dt>
				<dd>{$server[os]}</dd>
			</dl>
			
			<dl>
				<dt>{lang}wcf.acp.index.system.webserver{/lang}</dt>
				<dd>{$server[webserver]}</dd>
			</dl>
			
			<dl>
				<dt>{lang}wcf.acp.index.system.php{/lang}</dt>
				<dd>
					{if $__wcf->session->getPermission('admin.system.package.canInstallPackage') && $__wcf->session->getPermission('admin.system.package.canUpdatePackage')}
						<a href="{link controller='PHPInfo'}{/link}">{PHP_VERSION}</a>
					{else}
						{PHP_VERSION}
					{/if}
				</dd>
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
			
			{event name='serverFields'}
		</fieldset>
		
		{event name='systemFieldsets'}
	</div>
	
	<div id="credits" class="container containerPadding hidden tabMenuContent">
		<fieldset>
			<legend>{lang}wcf.acp.index.credits{/lang}</legend>
			
			<dl>
				<dt>{lang}wcf.acp.index.credits.developedBy{/lang}</dt>
				<dd><a href="{@$__wcf->getPath()}acp/dereferrer.php?url={"http://www.woltlab.com"|rawurlencode}" class="externalURL">WoltLab&reg; GmbH</a></dd>
			</dl>
			
			<dl>
				<dt>{lang}wcf.acp.index.credits.productManager{/lang}</dt>
				<dd>
					<ul class="dataList">
						<li>Marcel Werk</li>
					</ul>
				</dd>
			</dl>
			
			<dl>
				<dt>{lang}wcf.acp.index.credits.developer{/lang}</dt>
				<dd>
					<ul class="dataList">
						<li>Tim D&uuml;sterhus</li>
						<li>Alexander Ebert</li>
						<li>Matthias Schmidt</li>
						<li>Marcel Werk</li>
					</ul>
				</dd>
			</dl>
			
			<dl>
				<dt>{lang}wcf.acp.index.credits.designer{/lang}</dt>
				<dd>
					<ul class="dataList">
						<li>Harald Szekely</li>
						<li>Marcel Werk</li>
					</ul>
				</dd>
			</dl>
			
			<dl>
				<dt>{lang}wcf.acp.index.credits.contributor{/lang}</dt>
				<dd>
					<ul class="dataList">
						<li>Andrea Berg</li>
						<li>Thorsten Buitkamp</li>
						<li>
							<a href="{@$__wcf->getPath()}acp/dereferrer.php?url={"https://github.com/WoltLab/WCF/contributors"|rawurlencode}" class="externalURL">{lang}wcf.acp.index.credits.contributor.more{/lang}</a>
						</li>
					</ul>
				</dd>
			</dl>
			
			<dl>
				<dt></dt>
				<dd>Copyright &copy; 2001-2015 WoltLab&reg; GmbH. All rights reserved.</dd>
			</dl>
			
			<dl>
				<dt></dt>
				<dd>{lang}wcf.acp.index.credits.trademarks{/lang}</dd>
			</dl>
		</fieldset>
	</div>
	
	{event name='tabMenuContents'}
</div>

{include file='footer'}
