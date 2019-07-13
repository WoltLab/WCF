<ul class="inlineList smileyList">
	{foreach from=$smilies item=smiley name=smilies}
		<li><a title="{$smiley->getTitle()}" class="jsTooltip jsSmiley" role="button" tabindex="{if $tpl.foreach.smilies.iteration === 1}0{else}-1{/if}">{@$smiley->getHtml()}</a></li>
	{/foreach}
</ul>
