<?php
/**
 * ROOFLib
 * Version 0.7
 * MIT License
 * Ray Minge
 * the@rayminge.com
 *
 * @package ROOFLib 0.7
 */



require_once(dirname(__FILE__).'/class.form.php');

class FI_Switch extends FI_Group {

/**
 * Gets the type of the FormItem
 *
 * @return String "Switch";
 */
	public static function getType() {
		return 'Switch';
	}


/**
 * Creates a new FI_Switch
 *
 * @param String $name The name of the form item. Must be unique within the group or form.
 * @param String $label The label of the form item as printed to the page.
 * @param Array $options An array of parameters and their values. See description()
 */
	function __construct($name, $label, $options = Array()) {
		parent::__construct($name, $label, $options);

		$defaultValues = Array(
			'selected' => NULL,
			'mode'=>'radio', // options are 'select', 'radio';
		);

		$this->items = Array();
		$this->merge($options, $defaultValues);
	}


/**
* Prints the form item including label and input.
*
* @param Bool $email Indicates if this is being printed for email purposes
* @param Bool $nameAbove Indicates if this is being printed in div layout.
*
* @return String The HTML string.
*/
	public function printRow($email = false, $nameAbove = false) {
		$html = '';
		$js = '';
		$this->form->js_files []= 'switch.js';
		$group_html = '';
		if ($this->mode != 'radio') {
			$select_html = '<select name="'.$this->name().'" onchange="_SwitchControllers[\''.$this->name().'\'].switchIndex(this.selectedIndex)">';
			$options = Array();
			$selected = is_null($this->selected)?'':trim($this->name().'_'.$this->selected);
			$selected_id = ($selected?reset($this->items[$selected]->attr('id')):'');
			$first = '';
			foreach ($this->items as $name => $fi_item) {
				$options []= reset($fi_item->attr('id'));
				if (! $first) {
					$first = reset($fi_item->attr('id'));
				}
				$select_html .= '<option target="'.reset($fi_item->attr('id')).'" value="'.$fi_item->name().'"'.(($fi_item->name() == $selected)?' selected="selected"':'').'>'.$fi_item->label().'</option>';
				$group_html .= $fi_item->printRow($email, $nameAbove);
			}
			$select_html .= '</select>';
			$js .= '$(function () { new SwitchController("'.$this->name().'", ["'.join('", "', $options).'"], "'.($selected?$selected:$first).'") });';
			$html .= '<script type="text/javascript">'.$js.'</script>';
			$attr = $this->attrString();
			if ($nameAbove) {$html .= '<div '.$attr.'><div class="'.$this->cfg('class_fieldname').'">'.$select_html.'</div><div class="'.$this->cfg('class_fieldvalue').'">'.$group_html.'</div></div>'; }
			else { $html .= '<tr '.$attr.'><td class="'.$this->cfg('class_fieldname').'">'.$select_html.'</td><td class="'.$this->cfg('class_fieldvalue').'"><table>'.$group_html.'</table></td></tr>';}
		} else {
			$options = Array();
			$selected = is_null($this->selected)?'':trim($this->name().'_'.$this->selected);
			$selected_id = ($selected?reset($this->items[$selected]->attr('id')):'');
			$first = '';
			$index = 0;
			foreach ($this->items as $name => $fi_item) {
				$options []= reset($fi_item->attr('id'));
				if (! $first) {
					$first = reset($fi_item->attr('id'));
				}
				$select_html .= '<label><input type="radio" target="'.reset($fi_item->attr('id')).'" name="'.$this->name().'" onchange="_SwitchControllers[\''.$this->name().'\'].switchIndex(\''.$index++.'\')" value="'.$fi_item->name().'"'.(($fi_item->name() == $selected)?' selected="selected"':'').'>'.$fi_item->label().'</label>';
				$group_html .= $fi_item->printRow($email, $nameAbove);
			}

			$js .= '$(function () { new SwitchController("'.$this->name().'", ["'.join('", "', $options).'"], "'.($selected?$selected:$first).'") });';
			$html .= '<script type="text/javascript">'.$js.'</script>';
			$attr = $this->attrString();
			if ($nameAbove) {$html .= '<div '.$attr.'><div class="'.$this->cfg('class_fieldname').'">'.$select_html.'</div><div class="'.$this->cfg('class_fieldvalue').'">'.$group_html.'</div></div>'; }
			else { $html .= '<tr '.$attr.'><td class="'.$this->cfg('class_fieldname').'">'.$select_html.'</td><td class="'.$this->cfg('class_fieldvalue').'"><table>'.$group_html.'</table></td></tr>';}
		}
		return $html;
	}

	public function printForm($nameAbove) {
		return $this->printRow(false, true);
	}
/**
 * Gets or Sets the value of the FormItem
 *
 * @param String $input Providing an input indicates that the FormItem should be printed with that default.
 *
 * @return Array If using this function as a Getter, gets the value of the item.
 */
	public function value($input = NULL) {
		if ($input === NULL) { // get
			$out = parent::value();
			$selected = (get_magic_quotes_gpc())?(stripslashes($_POST[$this->name()])):$_POST[$this->name()];
			$matches = preg_split('/_/', $selected);
			$selected_index = array_pop($matches);
			return Array($selected_index => $out[$selected_index]);
		} else { // set
			$key = key($input);
			$this->selected = $key;
			parent::value($input);
		}
	}
}
?>
