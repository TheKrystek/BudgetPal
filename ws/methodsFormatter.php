<?php

Class methodsFormatter{

	var $class;
	var $showComments;
	var $showMethodBody;
	var $counter;

	public function __construct($class){
		$this->class = $class;
		$this->showComments = false;
		$this->showMethodBody = true;
		$this->counter=0;
	}

	public function EachInSeparateBlock(){
		$class_methods = get_class_methods($this->class);
		foreach ($class_methods as $method_name) {
			$this->getExample($method_name);
			echo '<pre class="prettyprint php">';
			$this->formatSingleMethod($method_name);
			echo '</pre>';	
		}
	}

	public function getExample($method_name){
		
		$method = new ReflectionMethod($this->class, $method_name);
		$params = $method->getParameters();
		$params_num = $method->getNumberOfParameters();
		$comment = $method->getDocComment();
		$header = "";
		if (strlen($comment)>0){
			$example_link = $this->getExampleFromComment($comment, $params,$method_name);
			echo '<a href="'.$example_link.'">'.$example_link.'</a>';
		}
	}
	
	
	public function formatSingleMethod($method_name){
		$this->counter++;
		$method = new ReflectionMethod($this->class, $method_name);
		$params = $method->getParameters();
		
		$params_num = $method->getNumberOfParameters();
		$params_type = array();
		$comment = $method->getDocComment();
		$header = "";
		if (strlen($comment)>0){
			if ($this->showComments)
				printf("\n    %s\n", str_replace("\t"," ",$comment));
			$header .= trim($this->getValueFromComment("@return",$comment))." ";
			$params_type = $this->getParamTypesFromComment($comment);
		}
		else
		{
			$header .=  'void ';

		}
		$header .=  $method_name.'(';

		if ($params_num == 0){
			$header .=  'void';
		}
		else{
			if (count($params_type) == $params_num){
		    	for ($i = 0; $i < $params_num -1; $i++) {
		        	$header .=  $params_type[$i]." ".$params[$i]->getName().', ';
		    	}
		    	$header .=  $params_type[$params_num -1]." ".$params[$params_num -1]->getName();
			}
		//	else 
			//	die($params_num." ".count($example_val).'Number of parameters in method header and number of examples isn\'t equal');
		}
		$header .=  ')';


		if ($this->showMethodBody){
			echo '<label class="collapse" for="method_'.$this->counter.'">'.$header.'</label>';
  			echo '<input id="method_'.$this->counter.'" type="checkbox">';
  			echo '<div>';
				$this->methodBody($method);
  			echo '</div>';
		}
		else
		{
			echo $header;
		}
	}


	private function getValueFromComment($value, $comment){
		$offset = strpos($comment,$value);
		$pos = strpos($comment," ",$offset);
		return substr($comment, $pos, ( strpos($comment, PHP_EOL, $pos) ) - $pos);
	}

	private function getDescriptionFromComment($comment){
		return $this->getValueFromComment('@desc', $comment);
	}
	
	private function getParamTypesFromComment($comment)
	{
		$params = $this->getValueFromComment('@param',$comment);
		$params_array = explode(',',$params);
		foreach ($params_array as &$param) {
			$param = trim($param);
		}
		return $params_array;
	}
	
	private function getExampleFromComment($comment, $params,$methodName){
		$example_array = $this->getValueFromComment('@example', $comment);
		$examples = explode(',',$example_array);
		
		if (count($params) == count($examples)){
			$link = "server.php?a=".strtolower($methodName);
			
			for ($i = 0; $i < count($examples); $i++) {
				$arg = "&".trim($params[$i]->getName())."=".trim($examples[$i]);
				$link .= $arg;
			}
			return $link;
		}
			die(count($params)." ".count($examples).' Number of parameters in method header and number of examples isn\'t equal');
	}
	
	

	private function methodBody($method){
		$filename = $method->getFileName();
		$start_line = $method->getStartLine(); 
		$end_line = $method->getEndLine();
		$length = $end_line - $start_line;
		$source = file($filename);
		$body = implode("", array_slice($source, $start_line, $length));
		print_r($body);
	}

}

?>