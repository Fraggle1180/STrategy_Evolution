<?
class fsb_sms	{
	protected $log;
	protected $writelog;

	function __construct()	{
		$settings = fsb_getSettings();
		$this->writelog = $settings->get('sms::common::writelog');

		$this->log = fsb_getLog();
	}

	function send($number, $text)	{
		$settings = fsb_getSettings();
		$key = $settings->get('sms::sendtype');

		if ($this->writelog)	$this->log->write('sms', "Send: type=$key, number=$number, text: $text");

		if ($key == 'nul')	return true;
		if ($key == 'con')	{
			print("SMS send($number): $text");
			return true;
		}

		return $this->sendBy_SMSPilot($number, $text);
	}

	protected function sendBy_SMSPilot($number, $text)	{
		$settings = fsb_getSettings();
		$key = $settings->get('sms::apikeys::smspilot');

		$url  = 'http://smspilot.ru/api.php?send='.urlencode($text).'&to='.urlencode($number).'&from=inform&apikey='.$key.'&format=json';
		$json = file_get_contents($url);

		$jarr = json_decode($json, true);

		if (is_null($jarr ))		return false;
		if (isset($jarr['error']))	return false;
		if (!isset($jarr['send']))	return false;

		return true;
	}
};
