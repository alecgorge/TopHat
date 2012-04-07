<?php

class Table {
	private $start = '';
	private $end = '</table>';
	private $header = '';
	private $footer = '';
	private $rows = '';
	private $rowClasses = array();
	private $rowCount = 0;

	/**
	 * @param string $id The HTML ID of the table.
	 * @param array $rowClasses An array of strings where each string is a class to be applied the corresponding cell. For example: <code>$rowClasses[0] = "test";</code> would make the first cell in every column have a class of "test".
	 * @param array $attr An associative array of attributes in the form <code>"attribute name" => "attribute value"</code>. The value is automatically escapped.
	 * @param int $cellpadding Padding in each cell.
	 * @param int $cellspacing "Margin" around each cell.
	 */
	public function  __construct ($id = '', $rowClasses = array(), $attr = array(), $cellpadding = 0, $cellspacing = 0, $type = 'table-striped') {
		$this->rowClasses = $rowClasses;
		$this->start = sprintf('<table id="%s" cellpadding="%s" cellspacing="%s" class="table %s" %s>'."\n", $id, $cellpadding, $cellspacing, $type, $this->attr($attr));
	}

	public function attr ($attr) {
		$r = "";
		foreach($attr as $key => $val) {
			$r .= $key.'="'.htmlspecialchars($val, ENT_COMPAT, 'UTF-8', false).'" ';
		}
		return rtrim($r);
	}

	public function addRow($values, $rowClass = "", $classes = false, $colspan = false) {
		if($classes === false) {
			$classes = $this->rowClasses;
		}

		$this->rowCount++;

		$this->rows .= sprintf("\t<tr class=\"%s\">\n", ($this->rowCount % 2 == 0 ? 'even' : 'odd').trim(" ".$rowClass));
		foreach($values as $k => $v) {
			$this->rows .= sprintf("\t\t<td class='%s'%s>%s</td>\n", $classes[$k], ($colspan ? " colspan='$colspan'" : ""), $v);
		}
		$this->rows .= "\t</tr>\n";

		return $this;
	}

	public function buildHtml () {
		$this->setHtml($this->start.$this->header.$this->footer.$this->rows.$this->end);
		return $this->html;
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

		$this->header .= "\t<thead><tr>\n";
		foreach($values as $k => $v) {
			$this->header .= sprintf("\t\t<th class='%s'>%s</th>\n", $classes[$k], $v);
		}
		$this->header .= "\t</tr></thead>\n";
		return $this;
	}

	public function addFooter($values, $classes = array()) {
		if(empty($classes)) {
			$classes = $this->rowClasses;
		}

		$this->footer .= "\t<tfoot><tr>\n";
		foreach($values as $k => $v) {
			$this->footer .= sprintf("\t\t<td class='%s'>%s</td>\n", $classes[$k], $v);
		}
		$this->footer .= "\t</tr></tfoot>\n";
		return $this;
	}

	public function addCaption($cap) {
		$this->caption .= "\t<caption>$cap</caption>\n";
		return $this;
	}

	public function html () {
		return $this->buildHtml();
	}
}