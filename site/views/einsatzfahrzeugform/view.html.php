<?php

/**
 * @version    CVS: 3.9
 * @package    Com_Einsatzkomponente
 * @author     Ralf Meyer <ralf.meyer@einsatzkomponente.de>
 * @copyright  Copyright (C) 2015. Alle Rechte vorbehalten.
 * @license    GNU General Public License Version 2 oder später; siehe LICENSE.txt
 */
// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

/**
 * View to edit
 *
 * @since  1.6
 */
class EinsatzkomponenteViewEinsatzfahrzeugform extends JViewLegacy
{
	protected $state;

	protected $item;

	protected $form;

	protected $params;

	protected $canSave;

	/**
	 * Display the view
	 *
	 * @param   string  $tpl  Template name
	 *
	 * @return void
	 *
	 * @throws Exception
	 */
	public function display($tpl = null)
	{
		$app  = JFactory::getApplication();
		$user = JFactory::getUser();

		$this->state   = $this->get('State');
		$this->item    = $this->get('Data');
		$this->params  = $app->getParams('com_einsatzkomponente');
		$this->canSave = $this->get('CanSave');
		$this->form		= $this->get('Form');

		// Import CSS + JS 
		$document = JFactory::getDocument();
		$document->addStyleSheet('components/com_einsatzkomponente/assets/css/einsatzkomponente.css');	
		$document->addStyleSheet('components/com_einsatzkomponente/assets/css/responsive.css');

		if ($this->params->get('display_fahrzeuge_bootstrap','0')) :
		// Import Bootstrap
		JHtml::_('bootstrap.framework');
		$document->addStyleSheet($this->baseurl . '/media/jui/css/bootstrap.min.css');
		endif;
		
		// Import CSS aus Optionen
		$document->addStyleDeclaration($this->params->get('fahrzeug_css',''));  

		
		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new Exception(implode("\n", $errors));
		}

		

		$this->_prepareDocument();

		parent::display($tpl);
	}

	/**
	 * Prepares the document
	 *
	 * @return void
	 *
	 * @throws Exception
	 */
	protected function _prepareDocument()
	{
		$app   = JFactory::getApplication();
		$menus = $app->getMenu();
		$title = null;

		// Because the application sets a default page title,
		// we need to get it from the menu item itself
		$menu = $menus->getActive();

		if ($menu)
		{
			$this->params->def('page_heading', $this->params->get('page_title', $menu->title));
		}
		else
		{
			$this->params->def('page_heading', JText::_('COM_EINSATZKOMPONENTE_DEFAULT_PAGE_TITLE'));
		}

		$title = $this->params->get('page_title', '');

		if (empty($title))
		{
			$title = $app->get('sitename');
		}
		elseif ($app->get('sitename_pagetitles', 0) == 1)
		{
			$title = JText::sprintf('JPAGETITLE', $app->get('sitename'), $title);
		}
		elseif ($app->get('sitename_pagetitles', 0) == 2)
		{
			$title = JText::sprintf('JPAGETITLE', $title, $app->get('sitename'));
		}

		$this->document->setTitle($title);

		if ($this->params->get('menu-meta_description'))
		{
			$this->document->setDescription($this->params->get('menu-meta_description'));
		}

		if ($this->params->get('menu-meta_keywords'))
		{
			$this->document->setMetadata('keywords', $this->params->get('menu-meta_keywords'));
		}

		if ($this->params->get('robots'))
		{
			$this->document->setMetadata('robots', $this->params->get('robots'));
		}
	}
}
