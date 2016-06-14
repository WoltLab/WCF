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
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Util
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
	 * @var	string[]
	 */
	protected static $availableTimezones = [
		// there is not support for UTC-12:00 in php
		// '...', // (UTC-12:00) International Date Line West
		'Pacific/Samoa', // (UTC-11:00) Midway Island, American Samoa
		'Pacific/Honolulu', // (UTC-10:00) Hawaii
		'America/Anchorage', // (UTC-09:00) Alaska
		'America/Los_Angeles', // (UTC-08:00) Pacific Time (US & Canada), Tijuana, Baja California
		'America/Phoenix', // (UTC-07:00) Arizona
		'America/Chihuahua', // (UTC-07:00) Chihuahua, Mazatlan
		'America/Denver', // (UTC-07:00) Mountain Time (US & Canada)
		'America/Chicago', // (UTC-06:00) Central Time (US & Canada)	
		'America/Mexico_City', // (UTC-06:00) Mexico City, Monterrey
		'America/Tegucigalpa', // (UTC-06:00) Central America
		'America/Regina', // (UTC-06:00) Saskatchewan
		'America/Bogota', // (UTC-05:00) Bogota, Lima
		'America/New_York', // (UTC-05:00) Eastern Time (US & Canada)
		'America/Indiana/Indianapolis', // (UTC-05:00) Indiana (East)
		'America/Rio_Branco', // (UTC-05:00) Rio Branco
		'America/Caracas', // (UTC-04:30) Caracas
		'America/Asuncion', // (UTC-04:00) Asuncion
		'America/Halifax', // (UTC-04:00) Atlantic Time (Canada)
		'America/Cuiaba', // (UTC-04:00) Cuiaba
		'America/La_Paz', // (UTC-04:00) Georgetown, La Paz, Manaus
		'America/Santiago', // (UTC-04:00) Santiago
		'America/St_Johns', // (UTC-03:30) Newfoundland
		'America/Sao_Paulo', // (UTC-03:00) Brasilia
		'America/Argentina/Buenos_Aires', // (UTC-03:00) Buenos Aires
		'America/Cayenne', // (UTC-03:00) Cayenne
		'America/Godthab', // (UTC-03:00) Greenland
		'America/Montevideo', // (UTC-03:00) Montevideo
		'Atlantic/South_Georgia', // (UTC-02:00) Mid-Atlantic
		'Atlantic/Azores', // (UTC-01:00) Azores
		'Atlantic/Cape_Verde', // (UTC-01:00) Cape Verde Is.
		'Africa/Casablanca', // (UTC) Casablanca
		'Europe/London', // (UTC) Dublin, Lisbon, London
		'Africa/Monrovia', // (UTC) Monrovia, Reykjavik
		'Europe/Berlin', // (UTC+01:00) Amsterdam, Berlin, Rome, Stockholm, Vienna
		'Europe/Belgrade', // (UTC+01:00) Belgrade, Bratislava, Budapest, Ljubljana, Prague
		'Europe/Paris', // (UTC+01:00) Brussels, Copenhagen, Madrid, Paris
		'Europe/Sarajevo', // (UTC+01:00) Sarajevo, Skopje, Warsaw, Zagreb
		'Africa/Algiers', // (UTC+01:00) West Central Africa
		'Africa/Windhoek', // (UTC+01:00) Windhoek
		'Europe/Athens', // (UTC+02:00) Athens, Bucharest, Istanbul
		'Asia/Beirut', // (UTC+02:00) Beirut
		'Asia/Damascus', // (UTC+02:00) Damascus
		'Africa/Harare', // (UTC+02:00) Harare, Pretoria
		'Europe/Helsinki', // (UTC+02:00) Helsinki, Kiev, Riga, Sofia, Tallinn, Vilnius
		'Asia/Jerusalem', // (UTC+02:00) Jerusalem
		'Africa/Cairo', // (UTC+02:00) Cairo
		'Europe/Kaliningrad', // (UTC+02:00) Kaliningrad
		'Asia/Amman', // (UTC+03:00) Amman
		'Asia/Baghdad', // (UTC+03:00) Baghdad
		'Europe/Minsk', // (UTC+03:00) Minsk
		'Europe/Moscow', // (UTC+03:00) Moscow, Volgograd
		'Asia/Kuwait', // (UTC+03:00) Kuwait, Riyadh
		'Africa/Nairobi', // (UTC+03:00) Nairobi
		'Asia/Tehran', // (UTC+03:30) Tehran
		'Asia/Muscat', // (UTC+04:00) Muscat
		'Asia/Baku', // (UTC+04:00) Baku
		'Asia/Yerevan', // (UTC+04:00) Yerevan
		'Indian/Mauritius', // (UTC+04:00) Port Loius
		'Asia/Tbilisi', // (UTC+04:00) Tbilisi
		'Asia/Kabul', // (UTC+04:30) Kabul
		'Asia/Karachi', // (UTC+05:00) Karachi
		'Asia/Yekaterinburg', // (UTC+05:00) Ekaterinburg
		'Asia/Tashkent', // (UTC+05:00) Tashkent
		'Asia/Kolkata', // (UTC+05:30) Calcutta, New Dehli
		'Asia/Colombo', // (UTC+05:30) Sri Jayawardenepura
		'Asia/Katmandu', // (UTC+05:45) Kathmandu
		'Asia/Almaty', // (UTC+06:00) Almaty
		'Asia/Dhaka', // (UTC+06:00) Dhaka
		'Asia/Novosibirsk', // (UTC+06:00) Novosibirsk
		'Asia/Rangoon', // (UTC+06:30) Yangon (Rangoon)
		'Asia/Bangkok', // (UTC+07:00) Bangkok, Jakarta
		'Asia/Krasnoyarsk', // (UTC+07:00) Krasnoyarsk
		'Asia/Irkutsk', // (UTC+08:00) Irkutsk
		'Asia/Kuala_Lumpur', // (UTC+08:00) Kuala Lumpur, Singapore
		'Asia/Chongqing', // (UTC+08:00) Beijing, Chongqing, Hong Kong
		'Australia/Perth', // (UTC+08:00) Perth
		'Asia/Taipei', // (UTC+08:00) Taipei
		'Asia/Ulaanbaatar', // (UTC+08:00) Ulaan Bataar
		'Asia/Yakutsk', // (UTC+09:00) Yakutsk
		'Asia/Tokyo', // (UTC+09:00) Tokyo
		'Asia/Seoul', // (UTC+09:00) Seoul
		'Australia/Adelaide', // (UTC+09:30) Adelaide
		'Australia/Darwin', // (UTC+09:30) Darwin
		'Australia/Brisbane', // (UTC+10:00) Brisbane
		'Australia/Sydney', // (UTC+10:00) Canberra, Melbourne, Sydney
		'Pacific/Guam', // (UTC+10:00) Guam, Port Moresby
		'Australia/Hobart', // (UTC+10:00) Hobart
		'Asia/Vladivostok', // (UTC+10:00) Vladivostok
		'Pacific/Noumea', // (UTC+11:00) New Caledonia
		'Pacific/Auckland', // (UTC+12:00) Auckland
		'Pacific/Fiji', // (UTC+12:00) Fiji
		'Pacific/Tongatapu', // (UTC+13:00) Nukualofa
		'Pacific/Apia', // (UTC+13:00) Samoa
	];
	
	/**
	 * first day of the week
	 * 0=sunday
	 * 1=monday
	 * @var	integer
	 */
	private static $firstDayOfTheWeek = null;
	
	/**
	 * order of the week days
	 * @var	string[]
	 */
	private static $weekDays = null;
	
	/**
	 * order of the week days (short textual representation)
	 * @var	string[]
	 */
	private static $shortWeekDays = null;
	
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
		
		$direction = '';
		switch ($interval->format('%R')) {
			case '+':
				$direction = 'past';
			break;
			case '-':
				$direction = 'future';
			break;
		}
		
		if ($fullInterval) {
			return WCF::getLanguage()->getDynamicVariable('wcf.date.interval.full.'.$direction, [
				'days' => $days - 7 * $weeks,
				'firstElement' => $years ? 'years' : ($months ? 'months' : ($weeks ? 'weeks' : ($days ? 'days' : ($hours ? 'hours' : 'minutes')))),
				'hours' => $hours,
				'lastElement' => !$minutes ? (!$hours ? (!$days ? (!$weeks ? (!$months ? 'years' : 'months') : 'weeks') : 'days') : 'hours') : 'minutes',
				'minutes' => $minutes,
				'months' => $months,
				'weeks' => $weeks,
				'years' => $years
			]);
		}
		
		if ($years) {
			return WCF::getLanguage()->getDynamicVariable('wcf.date.interval.years.'.$direction, [
				'years' => $years
			]);
		}
		
		if ($months) {
			return WCF::getLanguage()->getDynamicVariable('wcf.date.interval.months.'.$direction, [
				'months' => $months
			]);
		}
		
		if ($weeks) {
			return WCF::getLanguage()->getDynamicVariable('wcf.date.interval.weeks.'.$direction, [
				'weeks' => $weeks
			]);
		}
		
		if ($days) {
			return WCF::getLanguage()->getDynamicVariable('wcf.date.interval.days.'.$direction, [
				'days' => $days
			]);
		}
		
		if ($hours) {
			return WCF::getLanguage()->getDynamicVariable('wcf.date.interval.hours.'.$direction, [
				'hours' => $hours
			]);
		}
		
		return WCF::getLanguage()->getDynamicVariable('wcf.date.interval.minutes.'.$direction, [
			'minutes' => $minutes
		]);
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
				$date = str_replace(['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'], [
					$language->get('wcf.date.day.sunday'),
					$language->get('wcf.date.day.monday'),
					$language->get('wcf.date.day.tuesday'),
					$language->get('wcf.date.day.wednesday'),
					$language->get('wcf.date.day.thursday'),
					$language->get('wcf.date.day.friday'),
					$language->get('wcf.date.day.saturday')
				], $date);
			}
			
			// textual representation of a day, three letters (D)
			if (strpos($format, 'D') !== false) {
				$date = str_replace(['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'], [
					$language->get('wcf.date.day.sun'),
					$language->get('wcf.date.day.mon'),
					$language->get('wcf.date.day.tue'),
					$language->get('wcf.date.day.wed'),
					$language->get('wcf.date.day.thu'),
					$language->get('wcf.date.day.fri'),
					$language->get('wcf.date.day.sat')
				], $date);
			}
			
			// full textual representation of a month (F)
			if (strpos($format, 'F') !== false) {
				$date = str_replace(['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'], [
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
				], $date);
			}
			
			// short textual representation of a month (M)
			if (strpos($format, 'M') !== false) {
				$date = str_replace(['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'], [
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
				], $date);
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
	 * @return	string[]
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
	 * @throws	SystemException
	 */
	public static function validateDate($date) {
		if (preg_match('~^(?P<year>[0-9]{4})-(?P<month>[0-9]{2})-(?P<day>[0-9]{2})~', $date, $matches)) {
			if (!checkdate($matches['month'], $matches['day'], $matches['year'])) {
				throw new SystemException("Date '".$date."' is invalid");
			}
		}
		else {
			throw new SystemException("Date '".$date."' is not a valid ISO-8601 date");
		}
	}
	
	/**
	 * Returns the first day of the week.
	 * 
	 * @return	integer
	 */
	public static function getFirstDayOfTheWeek() {
		if (self::$firstDayOfTheWeek === null) {
			self::$firstDayOfTheWeek = intval(WCF::getLanguage()->get('wcf.date.firstDayOfTheWeek'));
			if (self::$firstDayOfTheWeek != 1 && self::$firstDayOfTheWeek != 0) self::$firstDayOfTheWeek = 0;
		}
		
		return self::$firstDayOfTheWeek;
	}
	
	/**
	 * Returns the order of the week days.
	 * 
	 * @return	string[]
	 */
	public static function getWeekDays() {
		if (self::$weekDays === null) {
			if (self::getFirstDayOfTheWeek() == 1) {
				self::$weekDays = [
					1 => 'monday',
					2 => 'tuesday',
					3 => 'wednesday',
					4 => 'thursday',
					5 => 'friday',
					6 => 'saturday',
					0 => 'sunday'
				];
			}
			else {
				self::$weekDays = [
					0 => 'sunday',
					1 => 'monday',
					2 => 'tuesday',
					3 => 'wednesday',
					4 => 'thursday',
					5 => 'friday',
					6 => 'saturday'
				];
			}
		}
		
		return self::$weekDays;
	}
	
	/**
	 * Returns the order of the week days (short textual representation).
	 * 
	 * @return	string[]
	 */
	public static function getShortWeekDays() {
		if (self::$shortWeekDays === null) {
			if (self::getFirstDayOfTheWeek() == 1) {
				self::$shortWeekDays = [
					1 => 'mon',
					2 => 'tue',
					3 => 'wed',
					4 => 'thu',
					5 => 'fri',
					6 => 'sat',
					0 => 'sun'
				];
			}
			else {
				self::$shortWeekDays = [
					0 => 'sun',
					1 => 'mon',
					2 => 'tue',
					3 => 'wed',
					4 => 'thu',
					5 => 'fri',
					6 => 'sat'
				];
			}
		}
		
		return self::$shortWeekDays;
	}
	
	/**
	 * Returns the number of weeks in the given year.
	 * 
	 * @param	integer		$year
	 * @return	integer
	 */
	public static function getWeeksInYear($year) {
		$date = new \DateTime();
		$date->setISODate($year, 53, self::getFirstDayOfTheWeek());
		return ($date->format('W') == 53 ? 53 : 52);
	}
	
	/**
	 * Forbid creation of DateUtil objects.
	 */
	private function __construct() {
		// does nothing
	}
}
