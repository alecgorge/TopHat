<?php

/**
 * A class that allows you to easily generate HTML forms.
 *
 * Based on http://www.dyn-web.com/code/form_builder/
 */
class Form {
	public $form = "";

	/**
	 * Create a HTML from using a object-oriented interface.
	 *
	 * Contains methods for generating common form elements such as text boxes, text areas, select lists, radio buttons, check boxes, and submit buttons.
	 *
	 * Each of the form class methods return a string which you can assign to a variable or echo/print.
	 * The methods for adding form elements have named arguments for the required and most common attributes. You can use an optional associative array to add any other attributes you wish.
	 * An addAttributes method is used to add the optional attributes passed in the associative array. It can be used to add class, JavaScript event handler attributes, or other attributes. In keeping with the requirements for xhtml validity, it does not output minimized boolean attributes, but instead writes out the full attribute-value pair.
	 * Descriptions of the individual methods below demonstrate the addition of extra attributes.
	 *
	 * @param string $action Destination URL to which form is submitted
	 * @param string $method Form method - get or post (default is post)
	 * @param string $id Unique id to be assigned to the form element's id attribute (optional)
	 * @param array $attr_ar Associative array of additional attributes (optional)
	 */
	public function __construct ($action = '#', $method = 'post', $id = NULL, $attr_ar = array()) {
        $str = "<form action=\"$action\" method=\"$method\"";

		// only add non-null attributes
        if ( !is_null($id) ) {
            $str .= " id=\"$id\"";
        }

		// only add attributes if needed
        $str .= ( $attr_ar ? $this->addAttributes( $attr_ar ) . '>': '>');

		$this->form = $form;
	}

	/**
	 * Takes an array of attributes and adds them.
	 *
	 * @param array $attr_ar An assoc array of attributes.
	 * @return string The attribute string.
	 */
    private function addAttributes( $attr_ar ) {
        $str = '';
        // check minimized attributes
        $min_atts = array('checked', 'disabled', 'readonly', 'multiple');
        foreach( $attr_ar as $key=>$val ) {
            if ( in_array($key, $min_atts) ) {
                if ( !empty($val) ) {
                    $str .= " $key=\"$key\"";
                }
            } else {
                $str .= " $key=\"$val\"";
            }
        }
		return $str;
    }

	/**
	 * Used to add input elements of type text, checkbox, radio, hidden, password, submit and image. It has named arguments for type, name and value. A text box with just these attributes is added as follows:
	 * <code>$frm->addInput('text', 'firstName', 'Sharon');</code>
	 *
	 * @param string $type Input element's type attribute value. Possible values: text, checkbox, radio, hidden, password, submit and image.
	 * @param string $name Value you specify is assigned to name attribute of the input element.
	 * @param string $value Value assigned to input element.
	 * @param array $attr_ar Associative array of additional attributes (optional)
	 * @return boolean True.
	 */
    public function addInput($type, $name, $value, $attr_ar = array() ) {
        $str = "<input type=\"$type\" name=\"$name\" value=\"$value\"";
        if ($attr_ar) {
            $str .= $this->addAttributes( $attr_ar );
        }
        $str .= ' />';

		$this->form .= $str;
		return true;
	}

	/**
	 * To add a text area use the addTextArea method. Default values are provided for rows (4) and columns (30) or you can specify your own.
	 *
	 * @param string $name Assigned to name attribute of text area
	 * @param integer $rows Number of rows, controlling height of text area
	 * @param integer $cols Number of columns, controlling width of text area
	 * @param string $value Content of text area (optional)
	 * @param array $attr_ar Associative array of additional attributes (optional)
	 * @return boolean True
	 */
    public function addTextarea($name, $rows = 4, $cols = 30, $value = '', $attr_ar = array() ) {
        $str = "<textarea name=\"$name\" rows=\"$rows\" cols=\"$cols\"";
        if ($attr_ar) {
            $str .= $this->addAttributes( $attr_ar );
        }
        $str .= ">$value</textarea>";

		$this->form .= $str;
		return true;
    }

    // for attribute refers to id of associated form element
    public function addLabelFor($forID, $text, $attr_ar = array() ) {
        $str = "<label for=\"$forID\"";
        if ($attr_ar) {
            $str .= $this->addAttributes( $attr_ar );
        }
        $str .= ">$text</label>";

		$this->form .= $str;
		return true;
    }

    // from parallel arrays for option values and text
    public function addSelectListArrays($name, $val_list, $txt_list, $selected_value = NULL, $header = NULL, $attr_ar = array() ) {
        $option_list = array_combine( $val_list, $txt_list );
        $str = $this->addSelectList($name, $option_list, true, $selected_value, $header, $attr_ar );

		$this->form .= $str;
		return true;
    }

    // option values and text come from one array (can be assoc)
    // $bVal false if text serves as value (no value attr)
    public function addSelectList($name, $option_list, $bVal = true, $selected_value = NULL, $header = NULL, $attr_ar = array() ) {
        $str = "<select name=\"$name\"";
        if ($attr_ar) {
            $str .= $this->addAttributes( $attr_ar );
        }
        $str .= ">\n";
        if ( isset($header) ) {
            $str .= "  <option value=\"\">$header</option>\n";
        }
        foreach ( $option_list as $val => $text ) {
            $str .= $bVal? "  <option value=\"$val\"": "  <option";
            if ( isset($selected_value) && ( $selected_value === $val || $selected_value === $text) ) {
                $str .= ' selected="selected"';
            }
            $str .= ">$text</option>\n";
        }
        $str .= "</select>";

		$this->form .= $str;
		return true;
    }

    public function endForm() {
		$this->form .= "</form>";
		return true;
    }

	public function getHTML() {
		return $this->form;
	}

	public function endAndGetHTML () {
		$this->endForm();
		return $this->getHTML();
   	}
}

?>