<?php

namespace JUI {
	/**
	 * The Manager class helps to add new Views to the output. It provides
	 * the parsed JSON Code.
	 *
	 * @param  pluginName  the name of the plugin currently used (optionally)
	 */
	class Manager {
		const SWIPE_TOP    = 'swipetop';
		const SWIPE_LEFT   = 'swipeleft';
		const SWIPE_RIGHT  = 'swiperight';
		const SWIPE_BOTTOM = 'swipebottom';
		
		private $changed = false;
		private $plugin = '';
		private $elements;
		private $head;
		private $flyover = FALSE;
		private $warning = null;
		
		function __construct() {
			$a = func_get_args(); 
			$i = func_num_args(); 
			
			if (method_exists($this, $f='__construct'.$i)) {
				call_user_func_array(array($this,$f), $a);
			}
		}
		
		private function __construct1($pluginName) {
			$this->plugin = $pluginName;
		}
		
		/**
		 * returns whether Views were added to the output
		 *
		 * @return  whether Views were added to the output
		 */
		function hasChanged() {
			return $this->changed;
		}
		
		/**
		 * adds an element of the type JUI\View to the output
		 *
		 * @param  view  object of the type JUI\View
		 */
		function add($view) {
			$this->changed = true;
			
			if($view instanceof View) {
				if($view->getArray() != null)
					$this->elements[] = $view->getArray();
			} else if(is_string($view)) {
				$this->elements[] = array("type"=>"text", "value"=>$view);
			}
		}
		
		/**
		 * adds an element of the type JUI\View to the output
		 *
		 * @return    json encoded output of the Views
		 */
		function getJsonString() {
			global $loginManager;

			if($this->flyover) {
				$this->elements = Array("type"=>"flyover", "value"=>$this->getArray());
				return json_encode($this->elements);
			}
			
			if(empty($this->elements)) {
				return '[]';
			}


			if($loginManager->needRevalidation()) {
				$this->head['jwt'] = $loginManager->revalidate();
			}
			
			return json_encode(Array("data"=>$this->getArray(), "head"=>$this->head));
		}
		
		function getArray() {
			if(!empty($this->warning)) {
				return array_merge($this->elements, array(array("type"=>"warning", "value"=>$this->warning)));
			}
			
			return $this->elements;
		}
		
		/**
		 * adds an newline to the output (only visible in some cases)
		 */
		function nline($size=1) {
			$this->newline($size);
		}
		
		function newline($size=1) {
			if(is_integer($size) && $size > 1) {
				for($i = 0; $i < $size; $i++) {
					$this->newline();
				}
			} else {
				$this->elements[] = Array("type"=>"nl");
			}
		}
		
		function hline() {
			$this->horizontalline();
		}
		
		function horizontalline() {
			$this->elements[] = Array("type"=>"hline");
		}
		
		function setFlyover($enabled = TRUE) {
			$this->flyover = $enabled;
		}
		
		function setBackgroundColor($color) {
			$this->head['bgcolor'] = $color;
		}
		
		function setBgColor($color) {
			$this->setBackgroundColor($color);
		}
		
		function setSwipe($swipe, $click) {
			if($click instanceof Click) {
				$action = $click->getClickString();
				
				if($swipe == Manager::SWIPE_TOP) {
					$this->head['swipetop'] = $action;
				}
				
				if($swipe == Manager::SWIPE_LEFT) {
					$this->head['swipeleft'] = $action;
				}
				
				if($swipe == Manager::SWIPE_RIGHT) {
					$this->head['swiperight'] = $action;
				}
				
				if($swipe == Manager::SWIPE_BOTTOM) {
					$this->head['swipebottom'] = $action;
				}
			}
		}
		
		function setShare($view, $parameter="") {
			$this->head['share']['name'] = $this->plugin;
			$this->head['share']['view'] = $view;
			$this->head['share']['command'] = $parameter;
		}
		
		function scrollBottom($bottom=TRUE) {
			if($bottom) {
				$this->head['scroll'] = 'bottom';
			} else {
				$this->head['scroll'] = 'top';
			}
		}
		
		function setWarning($string) {
			$this->warning = $string;
		}
	}

	/* START MASTER CLASSES */
	class ClickView extends View {
		function setClick($click) {
			if($click instanceof Click) {
				$this->element['click'] = $click->getClickString();
			} else {
				$this->element['click'] = null;
			}
		}

