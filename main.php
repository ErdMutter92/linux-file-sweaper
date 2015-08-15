<?php

class Main
{
	protected $args;
	protected $user;
	protected $home;
	protected $dir;
	protected $config;
	protected $verbose;
	
	protected $sweap = array(
			'iso' => 'unsorted',
			'deb' => 'unsorted',
			'gz' => 'unsorted',
			'jpg' => 'unsorted',
			'mp4' => 'unsorted',
			'mp3' => 'unsorted',
	);
	
	protected $ignore = array(
			'crdownload',
	);
	
	public function __construct($argv)
	{
		$this->verbose = ($this->verbose == true) ? true : false;
		$argv[] = '-clean';
		$argv[] = '';
		$this->args = $argv;
		
		// get the systems username and set the home dir.
		$this->user = get_current_user();
		$this->home = '/home/' . $this->user . '/';
		
		foreach ($this->args as $location => $args) {
			$chars = str_split($args);
			if ($chars[0] === '-') {
				$tmp = explode('-', $args);
				$function = end($tmp);
				unset($tmp);
				
				// this does a check to make sure we can only
				// run public functions via this constructor.
				$check = new ReflectionMethod($this, $function);
				if (!$check->isPublic()) { continue; }
				
				$this->$function($argv[++$location]);
			}
		}
	}
	
	/**
	 * Takes the directory flag and sets it to the varibale.
	 * default: Downloads
	 * 
	 * @param string $dir
	 */
	public function D($dir)
	{
		$this->dir = $dir;
		return $this;
	}
	
	/**
	 * Takes the verbose flag and sets it to the variable.
	 * default: false
	 * 
	 * @param boolean $verbose
	 */
	public function V($verbose)
	{
		$verbose = ($verbose == true) ? true : false;
		$this->verbose = $verbose;
		return $this;
	}
	
	/**
	 * Takes the config flag and sets it to the variable.
	 * default: internal
	 * 
	 * @param unknown $config
	 */
	public function C($config)
	{
		if ($this->verbose === true) {
			echo 'Loading config file ' . $config, PHP_EOL;
		}
		
		$this->config = $config;
		
		// reads the config file.
		$contents = file_get_contents($config);
		$configs = json_decode($contents);
		
		// reset the sweap config to that in the config file
		// is present.
		if (property_exists($configs, 'sweap')) {
			$this->sweap = array();
		}
		foreach ($configs->sweap as $key => $sweap) {
			$this->sweap[$key] = $sweap;
		}

		// reset the ignore config to that in the config file
		// is present.
		if (property_exists($configs, 'ignore')) {
			$this->ignore = array();
		}
		foreach ($configs->ignore as $key => $ignore) {
			$this->ignore[$key] = $ignore;
		}
	}
	
	protected function buildDirTree($dir = null)
    {
        $array = array(
            'file' => array(),
        );
        if (is_null($dir)) {
            $dir = $this->dir;
        }
        
        $scandir = scandir($dir);
        
        foreach ($scandir as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }
            
            if (is_dir($dir . $file)) {
                $array[$file] = $this->buildDirTree($dir . $file);
            } else {
                $array['file'][] = $file;
            } 
        }
        
        return $array;
    }
	
	public function clean()
	{
		// Default the directory to be swept to downloads.
		if (is_null($this->dir)) {
			$this->dir = 'Downloads';
		}
		
		// creates a string of the desired directory
		// location in relation to the home directory.
		$dir = $this->home . $this->dir . '/';
		
		// Generate a file/dir tree
		$tree = $this->buildDirTree($dir);
		
		// craw through the array for unplaced files in the
		// main folder, and move them acordingly.
		foreach($tree['file'] as $file) {
			$pathInfo = pathinfo($dir . $file);
			
			// if there is no extension in the pathinfo
			// set it to unknown.
			if (isset($pathInfo['extension'])) {
				$fileExtension = $pathInfo['extension'];
			} else {
				$fileExtension = 'unknown';
			}
			
			// if the extension is in the ignore list
			// skip over the file.
			if (in_array($fileExtension, $this->ignore)) {
				continue;
			}
			
			// check the sweap list, and use its instructions
			// on where to place stuff. If unlisted, keep it in
			// the current directory.
			$inSweapList = key_exists($fileExtension, $this->sweap);
			if (! is_dir($dir . $fileExtension) && !$inSweapList) {
				if ($this->verbose === true) {
					echo 'Creating directory ' . $dir . $fileExtension, PHP_EOL;
				}
				mkdir($dir . $fileExtension);
			}
			
			if ($inSweapList) {
				$sweapDir = $this->home  . $this->sweap[$fileExtension];
				
				// if the folder in the sweap directory
				// does not exist create it.
				if (! is_dir($sweapDir)) {
					if ($this->verbose === true) {
						echo 'Creating directory ' . $sweapDir, PHP_EOL;
					}
					mkdir($sweapDir);
				}

				// move the file to the correct location.
				$from = $dir . '' . $file;
				$to = $sweapDir . '/' . $file;
				rename($from, $to);
			} else {
				
				// move the file to the correct location.
				$from = $dir . '' . $file;
				$to = $dir . '' . $fileExtension . '/' . $file;
				rename($from, $to);
			}
			
			if ($this->verbose === true) {
				echo 'Swept ' . $file . ' to ' . $to, PHP_EOL;
			}
		}
		
	}
}

$main = new Main($argv);