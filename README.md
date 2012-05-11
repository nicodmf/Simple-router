Simple-router
=============
A simple routing class, which include pattern, actions and scheme, see the code to understand.

Use
---

```php
	//Patterns
	$lang='fr|en|es';
	$version='1.0|2.0';

	//Define routes
	$routes = new Alinea\Routing\Routes(array(
		new Alinea\Routing\Route("langs", "/"),
		new Alinea\Routing\Route("user", "/user"),
		new Alinea\Routing\Route("editor", "/{lang}/{version}/editor", array($lang, $version)),
		new Alinea\Routing\Route("lang", "/{lang}", array($lang)),
		new Alinea\Routing\Route("version", "/{lang}/{version}/{test}", array($lang, $version)),
	)
	
	//Analyse
	$route = $routes->analyse($get_route);

	//Routing
	if(!$route){
		header("HTTP/1.0 404 Not Found");
	}else{
		switch($route->name){
			case "langs": ... ;breaks;
			...
		}
	}
```
