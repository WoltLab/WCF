<?xml version="1.0" encoding="{@CHARSET}"?>
<feed xmlns="http://www.w3.org/2005/Atom">
	<title>{lang}wcf.user.notification.feed.title{/lang}</title>
	<id>{@PAGE_URL}/</id>
	<updated>{@'c'|gmdate:TIME_NOW}</updated>
	<link href="{@PAGE_URL}/" />
	<generator uri="http://www.woltlab.com/" version="{@WCF_VERSION}">
		WoltLab Community Framework
	</generator>
	<subtitle>{lang}wcf.user.notification.feed.description{/lang}</subtitle>

	{foreach from=$notifications item=notification}
		<entry>
			<title><![CDATA[{$notification->shortOutput}]]></title>
			<id>{@PAGE_URL}/index.php?page=UserNotification&amp;notificationID={@$notification->notificationID}</id>
			<updated>{@'c'|gmdate:$notification->time}</updated>
			<author>
				<name>{$this->user->username}</name>
			</author>
			<content type="html"><![CDATA[{@$notification->longOutput}]]></content>
			<link href="{@PAGE_URL}/index.php?page=UserNotification&amp;notificationID={@$notification->notificationID}" />
		</entry>
	{/foreach}
</feed>