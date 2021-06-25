{capture assign='pageTitle'}{lang version=$availableUpgradeVersion}wcf.acp.package.enableUpgradeOverride{/lang}{/capture}
{include file='header' pageTitle=$pageTitle}

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{$pageTitle}</h1>
	</div>
	
	<nav class="contentHeaderNavigation">
		<ul>
			<li><a href="{link controller='PackageList'}{/link}" class="button"><span class="icon icon16 fa-list"></span> <span>{lang}wcf.acp.menu.link.package.list{/lang}</span></a></li>
			
			{event name='contentHeaderNavigation'}
		</ul>
	</nav>
</header>

{@$form->getHtml()}

{include file='footer'}
