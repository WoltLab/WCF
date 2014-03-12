<?php
namespace wcf\util;
use wcf\data\language\Language;
use wcf\data\user\User;
use wcf\system\exception\SystemException;
use wcf\system\WCF;

/**
 * Contains date-related functions.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2014 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	util
 * @category	Community Framework
 */
final class DateUtil {
	/**
	 * name of the default date format language variable
	 * @var	string
	 */
	const DATE_FORMAT = 'wcf.date.dateFormat';
	
	/**
	 * name of the default time format language variable
	 * @var	string
	 */
	const TIME_FORMAT = 'wcf.date.timeFormat';
	
	/**
	 * list of available time zones
	 * @var	array<string>
	 */
	protected static $availableTimezones = array(
		'Pacific/Kwajalein', // (GMT-12:00) International Date Line West
		'Pacific/Midway', // (GMT-11:00) Midway Island
		'Pacific/Samoa', // (GMT-11:00) Samoa
		'Pacific/Honolulu', // (GMT-10:00) Hawaii
		'America/Anchorage', // (GMT-09:00) Alaska
		'America/Tijuana', // (GMT-08:00) Tijuana, Baja California
		'America/Los_Angeles', // (GMT-08:00) Pacific Time (US & Canada)
		'America/Phoenix', // (GMT-07:00) Arizona
		'America/Chihuahua', // (GMT-07:00) Chihuahua, Mazatlan
		'America/Denver', // (GMT-07:00) Mountain Time (US & Canada)
		'America/Chicago', // (GMT-06:00) Central Time (US & Canada)	
		'America/Mexico_City', // (GMT-06:00) Mexico City, Monterrey
		'America/Tegucigalpa', // (GMT-06:00) Central America
		'America/Regina', // (GMT-06:00) Saskatchewan
		'America/Bogota', // (GMT-05:00) Bogota, Lima
		'America/New_York', // (GMT-05:00) Eastern Time (US & Canada)
		'America/Indiana/Indianapolis', // (GMT-05:00) Indiana (East)
		'America/Rio_Branco', // (GMT-05:00) Rio Branco
		'America/Caracas', // (GMT-04:30) Caracas
		'America/Asuncion', // UTC-04:00 Asuncion
		'America/Halifax', // (GMT-04:00) Atlantic Time (Canada)
		'America/Cuiaba', // UTC-04:00 Cuiaba
		'America/La_Paz', // (GMT-04:00) Georgetown, La Paz, Manaus
		'America/Santiago', // (GMT-04:00) Santiago
		'America/St_Johns', // (GMT-03:30) Newfoundland
		'America/Sao_Paulo', // (GMT-03:00) Brasilia
		'America/Argentina/Buenos_Aires', // (GMT-03:00) Buenos Aires
		'America/Cayenne', // UTC-03:00 Cayenne
		'America/Godthab', // (GMT-03:00) Greenland
		'America/Montevideo', // (GMT-03:00) Montevideo
		'Atlantic/South_Georgia', // (GMT-02:00) Mid-Atlantic
		'Atlantic/Azores', // (GMT-01:00) Azores
		'Atlantic/Cape_Verde', // (GMT-01:00) Cape Verde Is.
		'Africa/Casablanca', // (GMT) Casablanca
		'Europe/London', // (GMT) Dublin, Lisbon, London
		'Africa/Monrovia', // (GMT) Monrovia, Reykjavik
		'Europe/Berlin', // (GMT+01:00) Amsterdam, Berlin, Rome, Stockholm, Vienna
		'Europe/Belgrade', // (GMT+01:00) Belgrade, Bratislava, Budapest, Ljubljana, Prague
		'Europe/Paris', // (GMT+01:00) Brussels, Copenhagen, Madrid, Paris
		'Europe/Sarajevo', // (GMT+01:00) Sarajevo, Skopje, Warsaw, Zagreb
		'Africa/Algiers', // (GMT+01:00) West Central Africa
		'Africa/Windhoek', // (GMT+01:00) Windhoek
		'Asia/Amman', // (GMT+02:00) Amman
		'Europe/Athens', // (GMT+02:00) Athens, Bucharest, Istanbul
		'Asia/Beirut', // (GMT+02:00) Beirut
		'Asia/Damascus', // (GMT+02:00) Damascus
		'Africa/Harare', // (GMT+02:00) Harare
		'Europe/Helsinki', // (GMT+02:00) Helsinki, Kiev, Riga, Sofia, Tallinn, Vilnius
		'Asia/Jerusalem', // (GMT+02:00) Jerusalem
		'Africa/Cairo', // (GMT+02:00) Cairo
		'Europe/Minsk', // (GMT+02:00) Minsk
		'Asia/Baghdad', // (GMT+03:00) Baghdad
		'Asia/Kuwait', // (GMT+03:00) Kuwait, Riyadh
		'Africa/Nairobi', // (GMT+03:00) Nairobi
		'Asia/Tehran', // (GMT+03:30) Tehran
		'Asia/Muscat', // (GMT+04:00) Muscat
		'Asia/Baku', // (GMT+04:00) Baku
		'Asia/Yerevan', // (GMT+04:00) Yerevan
		'Europe/Moscow', // (GMT+04:00) Moscow, Volgograd
		'Indian/Mauritius', // UTC+04:00 Port Loius
		'Asia/Tbilisi', // UTC+04:00 Tbilisi
		'Asia/Kabul', // UTCU+04:30 Kabul
		'Asia/Karachi', // (GMT+05:00) Karachi
		'Asia/Yekaterinburg', // (GMT+05:00) Ekaterinburg
		'Asia/Tashkent', // (GMT+05:00) Tashkent
		'Asia/Kolkata', // (GMT+05:30) Calcutta, New Dehli
		'Asia/Colombo', // (GMT+05:30) Sri Jayawardenepura
		'Asia/Katmandu', // (GMT+05:45) Kathmandu
		'Asia/Almaty', // (GMT+06:00) Almaty
		'Asia/Dhaka', // (GMT+06:00) Dhaka
		'Asia/Novosibirsk', // (GMT+06:00) Novosibirsk
		'Asia/Rangoon', // (GMT+06:30) Yangon (Rangoon)
		'Asia/Bangkok', // (GMT+07:00) Bangkok, Jakarta
		'Asia/Krasnoyarsk', // (GMT+07:00) Krasnoyarsk
		'Asia/Irkutsk', // (GMT+08:00) Irkutsk
		'Asia/Kuala_Lumpur', // (GMT+08:00) Kuala Lumpur, Singapore
		'Asia/Chongqing', // (GMT+08:00) Beijing, Chongqing, Hong Kong
		'Australia/Perth', // (GMT+08:00) Perth
		'Asia/Taipei', // (GMT+08:00) Taipei
		'Asia/Ulaanbaatar', // (GMT+08:00) Ulaan Bataar
		'Asia/Yakutsk', // (GMT+09:00) Yakutsk
		'Asia/Tokyo', // (GMT+09:00) Tokyo
		'Asia/Seoul', // (GMT+09:00) Seoul
		'Australia/Adelaide', // (GMT+09:30) Adelaide
		'Australia/Darwin', // (GMT+09:30) Darwin
		'Australia/Brisbane', // (GMT+10:00) Brisbane
		'Australia/Sydney', // (GMT+10:00) Canberra, Melbourne, Sydney
		'Pacific/Guam', // (GMT+10:00) Guam, Port Moresby
		'Australia/Hobart', // (GMT+10:00) Hobart
		'Asia/Vladivostok', // (GMT+10:00) Vladivostok
		'Asia/Magadan', // (GMT+11:00) Magadan
		'Pacific/Noumea', // UTC+11:00 New Caledonia
		'Pacific/Auckland', // (GMT+12:00) Auckland
		'Pacific/Fiji', // (GMT+12:00) Fiji
		'Asia/Kamchatka', // (GMT+12:00) Kamchatka
		'Pacific/Tongatapu', // (GMT+13:00) Nukualofa
	);
	
