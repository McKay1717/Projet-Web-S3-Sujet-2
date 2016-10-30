<?php

namespace App\Helper;

class OperationsController {
	public function date_us_to_fr($date) {
		return date ( "%j/%m/%Y", strtotime ( $date ) );
	}
	public function date_fr_to_us($date) {
		return date ( "%Y-%m-%j", strtotime ( $date ) );
	}
	public function validate($date) {
		$parse = date_parse ( $date );
		if ($parse != FALSE) {
			return checkdate ( $parse ['month'], $parse ['day'], $parse ['year'] );
		} else
			return false;
	}
}