		function setLongClick($longclick) {
			if($longclick instanceof Click) {
				$this->element['longclick'] = $longclick->getClickString();
			} else {
				$this->element['longclick'] = null;
			}
		}
	}
	
	class InputView extends View {
		function __construct($pName) {
			$this->element['name'] = $pName;
		}

		function setLabel($value) {
			$this->element['label'] = $value;
		}
		
		function setFocus($focus=TRUE) {
			$this->element['focus'] = $focus;
		}
	}
	
	class View {
		const VISIBLE = 0;
		const INVISIBLE = 1;
		const GONE = 2;
		
		public $element;
		
		function getArray() {
			return $this->element;
		}
		
		function setValue($value) {
			if(is_array($value) && !empty($this->element['name'])) {
				$name = $this->element['name'];
				
				if(!empty($value[$name]) && !is_array($value[$name])) {
					$this->element['value'] = $value[$name];
				} else {
					$this->element['value'] = '';
				}
			} else if(is_object($value)) {
				$this->element['value'] = "[OBJECT]";
			} else if(!is_array($value)) {
				$this->element['value'] = $value;
			}
		}
		
		function setColor($color) {
			$this->element['color'] = $color;
		}
		
		function setBackgroundColor($color) {
			$this->element['background'] = $color;
		}
		
		function setBgColor($color) {
			$this->setBackgroundColor($color);
		}
		
		function setVisible($visibility = View::VISIBLE) {
			if($visibility == View::INVISIBLE) {
				$this->element['visible'] = 'hidden';
			} else if($visibility == View::GONE) {
				$this->element['visible'] = 'away';
			} else {
				$this->element['visible'] = null;
			}
		}
		
		function setWidth($width) {
			$this->element['width'] = $width;
		}
		
		function setHeight($height) {
			$this->element['height'] = $height;
		}

		function setId($id) {
			$this->element['id'] = $id;
		}
		
		function setMargin() {
			$a = func_get_args(); 
			$i = func_num_args();
			
			if (method_exists($this, $f='setMargin'.$i)) {
				call_user_func_array(array($this,$f), $a);
			}
		}
		
		private function setMargin1($margin) {
			if(is_numeric($margin)) {
				$this->element['margin'] = $margin;
			}
		}
		
		private function setMargin2($marginTopBottom, $marginLeftRight) {
			
			if(is_numeric($marginTopBottom) && is_numeric($marginLeftRight)) {
				$this->element['margin'] = $marginTopBottom;
				$this->element['marginLeft'] = $marginLeftRight;
				$this->element['marginRight'] = $marginLeftRight;
			}
		}

		private function setMargin3($marginTop, $marginLeftRight, $marginBottom) {
			if(is_numeric($marginTop) && is_numeric($marginLeftRight) && is_numeric($marginBottom)) {
				$this->element['marginTop'] = $marginTop;
				$this->element['margin'] = $marginLeftRight;
				$this->element['marginBottom'] = $marginBottom;
			}
		}
		
		private function setMargin4($marginTop, $marginLeft, $marginRight, $marginBottom) {
			if(is_numeric($marginTop) && is_numeric($marginLeft) && is_numeric($marginRight) && is_numeric($marginBottom)) {
				$this->element['marginTop'] = $marginTop;
				$this->element['marginLeft'] = $marginLeft;
				$this->element['marginRight'] = $marginRight;
				$this->element['margin'] = $marginBottom;
			}
		}
		
		function setMarginTop($margin) {
			if(is_numeric($margin)) {
				$this->element['marginTop'] = $margin;
			}
		}
		
		function setMarginLeft($margin) {
			if(is_numeric($margin)) {
				$this->element['marginLeft'] = $margin;
			}
		}
		
		function setMarginRight($margin) {
			if(is_numeric($margin)) {
				$this->element['marginRight'] = $margin;
			}
		}
		
		function setMarginBottom($margin) {
			if(is_numeric($margin)) {
				$this->element['marginBottom'] = $margin;
			}
		}

		/* PADDING */
		function setPadding() {
			$a = func_get_args(); 
			$i = func_num_args();
			
			if (method_exists($this, $f='setPadding'.$i)) {
				call_user_func_array(array($this,$f), $a);
			}
		}

		private function setPadding1($padding) {
			if(is_numeric($padding)) {
				$this->element['padding'] = $padding;
			}
		}

