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
jimport('joomla.application.component.controllerform');
/**
 * Einsatzbericht controller class.
 */
class EinsatzkomponenteControllerEinsatzbericht extends JControllerForm
{
    function __construct() {
        $this->view_list = 'einsatzberichte';
        parent::__construct();
    }
    public function pdf() {
    	// Check for request forgeries
	JSession::checkToken() or die(JText::_('JINVALID_TOKEN'));
	require_once JPATH_SITE.'/administrator/components/com_einsatzkomponente/helpers/einsatzkomponente.php'; // Helper-class laden
	
    	$cid = JFactory::getApplication()->input->get('id', array(), 'array');
    	if (!is_array($cid) || count($cid) < 1)
	{
	    JLog::add(JText::_($this->text_prefix . '_NO_ITEM_SELECTED'), JLog::WARNING, 'jerror');
	}
	else
	{
    	    $msg = EinsatzkomponenteHelper::pdf($cid);
	    $this->setRedirect('index.php?option=com_einsatzkomponente&view=einsatzbericht&layout=edit&id='.$cid[0], $msg);
	}
    }
 
     function swf()  
     { 
        $pview = JFactory::getApplication()->input->get('view', 'einsatzbericht');
	$rep_id = JFactory::getApplication()->input->get('id', '0');

	if (parent::save()) :
	    if ($rep_id == '0') :
		$db = JFactory::getDBO();
		$query = "SELECT id FROM #__eiko_einsatzberichte ORDER BY id DESC LIMIT 1";
		$db->setQuery($query);
		$rows = $db->loadObjectList();
		$rep_id      = $rows[0]->id;
		$msg    = JText::_( 'Neuer Einsatzbericht gespeichert ! Sie können jetzt die Einsatzbilder zu diesem Einsatz hinzufügen.' );
	        $this->setRedirect('index.php?option=com_einsatzkomponente&view=swfupload&pview='.$pview.'&rep_id='.$rep_id.'', $msg); 
	    endif;
	else:
            $this->setRedirect('index.php?option=com_einsatzkomponente&view=einsatzbericht&layout=edit', $msg); 
	endif;
		
	if (!$rep_id == '0') :
            //$msg    = JText::_( '' );  
            $this->setRedirect('index.php?option=com_einsatzkomponente&view=swfupload&pview='.$pview.'&rep_id='.$rep_id.'', $msg); 
	endif;
	}
	//function  

