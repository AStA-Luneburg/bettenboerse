<?php
namespace AStA\Bettenboerse\Helpers;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class JSONDateTime extends \DateTime implements \JsonSerializable
{
    public function jsonSerialize()
    {
       return $this->format("c");
    }
}

function get_match_color($value) {
	//value from 0 to 1
	$hue = $value * 120;

	return 'hsl(' . $hue . ', 100%, 50%)';
}

function get_match_bg_color($value) {
	//value from 0 to 1
	$hue = $value * 120;

	return 'hsl(' . $hue . ', 100%, 15%)';
}

function get_match_bg_color_alternate($value) {
	//value from 0 to 1
	$hue = $value * 120;

	return 'hsl(' . $hue . ', 100%, 96%)';
}

function seconds_to_days($seconds) {
	return floor($seconds / (60 * 60 * 24));
}