		private function setPadding2($paddingTopBottom, $paddingLeftRight) {
			
			if(is_numeric($paddingTopBottom) && is_numeric($paddingLeftRight)) {
				$this->element['padding'] = $paddingTopBottom;
				$this->element['paddingLeft'] = $paddingLeftRight;
				$this->element['paddingRight'] = $paddingLeftRight;
			}
		}

		private function setPadding3($paddingTop, $paddingLeftRight, $paddingBottom) {
			if(is_numeric($paddingTop) && is_numeric($paddingLeftRight) && is_numeric($paddingBottom)) {
				$this->element['paddingTop'] = $paddingTop;
				$this->element['padding'] = $paddingLeftRight;
				$this->element['paddingBottom'] = $paddingBottom;
			}
		}

		private function setPadding4($paddingTop, $paddingLeft, $paddingRight, $paddingBottom) {
			if(is_numeric($paddingTop) && is_numeric($paddingLeft) && is_numeric($paddingRight) && is_numeric($paddingBottom)) {
				$this->element['paddingTop'] = $paddingTop;
				$this->element['paddingLeft'] = $paddingLeft;
				$this->element['paddingRight'] = $paddingRight;
				$this->element['padding'] = $paddingBottom;
			}
		}

		function setPaddingTop($padding) {
			if(is_numeric($padding)) {
				$this->element['paddingTop'] = $padding;
			}
		}

		function setPaddingLeft($padding) {
			if(is_numeric($padding)) {
				$this->element['paddingLeft'] = $padding;
			}
		}

		function setPaddingRight($padding) {
			if(is_numeric($padding)) {
				$this->element['paddingRight'] = $padding;
			}
		}

		function setPaddingBottom($padding) {
			if(is_numeric($padding)) {
				$this->element['paddingBottom'] = $padding;
			}
		}
	}
	
	class Click {
		private $string = '';
		const openPlugin = "openPlugin";
		const openMedia = "openMedia";
		const openUrl = "openUrl";
		const toggleView = "toggleView";
		const addViews = "addViews";
		const submit = "submit";
		
		function __construct() {
			$a = func_get_args(); 
			$i = func_num_args(); 
			
			if (method_exists($this, $f='__construct'.$i)) {
				$this->string = call_user_func_array(array($this,$f), $a);
			}
		}
		
		private function __construct1($pAction) {
			if($pAction == Click::submit) {
				return $pAction . "()";
			}
		}
		
		private function __construct2($pAction, $pName) {
			if($pAction == Click::openUrl) {
				return $pAction . "('" . $pName . "')";
			} else if($pAction == Click::toggleView) {
				return $pAction . "('" . $pName . "')";
			} else if($pAction == Click::addViews) {
				if($pName instanceof View) {
					$viewArray[] = $pName->getArray();
					$json = json_encode($viewArray);
					$json = str_replace ('\'', '\\\'' , $json);
					
					return $pAction . "('" . $json . "')";
				}
			}
			
			return $this->__construct4($pAction, $pName, 'home', '');
		}
		
		private function __construct3($pAction, $pName, $pView) {
			if($pAction == Click::openMedia) {
				$pView = urlencode($pView);
				return $pAction . "('" . $pName . "', '" . $pView . "')";
			}
			
			return $this->__construct4($pAction, $pName, $pView, '');
		}
		
		private function __construct4($pAction, $pName, $pView, $pParameter) {
			if($this->isAllowedAction($pAction)) {
				if($pName instanceof \PluginManager) {
					$pName = $pName->getPluginName();
				}
				
				return $pAction . "('" . $pName . "', '" . $pView . "', '" . $pParameter . "')";
			}
			
			return '';
		}
		
		private function isAllowedAction($pAction) {
			if($pAction == Click::openPlugin) {
				return true;
			} else if($pAction == Click::openMedia) {
				return true;
			} else if($pAction == Click::openUrl) {
				return true;
			}
			
			return false;
		}

		public function getClickString() {
			return $this->string;
		}
	}
	/* END MASTER CLASSES */
	
	/**
	 * An element of the type ListView. It's a simple list with (maybe) specified actions for every entry
	 *
	 */
	class ListView extends View {
		function __construct() {
			$this->element['type'] = 'list';
		}
		
