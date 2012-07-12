<base href="{$baseHref}" />
<meta charset="utf-8" />
<meta name="description" content="{META_DESCRIPTION}" />
<meta name="keywords" content="{META_KEYWORDS}" />

<script type="text/javascript">
	//<![CDATA[
	var SID_ARG_2ND	= '{@SID_ARG_2ND_NOT_ENCODED}';
	var RELATIVE_WCF_DIR = '{@$__wcf->getPath()}';
	var SECURITY_TOKEN = '{@SECURITY_TOKEN}';
	var LANGUAGE_ID = {@$__wcf->getLanguage()->languageID};
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
			'wcf.global.button.add': '{lang}wcf.global.button.add{/lang}',
			'wcf.global.button.cancel': '{lang}wcf.global.button.cancel{/lang}',
			'wcf.global.button.collapsible': '{lang}wcf.global.button.collapsible{/lang}',
			'wcf.global.button.disable': '{lang}wcf.global.button.disable{/lang}',
			'wcf.global.button.disabledI18n': '{lang}wcf.global.button.disabledI18n{/lang}',
			'wcf.global.button.edit': '{lang}wcf.global.button.edit{/lang}',
			'wcf.global.button.enable': '{lang}wcf.global.button.enable{/lang}',
			'wcf.global.button.next': '{lang}wcf.global.button.next{/lang}',
			'wcf.global.button.preview': '{lang}wcf.global.button.preview{/lang}',
			'wcf.global.button.save': '{lang}wcf.global.button.save{/lang}',
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
			'wcf.global.confirmation.cancel': '{lang}wcf.global.confirmation.cancel{/lang}',
			'wcf.global.confirmation.confirm': '{lang}wcf.global.confirmation.confirm{/lang}',
			'wcf.global.confirmation.title': '{lang}wcf.global.confirmation.title{/lang}',
			'wcf.sitemap.title': '{lang}wcf.sitemap.title{/lang}'
			{event name='javascriptLanguageImport'}
		});
		
		WCF.Icon.addObject({
			'wcf.icon.loading': '{icon size='S'}spinner{/icon}',
			'wcf.icon.opened': '{icon size='S'}arrowDownInverse{/icon}',
			'wcf.icon.closed': '{icon size='S'}arrowRightInverse{/icon}',
			'wcf.icon.arrow.left': '{icon size='S'}arrowLeft{/icon}',
			'wcf.icon.arrow.left.circle': '{icon size='S'}circleArrowLeft{/icon}',
			'wcf.icon.arrow.right': '{icon size='S'}arrowRight{/icon}',
			'wcf.icon.arrow.right.circle': '{icon size='S'}circleArrowRight{/icon}',
			'wcf.icon.arrow.down': '{icon size='S'}arrowDown{/icon}',
			'wcf.icon.arrow.down.circle': '{icon size='S'}circleArroDown{/icon}',
			'wcf.icon.arrow.up': '{icon size='S'}arrowUp{/icon}',
			'wcf.icon.arrow.up.circle': '{icon size='S'}circleArrowUp{/icon}',
			'wcf.icon.dropdown': '{icon size='S'}dropdown{/icon}',
			'wcf.icon.edit': '{icon size='S'}edit{/icon}'
			{event name='javascriptIconImport'}
		});
		
		new WCF.Date.Time();
		new WCF.Effect.SmoothScroll();
		new WCF.Effect.BalloonTooltip();
		new WCF.Sitemap();
		WCF.Dropdown.init();
		
		{event name='javascriptInit'}

		{if $executeCronjobs}
			new WCF.Action.Proxy({
				autoSend: true,
				data: {
					className: 'wcf\\data\\cronjob\\CronjobAction',
					actionName: 'executeCronjobs'
				},
				showLoadingOverlay: false,
				failure: function() {
					return false;
				}
			});
		{/if}
	});
	//]]>
</script>
