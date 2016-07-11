{if !$__microdata|isset}{assign var=__microdata value=true}{/if}
{hascontent}
	<nav class="breadcrumbs">
		<ol{if $__microdata} itemprop="breadcrumb" itemscope itemtype="http://schema.org/BreadcrumbList"{/if}>
			{content}
				{foreach from=$__wcf->getBreadcrumbs() item=$breadcrumb}
					<li title="{$breadcrumb->getLabel()}"{if $__microdata} itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem"{/if}>
						<a href="{$breadcrumb->getURL()}"{if $__microdata} itemprop="url"{/if}><span{if $__microdata} itemprop="name"{/if}>{$breadcrumb->getLabel()}</span></a>
					</li>
				{/foreach}
				
				{event name='breadcrumbs'}
			{/content}
		</ol>
	</nav>
{/hascontent}
