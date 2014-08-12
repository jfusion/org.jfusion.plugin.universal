<?php namespace JFusion\Plugins\universal;
/**
 * @category   Plugins
 * @package    JFusion\Plugins
 * @subpackage universal
 * @author     JFusion Team <webmaster@jfusion.org>
 * @copyright  2008 JFusion. All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.jfusion.org
 */

use JFusion\Factory;
use JFusion\Framework;
use JFusion\Plugin\Plugin_Admin;

use Joomla\Form\Html\Select;
use Joomla\Language\Text;

use Psr\Log\LogLevel;

use stdClass;
use Exception;

/**
 * JFusion Admin Class for universal
 * For detailed descriptions on these functions please check Plugin_Admin
 *
 * @category   Plugins
 * @package    JFusion\Plugins
 * @subpackage universal
 * @author     JFusion Team <webmaster@jfusion.org>
 * @copyright  2008 JFusion. All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.jfusion.org
 */
class Admin extends \JFusion\Plugin\Admin
{
	/**
	 * @var $helper Helper
	 */
	var $helper;

	/**
	 * @return string
	 */
	function getTablename()
	{
		return $this->helper->getTable();
	}

	/**
	 * @return array
	 */
	function getUsergroupList()
	{
		$usergroupmap = $this->params->get('usergroupmap', false);
		$usergrouplist = array();
		if(is_object($usergroupmap) && isset($usergroupmap->name)) {
			foreach ($usergroupmap->name as $key => $value) {
				if ($value && isset($usergroupmap->value[$key])) {
					//append the default usergroup
					$default_group = new stdClass;
					$value = html_entity_decode($value);
					$default_group->id = base64_encode($usergroupmap->value[$key]);
					$default_group->name = $value;
					$usergrouplist[] = $default_group;
				}
			}
		}
		return $usergrouplist;
	}


	/**
	 * Returns the a list of users of the integrated software
	 *
	 * @param int $limitstart start at
	 * @param int $limit number of results
	 *
	 * @return array
	 */
	function getUserList($limitstart = 0, $limit = 0)
	{
		try {
			$f = array('USERNAME', 'EMAIL');
			$field = $this->helper->getQuery($f);

			// initialise some objects
			$db = Factory::getDatabase($this->getJname());

			$query = $db->getQuery(true)
				->select($field)
				->from('#__' . $this->getTablename());

			$db->setQuery($query, $limitstart, $limit);
			$userlist = $db->loadObjectList();
		} catch (Exception $e) {
			Framework::raise(LogLevel::ERROR, $e, $this->getJname());
			$userlist = array();
		}
		return $userlist;
	}

	/**
	 * @return int
	 */
	function getUserCount()
	{
		try {
			//getting the connection to the db
			$db = Factory::getDatabase($this->getJname());

			$query = $db->getQuery(true)
				->select('count(*)')
				->from('#__' . $this->getTablename());

			$db->setQuery($query);

			//getting the results
			return $db->loadResult();
		} catch (Exception $e) {
			Framework::raise(LogLevel::ERROR, $e, $this->getJname());
			return 0;
		}
	}

	/**
	 * @return bool
	 */
	function allowRegistration()
	{
		return true;
	}

	/**
	 * @param $name
	 * @param $value
	 * @param $node
	 * @param $control_name
	 * @return string
	 */
	function mapuser($name, $value, $node, $control_name)
	{
		$value = $this->helper->getMapRaw('user');

		return $this->map('map', $value, $node, $control_name, 'user');
	}

	/**
	 * @param $name
	 * @param $value
	 * @param $node
	 * @param $control_name
	 * @return string
	 */
	function user_auth($name, $value, $node, $control_name)
	{
		$output = '<textarea name="' . $control_name . '[' . $name . ']" rows="20" cols="55">' . $value . '</textarea>';
		return $output;
	}

	/**
	 * @param $name
	 * @param $value
	 * @param $node
	 * @param $control_name
	 * @return string
	 */
	function mapgroup($name, $value, $node, $control_name)
	{
		$value = $this->helper->getMapRaw('group');
		return $this->map('map', $value, $node, $control_name, 'group');
	}

