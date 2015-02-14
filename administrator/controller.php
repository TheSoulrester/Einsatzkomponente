<?php
/**
 * @version     3.0.0
 * @package     com_einsatzkomponente
 * @copyright   Copyright (C) 2013 by Ralf Meyer. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Ralf Meyer <webmaster@feuerwehr-veenhusen.de> - http://einsatzkomponente.de
 */
 
// No direct access
defined('_JEXEC') or die;

class EinsatzkomponenteController extends JControllerLegacy
{
	/**
	 * Method to display a view.
	 *
	 * @param	boolean			$cachable	If true, the view output will be cached
	 * @param	array			$urlparams	An array of safe url parameters and their variable types, for valid values see {@link JFilterInput::clean()}.
	 *
	 * @return	JController		This object to support chaining.
	 * @since	1.5
	 */
	public function display($cachable = false, $urlparams = false)
	{
		require_once JPATH_SITE.'/administrator/components/com_einsatzkomponente/helpers/einsatzkomponente.php'; // Helper-class laden
		
		$version = new JVersion;
		if (!$version->isCompatible('3.0')) :
		$document = JFactory::getDocument();
 		$document->addScript('components/com_einsatzkomponente/assets/bootstrap/js/bootstrap.min.js');	
 		$document->addStyleSheet('components/com_einsatzkomponente/assets/bootstrap/css/bootstrap.min.css');
		endif;
		
		// Version auf BETA überprüfen, und gegebenenfalls eine Warnung ausgeben
		$db = JFactory::getDbo();
		$db->setQuery('SELECT manifest_cache FROM #__extensions WHERE name = "com_einsatzkomponente"');
		$params = json_decode( $db->loadResult(), true );
        $version = $params['version'];
        if($version!=str_replace("Beta","",$version)):
		
		?>
    <div class="alert alert-info">
    <button type="button" class="close" data-dismiss="alert">&times;</button>
	<strong>Hinweis :</strong><br/>Achtung Beta-Version <?php echo $params['version'];?> !!! Es wird nicht empfohlen, diese Version der Einsatzkomponente auf einer öffentlichen Live-Webseite zu betreiben.
    </div>        
		<?php else:
		$version = new JVersion;
		if (!$version->isCompatible('3.0')) :?>
		    <div class="alert alert-info">
    <button type="button" class="close" data-dismiss="alert">&times;</button>
	<strong>Hinweis :</strong><br/>Achtung Beta-Version <?php echo $params['version'];?> für Joomla 2.5 !!! Es wird nicht empfohlen, diese Version der Einsatzkomponente auf einer öffentlichen Live-Webseite zu betreiben.
    </div>        
		<?php endif;  
		endif;  
		
		//------------------------------------------------------------------------
		$db = JFactory::getDbo();
		$db->setQuery('SELECT manifest_cache FROM #__extensions WHERE name = "com_einsatzkomponente"');
		$params = json_decode( $db->loadResult(), true );
        $version = $params['version'];
        if($version!=str_replace("Premium","",$version)):
		$params = JComponentHelper::getParams('com_einsatzkomponente');
		$params->set('eiko', '1');
		endif;  
		//------------------------------------------------------------------------
		$view		= JFactory::getApplication()->input->getCmd('view', 'kontrollcenter');
        JFactory::getApplication()->input->set('view', $view);
		parent::display($cachable, $urlparams);
		return $this;
	}
}
