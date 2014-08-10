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

use JFusion\Plugin\Plugin_Front;

/**
 * JFusion Front Class for universal plugin
 * For detailed descriptions on these functions please check Plugin_Front
 *
 * @category   Plugins
 * @package    JFusion\Plugins
 * @subpackage universal
 * @author     JFusion Team <webmaster@jfusion.org>
 * @copyright  2008 JFusion. All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.jfusion.org
 */
class Front extends Plugin_Front
{
    /**
     * @return string
     */
    function getRegistrationURL()
	{
		return $this->params->get('registerurl');
	}

    /**
     * @return string
     */
    function getLostPasswordURL()
	{
		return $this->params->get('lostpasswordurl');
	}

    /**
     * @return string
     */
    function getLostUsernameURL()
	{
		return $this->params->get('lostusernameurl');
	}
}