		/**
		 * adds an item to the list
		 *
		 * @param value [String] value of the entry
		 * @param click [JUI\Click] the action that should be triggered on click
		 * @param longclick [JUI\Click] the action that should be triggered on a long click
		 */
		function addItem() {
			$a = func_get_args(); 
			$i = func_num_args(); 
			
			if (method_exists($this, $f='addItem'.$i)) {
				call_user_func_array(array($this,$f), $a);
			}
		}
		
		private function addItem1($pString) {
			$this->element['value'][] = $pString;
			$this->element['click'][] = '';
			$this->element['longclick'][] = '';
		}
		
		private function addItem2($pString, $click) {
			$this->element['value'][] = $pString;
			
			if($click instanceof Click) {
				$this->element['click'][] = $click->getClickString();
			} else {
				$this->element['click'][] = '';
			}
			
			$this->element['longclick'][] = '';
		}
		
		private function addItem3($pString, $click, $longclick) {
			$this->element['value'][] = $pString;
			
			if($click instanceof Click) {
				$this->element['click'][] = $click->getClickString();
			} else {
				$this->element['click'][] = '';
			}
			
			if($longclick instanceof Click) {
				$this->element['longclick'][] = $longclick->getClickString();
			} else {
				$this->element['longclick'][] = '';
			}
		}
		
		function setValue($value) {
			if(!empty($value) && is_array($value)) {
				$this->element['value'] = $value;
			}
		}
	}

	class Select extends InputView {
		function __construct($pName) {
			$this->element['type'] = 'select';
			parent::__construct($pName);
		}
		
		function addItem() {
			$a = func_get_args(); 
			$i = func_num_args(); 
			
			if (method_exists($this, $f='addItem'.$i)) {
				call_user_func_array(array($this,$f), $a);
			}
		}
		
		function addItem1($text) {
			$this->element['value'][] = $text;
		}
		
		function addItem2($text, $value) {
			$this->element['value'][] = array($text, $value);
		}
		
		function setValue($value) {
			if(!empty($value) && is_array($value)) {
				$this->element['value'] = $value;
			}
		}

		function setOnChange($actionName) {
			if(is_string($actionName)) {
				$this->element['change'] = $actionName;
			} else if($actionName instanceof \JUI\Click) {
				$this->element['change'] = $actionName->getClickString();
			}
		}
	}

	class Button extends ClickView {
		function __construct() {
			$a = func_get_args(); 
			$i = func_num_args(); 
			
			if (method_exists($this, $f='__construct'.$i)) {
				call_user_func_array(array($this,$f), $a);
			}
		}
		
		function __construct1($value) {
			$this->element['type'] = 'button';
			$this->setValue($value);
		}
		
		function __construct2($value, $submit) {
			if(is_bool($submit) && $submit) {
				$this->element['type'] = 'button';
				$this->setValue($value);
				$this->setClick( new Click(Click::submit) );
			} else if($submit instanceof Click) {
				$this->element['type'] = 'button';
				$this->setValue($value);
				$this->setClick($submit);
			}
		}
	}

	class Heading extends View {
		function __construct($pValue, $pSmall=false) {
			$this->element['type'] = 'heading';
			$this->element['value'] = $pValue;
			
			if(is_bool($pSmall) && $pSmall) {
				$this->setSmall();
			}
		}

		function setSmall($small = TRUE) {
			if($small) {
				$this->element['size'] = 'small';
			} else {
				$this->element['size'] = 'normal';
			}
		}
	}
	
	class Input extends InputView {
		const NORMAL = 1;
		const MULTILINE = 2;
		const PASSWORD = 3;
		const NUMBERS = 4;
		const DATE = 5;
		
		function __construct($pName) {
			$this->element['type'] = 'input';
			parent::__construct($pName);
		}

		function setPreset($preset = Input::NORMAL) {
			if($preset == Input::MULTILINE) {
				$this->element['preset'] = 'textarea';
			} else if($preset == Input::PASSWORD) {
				$this->element['preset'] = 'password';
			} else if($preset == Input::DATE) {
				$this->element['preset'] = 'date';
			}  else if($preset == Input::NUMBERS) {
				$this->element['preset'] = 'number';
			} else {
				$this->element['preset'] = null;
			}
		}
		
		function setMultiline($multiline = TRUE) {
			if($multiline) {
				$this->element['preset'] = 'textarea';
			} else {
				$this->element['preset'] = null;
			}
		}
		
		function setAccepted($accept = Input::NUMBERS) {
			if($accept == Input::NUMBERS) {
				$this->element['preset'] = 'number';
			} else {
				$this->element['preset'] = '';
			}
		}
		