	/**
	 * @param $name
	 * @param $value
	 * @param $node
	 * @param $control_name
	 * @param $type
	 *
	 * @return string
	 */
	function map($name, $value, $node, $control_name, $type)
	{
		$output = '';
		try {
			$jname = $this->getJname();

			$database_name = $this->params->get('database_name');
			$database_prefix = $this->params->get('database_prefix');

			try {
				$db = Factory::getDatabase($jname);
			} catch (Exception $e) {
				throw new Exception(Text::_('SAVE_CONFIG_FIRST'));
			}

			$tabelslist = $db->getTableList();
			if ($tabelslist) {
				$tl = array();
				$fl = array();

				$fieldtypes = $this->helper->getField();

				$table = new stdClass;
				$table->id = null;
				$table->name = Text::_('UNSET');
				$tl[] = $table;

				$firstTable = null;
				foreach ($tabelslist as $val) {
					if(strpos($val, $database_prefix) === 0 || $database_prefix == '') {
						$table = new stdClass;

						$table->name = $table->id = substr($val, strlen($database_prefix));

						$query = 'SHOW COLUMNS FROM ' . $val;
						$db->setQuery($query);
						$fieldslist = $db->loadObjectList();

						if (!$firstTable) $firstTable = $table->id;
						$fl[$table->id] = $fieldslist;
						$tl[] = $table;
					}
				}

				$mapuser = array();
				if ($value->table) {
					$mapuser = $fl[$value->table];
				} else {
					if ($firstTable) $mapuser = $fl[$firstTable];
				}

				$onchange = 'onchange="javascript: groupchange(this)"';

				$output .= '<table>';
				$output .= '<tr><td>';
				$output .= Select::genericlist( $tl, $control_name . '[' . $name . '][' . $type . '][table]', 'onchange="javascript: Joomla.submitbutton(\'applyconfig\')"', 'id', 'name', $value->table);
				$output .= '</td></tr>';
				$output .= '<tr><td>';
				if (!empty($value->table) ) {
					$output .= '<table>';
					foreach ($mapuser as $val) {
						$output .= '<tr><td>';
						//object(stdClass)#245 (6) { ["Field"]=>  string(2) "id" ["Type"]=>  string(6) "int(5)" ["Null"]=>  string(0) "" ["Key"]=>  string(0) "" ["Default"]=>  string(1) "0" ["Extra"]=>  string(0) "" }
						$output .= '<div>Name: ' . $val->Field . '</div>';
						$output .= '<div>Type: ' . $val->Type . '</div>';
						$output .= '<div>Default: "' . $val->Default . '" </div>';
						$null = $val->Null ? Text::_('YES') : Text::_('NO');
						$output .= '<div>Null: ' . $null . '</div>';
						$output .= '<div>Extra: "' . $val->Extra . '" </div></td><td>';
						if ( isset($value->field->{$val->Field}) ) {
							$mapuserfield = $value->field->{$val->Field};
						} else {
							$mapuserfield = '';
						}
						if ( isset($value->type->{$val->Field}) ) {
							$fieldstype = $value->type->{$val->Field};
						} else {
							$fieldstype = '';
						}
						$fieldsvaluearray = array();
						$fieldsvalue = '';
						if ( isset($value->value->{$val->Field}) ) {
							$fieldsvalue = $value->value->{$val->Field};
							if (is_array($fieldsvalue)) {
								$fieldsvaluearray = (array)$fieldsvalue;
								foreach ($fieldsvaluearray as &$val2) {
									$val2 = htmlentities($val2);
								}
							} else {
								$fieldsvalue = htmlentities($fieldsvalue);
							}
						}

						$onchange = 'size="8" multiple onchange="javascript: JFusion.Plugin.changeField(this,\'' . $val->Field . '\',\'' . $type . '\')"';
						$output .= '<table>';
						$output .= '<tr>';
						$output .= '<td>';
						$output .= Select::genericlist($fieldtypes, $control_name . '[' . $name . '][' . $type . '][field][' . $val->Field . '][]', $onchange, 'id', 'name', $mapuserfield);
						$output .= '</td>';
						$output .= '<td>';
						$onchange = 'onchange="javascript: JFusion.Plugin.changeValue(this,\'' . $val->Field . '\',\'' . $type . '\')"';
						$output .= '<div id="' . $type . $val->Field . '">';

						if (isset($mapuserfield[0])) {
							if (isset($fieldtypes[$mapuserfield[0]])) {
								if (isset($fieldtypes[$mapuserfield[0]]->types)) {
									$output .= Select::genericlist( $fieldtypes[$mapuserfield[0]]->types, $control_name . '[' . $name . '][' . $type . '][type][' . $val->Field . ']', $onchange, 'id', 'name', $fieldstype);
								}
							}
						}
						$output .= '</div>';
						$output .= '</td>';
						$output .= '<td>';
						$output .= '<div id="' . $type . $val->Field . 'value">';
						switch ($fieldstype) {
							case 'CUSTOM':
								$output .= '<textarea id="' . $control_name . $name . $type . 'value' . $val->Field . '" name="' . $control_name . '[' . $name . '][' . $type . '][value][' . $val->Field . ']" rows="8" cols="55">' . $fieldsvalue . '</textarea>';
								break;
							case 'DEFAULT':
							case 'VALUE':
							case 'DATE':
								$output .= '<input type="text" id="' . $control_name . $name . $type . 'value' . $val->Field . '" name="' . $control_name . '[' . $name . '][' . $type . '][value][' . $val->Field . ']" value="' . $fieldsvalue . '" size="100" class="inputbox" />';
								break;
							case 'ONOFF':
								foreach ($fieldsvaluearray as $key2 => $val2) {
									$output .= '<input type="text" id="' . $control_name . $name . $type . 'value' . $val->Field . $key2 . '" name="' . $control_name . '[' . $name . '][' . $type . '][value][' . $val->Field . '][' . $key2 . ']" value="' . $val2 . '" size="40" class="inputbox" />';
								}
								break;
						}
						$output .= '</div>';
						$output .= '</td>';
						$output .= '</tr>';
						$output .= '</table>';
						$output .= '</td></tr>';
					}
					$output .= '</table>';
				}
				$output .= '</td></tr>';
				$output .= '</table>';
			} else {
				throw new Exception(Text::_('SAVE_CONFIG_FIRST'));
			}
		} catch (Exception $e) {
			$output = $e->getMessage();
		}
		return $output;
	}

