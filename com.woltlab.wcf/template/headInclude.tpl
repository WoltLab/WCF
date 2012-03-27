<base href="{$baseHref}" />
<meta charset="utf-8" />
<meta name="description" content="{META_DESCRIPTION}" />
<meta name="keywords" content="{META_KEYWORDS}" />

<script type="text/javascript">
	//<![CDATA[
	var SID_ARG_2ND	= '{@SID_ARG_2ND_NOT_ENCODED}';
	var RELATIVE_WCF_DIR = '{@$__wcf->getPath()}';
	var SECURITY_TOKEN = '{@SECURITY_TOKEN}';
	//]]>
</script>
<script type="text/javascript" src="{@$__wcf->getPath()}js/3rdParty/jquery.min.js"></script>
<script type="text/javascript" src="{@$__wcf->getPath()}js/3rdParty/jquery-ui.min.js"></script>
<script type="text/javascript" src="{@$__wcf->getPath()}js/3rdParty/jquery.tools.min.js"></script>
<script type="text/javascript" src="{@$__wcf->getPath()}js/3rdParty/jquery-ui.nestedSortable.js"></script>
<script type="text/javascript" src="{@$__wcf->getPath()}js/WCF.js"></script>
<script type="text/javascript">
	//<![CDATA[
	WCF.User.init({@$__wcf->user->userID}, '{@$__wcf->user->username|encodeJS}');
	//]]>
</script>
{event name='javascriptInclude'}

<!-- Stylesheets -->
<link rel="stylesheet/less" type="text/css" href="{@$__wcf->getPath()}style/bootstrap.less" />
<script type="text/javascript">
	//<![CDATA[
	var less = { env: 'development' };
	//]]>
</script>
<script type="text/javascript" src="{@$__wcf->getPath()}js/3rdParty/less.min.js"></script>
<script type="text/javascript">
	//<![CDATA[
	less.watch();
	//]]>
</script>

<noscript>
	<style type="text/css">
		.javascriptOnly {
			display: none !important;
		}
	</style>
</noscript>

<script type="text/javascript">
	//<![CDATA[
	$(function() {
		WCF.Language.addObject({
			'wcf.global.button.next': '{lang}wcf.global.button.next{/lang}',
			'wcf.global.button.edit': '{lang}wcf.global.button.edit{/lang}',
			'wcf.global.error.title': '{lang}wcf.global.error.title{/lang}',
			'wcf.global.loading': '{lang}wcf.global.loading{/lang}',
			'wcf.date.relative.minutes': '{capture assign=relativeMinutes}{lang}wcf.date.relative.minutes{/lang}{/capture}{@$relativeMinutes|encodeJS}',
			'wcf.date.relative.hours': '{capture assign=relativeHours}{lang}wcf.date.relative.hours{/lang}{/capture}{@$relativeHours|encodeJS}',
			'wcf.date.relative.pastDays': '{capture assign=relativePastDays}{lang}wcf.date.relative.pastDays{/lang}{/capture}{@$relativePastDays|encodeJS}',
			'wcf.date.dateTimeFormat': '{lang}wcf.date.dateTimeFormat{/lang}',
			'__days': [ '{lang}wcf.date.day.sunday{/lang}', '{lang}wcf.date.day.monday{/lang}', '{lang}wcf.date.day.tuesday{/lang}', '{lang}wcf.date.day.wednesday{/lang}', '{lang}wcf.date.day.thursday{/lang}', '{lang}wcf.date.day.friday{/lang}', '{lang}wcf.date.day.saturday{/lang}' ],
			'wcf.global.thousandsSeparator': '{capture assign=thousandsSeparator}{lang}wcf.global.thousandsSeparator{/lang}{/capture}{@$thousandsSeparator|encodeJS}',
			'wcf.global.decimalPoint': '{capture assign=decimalPoint}{lang}wcf.global.decimalPoint{/lang}{/capture}{$decimalPoint|encodeJS}',
			'wcf.global.page.next': '{capture assign=pageNext}{lang}wcf.global.page.next{/lang}{/capture}{@$pageNext|encodeJS}',
			'wcf.global.page.previous': '{capture assign=pagePrevious}{lang}wcf.global.page.previous{/lang}{/capture}{@$pagePrevious|encodeJS}',
			'wcf.global.button.collapsible': '{lang}wcf.global.button.collapsible{/lang}',
			'wcf.global.button.disable': '{lang}wcf.global.button.disable{/lang}',
			'wcf.global.button.enable': '{lang}wcf.global.button.enable{/lang}',
			'wcf.global.confirmation.cancel': '{lang}wcf.global.confirmation.cancel{/lang}',
			'wcf.global.confirmation.confirm': '{lang}wcf.global.confirmation.confirm{/lang}',
			'wcf.global.confirmation.title': '{lang}wcf.global.confirmation.title{/lang}'
			{event name='javascriptLanguageImport'}
		});
		
		WCF.Icon.addObject({
			'wcf.icon.loading': '{icon size='S'}spinner1{/icon}',
			'wcf.icon.opened': '{icon size='S'}opened2{/icon}',
			'wcf.icon.closed': '{icon size='S'}closed2{/icon}',
			'wcf.icon.previous': '{icon size='S'}previous1{/icon}',
			'wcf.icon.previous.disabled': '{icon size='S'}previous1D{/icon}',
			'wcf.icon.next': '{icon size='S'}next1{/icon}',
			'wcf.icon.next.disabled': '{icon size='S'}next1D{/icon}',
			'wcf.icon.dropdown': '{icon size='S'}dropdown1{/icon}',
			'wcf.icon.edit': '{icon size='S'}edit1{/icon}'
			{event name='javascriptIconImport'}
		});
		
		new WCF.Date.Time();
		//new WCF.Effect.SmoothScroll();
		new WCF.Effect.BalloonTooltip();
		$('<span class="pointer"><span></span></span>').appendTo('.innerError');
		
		$('#sidebarContent').wcfSidebar();
		
		WCF.Dropdown.init();
		
		{event name='javascriptInit'}
	});
	//]]>
</script>