		function setHint($string) {
			$this->element['hint'] = $string;
		}
	}

	class Editor extends InputView {
		function __construct($pName) {
			$this->element['type'] = 'editor';
			parent::__construct($pName);
		}
	}

	class Color extends InputView {
		function __construct($pName) {
			$this->element['type'] = 'input';
			$this->element['preset'] = 'color';
			parent::__construct($pName);
		}
	}
	
	class Checkbox extends InputView {
		function __construct($pName) {
			$this->element['type'] = 'checkbox';
			parent::__construct($pName);
		}
		
		function setChecked($checked = TRUE) {
			if($checked) {
				$this->element['checked'] = 'true';
			} else {
				$this->element['checked'] = '';
			}
		}
	}
	
	class File extends InputView {
		function __construct($pName) {
			$this->element['type'] = 'file';
			parent::__construct($pName);
		}
		
		function setMultiple($multiple = TRUE) {
			if($multiple) {
				$this->element['multiple'] = $multiple;
			} else {
				$this->element['multiple'] = NULL;
			}
		}
	}

	class Image extends ClickView {
		function __construct() {
			$this->element['type'] = 'image';
			
			$a = func_get_args(); 
			$i = func_num_args(); 
			
			if (method_exists($this, $f='__construct'.$i)) {
				call_user_func_array(array($this,$f), $a);
			}
		}
		
		private function __construct1($base64) {
			$this->element['value'] = $base64;
		}
		
		function setImage($base64) {
			$this->element['value'] = $base64;
		}
	}
	
	class Link extends ClickView {
		function __construct() {
			$this->element['type'] = 'link';
			
			$a = func_get_args(); 
			$i = func_num_args(); 
			
			if (method_exists($this, $f='__construct'.$i)) {
				call_user_func_array(array($this,$f), $a);
			}
		}
		
		private function __construct1($text) {
			$this->setText($text);
		}

		function setText($text) {
			$this->element['value'] = $text;
		}
	}
	
	class Text extends ClickView {
		const LEFT = 0;
		const CENTER = 1;
		const RIGHT = 2;
		
		const BOLD = 3;
		const ITALIC = 4;
		const BOLDITALIC = 5;
		
		function __construct() {
			$this->element['type'] = 'text';
			
			$a = func_get_args(); 
			$i = func_num_args(); 
			
			if (method_exists($this, $f='__construct'.$i)) {
				call_user_func_array(array($this,$f), $a);
			}
		}
		
		private function __construct1($text) {
			$this->setText($text);
		}
		
		function setText($text) {
			$allowed = array('&#039;');
			$replace = array('\'');
			$this->element['value'] = str_replace($allowed, $replace, htmlspecialchars($text, ENT_QUOTES, "UTF-8"));
		}
		
		function setAlignment($alignment) {
			$this->setAlign($alignment);
		}
		
		function setAlign($alignment) {
			if($alignment == Text::LEFT) {
				$this->element['align'] = 'LEFT';
			} else if($alignment == Text::CENTER) {
				$this->element['align'] = 'CENTER';
			} else if($alignment == Text::RIGHT) {
				$this->element['align'] = 'RIGHT';
			}
		}
		
		function setAppearance($appearance) {
			if($appearance == Text::BOLD) {
				$this->element['appearance'] = 'bold';
			} else if($appearance == Text::ITALIC) {
				$this->element['appearance'] = 'italic';
			} else if($appearance == Text::BOLDITALIC) {
				$this->element['appearance'] = 'bolditalic';
			}
		}
	}
	
	class Frame extends View {
		function __construct() {
			$this->element['type'] = 'frame';
			
			$a = func_get_args(); 
			$i = func_num_args(); 
			
			if (method_exists($this, $f='__construct'.$i)) {
				call_user_func_array(array($this,$f), $a);
			}
		}
		
		private function __construct1($url) {
			$this->element['value'] = $url;
		}
		
		function setUrl($url) {
			$this->element['value'] = $url;
		}
		
		function setSrc($url) {
			$this->setUrl($url);
		}
		
		function setHtml($html) {
			$this->element['html'] = $html;
		}
	}
	
	class Widget extends View {
		function __construct() {
			$a = func_get_args(); 
			$i = func_num_args(); 
			
			if (method_exists($this, $f='__construct'.$i)) {
				call_user_func_array(array($this,$f), $a);
			}
		}

