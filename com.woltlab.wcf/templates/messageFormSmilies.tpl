{assign var=__tabCount value=0}
{capture assign=__categoryTabs}
	{foreach from=$smileyCategories item=smileyCategory}
		{assign var=__tabCount value=$__tabCount + 1}
		{assign var='__smileyAnchor' value='smilies-'|concat:$smileyCategory->categoryID}
		<li><a href="{$__wcf->getAnchor($__smileyAnchor)}" data-smiley-category-id="{@$smileyCategory->categoryID}">{$smileyCategory->title|language}</a></li>
	{/foreach}
{/capture}

<div id="smilies" class="jsOnly smiliesContent tabMenuContent container containerPadding{if $__tabCount} tabMenuContainer{/if}">
	{capture assign=__defaultSmilies}
		{include file='__messageFormSmilies' smilies=$defaultSmilies}
	{/capture}
	
	{if $__tabCount > 1}
		<nav class="menu">
			<ul>
				{@$__categoryTabs}
			</ul>
		</nav>
		
		{foreach from=$smileyCategories item=smileyCategory}
			{if !$smileyCategory->isDisabled}
				<div id="smilies-{@$smileyCategory->categoryID}" class="hidden">
					{if !$smileyCategory->categoryID}{@$__defaultSmilies}{/if}
				</div>
			{/if}
		{/foreach}
		
		<script data-relocate="true">
			//<![CDATA[
			$(function() {
				new WCF.Message.SmileyCategories();
			});
			//]]>
		</script>
	{else}
		{@$__defaultSmilies}
	{/if}
	
	{event name='fields'}
	
	<script data-relocate="true">
		//<![CDATA[
		$(function() {
			new WCF.Message.Smilies('{if $wysiwygSelector|isset}{$wysiwygSelector|encodeJS}{else}text{/if}');
		});
		//]]>
	</script>
</div>
