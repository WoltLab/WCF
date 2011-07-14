<?xml version="1.0" encoding="{@CHARSET}"?>
<rss version="2.0">
	<channel>
		<title>{lang}wcf.user.notification.feed.title{/lang}</title>
		<link>{@PAGE_URL}/</link>
		<description>{lang}wcf.user.notification.feed.description{/lang}</description>

		<pubDate>{@'r'|gmdate:TIME_NOW}</pubDate>
		<lastBuildDate>{@'r'|gmdate:TIME_NOW}</lastBuildDate>
		<generator>WoltLab Community Framework {@WCF_VERSION}</generator>
		<ttl>60</ttl>

		{foreach from=$notifications item=notification}
			<item>
				<title><![CDATA[{$notification->shortOutput}]]></title>
				<author>{$this->user->username}</author>
				<link>{@PAGE_URL}/index.php?page=UserNotification</link>
				<guid>{@PAGE_URL}/index.php?page=UserNotification&amp;notificationID={@$notification->notificationID}</guid>
				<pubDate>{@'r'|gmdate:$notification->time}</pubDate>
				<description><![CDATA[{@$notification->longOutput}]]></description>
			</item>
		{/foreach}
	</channel>
</rss>