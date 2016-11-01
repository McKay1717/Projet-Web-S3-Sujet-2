<?php

namespace App\Helper;

class helper_date {
	public function formatForDb($date) {
		if (strpos ( $date, '/' ))
			$date = $this->date_fr_to_us ( $date );
		return $date;
	}
	public function date_us_to_fr($date) {
		$date = explode("-", $date);
		$newsdate=$date[2].'/'.$date[1].'/'.$date[0];
		return $newsdate;
	}
	public function date_fr_to_us($date) {
		$date = explode("/", $date);
		$newsdate=$date[2].'-'.$date[1].'-'.$date[0];
		return $newsdate;
	}
	function validateDate($date) {
		$date = $this->formatForDb($date);
		return ! ( bool ) strtotime ( $date ) && $date != "1970-01-01";
	}
}
