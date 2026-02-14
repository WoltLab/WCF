{include file='header' pageTitle='wcf.acp.group.list'}

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.group.list{/lang}</h1>
	</div>
	
	{hascontent}
		<nav class="contentHeaderNavigation">
			<ul>
				{content}
					{if $__wcf->getSession()->getPermission('admin.user.canAddGroup')}
						<li><a href="{link controller='UserGroupAdd'}{/link}" class="button">{icon name='plus'} <span>{lang}wcf.acp.group.add{/lang}</span></a></li>
					{/if}
						
					{event name='contentHeaderNavigation'}
				{/content}
			</ul>
		</nav>
	{/hascontent}
</header>

<div class="section">
	{unsafe:$gridView->render()}
</div>

{include file='footer'}
