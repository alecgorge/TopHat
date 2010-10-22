<?php

class Table {
	private $html = '';
	private $rowClasses = array();
	private $rowCount = 1;

	public function  __construct ($id = '', $rowClasses = array(), $attr = array(), $cellpadding = 0, $cellspacing = 0) {
		$this->html = sprintf('<table id="%s" cellpadding="%s" cellspacing="%s" %s>'."\n", $id, $cellpadding, $cellspacing, $this->attr($attr));
	}

	public function attr ($attr) {
		$r = "";
		foreach($attr as $key => $val) {
			$r .= $key.'="'.$value.'" ';
		}
		return rtrim($r);
	}

	public function addRow($values, $rowClass = "", $classes = false) {
		if($classes === false) {
			$classes = $this->rowClasses;
		}

		$this->html .= sprintf("\t<tr class=\"%s\">\n", ($this->rowCount % 2 == 0 ? 'even' : 'odd').trim(" ".$rowClass));
		foreach($values as $k => $v) {
			$this->html .= sprintf("\t\t<td%s>%s</td>\n", $classes[$k], $v);
		}
		$this->html .= "\t</tr>\n";

		$this->rowCount++;
	}

	public function addHtml ($html) {
		$this->html .= $html;
	}

	public function setHtml ($html) {
		$this->html = $html;
	}

	public function addHeader($values, $classes = array()) {
		$this->html .= "\t<thead>\n";
		foreach($values as $k => $v) {
			$this->html .= sprintf("\t\t<th%s>%s</th>\n", $classes[$k], $v);
		}
		$this->html .= "\t</thead>\n";
	}

	public function addFooter($values, $classes = array()) {
		$this->html .= "\t<tfoot>\n";
		foreach($values as $k => $v) {
			$this->html .= sprintf("\t\t<td%s>%s</td>\n", $classes[$k], $v);
		}
		$this->html .= "\t</tfoot>\n";
	}

	public function addCaption($cap) {
		$this->html .= "\t<caption>$cap</caption>\n";
	}

	public function html () {
		$this->html .= "</table>\n";
		return $this->html;
	}
}