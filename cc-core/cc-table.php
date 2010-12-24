<?php

class Table {
	private $html = '';
	private $rowClasses = array();
	private $rowCount = 1;

	public function  __construct ($id = '', $rowClasses = array(), $attr = array(), $cellpadding = 0, $cellspacing = 0) {
		$this->rowClasses = $rowClasses;
		$this->html = sprintf('<table id="%s" cellpadding="%s" cellspacing="%s" %s>'."\n", $id, $cellpadding, $cellspacing, $this->attr($attr));
	}

	public function attr ($attr) {
		$r = "";
		foreach($attr as $key => $val) {
			$r .= $key.'="'.$value.'" ';
		}
		return rtrim($r);
	}

	public function addRow($values, $rowClass = "", $classes = false, $colspan = false) {
		if($classes === false) {
			$classes = $this->rowClasses;
		}

		$this->html .= sprintf("\t<tr class=\"%s\">\n", ($this->rowCount % 2 == 0 ? 'even' : 'odd').trim(" ".$rowClass));
		foreach($values as $k => $v) {
			$this->html .= sprintf("\t\t<td class='%s'%s>%s</td>\n", $classes[$k], ($colspan ? " colspan='$colspan'" : ""), $v);
		}
		$this->html .= "\t</tr>\n";

		$this->rowCount++;
		return $this;
	}

	public function addHtml ($html) {
		$this->html .= $html;
		return $this;
	}

	public function setHtml ($html) {
		$this->html = $html;
		return $this;
	}

	public function addSectionHeader($title) {
		$this->addRow($title, "header", false, 1000);
		return $this;
	}

	public function addHeader($values, $classes = array()) {
		if(empty($classes)) {
			$classes = $this->rowClasses;
		}

		$this->html .= "\t<thead>\n";
		foreach($values as $k => $v) {
			$this->html .= sprintf("\t\t<th class='%s'>%s</th>\n", $classes[$k], $v);
		}
		$this->html .= "\t</thead>\n";
		return $this;
	}

	public function addFooter($values, $classes = array()) {
		if(empty($classes)) {
			$classes = $this->rowClasses;
		}

		$this->html .= "\t<tfoot>\n";
		foreach($values as $k => $v) {
			$this->html .= sprintf("\t\t<td class='%s'>%s</td>\n", $classes[$k], $v);
		}
		$this->html .= "\t</tfoot>\n";
		return $this;
	}

	public function addCaption($cap) {
		$this->html .= "\t<caption>$cap</caption>\n";
		return $this;
	}

	public function html () {
		$this->html .= "</table>\n";
		return $this->html;
	}
}