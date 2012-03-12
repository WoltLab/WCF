{if !$__microdata|isset}{assign var=__microdata value=true}{/if}
{if $__wcf->getBreadcrumbs()|count}
<nav class="wcf-breadcrumbs wcf-marginTop">
	<ul>
		{foreach from=$__wcf->getBreadcrumbs() item=$breadcrumb}
			<li title="{$breadcrumb->getLabel()}"{if $__microdata} itemscope="itemscope" itemtype="http://data-vocabulary.org/Breadcrumb"{/if}>
				{if $breadcrumb->getURL()}<a href="{$breadcrumb->getURL()}"{if $__microdata} itemprop="url"{/if}>{/if}<span{if $__microdata} itemprop="title"{/if}>{$breadcrumb->getLabel()}</span>{if $breadcrumb->getURL()}</a>{/if} <span class="pointer"><span>&raquo;</span></span>
			</li>
		{/foreach}
	</ul>
</nav>
{/if}
