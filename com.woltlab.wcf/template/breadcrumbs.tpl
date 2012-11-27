{if !$__microdata|isset}{assign var=__microdata value=true}{/if}
{if $__wcf->getBreadcrumbs()|count}
<nav class="breadcrumbs marginTop">
	<ul>
		{foreach from=$__wcf->getBreadcrumbs() item=$breadcrumb}
			<li title="{$breadcrumb->getLabel()}"{if $__microdata} itemscope="itemscope" itemtype="http://data-vocabulary.org/Breadcrumb"{/if}>
				<a href="{$breadcrumb->getURL()}"{if $__microdata} itemprop="url"{/if}><span{if $__microdata} itemprop="title"{/if}>{$breadcrumb->getLabel()}</span></a> <span class="pointer"><span>&raquo;</span></span>
			</li>
		{/foreach}
	</ul>
</nav>
{/if}
