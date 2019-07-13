{assign var=__tabCount value=0}
{capture assign=__categoryTabs}
	{foreach from=$smileyCategories item=smileyCategory}
		{assign var=__tabCount value=$__tabCount + 1}
		{assign var='__smileyAnchor' value='smilies-'|concat:$smileyCategory->categoryID}
		<li data-name="smilies-{@$smileyCategory->categoryID}" data-smiley-category-id="{@$smileyCategory->categoryID}"><a>{$smileyCategory->getTitle()}</a></li>
	{/foreach}
{/capture}

<div class="messageTabMenuContent{if $__tabCount} messageTabMenu{/if}" data-preselect="true" data-collapsible="false" id="smilies-{if $wysiwygSelector|isset}{$wysiwygSelector}{else}text{/if}">
	{capture assign=__defaultSmilies}
		{assign var='__firstSmileyCategory' value=$smileyCategories|reset}
		{if $__firstSmileyCategory->categoryID}
			{include file='__messageFormSmilies' smilies=$__wcf->getSmileyCache()->getCategorySmilies($__firstSmileyCategory->categoryID)}
		{else}
			{include file='__messageFormSmilies' smilies=$__wcf->getSmileyCache()->getCategorySmilies()}
		{/if}
	{/capture}
	
	{if $__tabCount > 1}
		<nav class="jsOnly">
			<ul>
				{@$__categoryTabs}
			</ul>
		</nav>
		
		{foreach from=$smileyCategories item=smileyCategory}
			<div class="messageTabMenuContent" id="smilies-{if $wysiwygSelector|isset}{$wysiwygSelector|encodeJS}{else}text{/if}-{@$smileyCategory->categoryID}">
				{if !$smileyCategory->categoryID}{@$__defaultSmilies}{/if}
			</div>
		{/foreach}
		
		<script data-relocate="true">
			$(function() {
				new WCF.Message.SmileyCategories('{if $wysiwygSelector|isset}{$wysiwygSelector|encodeJS}{else}text{/if}');
			});
		</script>
	{else}
		{@$__defaultSmilies}
	{/if}
	
	{event name='fields'}
	
	<script data-relocate="true">
		require(['WoltLabSuite/Core/Ui/Smiley/Insert'], function (UiSmileyInsert) {
			new UiSmileyInsert('{if $wysiwygSelector|isset}{$wysiwygSelector|encodeJS}{else}text{/if}');
		});
	</script>
</div>
