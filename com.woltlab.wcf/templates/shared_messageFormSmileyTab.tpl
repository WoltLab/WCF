{assign var=__tabCount value=0}
{capture assign=__categoryTabs}
	{foreach from=$smileyCategories item=smileyCategory}
		{assign var=__tabCount value=$__tabCount + 1}
		{assign var='__smileyAnchor' value='smilies-'|concat:$smileyCategory->categoryID}
		<li data-name="smilies-{$smileyCategory->categoryID}" data-smiley-category-id="{$smileyCategory->categoryID}"><button type="button">{$smileyCategory->getTitle()}</button></li>
	{/foreach}
{/capture}

<div class="messageTabMenuContent{if $__tabCount} messageTabMenu{/if}" data-preselect="true" data-collapsible="false" id="smilies-{if $wysiwygSelector|isset}{$wysiwygSelector}{else}text{/if}">
	{assign var='__firstSmileyCategory' value=$smileyCategories|reset}
	{capture assign=__defaultSmilies}
		{if $__firstSmileyCategory->categoryID}
			{include file='shared_messageFormSmilies' smilies=$__wcf->getSmileyCache()->getCategorySmilies($__firstSmileyCategory->categoryID)}
		{else}
			{include file='shared_messageFormSmilies' smilies=$__wcf->getSmileyCache()->getCategorySmilies()}
		{/if}
	{/capture}
	
	{if $__tabCount > 1}
		<nav class="jsOnly">
			<ul>
				{unsafe:$__categoryTabs}
			</ul>
		</nav>
		
		{foreach from=$smileyCategories item=smileyCategory}
			<div class="messageTabMenuContent" id="smilies-{if $wysiwygSelector|isset}{$wysiwygSelector|encodeJS}{else}text{/if}-{$smileyCategory->categoryID}">
				{if $__firstSmileyCategory->categoryID == $smileyCategory->categoryID}
					{unsafe:$__defaultSmilies}
				{else}
					{include file='shared_messageFormSmilies' smilies=$__wcf->getSmileyCache()->getCategorySmilies($smileyCategory->categoryID)}
				{/if}
			</div>
		{/foreach}
	{else}
		{unsafe:$__defaultSmilies}
	{/if}
	
	{event name='fields'}
	
	<script data-relocate="true">
		require(['WoltLabSuite/Core/Ui/Smiley/Insert'], function (UiSmileyInsert) {
			new UiSmileyInsert('{if $wysiwygSelector|isset}{unsafe:$wysiwygSelector|encodeJS}{else}text{/if}');
		});
	</script>
</div>
