<script type="text/javascript">
//<![CDATA[
	new PeriodicalExecuter(function() {
		new Ajax.Request('index.php?page=UserNotificationCount'+SID_ARG_2ND, {
					method: 'get',
					onSuccess: function(transport) {
						outstandingNotifications = parseInt(transport.responseText);
						if (outstandingNotifications > 0) {
							$('userMenuNotificationOverview').addClassName('new');
							$('userMenuNotificationOverview').down('span').update('{lang}wcf.header.userMenu.userNotifications{/lang} (' + outstandingNotifications + ')');
							new Effect.Pulsate('userMenuNotificationOverview', { pulses: 2, duration: 3.0 });
						}
					}
		});
	}, {USER_NOTIFICATION_USER_MENU_LINK_AUTOREFRESH_INTERVALL});
//]]>
</script>