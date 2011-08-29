<nav id="breadcrumbs" class="breadcrumbs">
	<ul>
		{foreach from=$__wcf->getBreadcrumbs()->get() item=$breadcrumb}
			<li>
				{if $breadcrumb->getURL()}<a href="{$breadcrumb->getURL()}">{/if}<span>{$breadcrumb->getLabel()}</span>{if $breadcrumb->getURL()}</a>{/if} &raquo;
			</li>
		{/foreach}
	</ul>
</nav>