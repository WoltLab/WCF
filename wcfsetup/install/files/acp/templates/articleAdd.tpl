{include file='header' pageTitle='wcf.acp.article.'|concat:$action}

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.article.{$action}{/lang}</h1>
	</div>

	<nav class="contentHeaderNavigation">
		<ul>
			{if $action == 'edit'}
				<li>
					{unsafe:$interactionContextMenu->render()}
				</li>
			{/if}

			{event name='contentHeaderNavigation'}
		</ul>
	</nav>
</header>

{if $action == 'edit'}
	<woltlab-core-notice type="info" class="jsArticleNoticeTrash"{if !$article->isDeleted} hidden{/if}>{lang}wcf.acp.article.trash.notice{/lang}</woltlab-core-notice>
	
	{if $lastVersion && $__wcf->session->getPermission('admin.general.canUseAcp')}
		<woltlab-core-notice type="info">{lang}wcf.acp.article.lastVersion{/lang}</woltlab-core-notice>
	{/if}
{/if}

{unsafe:$form->getHtml()}

{include file='footer'}
