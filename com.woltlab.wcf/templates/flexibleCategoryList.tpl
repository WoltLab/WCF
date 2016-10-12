{if !$flexibleCategoryList|isset}{assign var=flexibleCategoryList value=$categoryList}{/if}
{if !$flexibleCategoryListName|isset}{assign var=flexibleCategoryListName value='categoryIDs'}{/if}
{if !$flexibleCategoryListID|isset}{assign var=flexibleCategoryListID value='flexibleCategoryList'}{/if}
{if !$flexibleCategoryListSelectedIDs|isset}{assign var=flexibleCategoryListSelectedIDs value=$categoryIDs}{/if}
<ol class="flexibleCategoryList" id="{$flexibleCategoryListID}">
	{foreach from=$flexibleCategoryList item=categoryItem}
		<li>
			<div class="containerHeadline">
				<h3><label{if $categoryItem->getDescription()} class="jsTooltip" title="{$categoryItem->getDescription()}"{/if}><input type="checkbox" name="{$flexibleCategoryListName}[]" value="{@$categoryItem->categoryID}" class="jsCategory"{if $categoryItem->categoryID|in_array:$flexibleCategoryListSelectedIDs} checked{/if}> {$categoryItem->getTitle()}</label></h3>
			</div>
			
			{if $categoryItem->hasChildren()}
				<ol>
					{foreach from=$categoryItem item=subCategoryItem}
						<li>
							<label{if $subCategoryItem->getDescription()} class="jsTooltip" title="{$subCategoryItem->getDescription()}"{/if} style="font-size: 1rem;"><input type="checkbox" name="{$flexibleCategoryListName}[]" value="{@$subCategoryItem->categoryID}" class="jsChildCategory"{if $subCategoryItem->categoryID|in_array:$flexibleCategoryListSelectedIDs} checked{/if}> {$subCategoryItem->getTitle()}</label>
							
							{if $subCategoryItem->hasChildren()}
								<ol>
									{foreach from=$subCategoryItem item=subSubCategoryItem}
										<li>
											<label{if $subSubCategoryItem->getDescription()} class="jsTooltip" title="{$subSubCategoryItem->getDescription()}"{/if}><input type="checkbox" name="{$flexibleCategoryListName}[]" value="{@$subSubCategoryItem->categoryID}" class="jsSubChildCategory"{if $subSubCategoryItem->categoryID|in_array:$flexibleCategoryListSelectedIDs} checked{/if}> {$subSubCategoryItem->getTitle()}</label>
										</li>
									{/foreach}
								</ol>
							{/if}
						</li>
					{/foreach}
				</ol>
			{/if}
		</li>
	{/foreach}
</ol>
<script data-relocate="true">
	$(function() {
		new WCF.Category.FlexibleCategoryList('{$flexibleCategoryListID}');
	});
</script>