	/**
	 * Returns a formatted date.
	 * 
	 * @param	\DateTime			$time
	 * @param	string				$format
	 * @param	\wcf\data\language\Language	$language
	 * @param	\wcf\data\user\User		$user
	 * @return	string
	 */
	public static function format(\DateTime $time = null, $format = null, Language $language = null, User $user = null) {
		// get default values
		if ($time === null) $time = new \DateTime();
		if ($user === null) $user = WCF::getUser();
		if ($language === null) $language = WCF::getLanguage();
		if ($format === null) $format = self::DATE_FORMAT;
		
		// set time zone
		$time->setTimezone($user->getTimeZone());
		
		// format date
		$output = $time->format($language->get($format));
		
		// localize output
		$output = self::localizeDate($output, $language->get($format), $language);
		
		return $output;
	}
	
	/**
	 * Returns a formatted date interval. If $fullInterval is set true, the
	 * complete interval is returned, otherwise a rounded interval is used.
	 * 
	 * @param	\DateInterval	$interval
	 * @param	boolean		$fullInterval
	 * @return	string
	 */
	public static function formatInterval(\DateInterval $interval, $fullInterval = false) {
		$years = $interval->format('%y');
		$months = $interval->format('%m');
		$days = $interval->format('%d');
		$weeks = floor($days / 7);
		$hours = $interval->format('%h');
		$minutes = $interval->format('%i');
		switch ($interval->format('%R')) {
			case '+':
				$direction = 'past';
			break;
			case '-':
				$direction = 'future';
			break;
		}
		
		if ($fullInterval) {
			return WCF::getLanguage()->getDynamicVariable('wcf.date.interval.full.'.$direction, array(
				'days' => $days - 7 * $weeks,
				'firstElement' => $years ? 'years' : ($months ? 'months' : ($weeks ? 'weeks' : ($days ? 'days' : ($hours ? 'hours' : 'minutes')))),
				'hours' => $hours,
				'lastElement' => !$minutes ? (!$hours ? (!$days ? (!$weeks ? (!$months ? 'years' : 'months') : 'weeks') : 'days') : 'hours') : 'minutes',
				'minutes' => $minutes,
				'months' => $months,
				'weeks' => $weeks,
				'years' => $years
			));
		}
		
		if ($years) {
			return WCF::getLanguage()->getDynamicVariable('wcf.date.interval.years.'.$direction, array(
				'years' => $years
			));
		}
		
		if ($months) {
			return WCF::getLanguage()->getDynamicVariable('wcf.date.interval.months.'.$direction, array(
				'months' => $months
			));
		}
		
		if ($weeks) {
			return WCF::getLanguage()->getDynamicVariable('wcf.date.interval.weeks.'.$direction, array(
				'weeks' => $weeks
			));
		}
		
		if ($days) {
			return WCF::getLanguage()->getDynamicVariable('wcf.date.interval.days.'.$direction, array(
				'days' => $days
			));
		}
		
		if ($hours) {
			return WCF::getLanguage()->getDynamicVariable('wcf.date.interval.hours.'.$direction, array(
				'hours' => $hours
			));
		}
		
		return WCF::getLanguage()->getDynamicVariable('wcf.date.interval.minutes.'.$direction, array(
			'minutes' => $minutes
		));
	}
	
