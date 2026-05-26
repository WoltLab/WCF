{if PACKAGE_ID && $__wcf->user->userID && $__isLogin|empty}
	{hascontent}
		<nav class="acpBreadcrumbs" aria-label="{lang}wcf.page.breadcrumb{/lang}">
			<ol class="acpBreadcrumbs__list">
				{content}
					{foreach from=$__wcf->getACPMenu()->getBreadcrumbs() item='acpBreadcrumb' name='acpBreadcrumbs'}
						<li class="acpBreadcrumbs__item">
							{if $acpBreadcrumb->getLink()}
								<a class="acpBreadcrumbs__item__link" href="{$acpBreadcrumb->getLink()}">
									<span class="acpBreadcrumbs__item__title">{$acpBreadcrumb}</span>
								</a>
							{else}
								<span class="acpBreadcrumbs__item__title">{$acpBreadcrumb}</span>
							{/if}

							{if !$tpl.foreach.acpBreadcrumbs.last}
								<span class="acpBreadcrumbs__item__separator" aria-hidden="true">
									<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 6l6 6-6 6"></path></svg>
								</span>
							{/if}
						</li>
					{/foreach}
				{/content}
			</ol>
		</nav>
	{/hascontent}
{/if}
