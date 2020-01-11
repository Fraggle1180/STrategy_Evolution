<?
class fsb_list implements Iterator	{
	protected $prefix;
	protected $delimiter;
	protected $list;
	protected $current_list;
	protected $isFirst;

	function __construct($prefix = '', $delimiter = '')	{
		$this->prefix	= $prefix;
		$this->delimiter= $delimiter;

		$this->init();
	}

	function init()	{
		$this->current_list = -1;
		$this->shift();
	}

	function add($text)	{
		$this->add_to($this->addWhat($text));
		return 1;
	}

	protected function addWhat($text)	{
		$what  = ($this->isFirst) ? '' : $this->delimiter;
		$what .= $text;

		return $what;
	}

	protected function add_to($text)	{
		$this->list[$this->current_list] .= $text;
		$this->isFirst = false;
	}

	function shift()	{
		$this->current_list++;
		$this->list[$this->current_list] = $this->prefix;
		$this->isFirst = true;
	}

	public function rewind()	{
		reset($this->list);
	}
  
	public function current()	{
		return current($this->list);
	}
  
	public function key()	{
		return key($this->list);
	}
  
	public function next()	{
		return next($this->list);
	}
  
	public function valid()	{
		$key = key($this->list);
		$var = ($key !== NULL && $key !== FALSE);
		return $var;
	}
};

class fsb_list_limited extends fsb_list	{
	protected $limit;
	protected $current_len;

	function init()	{
		parent::init();
		$this->set_limit(1048576);
	}

	function shift()	{
		$this->current_len = strlen($this->prefix);
		parent::shift();
	}

	function set_limit($limit)	{
		$this->limit = $limit;
	}

	function add($text)	{
		$addWhat = $this->addWhat($text);
		$add_len = strlen($addWhat);

		$bExceed = ($this->current_len + $add_len > $this->limit);
		$return  = 1;

		if ($bExceed)	{
			if ($this->isFirst)	{
				# добавить - новый
				$this->add_to($addWhat);
				$this->shift();
				$return = -1;
			}	else	{
				# новый - добавить
				$this->shift();
				$this->add_to($this->addWhat($text));	# после shift() мог поменяться addWhat() - пропасть разделитель в начале; поэтому нужен повторный вызов
				$return = -2;
			}
		}	else	{
			# добавить
			$this->add_to($addWhat);
		}


		return $return;
	}

	protected function add_to($text)	{
		parent::add_to($text);
		$this->current_len += strlen($text);
	}
}