	/**
	 * Returns a localized date output.
	 * 
	 * @param	string				$date
	 * @param	string				$format
	 * @param	\wcf\data\language\Language	$language
	 * @return	string
	 */
	public static function localizeDate($date, $format, Language $language) {
		if ($language->languageCode != 'en') {
			// full textual representation of the day of the week (l)
			if (strpos($format, 'l') !== false) {
				$date = str_replace(array('Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'), array(
					$language->get('wcf.date.day.sunday'),
					$language->get('wcf.date.day.monday'),
					$language->get('wcf.date.day.tuesday'),
					$language->get('wcf.date.day.wednesday'),
					$language->get('wcf.date.day.thursday'),
					$language->get('wcf.date.day.friday'),
					$language->get('wcf.date.day.saturday')
				), $date);
			}
			
			// textual representation of a day, three letters (D)
			if (strpos($format, 'D') !== false) {
				$date = str_replace(array('Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'), array(
					$language->get('wcf.date.day.sun'),
					$language->get('wcf.date.day.mon'),
					$language->get('wcf.date.day.tue'),
					$language->get('wcf.date.day.wed'),
					$language->get('wcf.date.day.thu'),
					$language->get('wcf.date.day.fri'),
					$language->get('wcf.date.day.sat')
				), $date);
			}
			
			// full textual representation of a month (F)
			if (strpos($format, 'F') !== false) {
				$date = str_replace(array('January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'), array(
					$language->get('wcf.date.month.january'),
					$language->get('wcf.date.month.february'),
					$language->get('wcf.date.month.march'),
					$language->get('wcf.date.month.april'),
					$language->get('wcf.date.month.may'),
					$language->get('wcf.date.month.june'),
					$language->get('wcf.date.month.july'),
					$language->get('wcf.date.month.august'),
					$language->get('wcf.date.month.september'),
					$language->get('wcf.date.month.october'),
					$language->get('wcf.date.month.november'),
					$language->get('wcf.date.month.december')
				), $date);
			}
			
			// short textual representation of a month (M)
			if (strpos($format, 'M') !== false) {
				$date = str_replace(array('Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'), array(
					$language->get('wcf.date.month.short.jan'),
					$language->get('wcf.date.month.short.feb'),
					$language->get('wcf.date.month.short.mar'),
					$language->get('wcf.date.month.short.apr'),
					$language->get('wcf.date.month.short.may'),
					$language->get('wcf.date.month.short.jun'),
					$language->get('wcf.date.month.short.jul'),
					$language->get('wcf.date.month.short.aug'),
					$language->get('wcf.date.month.short.sep'),
					$language->get('wcf.date.month.short.oct'),
					$language->get('wcf.date.month.short.nov'),
					$language->get('wcf.date.month.short.dec')
				), $date);
			}
		}
		
		return $date;
	}
	
