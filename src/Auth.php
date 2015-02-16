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

use JFusion\User\Userinfo;
use Joomla\Language\Text;
use RuntimeException;

/**
 * JFusion Auth Class for universal
 * For detailed descriptions on these functions please check Plugin_Auth
 *
 * @category   Plugins
 * @package    JFusion\Plugins
 * @subpackage universal
 * @author     JFusion Team <webmaster@jfusion.org>
 * @copyright  2008 JFusion. All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.jfusion.org
 */
class Auth extends \JFusion\Plugin\Auth
{
	/**
	 * @var $helper Helper
	 */
	var $helper;

	/**
	 * @param Userinfo $userinfo
	 *
	 * @return string
	 * @throws RuntimeException
	 */
	function generateEncryptedPassword($userinfo)
	{
		$testcrypt = null;
		$password = $this->helper->getFieldType('PASSWORD');
		if(empty($password)) {
			throw new RuntimeException(Text::_('UNIVERSAL_NO_PASSWORD_SET'));
		} else {
			$testcrypt = $this->helper->getHashedPassword($password->fieldtype, $password->value, $userinfo);
		}
		return $testcrypt;
	}

	/**
	 * used by framework to ensure a password test
	 *
	 * @param Userinfo $userinfo userdata object containing the userdata
	 *
	 * @return boolean
	 */
	function checkPassword($userinfo) {
		$user_auth = $this->params->get('user_auth');

		$user_auth = rtrim(trim($user_auth), ';');
		ob_start();
		$check = eval($user_auth . ';');
		$error = ob_get_contents();
		ob_end_clean();
		if ($check===false && strlen($error)) {
			die($error);
		}
		if ($check === true) {
			return true;
		} else {
			return false;
		}
	}
}
