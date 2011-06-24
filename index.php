<?php
/**
 * One-File PHP Framework. Because you don't need all that crap.
 * Be sure your .htaccess maps everything to index.php?q=$1
*/

/**
 * Configuration
*/

class Config
{
	const HTACCESS_QUERY_PARAM = 'q';
	const SITE_TITLE = 'One-File PHP Framework';
	const SITE_SLOGAN = 'Because you don\'t need all that crap.';
	const BASE_URL = 'http://thorie.com/onefile';
	const ENVIRONMENT = 'development';
	const LATEST_VERSION = '1.2';
	const TIMEZONE = 'America/Los_Angeles';
	public static function getMenuItems()
	{
		return array(
			'Home' => self::BASE_URL . '/',
			'View Latest (Version ' . self::LATEST_VERSION . ')' => self::BASE_URL . 
				'/download/view?version=' . self::LATEST_VERSION,
			'Download Latest' => self::BASE_URL . 
				'/download/get?version=' . self::LATEST_VERSION,
		);
	}
}

/** 
 * Bootstrap
*/

class Bootstrap
{
	// Custom boostrap code, if needed.
	public static function boot()
	{
	}

	// Standard bootstrap. Needs no modifications.
	public static function run()
	{
		if (Config::ENVIRONMENT == 'development') {
			error_reporting(E_ALL | E_STRICT);
			ini_set('display_errors', true);
		} else {
			error_reporting(0);
			ini_set('display_errors', false);
		}
		date_default_timezone_set(Config::TIMEZONE);
		if (array_key_exists(Config::HTACCESS_QUERY_PARAM, $_REQUEST)) {
			$requestPath = $_REQUEST[Config::HTACCESS_QUERY_PARAM];
		} else {
			$requestPath = '/';
		}
		list($modelName, $actionName) = explode("/", $requestPath);
		if (!$modelName) { 
			$modelName = 'default'; 
		}
		$modelName = ucwords($modelName) . 'Controller';
		if (!$actionName) { 
			$actionName = 'default'; 	
		}
		$actionName .= 'Action';
		$controller = new $modelName;
		self::boot();
		$controller->{$actionName}($_REQUEST);
	}
}

Bootstrap::run();

/**
 * Controllers
*/

class BaseController
{
	public function render($vars = null, $view = 'default')
	{
		if (!is_array($vars)) {
			$vars = array();
		}

		// Built-in view variables.
		$vars['base_url'] = Config::BASE_URL;
		$vars['fw_ver'] = Config::LATEST_VERSION;

		// Render header as a panel.
		$vars += $this->getHeaderVars();	
		$vars['header'] = HeaderView::render($vars);

		// Render footer as a panel.
		$vars += $this->getFooterVars();
		$vars['footer'] = FooterView::render($vars);


		$viewName = ucwords($view).'View';
		$view = new $viewName;
		echo $view->render($vars);
	}
	private function getFooterVars()
	{
		return array(
			'year' => date('Y'),
		);
	}
	private function getHeaderVars()
	{
		$menu = "<ul class='menu'>";
		foreach (Config::getMenuItems() as $name => $link) {
			$menu .= "<li class='menuitem'><a href='$link'>$name</a></li>";
		}
		$menu .= "</ul>";


		return array(
			'title' => Config::SITE_TITLE,
			'slogan' => Config::SITE_SLOGAN,
			'menu' => $menu,
		);
	}
}

class DefaultController extends BaseController
{
	public function defaultAction($request)
	{
		$vars = array();
		parent::render($vars);	
	}
}

class DownloadController extends BaseController
{
	public function viewAction($request)
	{
		if (!is_numeric($request['version'])) {
			throw new Exception("Not numeric.");
		}
		$filename = 'ofphpfw-' . $request['version'] . '/index.php';
		if (!file_exists($filename)) {
			throw new Exception("No such version.");
		}
		$vars = array(
			'codeblock' => "<div style='height:50%;overflow:scroll'><pre>" . 
				htmlentities(file_get_contents($filename), ENT_QUOTES) . 
				"</pre></div>",
		);
			
		parent::render($vars, 'download');
	}
}


/**
 * Models
*/

class SampleModel
{
}

/**
 * Views
*/

abstract class BaseView
{
	abstract public static function render($vars = null);
}

class HeaderView extends BaseView
{
	public static function render($vars = null)
	{
		return <<<EOT
<html>
<head>
<title>{$vars['title']}</title>
</head>
<style>
.menu {
	padding: 0px;
	list-style-type: none;
	width: 480px;
	height: 35px;
	margin: auto;
}
.menuitem {
	float: left;
}
.menuitem a {
	padding-left: 22px;
	padding-right: 22px;
	display: block;
}
</style>
<body>
<div align="center" style="font-size:200%">{$vars['title']}</div>
<div align="center" style="font-size:120%">{$vars['slogan']}</div>
<div align="center" style="margin-top:30px">{$vars['menu']}</div>
</div>
EOT;
	}
}

class FooterView extends BaseView
{
	public static function render($vars = null)
	{
		return <<<EOT
<div align="center">
&copy; Copyright 2010-{$vars['year']} All Rights Reserved<br>
<b>Powered by <a href='{$vars['base_url']}/'>One-File PHP Framework</a> v {$vars['fw_ver']}</b>
</div>
</body>
</html>
EOT;
	}
}

class DefaultView extends BaseView
{
	public static function render($vars = null)
	{
		return <<<EOT
{$vars['header']}
<div style="margin:20px;padding:20px;border:solid 1px lightblue">
<h2>The Story</h2>
<p>Many frameworks these days have a myriad of directories and files meant to organize and separate your code so it's theoretically more manageable. But the reality is, without discipline, you can still build a giant mess on even the cleanest framework. If you have discipline, you can have clean manageable code. Even if it's all in one file.
<p>The One-File PHP Framework is just one index.php file (with the help of one .htaccess file). It's fully MVC. It has a configuration system and a bootstrap. The controllers are defined near the top, the models in the middle, and the views last.
<h2>Performance</h2>
<p>Q. What if my site grows large and unwieldy and all the code is in one file? 
<p>A. Well, if that's the case you probably don't know what you're doing and duplicating a lot of code. If it's truly minimal, it should fit into one file. Use inheritance. Write better classes. Eliminate overhead code. If your views are getting large, it may be time to think about storing your views in a database. The default .htaccess file shipped with the One-File PHP Framework has a rewrite rule that will let you store static content (like CSS, Javascript) into the /media folder. This whole site is built on one index.php file that is less than 300 lines long.
<h2>Editing</h2>
<p>Q. Isn't it difficult to edit one file?
<p>A. On the contrary, it's easier to search for things and make changes.
<p>Q. What if you have a designer who needs to style the view?
<p>A. Then you should clean up your code and build an interface for your designer to edit the view without a need to edit the source code.
<p>Q. What about version control? 
<p>A. You need to have the discipline to properly describe your changes in the log. Also, most popular systems have decent diff'ing capabilities.
</div>
{$vars['footer']}
EOT;
	}
}

class DownloadView extends BaseView
{
	public static function render($vars = null)
	{
		return <<<EOT
{$vars['header']}
<div style="margin:20px;padding:20px;border:solid 1px lightblue">
{$vars['codeblock']}
</div>
{$vars['footer']}
EOT;
	}
}



