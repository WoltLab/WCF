{hascontent}
	<div class="messageTabMenu" data-preselect="{if $preselectTabMenu|isset}{$preselectTabMenu}{else}false{/if}" data-wysiwyg-container-id="{if $wysiwygContainerID|isset}{$wysiwygContainerID}{else}text{/if}">
		<nav class="messageTabMenuNavigation jsOnly">
			<ul>
				{content}
					{if MODULE_SMILEY && !$smileyCategories|empty}<li data-name="smilies"><a><span class="icon icon16 fa-smile-o"></span> <span>{lang}wcf.message.smilies{/lang}</span></a></li>{/if}
					{event name='tabMenuTabs'}
				{/content}
			</ul>
		</nav>
		
		{if MODULE_SMILEY && !$smileyCategories|empty}{include file='messageFormSmilies'}{/if}
		
		{event name='tabMenuContents'}
	</div>
	
	<script data-relocate="true">
		$(function() {
			$('.messageTabMenu').messageTabMenu();
		});
	</script>
{/hascontent}
