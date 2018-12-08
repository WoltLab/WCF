{include file='header' pageTitle='wcf.acp.page.boxOrder'}

<style>
	#pbo {
		border: 1px solid #ccc;
		padding: 5px;
	}
	
	#pbo [data-placeholder] {
		background-color: rgb(224, 224, 224);
		padding: 20px 10px;
		position: relative;
	}
	
	#pbo [data-placeholder]::before {
		content: attr(data-title);
		color: rgb(102, 102, 102);
		font-size: 11px;
		left: 50%;
		position: absolute;
		top: 5px;
		transform: translateX(-50%);
	}
	
	#pbo [data-placeholder] + [data-placeholder] {
		margin-top: 10px;
	}
	
	#pboMain {
		display: flex;
		justify-content: space-between;
	}
	
	#pboMain:not(:first-child) {
		margin-top: 20px;
	}
	
	#pboMain:not(:last-child) {
		margin-bottom: 20px;
	}
	
	#pboMain > div {
		flex: 0 0 calc(33% - 10px);
		max-width: calc(33% - 10px);
	}
	
	#pbo [data-placeholder] ul {
		list-style-position: inside;
		list-style-type: decimal;
		margin-top: 20px;
	}
	
	#pbo [data-placeholder] li {
		padding: 5px;
	}
	
	#pbo [data-placeholder] .ui-sortable > li {
		cursor: move;
	}
	
	#pbo [data-placeholder] .ui-sortable > li::before {
		content: "\f047";
		font-family: FontAwesome;
		padding-right: 5px;
	}
	
	#pbo [data-placeholder] li + li {
		margin-top: 5px;
	}
	
	#pbo [data-placeholder="headerBoxes"] > ul,
	#pbo [data-placeholder="footerBoxes"] > ul {
		display: flex;
		flex-wrap: wrap;
	}
	
	#pbo [data-placeholder="headerBoxes"] > ul > li,
	#pbo [data-placeholder="footerBoxes"] > ul > li {
		flex: 0 0 calc(25% - 10px);
		max-width: calc(25% - 10px);
	}
	
	#pbo [data-placeholder] .sortablePlaceholder::before {
		/* this avoids the icon from being displayed, but will also
		   enforce a matching height for the placeholder */
		visibility: hidden;
	}
	
	#pboContentContainer:first-child:not(:last-child),
	#pboContentContainer:last-child:not(:first-child) {
		flex: 0 0 calc(66% - 10px);
		max-width: calc(66% - 10px);
	}
	
	#pboContentContainer:first-child:last-child {
		flex: 0 0 100%;
		max-width: none;
	}
	
	#pboContent {
		background-color: #BBDEFB;
		padding: 40px 20px;
		text-align: center;
	}
	
	#pboContent:not(:first-child) {
		margin-top: 10px;
	}
	
	#pboContent:not(:last-child) {
		margin-bottom: 10px;
	}
</style>

<script data-relocate="true">
	require(['Dictionary', 'Language', 'WoltLabSuite/Core/Acp/Ui/Page/BoxOrder'], function (Dictionary, Language, AcpUiPageBoxOrder) {
		Language.addObject({
			'wcf.acp.page.boxOrder.discard.confirmMessage': '{lang}wcf.acp.page.boxOrder.discard.confirmMessage{/lang}'
		});
		
		var boxes = new Dictionary();
		{foreach from=$boxes key=position item=boxData}
			{if $position != 'mainMenu'}
				boxes.set('{$position}', [{implode from=$boxData item=box}{ boxID: {@$box->boxID}, name: '{$box->name|encodeJS}' }{/implode}]);
			{/if}
		{/foreach}
		
		AcpUiPageBoxOrder.init({@$page->pageID}, boxes);
	});
	
</script>

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.page.boxOrder{/lang}</h1>
		<p class="contentHeaderDescription">{$page->name}</p>
	</div>
	
	<nav class="contentHeaderNavigation">
		<ul>
			{if $hasCustomShowOrder}<li><a href="#" class="button jsButtonCustomShowOrder"><span class="icon icon16 fa-times"></span> <span>{lang}wcf.acp.page.boxOrder.discard{/lang}</span></a></li>{/if}
			<li><a href="{link controller='PageEdit' id=$page->pageID}{/link}" class="button"><span class="icon icon16 fa-pencil"></span> <span>{lang}wcf.acp.page.edit{/lang}</span></a></li>
			
			{event name='contentHeaderNavigation'}
		</ul>
	</nav>
</header>

<div class="section">
	<div id="pbo">
		{if $boxes[hero]|isset}<div data-placeholder="hero" data-title="{lang}wcf.acp.box.position.hero{/lang}"></div>{/if}
		{if $boxes[headerBoxes]|isset}<div data-placeholder="headerBoxes" data-title="{lang}wcf.acp.box.position.headerBoxes{/lang}"></div>{/if}
		{if $boxes[top]|isset}<div data-placeholder="top" data-title="{lang}wcf.acp.box.position.top{/lang}"></div>{/if}
		
		<div id="pboMain">
			{if $boxes[sidebarLeft]|isset}<div data-placeholder="sidebarLeft" data-title="{lang}wcf.acp.box.position.sidebarLeft{/lang}"></div>{/if}
			<div id="pboContentContainer">
				{if $boxes[contentTop]|isset}<div data-placeholder="contentTop" data-title="{lang}wcf.acp.box.position.contentTop{/lang}"></div>{/if}
				<div id="pboContent">{lang}wcf.acp.page.boxOrder.position.content{/lang}</div>
				{if $boxes[contentBottom]|isset}<div data-placeholder="contentBottom" data-title="{lang}wcf.acp.box.position.contentBottom{/lang}"></div>{/if}
			</div>
			{if $boxes[sidebarRight]|isset}<div data-placeholder="sidebarRight" data-title="{lang}wcf.acp.box.position.sidebarRight{/lang}"></div>{/if}
		</div>
		
		{if $boxes[bottom]|isset}<div data-placeholder="bottom" data-title="{lang}wcf.acp.box.position.bottom{/lang}"></div>{/if}
		{if $boxes[footerBoxes]|isset}<div data-placeholder="footerBoxes" data-title="{lang}wcf.acp.box.position.footerBoxes{/lang}"></div>{/if}
		{if $boxes[footer]|isset}<div data-placeholder="footer" data-title="{lang}wcf.acp.box.position.footer{/lang}"></div>{/if}
	</div>
	
	<div class="formSubmit">
		<button class="button buttonPrimary" data-type="submit">{lang}wcf.global.button.saveSorting{/lang}</button>
	</div>
</div>

{include file='footer'}