	/**
	 * Creates a DateTime object with the given unix timestamp.
	 * 
	 * @param	integer		$timestamp
	 * @return	\DateTime
	 */
	public static function getDateTimeByTimestamp($timestamp) {
		return new \DateTime('@'.$timestamp);
	}
	
	/**
	 * Returns a list of available timezones.
	 * 
	 * @return	array<string>
	 */
	public static function getAvailableTimezones() {
		return self::$availableTimezones;
	}
	
	/**
	 * Calculates the age of a given date.
	 * 
	 * @param	string		$date		format YYYY-MM-DD
	 * @return	integer
	 */
	public static function getAge($date) {
		// split date
		$year = $month = $day = 0;
		$value = explode('-', $date);
		if (isset($value[0])) $year = intval($value[0]);
		if (isset($value[1])) $month = intval($value[1]);
		if (isset($value[2])) $day = intval($value[2]);
			
		// calc
		if ($year) {
			$age = self::format(null, 'Y') - $year;
			if (self::format(null, 'n') < $month) $age--;
			else if (self::format(null, 'n') == $month && self::format(null, 'j') < $day) $age--;
			return $age;
		}
		
		return 0;
	}
	
	/**
	 * Validates if given date is valid ISO-8601.
	 * 
	 * @param	string		$date
	 */
	public static function validateDate($date) {
		if (preg_match('~^(?<year>[0-9]{4})-(?<month>[0-9]{2})-(?<day>[0-9]{2})~', $date, $matches)) {
			if (!checkdate($matches['month'], $matches['day'], $matches['year'])) {
				throw new SystemException("Date '".$date."' is invalid");
			}
		}
		else {
			throw new SystemException("Date '".$date."' is not a valid ISO-8601 date");
		}
	}
	
	private function __construct() { }
}