		private function __construct1($plugin) {
			$this->__construct2($plugin, 'default');
		}
		
		private function __construct2($plugin, $name) {
			global $pluginManager;
			$this->element['type'] = 'container';
			
			if($plugin instanceof PluginManager) {
				$plugin = $plugin->getPluginName();
			}
			
			$out = $pluginManager->getWidgetArray($plugin, $name);
			
			/*
			ob_start();
			$pluginManager->getWidget($plugin, $name);
			$out = ob_get_contents(); 
			ob_end_clean();
			*/
			//$out = json_decode($out);
			$this->setValue($out);
		}
		
		function setValue($value) {
			if(!empty($value) && is_array($value)) {
				$this->element['value'] = $value;
			}
		}
	}

	class Container extends View {
		function __construct() {
			$this->element['type'] = 'container';
		}
		
		function setValue($value) {
			if(!empty($value) && is_array($value)) {
				$this->element['value'] = $value;
			}
		}

		function add($value) {
			if($value instanceof \JUI\View) {
				$this->element['value'][] = $value->getArray();
			}
		}

		function nline($size=1) {
			$this->newline($size);
		}
		
		function newline($size=1) {
			if(is_integer($size) && $size > 1) {
				for($i = 0; $i < $size; $i++) {
					$this->newline();
				}
			} else {
				$this->element['value'][] = Array("type"=>"nl");
			}
		}
	}
	
	class Spoiler extends View {
		const HIDE = FALSE;
		const SHOW = TRUE;
		
		function __construct() {
			$a = func_get_args(); 
			$i = func_num_args(); 
			
			if (method_exists($this, $f='__construct'.$i)) {
				call_user_func_array(array($this,$f), $a);
			}
			
			$this->element['type'] = 'spoiler';
		}

		private function __construct1($label) {
			$this->element['label'] = $label;
		}
		
		function add($parameter) {
			if($parameter instanceof View) {
				$this->element['value'][] = $parameter->getArray();
			}
		}
		
		function addView($view) {
			$this->add($view);
		}
		
		function setDefault($default) {
			if($default == Spoiler::SHOW) {
				$this->element['default'] = 'SHOW';
			} else {
				unset($this->element['default']);
			}
		}
	}
	
	class Range extends InputView {
		function __construct($name) {
			$this->element['type'] = 'range';
			parent::__construct($name);
		}
		
		function setMin($min) {
			if(is_numeric($min)) {
				$this->element['min'] = $min;
			}
		}
		
		function setMax($max) {
			if(is_numeric($max)) {
				$this->element['max'] = $max;
			}
		}
		
		function setValue($value) {
			if(is_numeric($value)) {
				$this->element['value'] = $value;
			}
		}
		
		function setOnChange($actionName) {
			if(is_string($actionName)) {
				$this->element['change'] = $actionName;
			}
		}
	}
	
	class Table extends View {
		function __construct() {
			$this->element['type'] = 'table';
		}
		
		function addRow($row) {
			if($row instanceof \JUI\Table\Row) {
				$this->element['value'][] = $row->getArray();
				$this->element['click'][] = $row->click;
				$this->element['longclick'][] = $row->longclick;
			}
		}
	}
}

namespace JUI\Table {
	class Row extends TableElement {
		public $click;
		public $longclick;
		
		function __construct() {
			
		}
		
		function setClick($click) {
			if($click instanceof \JUI\Click) {
				$this->click = $click->getClickString();
			} else {
				$this->click = null;
			}
		}

		function setLongClick($longclick) {
			if($longclick instanceof \JUI\Click) {
				$this->longclick = $longclick->getClickString();
			} else {
				$this->longclick = null;
			}
		}
		
		function addColumn($column) {
			if($column instanceof Column) {
				if($column->getArray() != null)
					$this->value[] = $column->getArray();
			} else if(is_string($column)) {
				$this->value[] = $column;
			} else if($column instanceof \JUI\View) {
				if($column->getArray() != null)
					$this->value[][] = $column->getArray();
			}
		}
	}
	
	class Column extends TableElement {
		function __construct() {
			
		}
		
		function add($view) {
			if($view instanceof \JUI\View) {
				$this->value[] = $view->getArray();
			}
		}
	}
	
	class TableElement {
		protected $value = null;
		
		function getArray() {
			return $this->value;
		}
	}
}

?>