    	function save($key = NULL, $urlVar = NULL) {

		// Check for request forgeries
		JSession::checkToken() or die(JText::_('JINVALID_TOKEN'));

		// Get items to remove from the request.
		$send = 'false';
		$cid = JFactory::getApplication()->input->get('id','0');
		$params = JComponentHelper::getParams('com_einsatzkomponente');

		if (parent::save()) :

		if(!$_FILES['data']['name']['0'] =='') : 
		if (!$cid) :
		$db = JFactory::getDBO();
		$query = "SELECT id FROM #__eiko_einsatzberichte ORDER BY id DESC LIMIT 1";
		$db->setQuery($query);
		$rows = $db->loadObjectList();
		$cid      = $rows[0]->id;
		endif;
		upload ($cid,'data');
			endif;		

				if ( $params->get('send_mail_auto', '0') ): 
		if (!$cid) :
		$db = JFactory::getDBO();
		$query = "SELECT id FROM #__eiko_einsatzberichte ORDER BY id DESC LIMIT 1";
		$db->setQuery($query);
		$rows = $db->loadObjectList();
		$cid      = $rows[0]->id;
		$send = sendMail_auto($cid,'neuer Bericht: ');
		else:
		$send = sendMail_auto($cid,'Update: ');
		endif;
		endif;
	endif;
    //print_r ($send);break;
    }
}
	    function sendMail_auto($cid,$status) {

		

		//$model = $this->getModel();
		$params = JComponentHelper::getParams('com_einsatzkomponente');
		$user = JFactory::getUser();
		$query = 'SELECT * FROM #__eiko_einsatzberichte WHERE id = "'.$cid.'" LIMIT 1';
		$db = JFactory::getDBO();
		$db->setQuery($query);
		$result = $db->loadObjectList();
	
		$mailer = JFactory::getMailer();
		$config = JFactory::getConfig();
		
		$sender = array( 
    	$user->email,
    	$user->name );
		
		$mailer->setSender($sender);
		
		$user = JFactory::getUser();
		$recipient = $params->get('mail_empfaenger_auto',$user->email);
		
		$recipient 	 = explode( ',', $recipient);
		
					$data = array();
					foreach(explode(',',$result[0]->auswahl_orga) as $value):
						$db = JFactory::getDbo();
						$query	= $db->getQuery(true);
						$query
							->select('name')
							->from('#__eiko_organisationen')
							->where('id = "' .$value.'"');
						$db->setQuery($query);
						$results = $db->loadObjectList();
						if(count($results)){
							$data[] = ''.$results[0]->name.''; 
						}
					endforeach;
					$auswahl_orga=  implode(',',$data); 

					$orga		 = explode( ',', $auswahl_orga);
		$orgas 		 = str_replace(",", " +++ ", $auswahl_orga);
 
		$mailer->addRecipient($recipient);
		
		$mailer->setSubject($status.''.$orga[0].'  +++ '.$result[0]->summary.' +++');
		
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
					$query
						->select('*')
						->from('#__eiko_tickerkat')
						->where('id = "' .$result[0]->tickerkat.'"  AND state = "1" ');
					$db->setQuery($query);
					$kat = $db->loadObject();
		
		$link = JRoute::_( JURI::root() . 'index.php?option=com_einsatzkomponente&view=einsatzbericht&id='.$result[0]->id.'&Itemid='.$params->get('homelink','')); 
		
		$body   = ''
				. '<h2>+++ '.$result[0]->summary.' +++</h2>';
		if ($params->get('send_mail_kat','0')) :	
		$body   .= '<h4>'.JText::_($kat->title).'</h4>';
		endif;
		if ($params->get('send_mail_orga','0')) :	
		$body   .= '<span><b>Eingesetzte Kräfte:</b> '.$orgas.'</span>';
		endif;
		$body   .= '<div>';
		if ($params->get('send_mail_desc','0')) :	
		if ($result[0]->desc) :	
    	$body   .= '<p>'.$result[0]->desc.'</p>';
		else:
    	$body   .= '<p>Ein ausführlicher Bericht ist zur Zeit noch nicht vorhanden.</p>';
		endif;
		endif;
		if ($params->get('send_mail_link','0')) :	
    	$body   .= '<p><a href="'.$link.'" target="_blank">Link zur Homepage</a></p>';
		endif;
		if ($result[0]->image) :	
		if ($params->get('send_mail_image','0')) :	
		$body   .= '<img src="'.JURI::root().$result[0]->image.'" style="margin-left:10px;float:right;height:50%;" alt="Einsatzbild"/>';
		endif;
		endif;
		$body   .= '</div>';
		

		$mailer->isHTML(true);
		$mailer->Encoding = 'base64';
		$mailer->setBody($body);
		// Optionally add embedded image
		//$mailer->AddEmbeddedImage( JPATH_COMPONENT.'/assets/logo128.jpg', 'logo_id', 'logo.jpg', 'base64', 'image/jpeg' );
		
		$send = $mailer->Send();
        return 'gesendet'; 
    }
	
	function upload($id,$fieldName)
	{
		jimport('joomla.filesystem.file');
		jimport('joomla.filesystem.folder');

		$user	= JFactory::getUser();
 
		//this is the name of the field in the html form, filedata is the default name for swfupload
		//so we will leave it as that
		//$fieldName = 'Filedata';
		
		ini_set('memory_limit', -1);
		
		$params = JComponentHelper::getParams('com_einsatzkomponente');
		$count_data=count($_FILES['data']['name']) ;  ######### count the data #####
$count = 0;
while($count < $count_data)
{
		$fileName = $_FILES['data']['name'][$count];//echo $count.'= Name:'.$fileName.'<br/>';
		$fileName = JFile::makeSafe($fileName);
		$uploadedFileNameParts = explode('.',$fileName);
		$uploadedFileExtension = array_pop($uploadedFileNameParts);
 
		$fileTemp = $_FILES['data']['tmp_name'][$count];
		$count++;
		// remove invalid chars
//		$file_extension = strtolower(substr(strrchr($fileName,"."),1));
//		$name_cleared = preg_replace("#[^A-Za-z0-9 _.-]#", "", $fileName);
//		if ($name_cleared != $file_extension){
//			$fileName = $name_cleared;
//		}
					
					
					
						
		$rep_id = $id;   // Einsatz_ID holen für Zuordnung der Bilder in der Datenbank
		$watermark_image = JRequest::getVar('watermark_image', $params->get('watermark_image'));
		
		// Check ob Bilder in einen Unterordner (OrdnerName = ID-Nr.) abgespeichert werden sollen :
		if ($params->get('new_dir', '1')) :
		$rep_id_ordner = '/'.$rep_id;
		else:
		$rep_id_ordner = '';
		endif;
		
		$fileName = $rep_id.'-'.$fileName;
		
		
		 // Check if dir already exists
        if (!JFolder::exists(JPATH_SITE.'/'.$params->get('uploadpath', 'images/com_einsatzkomponente/einsatzbilder').$rep_id_ordner)) 
		{ JFolder::create(JPATH_SITE.'/'.$params->get('uploadpath', 'images/com_einsatzkomponente/einsatzbilder').$rep_id_ordner);     }
		else  {}
        if (!JFolder::exists(JPATH_SITE.'/'.$params->get('uploadpath', 'images/com_einsatzkomponente/einsatzbilder').'/thumbs'.$rep_id_ordner)) 
		{ JFolder::create(JPATH_SITE.'/'.$params->get('uploadpath', 'images/com_einsatzkomponente/einsatzbilder').'/thumbs'.$rep_id_ordner);  }
		else  {}
	    
		$uploadPath  = JPATH_SITE.'/'.$params->get('uploadpath', 'images/com_einsatzkomponente/einsatzbilder').$rep_id_ordner.'/'.$fileName ;
		$uploadPath_thumb  = JPATH_SITE.'/'.$params->get('uploadpath', 'images/com_einsatzkomponente/einsatzbilder').'/thumbs'.$rep_id_ordner.'/'.$fileName ;
 //echo $fileTemp.' xxxx '.$uploadPath;exit; 
		if(!JFile::upload($fileTemp, $uploadPath)) 
		{
			echo JText::_( 'Bild konnte nicht verschoben werden' );
			return;
		}
		else
		{
			

		 // Check if dir already exists
        if (!JFolder::exists(JPATH_SITE.'/'.$params->get('uploadpath', 'images/com_einsatzkomponente/einsatzbilder').'/thumbs')) 
		{ JFolder::create(JPATH_SITE.'/'.$params->get('uploadpath', 'images/com_einsatzkomponente/einsatzbilder').'/thumbs');        }
		else  {}
		
		
		// Exif-Information --- Bild richtig drehen
	    $bild = $uploadPath;
		$image = imagecreatefromstring(file_get_contents($bild));
		$exif = exif_read_data($bild);
		if(!empty($exif['Orientation'])) {
			switch($exif['Orientation']) {
				case 8:
					$image = imagerotate($image,90,0);
					break;
				case 3:
					$image = imagerotate($image,180,0);
					break;
				case 6:
					$image = imagerotate($image,-90,0);
					break;
			}
		}
		 
		// scale image
		list( $original_breite, $original_hoehe, $typ, $imgtag, $bits, $channels, $mimetype ) = @getimagesize( $bild );
		$ratio = imagesx($image)/imagesy($image); // width/height
		if($ratio > 1) {
			$width = $original_breite;
			$height = round($original_breite/$ratio);
		} else {
			$width = round($original_hoehe*$ratio);
			$height = $original_hoehe;
		}
		$scaled = imagecreatetruecolor($width, $height);
		imagecopyresampled($scaled, $image, 0, 0, 0, 0, $width, $height, imagesx($image), imagesy($image));
		 
		imagejpeg($scaled, $bild);
		//imagedestroy($image);
		imagedestroy($scaled);

		
		// thumbs erstellen und unter /thumbs abspeichern
	    $bild = $uploadPath;
		@list( $original_breite, $original_hoehe, $typ, $imgtag, $bits, $channels, $mimetype ) = @getimagesize( $bild );
		$speichern = $uploadPath_thumb;
     	$originalbild = imagecreatefromjpeg( $bild ); 
	    $maxbreite = $params->get('thumbwidth', '100');
	    $maxhoehe = $params->get('thumbhigh', '100');
	  	$quadratisch = $params->get('quadratisch', 'true');
		$qualitaet = '80';
 
    if ($quadratisch === 'false')
    {
        // Höhe und Breite für proportionales Thumbnail berechnen
        if ($original_breite > $maxbreite || $original_hoehe > $maxhoehe)
        {
            $thumb_breite = $maxbreite;
            $thumb_hoehe  = $maxhoehe;
            if ($thumb_breite / $original_breite * $original_hoehe > $thumb_hoehe)
            {
                $thumb_breite = round( $thumb_hoehe * $original_breite / $original_hoehe );
            }
            else
            {
                $thumb_hoehe = round( $thumb_breite * $original_hoehe / $original_breite );
            }
        }
        else
        {
            $thumb_breite = $original_breite;
            $thumb_hoehe = $original_hoehe;
        }
		
        // Thumbnail erstellen
        $thumb = imagecreatetruecolor( $thumb_breite, $thumb_hoehe );
        imagecopyresampled( $thumb, $originalbild, 0, 0, 0, 0, $thumb_breite, $thumb_hoehe, $original_breite, $original_hoehe );
    }
    else if ($quadratisch === 'true')
    {
        // Kantenlänge für quadratisches Thumbnail ermitteln
        $originalkantenlaenge = $original_breite < $original_hoehe ? $original_breite : $original_hoehe;
        $tmpbild = imagecreatetruecolor( $originalkantenlaenge, $originalkantenlaenge );
        if ($original_breite > $original_hoehe)
        {
            imagecopy( $tmpbild, $originalbild, 0, 0, round( $original_breite-$originalkantenlaenge )/2, 0, $original_breite, $original_hoehe );
        }
        else if ($original_breite <= $original_hoehe )
        {
            imagecopy( $tmpbild, $originalbild, 0, 0, 0, round( $original_hoehe-$originalkantenlaenge )/2, $original_breite, $original_hoehe );
        }
        // Thumbnail für Einsatzliste usw. erstellen
        $thumb = imagecreatetruecolor( $maxbreite, $maxbreite );
        imagecopyresampled( $thumb, $tmpbild, 0, 0, 0, 0, $maxbreite, $maxbreite, $originalkantenlaenge, $originalkantenlaenge );
    }

 
        imagejpeg( $thumb, $speichern, $qualitaet ); 
   		imagedestroy( $thumb );
			
			
			
			$custompath = $params->get('uploadpath', 'images/com_einsatzkomponente/einsatzbilder');
			chmod($uploadPath, 0644);
			chmod($uploadPath_thumb, 0644);
			$db = JFactory::getDBO();
			$query = 'INSERT INTO #__eiko_images SET report_id="'.$rep_id.'", image="'.$custompath.$rep_id_ordner.'/'.$fileName.'", thumb="'.$custompath.'/thumbs'.$rep_id_ordner.'/'.$fileName.'", state="1", created_by="'.$user->id.'"';
			$db->setQuery($query);
			$db->query();
			
		$db = JFactory::getDBO();
		$query = 'SELECT image FROM #__eiko_einsatzberichte WHERE id ="'.$rep_id.'" ';
		$db->setQuery($query);
		$rows = $db->loadObjectList();
		$check_image      = $rows[0]->image;

		if ($params->get('titelbild_auto', '1')):
		if ($check_image == ''):
		$db		= JFactory::getDBO();
		$query	= $db->getQuery(true);
		$query->update('#__eiko_einsatzberichte');
		$query->set('image = "'.$custompath.$rep_id_ordner.'/'.$fileName.'" ');
		$query->where('id ="'.$rep_id.'"');
		$db->setQuery((string) $query);

		try
		{
			$db->execute();
		}
		catch (RuntimeException $e)
		{
			JError::raiseError(500, $e->getMessage());
		}
		endif;
		endif;
			
			echo JText::_( 'Bild wurde hochgeladen' ).'<br/>';
			
			
$source = JPATH_SITE.'/'.$params->get('uploadpath', 'images/com_einsatzkomponente/einsatzbilder').$rep_id_ordner.'/'.$fileName ; //the source file
$destination =  JPATH_SITE.'/'.$params->get('uploadpath', 'images/com_einsatzkomponente/einsatzbilder').$rep_id_ordner.'/'.$fileName ; //were to place the thumb
$watermark =  JPATH_SITE.'/administrator/components/com_einsatzkomponente/assets/images/watermark/'.$watermark_image.''; //the watermark files

    // Einsatzbilder resizen
	$image_resize = $params->get('image_resize', 'true');
    if ($image_resize === 'true'):
	$newwidth = $params->get('image_resize_max_width', '800');
	$newheight = $params->get('image_resize_max_height', '600');
    list($width, $height) = getimagesize($source);
    if($width > $height && $newheight < $height){
        $newheight = $height / ($width / $newwidth);
    } else if ($width < $height && $newwidth < $width) {
        $newwidth = $width / ($height / $newheight);   
    } else {
        $newwidth = $width;
        $newheight = $height;
    }
    $thumb = imagecreatetruecolor($newwidth, $newheight);
    $source_name = imagecreatefromjpeg($source);
    imagecopyresized($thumb, $source_name, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);
	imagejpeg($thumb, $destination, 100);  
	endif;

    // Wasserzeichen einbauen
	$watermark_show = $params->get('watermark_show', 'true');
    if ($watermark_show === 'true'):
	$watermark_pos_x = $params->get('watermark_pos_x', '0');
	$watermark_pos_y = $params->get('watermark_pos_y', '60');
	list($sourcewidth,$sourceheight)=getimagesize($source);
	list($watermarkwidth,$watermarkheight)=getimagesize($watermark);

	$w_pos_x = $watermark_pos_x;
	$w_pos_y = $sourceheight-$watermark_pos_y;

	$source_img = imagecreatefromjpeg($source);
	$watermark_img = imagecreatefrompng($watermark);
	imagecopy($source_img, $watermark_img, $w_pos_x, $w_pos_y, 0, 0, $watermarkwidth,$watermarkheight);
	imagejpeg($source_img, $destination, 100);  
	imagedestroy ($source_img);
	imagedestroy ($watermark_img);
	endif;
		}} 
 // Ende der Schleife			
	}

