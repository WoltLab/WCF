{if $__wcf->getBreadcrumbs()->get()|count}
<nav class="breadcrumbs">
	<ul>
		{foreach from=$__wcf->getBreadcrumbs()->get() item=$breadcrumb}
			<li title="{$breadcrumb->getLabel()}">
				{if $breadcrumb->getURL()}<a href="{$breadcrumb->getURL()}">{/if}<span>{$breadcrumb->getLabel()}</span>{if $breadcrumb->getURL()}</a>{/if} <span><span>&raquo;</span></span>
			</li>
		{/foreach}
	</ul>
</nav>
{/if}