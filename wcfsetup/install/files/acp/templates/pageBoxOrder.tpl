{include file='header' pageTitle='wcf.acp.page.boxOrder'}

<style>
	#pbo [data-placeholder] {
		background-color: rgb(224, 224, 224);
		padding: 10px;
	}
</style>

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.page.boxOrder{/lang}</h1>
	</div>
	
	<nav class="contentHeaderNavigation">
		<ul>
			<li><a href="{link controller='LabelAdd'}{/link}" class="button"><span class="icon icon16 fa-plus"></span> <span>{lang}wcf.acp.label.add{/lang}</span></a></li>
			
			{event name='contentHeaderNavigation'}
		</ul>
	</nav>
</header>

<div class="section" id="pbo">
	<div data-placeholder="hero"></div>
	<div class="pbo-quad" data-placeholder="headerBoxes"></div>
	<div data-placeholder="top"></div>
	
	<div class="pbo-main">
		<div data-placeholder="sidebarLeft"></div>
		<div>
			<div data-placeholder="contentTop"></div>
			<div class="pbo-content">{lang}wcf.acp.page.boxOrder.position.content{/lang}</div>
			<div data-placeholder="contentBottom"></div>
		</div>
		<div data-placeholder="sidebarRight"></div>
	</div>
	
	<div data-placeholder="bottom"></div>
	<div class="pbo-quad" data-placeholder="footerBoxes"></div>
	<div data-placeholder="footer"></div>
</div>

{include file='footer'}
