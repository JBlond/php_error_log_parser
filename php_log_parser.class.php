<?php
class php_log_parser {

	/**
	* @var String
	* @access private
	*/
	private $file = "";

	/**
	* @var String
	* @access private
	*/
	private $inhalt = "";

	/**
	* @var Array
	* @access private
	*/
	private $data = array();

	/**
	* @var Array
	* @access public
	*/
	private $stats = array();

	private $notice = true;

	/**
	* @param String $file
	* @return FruitParseError
	* @access public
	*/
	public function __construct($file,$notice=true){
		$this->file = $file;
		if($notice === false){
			$this->notice = false;
		}

		$this->inhalt = file_get_contents($this->file);
		if(!$this->inhalt){
			echo ("Can not open file: '".$this->file."' <br>Check the file name!");
			return false;
		}

		$this->paralyse_log();

	}

	/**
	* Parse raw data and keep only errors
	* @access private
	*/
	private function parse_data(){
		$this->inhalt = str_replace('﻿','',$this->inhalt);
		$input = explode("\n",$this->inhalt);
		$this->stats['rows'] = 0;
		$array_size = sizeof($input);
		for($i = 0; $i < $array_size; $i++){
			$this->stats['rows']++;
			$back = explode("]",$input[$i]);
			if(strpos($back['0'],'#')!==false){
				continue;
			}

			if(!isset($back['0'])){ //new line in file / empty line
				continue;
			}
			if(
				$back['0'] == '' ||
				strpos($back['0'],'#') !== false ||
				strpos($back['0'],'Stack') !== false ||
				!isset($back['1'])
			){
				continue;
			}
			$error_description = explode(' ' , $back['1']);
			$type = str_replace(':','',$error_description['2']);
			if($this->notice === false && strpos($type, 'Notice')){
				continue;
			}
			$detail = str_replace('PHP ','',str_replace($error_description['2'],'', $back['1']));
			$output = array('type' =>  $type, 'info' => $error_description['5'], 'detail' => $detail );
			$this->data[] = $output;
		}
	}

	/**
	* parse the log file
	* @access private
	*/
	private function paralyse_log(){
		$this->parse_data();
		$type = array();
		$detail = array();

		$size_of_data = sizeof($this->data);
		for($a = 0; $a < $size_of_data; $a++){
			if(isset($type[$this->data[$a]['type']])){
				$type[$this->data[$a]['type']]++;
			} else {
				$type[$this->data[$a]['type']] = 1;
			}

			if(isset($detail[$this->data[$a]['detail']])){
				$detail[$this->data[$a]['detail']]++;
			} else {
				$detail[$this->data[$a]['detail']] = 1;
				$this->stats['content_type'][$this->data[$a]['detail']] = $this->data[$a]['type'];
			}
		}
		// statistics + types
		$this->stats['analyzed_rows'] = sizeof($this->data);
		$key = array_keys($type);
		$this->stats['rowtypeCount'] = sizeof($key);
		for($x = 0; $x < $this->stats['rowtypeCount']; $x++) {
			$temp = array('name' => $key[$x],'value' => $type[$key[$x]], 'pro' => round(($type[$key[$x]]*100/$this->stats['analyzed_rows']),4).' %');
			$this->stats['rowtype'][] = $temp;
		}

		$this->stats['errCount'] = sizeof($detail);
		array_multisort($detail, SORT_DESC, SORT_NUMERIC);
		$this->stats['err'] = $detail;
	}

	/**
	* @access public
	* @return String $out
	*/
	public function table(){
		if(!isset($this->stats['rows'])){
			return;
		}
		$out = '<br /><br />Parsed '. $this->stats['rows'] .' lines. Found <b>'.$this->stats['analyzed_rows'].'</b> errors with <b>'.$this->stats['rowtypeCount'].'</b> type(s)<br /><br />';
		$out .= '<table>';
		$out .= '<tr><td><b>Name</b></td><td><b>Value</b></td><td><b>Percent</b></td></tr>';
		foreach ($this->stats['rowtype'] as $val) {
			$out .= '<tr><td>'. $val['name'].'</td><td>'.$val['value'].'</td><td>'.$val['pro'].'</td></tr>';
		}
		$out .= '</table>';
		$out .= '<br /><br />Listed the 50 most counted errors ( overall '.$this->stats['errCount'].' different errors ).<br /><br />';
		$out .= '<table>';
		$out .= '<tr><td><b>Rank</b></td><td><b>count</b></td><td><b>type</b></td><td><b>Error</b></td></tr>';
		$keys = array_keys($this->stats['err']);
		$keycount = count($keys);
		if ($keycount > 50){
			$keycount = 50; /* limit to 50 errors shown */
		}
		for($y = 0; $y < $keycount; $y++){
			if(trim($this->stats['err'][$keys[$y]]) == ''){
				continue;
			}
			$out .= '<tr><td>'.($y+1).'</td><td>'.$this->stats['err'][$keys[$y]].'</td><td>'.$this->stats['content_type'][$keys[$y]] .'</td><td>'. htmlentities($keys[$y]).'</td></tr>' ."\n";
		}
		$out .= '</table>';
		return $out;
	}

	/**
	* @access public
	* @return String $out
	*/
	public function output(){
		$out = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">'.
		'<html xmlns="http://www.w3.org/1999/xhtml"><head><title>php log parser</title>
		<style type="text/css">
		* {
			font-family: verdana;
			font-size: 11px;
			background-color: #EFEFEF;
			color: #101010;
			border:0;
			margin:0;
		}
		body {
			padding:20px;
		}
		table {
			/* border:1px solid #000000; */
			cell-spacing:none;
		}
		td {
			border-bottom:1px solid #000000;
			border-right:1px solid #000000;
			border-top:1px solid #000000;
		}

		tr > td {
			border-left:1px solid #000000;
		}
		</style></head><body>'.$this->table().'</body></html>';
		return $out;
	}
}
