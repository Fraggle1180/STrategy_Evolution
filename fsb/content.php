<?
class fsb_content	{
	protected $data;
	protected $data_internal;
	protected $include;
	protected $html_header;

	protected $db;
	protected $profiler;

	private   $script_dir;
	private   $header_shown;
	private   $http_status;

	function __construct()	{
		error_reporting(E_ALL);

		$this->data		= array();
		$this->data_internal	= array();

		$this->include		= array( 'controller' => (isset($_REQUEST['mode']) and $_REQUEST['mode']) ? $_REQUEST['mode'] : 'default', 'template' => 'default',
							'controller_dir' => 'ctrl', 'template_dir' => 'view' );
		$this->html_header	= 'Content-Type: text/html; charset=utf-8';

		$this->script_dir	= dirname($_SERVER["SCRIPT_FILENAME"]);
		$this->header_shown	= false;

		$this->db		= new fsb_dbconnection;
		$this->profiler		= new fsb_profiler;
		$this->profiler->Tick('content');

		$this->set_http_status(200);
	}

	function act()	{ 
		$this->profiler->Tick('content');
		try {
			$include  = $this->script_dir . DIRECTORY_SEPARATOR;
			if ($this->include['controller_dir'])	$include .= $this->include['controller_dir'] . DIRECTORY_SEPARATOR;
			$include .= $this->include['controller'] . '.php';

			include($include);
		}
		catch (Exception $e) {
			$this->include['template'] = '';

			print("<h4>Unhandled exception</h4>\n");
			print("Message: " . $e->getMessage() . "<br>\n");
			print("Source: " . $e->getFile() . ", line " . $e->getLine() . "<br>\n");
			print("Code: " . $e->getCode() . "<br>\n");
			print("<!-- <pre>Trace: " . print_r($e->getTrace(), 1) . "</pre><br>\n -->");
		}
		$this->profiler->Tick('content');
	} 

	function show()	{ 
		if (!$this->include['template'])	{
			$this->profiler->Tick('content', 'no template to show');
			return false;
		}

		$this->profiler->Tick('content');
		try {
			$include  = $this->script_dir . DIRECTORY_SEPARATOR;
			if ($this->include['template_dir'])	$include .= $this->include['template_dir'] . DIRECTORY_SEPARATOR;
			$include .= $this->include['template'] . '.php';

			$this->show_header();
			include($include);
		}
		catch (Exception $e) {
			print("<h4>Unhandled exception</h4>\n");
			print("Message: " . $e->getMessage() . "<br>\n");
			print("Source: " . $e->getFile() . ", line " . $e->getLine() . "<br>\n");
			print("Code: " . $e->getCode() . "<br>\n");
			print("<!-- <pre>Trace: " . print_r($e->getTrace(), 1) . "</pre><br>\n -->");
		}
		$this->profiler->Tick('content');
	} 

	function __destruct()	{
		$this->profiler->Tick('content');
	}

	function set_includeOption($name, $value1, $value2=null)	{
		return (is_null($value2)) ?
			$this->set_includeOption2($name, $value1) :
			$this->set_includeOption3($name, $value1, $value2);
	}

	private function set_includeOption2($name, $value_new)	{
		if (!array_key_exists($name, $this->include))
			return false;

		$this->include[$name] = $value_new;
		return true;
	}

	private function set_includeOption3($name, $value_old, $value_new)	{
		if (!array_key_exists($name, $this->include))
			return false;

		if ($this->include[$name] <> $value_old)
			return false;

		$this->include[$name] = $value_new;
		return true;
	}

	function set_http_status($status)	{
		$this->http_status = $status;
	} 

	function show_header()	{
		if ($this->header_shown)	return false;

		if ($this->http_status != 200)	{
			$statuses  = array( 400 => 'Bad Request', 401 => 'Unauthorized', 404 => 'Not Found', 422 => 'Unprocessable Entity', 500 => 'Internal Server Error' );
			$preheader = 'HTTP/1.0 ' . $this->http_status . ' ' . (isset($statuses[$this->http_status]) ? $statuses[$this->http_status] : 'Error') . '; ';
		}	else	$preheader = '';


		header($preheader . $this->html_header);
		$this->header_shown = true;

		return true;
	}
};
