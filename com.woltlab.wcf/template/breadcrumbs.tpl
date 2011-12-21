{if $__wcf->getBreadcrumbs()|count}
<nav class="breadcrumbs">
	<ul>
		{foreach from=$__wcf->getBreadcrumbs() item=$breadcrumb}
			<li title="{$breadcrumb->getLabel()}" itemscope="itemscope" itemtype="http://data-vocabulary.org/Breadcrumb">
				{if $breadcrumb->getURL()}<a href="{$breadcrumb->getURL()}" itemprop="url">{/if}<span itemprop="title">{$breadcrumb->getLabel()}</span>{if $breadcrumb->getURL()}</a>{/if} <span class="pointer"><span>&raquo;</span></span>
			</li>
		{/foreach}
	</ul>
</nav>
{/if}
