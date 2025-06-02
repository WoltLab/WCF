<{if $box->showHeader}section{else}div{/if} class="box{if $box->getImage()} boxWithImage{/if}{if $box->showEditButton()} boxWithEditButton{/if}{if $box->cssClassName} {$box->cssClassName}{/if}" data-box-identifier="{$box->identifier}">
	{if $box->showEditButton()}
		<a href="{link controller='BoxEdit' id=$box->boxID isACP=true}{/link}" class="boxEditButton jsTooltip" title="{lang}wcf.page.box.edit{/lang}">{icon name='pen-to-square'}</a>
	{/if}
	{if $box->getImage()}
		<div class="boxImage">
			{if $box->hasLink()}
				<a href="{$box->getLink()}">{unsafe:$box->getImage()}</a>
			{else}
				{unsafe:$box->getImage()}
			{/if}
		</div>
	{/if}
	
	{if $box->showHeader}
		<h2 class="boxTitle">
			{if $box->hasLink()}
				<a href="{$box->getLink()}">{$box->getTitle()}</a>
			{else}
				{$box->getTitle()}
			{/if}
		</h2>
	{/if}
	
	<div class="boxContent{if $box->boxType == 'text'} htmlContent{/if}">
		{unsafe:$box->getContent()}
	</div>
{if $box->showHeader}</section>{else}</div>{/if}
