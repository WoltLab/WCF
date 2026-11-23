{capture assign='pageTitle'}{lang version=$availableUpgradeVersion}wcf.acp.package.enableUpgradeOverride{/lang}{/capture}
{include file='header' pageTitle=$pageTitle}

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{$pageTitle}</h1>
	</div>
	
	{hascontent}
		<nav class="contentHeaderNavigation">
			<ul>
				{content}
					{event name='contentHeaderNavigation'}
				{/content}
			</ul>
		</nav>
	{/hascontent}
</header>

{unsafe:$form->getHtml()}

{include file='footer'}
