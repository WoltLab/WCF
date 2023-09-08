{include file='header' pageTitle='wcf.acp.license.edit'}

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.license.edit{/lang}</h1>
	</div>

	{hascontent}
		<nav class="contentHeaderNavigation">
			<ul>
				{content}
					{if $__wcf->session->getPermission('admin.configuration.package.canInstallPackage')}
						<li>
							<a href="{link controller='License'}{/link}" class="button">
								{icon name='cart-arrow-down'}
								<span>{lang}wcf.acp.license{/lang}</span>
							</a>
						</li>
					{/if}

					{event name='contentHeaderNavigation'}
				{/content}
			</ul>
		</nav>
	{/hascontent}
</header>

{@$form->getHtml()}

{include file='footer'}
