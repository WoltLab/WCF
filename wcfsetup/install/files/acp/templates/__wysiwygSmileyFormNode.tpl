<ul class="inlineList smileyList">
	{foreach from=$node->getSmilies() item=smiley name=smilies}
		<li><a class="jsSmiley" role="button" tabindex="{if $tpl.foreach.smilies.iteration === 1}0{else}-1{/if}">{@$smiley->getHtml('jsTooltip')}</a></li>
	{/foreach}
</ul>