	/**
	 * @param $name
	 * @param $value
	 * @param $node
	 * @param $control_name
	 * @return string
	 */
	function js($name, $value, $node, $control_name) {
//		$document = JFactory::getDocument();

		$list = $this->helper->getField();

		$list = json_encode($list);

		$output = <<<JS
        JFusion.Plugin.TypeAry = {$list};

		JFusion.Plugin.disableOptions = function(elements, disable) {
			elements.each(function(element) {
				var options = element.getElements('option');
				options.each(function(option) {
				    if (option.get('value') == disable && !option.selected) {
				        option.disabled = true;
				    }
				});
			});
		};

		JFusion.Plugin.update = function() {
			JFusion.Plugin.updateOptions('user');
			JFusion.Plugin.updateOptions('group');
		};

        JFusion.Plugin.updateOptions = function(type) {
			var elements = document.getElements('select[id^=paramsmap'+type+'field]');
			elements.each(function(element) {
				var options = element.getElements('option');
				options.each(function(option) {
				    if (option.disabled) {
				        option.disabled = false;
				    }
				});
			});
			elements.each(function(element) {
				var options = element.getElements('option');
				options.each(function(option) {
					if (option.selected) {
						var value = option.get('value');
						if (value != 'DEFAULT') {
							switch (value) {
								case 'REALNAME':
									JFusion.Plugin.disableOptions(elements, 'LASTNAME');
									JFusion.Plugin.disableOptions(elements, 'FIRSTNAME');
									break;
								case 'LASTNAME':
								case 'FIRSTNAME':
									JFusion.Plugin.disableOptions(elements, 'REALNAME');
									break;
								default:
							}
							JFusion.Plugin.disableOptions(elements, value);
						}
					}
				});
			});
        };

        JFusion.Plugin.changeField = function(ref, name, parmtype) {
        	var options = ref.getElements('option');
        	options.each(function(option) {
        		var value = option.get('value');
				if (option.selected && value) {
					if (JFusion.Plugin.TypeAry[value].types !== undefined) {
						options.each(function(option) {
							option.selected = false;
						});
						option.selected = true;
					}
				}
			});

            var id = $(parmtype+name);
            id.empty();

            $(parmtype+name+'value').empty();

			JFusion.Plugin.update();
			var value = ref.get('value');
            if ( value && JFusion.Plugin.TypeAry[value].types !== undefined ) {
            	var valueid = 'paramsmap'+parmtype+'type'+name;
	            var select = new Element('select', {
					'type': 'option',
					'id': valueid,
					'name': 'params[map][user][type]['+name+']',
					'events': {
			            'change': function () {
			                JFusion.Plugin.changeValue(this, name, parmtype);
			            }
			        }
	            });

				Array.each(JFusion.Plugin.TypeAry[value].types, function(type) {
					select.appendChild(new Element('option', {
						'html' : type.name,
						'value' : type.id
					}));
				});
				/*
                type.options.length = 0;
                for (var i=0; i<JFusion.Plugin.TypeAry[value].types.length; i++) {
                    type.options[type.options.length] = new Option(JFusion.Plugin.TypeAry[value].types[i].name, JFusion.Plugin.TypeAry[value].types[i].id);
                }
                */
                id.appendChild(select);
            }
        };

        JFusion.Plugin.changeValue = function(ref, name, parmtype) {
            var id = $(parmtype+name+'value');

			var paramsmap = $("paramsmap"+parmtype+"value"+name);
            if ( paramsmap ) {
                paramsmap.dispose();
            }

            var paramsmapon = $("paramsmap"+parmtype+"value"+name+"on");
            if (paramsmapon) {
                paramsmapon.dispose();
            }

            var paramsmapoff = $("paramsmap"+parmtype+"value"+name+"off");
            if (paramsmapoff) {
                paramsmapoff.dispose();
            }

            var value = ref.get('value');
            if(value == 'CUSTOM') {
                id.appendChild(new Element('textarea', {
					'id': 'paramsmap'+parmtype+'value'+name,
					'name': 'params[map]['+parmtype+'][value]['+name+']',
					'rows': 8,
					'cols': 55
            	}));
            } else if(value == 'DATE' || value == 'VALUE') {
                id.appendChild(new Element('input', {
					'type': 'text',
					'id': 'paramsmap'+parmtype+'value'+name,
					'name': 'params[map]['+parmtype+'][value]['+name+']',
					'size': 100,
					'value': value == 'DATE' ? 'Y-m-d H:i:s' : ''
            	}));
            } else if ( value == 'ONOFF') {
                id.appendChild(new Element('input', {
					'type': 'text',
					'id': 'paramsmap'+parmtype+'value'+name+'on',
					'name': 'params[map]['+parmtype+'][value]['+name+'][on]',
					'size': 40
            	}));
                id.appendChild(new Element('input', {
					'type': 'text',
					'id': 'paramsmap'+parmtype+'value'+name+'off',
					'name': 'params[map]['+parmtype+'][value]['+name+'][off]',
					'size': 40
            	}));
            }
        };

        window.addEvent('domready',function() {
			JFusion.Plugin.update();
        });
JS;
//		$document->addScriptDeclaration($output);

		$output = '<script type="text/javascript">' . $output . '</script>';

		return $output;
	}

