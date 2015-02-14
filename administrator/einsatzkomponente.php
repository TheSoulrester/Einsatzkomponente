<?php
/**
 * @version     3.0.0
 * @package     com_einsatzkomponente
 * @copyright   Copyright (C) 2013 by Ralf Meyer. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Ralf Meyer <webmaster@feuerwehr-veenhusen.de> - http://einsatzkomponente.de
 */
 
// no direct access
defined('_JEXEC') or die;


// Access check.
if (!JFactory::getUser()->authorise('core.manage', 'com_einsatzkomponente')) 
{
	throw new Exception(JText::_('JERROR_ALERTNOAUTHOR'));
}

// Require specific controller if requested
if($controller = JRequest::getWord('controller')) {
    $classname	= 'EinsatzkomponenteController'.$controller;
	$path = JPATH_COMPONENT.'/controllers/'.$controller.'.php';
        require_once $path;
	$classname	= 'EinsatzkomponenteController'.$controller;
	$controller = new $classname( );
    $controller->execute(JFactory::getApplication()->input->get('task'));
	$controller->redirect();
}

// Include dependancies
jimport('joomla.application.component.controller');
$controller	= JControllerLegacy::getInstance('Einsatzkomponente');
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();
