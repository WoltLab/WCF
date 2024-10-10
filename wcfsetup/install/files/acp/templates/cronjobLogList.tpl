{include file='header' pageTitle='wcf.acp.cronjob.log'}

<script data-relocate="true">
	$(function() {
		WCF.Language.addObject({
			'wcf.acp.cronjob.log.clear.confirm': '{jslang}wcf.acp.cronjob.log.clear.confirm{/jslang}',
			'wcf.acp.cronjob.log.error.details': '{jslang}wcf.acp.cronjob.log.error.details{/jslang}'
		});
		
		new WCF.ACP.Cronjob.LogList();
	});
</script>

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.cronjob.log{/lang}{if $gridView->countRows()} <span class="badge badgeInverse">{#$gridView->countRows()}</span>{/if}</h1>
	</div>
	
	{hascontent}
		<nav class="contentHeaderNavigation">
			<ul>
				{content}
					{if $gridView->countRows()}
						<li><a title="{lang}wcf.acp.cronjob.log.clear{/lang}" class="button jsCronjobLogDelete">{icon name='xmark'} <span>{lang}wcf.acp.cronjob.log.clear{/lang}</span></a></li>
					{/if}
					
					{event name='contentHeaderNavigation'}
				{/content}
			</ul>
		</nav>
	{/hascontent}
</header>

{unsafe:$gridView->render()}

{include file='footer'}
