<?php

class Message {
	public static function error ($message, $showClose = false) {
		return "<div class='alert alert-error'>" . self::closeButton($showClose) . $message . "</div>";
	}
	public static function success ($message, $showClose = false) {
		return "<div class='alert alert-success'>" . self::closeButton($showClose) . $message . "</div>";
	}
	public static function notice ($message, $showClose = false) {
		return "<div class='alert alert-info'>" . self::closeButton($showClose) . $message . "</div>";
	}
	public static function info ($message, $showClose = false) {
		return self::notice($message);
	}
	private static function closeButton($showClose = true) {
		return $showClose ? "<a class='close' data-dismiss='alert'>×</a>" : '';
	}
}
