{capture assign='__contentHeader'}
	<header class="contentHeader">
		<div class="contentHeaderTitle">
			<h1 class="contentTitle">{$__wcf->getActivePage()->getTitle()}</h1>
		</div>
		
		{hascontent}
			<nav class="contentHeaderNavigation">
				<ul>
					{content}
						{if $action == 'edit'}
							<li><a href="{$article->getLink()}" class="button buttonPrimary">{icon name='magnifying-glass'} <span>{lang}wcf.acp.article.button.viewArticle{/lang}</span></a></li>
						{/if}
						{event name='contentHeaderNavigation'}
					{/content}
				</ul>
			</nav>
		{/hascontent}
	</header>
{/capture}

{include file='header' contentHeader=$__contentHeader}

{if $action == 'edit'}
	<woltlab-core-notice type="info" class="jsArticleNoticeTrash"{if !$article->isDeleted} hidden{/if}>{lang}wcf.acp.article.trash.notice{/lang}</woltlab-core-notice>
	
	{if $lastVersion && $__wcf->session->getPermission('admin.general.canUseAcp')}
		<woltlab-core-notice type="info">{lang}wcf.acp.article.lastVersion{/lang}</woltlab-core-notice>
	{/if}
{/if}

{unsafe:$form->getHtml()}

{include file='footer'}
