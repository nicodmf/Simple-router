<?php

namespace Alinea\Routing{

	class Routes implements  \Iterator{
		private $routes;
		private $queries;
		private $run;
		
		public function __construct(array $routes, boolean $run=null){
			$this->run = $run;
			foreach($routes as $route)
				$this->add($route);
		}
		
		public function add(Route $route){
			$this->routes[] = $route;
		}
		public function analyse($url){
			$this->queries[] = $url;
			foreach($this as $route){
				if($route->matches($url)){
					if(true===$this->run)
						$route->run();
					else
						return $route;
				}
			}			
			return false;
		}
		/* Iterator Implementation */	
		public function current(){return current($this->routes);}
		public function key(){return key($this->routes);}
		public function next(){next($this->routes);}
		public function rewind(){reset($this->routes);}
		public function valid(){return isset($this->routes[$this->key()]);}	
	}

	use Alinea\Routing\Parameter\Method;
	use Alinea\Routing\Parameter\Pattern;
	use Alinea\Routing\Parameter\Action;

	class Route implements \ArrayAccess{
		public $name;
		private $arguments;
		private $pattern;
		
		private $_methods;
		private $_patterns;
		private $_actions;
		
		function __construct($name, $pattern, array $parameters=array()){
			$this->name = $name;
			/* Set Arguments */
			$this->arguments = array();
			preg_match_all("#\{([^}]*)\}#", $pattern, $arguments);
			foreach($arguments[1] as $key=>$argument){
				$this->arguments[$argument] = $argument;
			}
			/* Set Parameters */
			$this->_methods = $this->_patterns = $this->_actions = array();
			foreach($parameters as $parameter){
				if($parameter instanceof Method)
					$this->_methods[$parameter->name] = $parameter;
				elseif($parameter instanceof Pattern)//TODO: Add Throw error if not present
					$this->_patterns[$parameter->name] = $parameter;
				elseif($parameter instanceof Action)
					$this->_actions[] = $paramater;
			}
			/* Prepare Patterns */
			$patterns = $replacements = array();
			foreach($this->_patterns as $name=>$_pattern){
				$patterns[] = "#\{$name\}#";
				$replacements[] = "(".$_pattern->pattern.")";
			}
			/* Make Pattern */
			$pattern = preg_replace($patterns, $replacements, $pattern);
			$pattern = preg_replace("#\{[^}]*\}#", "([^/]*)", $pattern);
			$this->pattern = "#^$pattern$#";
			//var_dump($this->pattern);
			
		}
		public function run(){
			foreach($this->_actions as $action){
				call_user_func_array($action->value, $this->arguments);
			}
		}
		public function matches($url){
			//Todo: Add method control
			if( ! preg_match($this->pattern, $url, $matches))
				return false;
			array_shift($matches);
			foreach($this->arguments as $key=>$argument){
				$this->arguments[$key] = array_shift($matches);				
			}
			return true;
		}
		/* ArrayAccess Implementation */	
		public function offsetExists($key){return isset($this->arguments[$key]);}
		public function offsetGet($key){return $this->arguments[$key];}
		public function offsetSet($key, $value){throw new \Exception("Routes souldn't be write");}
		public function offsetUnset($key){throw new \Exception("Routes souldn't be write");}	}
}

namespace Alinea\Routing\Parameter{

	abstract class Parameter{
	}
	class Method extends Parameter{
		public $name;
		public $authorized;
		public function __construct($name, $authorized=true){
			$this->name = $name;
			$this->authorized = $authorized;
		}
	}	
	class Pattern extends Parameter{
		public $name;
		public $pattern;
		public function __construct($name, $pattern){
			$this->name = $name;
			$this->pattern = $pattern;
		}
	}
	class Action extends Parameter{
		public $callback;
		public function __construct($callback){
			$this->callback = $callback;
		}
	}
}

namespace {
	if($_SERVER['SCRIPT_FILENAME']==__FILE__ && getcwd()==__DIR__){
		print_r( getcwd ());
		$lang = new Alinea\Routing\Parameter\Pattern("lang", "en|fr|es");
		$version = new Alinea\Routing\Parameter\Pattern("version", "master|2.0");

		$routes = new Alinea\Routing\Routes(array(
			new Alinea\Routing\Route("root", "/"),
			new Alinea\Routing\Route("lang", "/{lang}", array($lang)),
			new Alinea\Routing\Route("version", "/{lang}/{version}", array($lang, $version)),
			new Alinea\Routing\Route("version", "/{lang}/{version}/{user}", array($lang, $version)),
		));
		if(defined("Alinea\Routing\Test")){
			print_r($routes->analyse("/"));
			print_r($routes->analyse("/es"));
			print_r($routes->analyse("/es/2.0"));
			print_r($routes->analyse("/es/2.0/test"));
		}
	}
}