<?php

class A
{
}

class Col
{
	public function __construct()
	{
		foreach (func_get_args() as $arg) {
			$this->ch[] = $arg;
		}
	}
	public $ch = [];
}

$ch1 = new Col(new A, new A);
$ch2 = new Col(new A);

$Col = new Col;
$Col->ch[] = new Col(new Col, new Col, new Col($ch1, $ch2));

var_dump($Col);


class It extends RecursiveIterator
{
	public function __construct(Col $Col)
	{
		$this->Col = $Col;
	}


}
