<?php

namespace App\Lib\SwoftDemo\Demo\TarsDemo\classes;

class SimpleStruct extends \TARS_Struct {
	const ID = 0;
	const COUNT = 1;
	const PAGE = 2;


	public $id; 
	public $count; 
	public $page; 


	protected static $_fields = array(
		self::ID => array(
			'name'=>'id',
			'required'=>true,
			'type'=>\TARS::INT64,
			),
		self::COUNT => array(
			'name'=>'count',
			'required'=>true,
			'type'=>\TARS::INT32,
			),
		self::PAGE => array(
			'name'=>'page',
			'required'=>true,
			'type'=>\TARS::SHORT,
			),
	);

	public function __construct() {
		parent::__construct('SwoftDemo_Demo_TarsDemo_SimpleStruct', self::$_fields);
	}
}
