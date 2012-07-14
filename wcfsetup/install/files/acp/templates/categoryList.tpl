{include file='header'}

{if $categoryNodeList|count}
	<script type="text/javascript">
		//<![CDATA[
		$(function() {
			{if $collapsibleObjectTypeID && $categoryNodeList|count > 1}
				new WCF.ACP.Category.Collapsible('wcf\\data\\category\\CategoryAction', {@$collapsibleObjectTypeID});
			{/if}
			
			{if $objectType->getProcessor()->canDeleteCategory()}
				new WCF.ACP.Category.Delete('wcf\\data\\category\\CategoryAction', $('.jsCategory'));
			{/if}
			{if $objectType->getProcessor()->canEditCategory()}
				new WCF.Action.Toggle('wcf\\data\\category\\CategoryAction', $('.jsCategory'), '> .buttons > .jsToggleButton');
				
				{if $categoryNodeList|count > 1}
					new WCF.Sortable.List('categoryList', 'wcf\\data\\category\\CategoryAction');
				{/if}
			{/if}
		});
		//]]>
	</script>
{/if}

<header class="box48 boxHeadline">
	<hgroup>
		<h1>{@$objectType->getProcessor()->getLanguageVariable('list')}</h1>
	</hgroup>
</header>

{hascontent}
	<div class="contentNavigation">
		<nav>
			<ul>
				{content}
					{if $objectType->getProcessor()->canAddCategory()}
						<li><a href="{link controller=$addController}{/link}" title="{$objectType->getProcessor()->getLanguageVariable('add')}" class="button"><img src="{@$__wcf->getPath()}icon/add.svg" alt="" class="icon24" /> <span>{@$objectType->getProcessor()->getLanguageVariable('add')}</span></a></li>
					{/if}
					
					{event name='contentNavigationButtons'}
				{/content}
			</ul>
		</nav>
	</div>
{/hascontent}

{if $categoryNodeList|count}
	<section id="categoryList" class="container containerPadding marginTop shadow{if $objectType->getProcessor()->canEditCategory() && $categoryNodeList|count > 1} sortableListContainer{/if}">
		<ol class="categoryList sortableList" data-object-id="0">
			{assign var=oldDepth value=0}
			{foreach from=$categoryNodeList item=category}
				{section name=i loop=$oldDepth-$categoryNodeList->getDepth()}</ol></li>{/section}
				
				<li class="{if $objectType->getProcessor()->canEditCategory() && $categoryNodeList|count > 1}sortableNode {/if}jsCategory" data-object-id="{@$category->categoryID}"{if $collapsedCategoryIDs|is_array} data-is-open="{if $collapsedCategoryIDs[$category->categoryID]|isset}0{else}1{/if}"{/if}>
					<span class="sortableNodeLabel">
						<span class="buttons">
							{if $objectType->getProcessor()->canEditCategory()}
								<a href="{link controller=$editController id=$category->categoryID title=$category->getTitle()}{/link}"><img src="{@$__wcf->getPath()}icon/edit.svg" alt="" title="{lang}wcf.global.button.edit{/lang}" class="icon16 jsTooltip" /></a>
							{else}
								<img src="{@$__wcf->getPath()}icon/edit.svg" alt="" title="{lang}wcf.global.button.edit{/lang}" class="icon16 disabled" />
							{/if}

							{if $objectType->getProcessor()->canDeleteCategory()}
								<img src="{@$__wcf->getPath()}icon/delete.svg" alt="" title="{lang}wcf.global.button.delete{/lang}" class="icon16 jsDeleteButton jsTooltip" data-object-id="{@$category->categoryID}" data-confirm-message="{@$objectType->getProcessor()->getLanguageVariable('delete.sure')}" />
							{else}
								<img src="{@$__wcf->getPath()}icon/delete.svg" alt="" title="{lang}wcf.global.button.delete{/lang}" class="icon16 disabled" />
							{/if}

							{if $objectType->getProcessor()->canEditCategory()}
								<img src="{@$__wcf->getPath()}icon/{if !$category->isDisabled}enabled{else}disabled{/if}.svg" alt="" title="{lang}wcf.global.button.{if !$category->isDisabled}disable{else}enable{/if}{/lang}" class="icon16 jsToggleButton jsTooltip" data-object-id="{@$category->categoryID}" />
							{else}
								<img src="{@$__wcf->getPath()}icon/{if !$category->isDisabled}enabled{else}disabled{/if}.svg" alt="" title="{lang}wcf.global.button.{if !$category->isDisabled}enable{else}disable{/if}{/lang}" class="icon16 disabled" />
							{/if}

							{event name='buttons'}
						</span>

						<span class="title">
							{$category->getTitle()}
						</span>
					</span>
					
					<ol class="categoryList sortableList" data-object-id="{@$category->categoryID}">
				{if !$categoryNodeList->current()->hasChildren()}
					</ol></li>
				{/if}
				{assign var=oldDepth value=$categoryNodeList->getDepth()}
			{/foreach}
			{section name=i loop=$oldDepth}</ol></li>{/section}
		</ol>
		
		{if $objectType->getProcessor()->canEditCategory() && $categoryNodeList|count > 1}
			<div class="formSubmit">
				<button class="button default" data-type="submit">{lang}wcf.global.button.save{/lang}</button>
			</div>
		{/if}
	</section>
		
	{hascontent}
		<div class="contentNavigation">
			<nav>
				<ul>
					{content}
						{if $objectType->getProcessor()->canAddCategory()}
							<li><a href="{link controller=$addController}{/link}" title="{$objectType->getProcessor()->getLanguageVariable('add')}" class="button"><img src="{@$__wcf->getPath()}icon/add.svg" alt="" class="icon24" /> <span>{@$objectType->getProcessor()->getLanguageVariable('add')}</span></a></li>
						{/if}

						{event name='contentNavigationButtons'}
					{/content}
				</ul>
			</nav>
		</div>
	{/hascontent}
{else}
	<p class="warning">{@$objectType->getProcessor()->getLanguageVariable('list.noneAvailable')}</p>
{/if}

{include file='footer'}