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
     * @param Userinfo $userinfo
     * @return string
     */
    function generateEncryptedPassword(Userinfo $userinfo)
    {
		$user_auth = $this->params->get('user_auth');

		$user_auth = rtrim(trim($user_auth),';');
    	ob_start();
		$testcrypt = eval('return '. $user_auth . ';');
		$error = ob_get_contents();
		ob_end_clean();
		if ($testcrypt===false && strlen($error)) {
			die($error);
		}
        return $testcrypt;
    }
}