	/**
	 * do plugin support multi usergroups
	 *
	 * @return bool
	 */
	function isMultiGroup()
	{
		$userid = $this->helper->getFieldType('USERID', 'group');
		if ($userid) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Debug Extras
	 *
	 * @return void
	 */
	function debugConfigExtra()
	{

		$usertable = $this->helper->getTable();
		if ($usertable) {
			$userid = $this->helper->getFieldType('USERID');

			$username = $this->helper->getFieldType('USERNAME');

			$email = $this->helper->getFieldType('EMAIL');

			if ( !$userid ) {
				Framework::raise(LogLevel::WARNING, Text::_('NO_USERID_DEFINED'), $this->getJname());
			}

			if ( !$email ) {
				Framework::raise(LogLevel::WARNING, Text::_('NO_EMAIL_DEFINED'), $this->getJname());
			}

			if ( !$username ) {
				Framework::raise(LogLevel::WARNING, Text::_('NO_USERNAME_DEFINED'), $this->getJname());
			}
			$grouptable = $this->helper->getTable('group');
			if ($grouptable) {
				$group_userid = $this->helper->getFieldType('USERID', 'group');
				$group_group = $this->helper->getFieldType('GROUP', 'group');

				if ( !$group_userid ) {
					Framework::raise(LogLevel::WARNING, Text::_('NO_GROUP_USERID_DEFINED'), $this->getJname());
				}
				if ( !$group_group ) {
					Framework::raise(LogLevel::WARNING, Text::_('NO_GROUP_GROUPID_DEFINED'), $this->getJname());
				}
			}
			$grouplist = $this->getUsergroupList();
			if (empty($grouplist)) {
				Framework::raise(LogLevel::WARNING, Text::_('NO_GROUPS_MAPPED'), $this->getJname());
			}
		} else {
			Framework::raise(LogLevel::WARNING, Text::_('NO_USERTABLE_DEFINED'), $this->getJname());
		}
	}
}