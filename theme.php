<?php
/**
 * $Header: /cvsroot/bitweaver/_bit_users/theme.php,v 1.1.1.1.2.3 2005/10/01 13:09:34 spiderr Exp $
 *
 * Copyright (c) 2004 bitweaver.org
 * Copyright (c) 2003 tikwiki.org
 * Copyright (c) 2002-2003, Luis Argerich, Garland Foster, Eduardo Polidor, et. al.
 * All Rights Reserved. See copyright.txt for details and a complete list of authors.
 * Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details
 *
 * $Id: theme.php,v 1.1.1.1.2.3 2005/10/01 13:09:34 spiderr Exp $
 * @package users
 * @subpackage functions
 */
global $gEditMode;
$gEditMode = 'theme';

/**
 * required setup
 */
include_once( '../bit_setup_inc.php' );
include_once( USERS_PKG_PATH.'BitUser.php' );
include_once( THEMES_PKG_PATH.'css_lib.php' );
include_once( THEMES_PKG_PATH.'theme_control_lib.php' );
include_once( KERNEL_PKG_PATH.'BitSystem.php' );

global $gBitUser;
global $gBitSystem;

if (!$gBitUser->isRegistered()) {
	$gBitSmarty->assign('msg', tra("Permission denied: You are not logged in"));
	$gBitSystem->display( 'error.tpl' );
	die;
}

include_once(USERS_PKG_PATH.'lookup_user_inc.php');

if ($gQueryUser->mUserId != $gBitUser->mUserId && !$gBitUser->object_has_permission($gBitUser->mUserId, $gQueryUser->mInfo['content_id'], 'bituser', 'bit_p_admin_user')) {
	$gBitSmarty->assign('msg', tra('You do not have permission to edit this user\'s theme'));
	$gBitSystem->display('error.tpl');
	die;
}

//******* HELPER FUNCTIONS *******
// get an array of filesnames in the given directory
function ls_a($wh)
{
	$files = NULL;
   if (is_dir($wh) && $handle = opendir($wh))
   {
       while (false !== ($file = readdir($handle)))
       {
           if ($file !== "." && $file !== ".." )
           {
               if(!isset($files)) $files=$file;
               else $files = $file."\r\n".$files;
           }
       }
       closedir($handle);
   }
   $arr=explode("\r\n", $files);
   return $arr;
}

function delete($dir, $pattern = "*.*")
{
	$deleted = false;
    $pattern = str_replace(array("\*","\?"), array(".*","."), preg_quote($pattern));
    if (substr($dir,-1) != "/") $dir.= "/";
    if (is_dir($dir)) {
    	$d = opendir($dir);
        while ($file = readdir($d)) {
            if (is_file($dir.$file) && ereg("^".$pattern."$", $file)){
                if (unlink($dir.$file))
                	$deleted[] = $file;
            }
        }
        closedir($d);
        return $deleted;
    }
    else return 0;
}

// it copies $wf to $wto
function copy_dirs($wf, $wto)
{
   if (!file_exists($wto))
   {
       mkdir($wto, 0777);
   }
   $arr=ls_a($wf);
   foreach ($arr as $fn)
   {
       if($fn)
       {
           $fl=$wf."/".$fn;
           $flto=$wto."/".$fn;
           if(is_dir($fl)) copy_dirs($fl, $flto);
           else // begin 2nd improvement
           {
               @copy($fl, $flto);
               chmod($flto, 0666);
           } // end 2nd improvement
       }
   }
}

//******** END HELPER FUNCTIONS **********

$F = array();
$errorMsg   = array();

// Special case: the admin has turned of custom user themes but this user currently uses his/her custom theme
if ($gQueryUser->mUserPrefs['theme'] == 'custom' && !$gBitUser->canCustomizeTheme() ) {
	$gQueryUser->storePreference('theme', NULL);		// Set their homepage theme to fall back to the site's themeImages
	$gQueryUser->mUserPrefs['theme'] = NULL;				// Update their mPrefs
}

$usingCustomTheme = ($gQueryUser->mUserPrefs['theme'] == 'custom' ? true : false);

$gBitSmarty->assign_by_ref('usingCustomTheme', $usingCustomTheme);

if( !$gBitUser->canCustomizeTheme() ) {
	$gBitSmarty->assign('msg', tra("Feature disabled"));
	$gBitSystem->display( 'error.tpl' );
	die;
}

$customCSSPath = $gQueryUser->getStoragePath('theme', $gQueryUser->mUserId, NULL);	// Path to this user's storage directory

$customCSSFile = $customCSSPath.'custom.css';	// Path to this user's custom stylesheet
$customCSSImageURL = $gQueryUser->getStorageURL( '/theme/images/', $gQueryUser->mUserId );
$gBitSmarty->assign_by_ref('customCSSImageURL',$customCSSImageURL);

// Create a custom.css for this user if they do not already have one
if (!file_exists($customCSSFile)) {
	if (!copy(THEMES_PKG_PATH.'/styles/basic/basic.css', $customCSSFile)) {
		$gBitSmarty->assign('msg', tra("Unable to create a custom CSS file for you!"));
		$gBitSystem->display( 'error.tpl' );
		die;
	}
}

if (isset($_REQUEST['fUseStandardTheme']) && $_REQUEST['fUseStandardTheme']) {
	$gQueryUser->storePreference('theme', NULL);
	$usingCustomTheme = false;
}

if (isset($_REQUEST['fUseCustomTheme']) && $_REQUEST['fUseCustomTheme']) {
	$gQueryUser->storePreference('theme', 'custom');
	$usingCustomTheme = true;
}

if ($usingCustomTheme) {
	$assignStyle = 'basic';

	// Action Responses
	if (isset($_REQUEST["fSaveCSS"])and $_REQUEST["fSaveCSS"]) {
		// Save any changes the user made to their CSS
		$fp = fopen($customCSSFile, "w");

		if (!$fp) {
			$gBitSmarty->assign('msg', tra("You dont have permission to write the style sheet"));
			$gBitSystem->display( 'error.tpl' );
			die;
		}

		fwrite($fp, $_REQUEST["textData"]);
		fclose ($fp);
		$successMsg[] = "CSS Updated and Saved";

	} elseif (isset($_REQUEST["fCancelCSS"]) && $_REQUEST['fCancelCSS']) {
		// Cancel (e.g. do nothing)
		$successMsg[] = "Changes have been cancelled";

	} elseif (isset($_REQUEST['fResetCSS'])) {
		// Reset CSS (e.g. copy an existing style as a base for their custom style)
		unlink_r( $customCSSPath );
		mkdir_p( $customCSSPath.'/images' );
		$resetStyle = $_REQUEST['resetStyle'];
		$cssData = $csslib->load_css2_file(THEMES_PKG_PATH."styles/$resetStyle/$resetStyle.css");
		if (file_exists($customCSSPath.'/images')) {
			$tcontrollib->expunge_dir($customCSSPath.'/images/');
		} else {
			mkdir_p($customCSSPath);
		}

		if (file_exists(THEMES_PKG_PATH."styles/$resetStyle/images")) {
			//clean out any old junk
			copy_dirs(THEMES_PKG_PATH."styles/$resetStyle/images", $customCSSPath.'/images/');
		}

		$fp = fopen($customCSSFile, "w");

		if (!$fp) {
			$gBitSmarty->assign('msg', tra("You dont have permission to write the style sheet"));
			$gBitSystem->display( 'error.tpl' );
			die;
		}

		fwrite($fp, $cssData);
		fclose ($fp);
		$successMsg[] = "Your CSS has been reset to the $resetStyle theme.";

	} elseif (isset($_REQUEST['fUpload'])) {
		if (!ereg(".JPG$|.PNG$|.GIF$|.BMP$",strtoupper($_FILES['fImgUpload']['name']))) {
			$errorMsg[] = "Your image must be one of the following types: .jpg, .png, .gif, .bmp";
		} else {
		//vd($_FILES['fImgUpload']['tmp_name']." -- ".$customCSSPath.'/images/'.$_FILES['fImgUpload']['name']);
			/*vd($_FILES['fImgUpload']['error'] == UPLOAD_ERR_OK);
			vd($_FILES['fImgUpload']['tmp_name']);
			vd($customCSSPath.'/images/'.$_FILES['fImgUpload']['name']);*/
			if (!file_exists($customCSSPath.'/images')) {
				mkdir_p($customCSSPath.'/images');
			}
			if ($_FILES['fImgUpload']['error'] == UPLOAD_ERR_OK && copy($_FILES['fImgUpload']['tmp_name'], $customCSSPath.'/images/'.$_FILES['fImgUpload']['name'])) {
				$successMsg[] = $_FILES['fImgUpload']['name']." successfully added.";
			}
			else {
				$errorMsg[] = "There was a problem uploading your image.";
			}
		}

	} elseif (isset($_REQUEST['fDeleteImg'])) {
		/*$imgArray = $_REQUEST['fDeleteImg'];
		foreach($imgArray as $key => $value) {
			$imgPath = $customCSSPath.'/images/'.$key;
			if (file_exists($imgPath)) {
				unlink($imgPath);
				$successMsg[] = "$key successfully deleted";
			} else {
				$errorMsg[] = "$key does not exists!";
			}
		}*/
		$imgName = key( $_REQUEST['fDeleteImg'] );
		$imgPath = $customCSSPath.'/images/'.$imgName;
		if (file_exists($imgPath)) {
			unlink($imgPath);
			$successMsg[] = "$imgName successfully deleted";
		} else {
			$errorMsg[] = "$imgName does not exists!";
		}
	} else {
		$action = 'edit';
	}
} else {
	// User is selecting from the standard themes
	if (isset($_REQUEST['fChangeTheme']) && $_REQUEST['fChangeTheme']) {
		$gQueryUser->storePreference('theme', NULL);
		$successMsg[] = "Theme successfully changed to ".$_REQUEST['fStyleChoice'];
		$assignStyle = $_REQUEST['fStyleChoice'];
	}
	header( 'Location: '.USERS_PKG_URL.'assigned_modules.php' );
}

// Get the list of themes the user can choose to derive from (aka Reset to)
$styles = &$tcontrollib->getStyles( NULL, ($usingCustomTheme ? FALSE : TRUE), FALSE );
$gBitSmarty->assign_by_ref( 'styles', $styles );

// $assignStyle is the default style which will be selected in the drop down list
if (!isset($assignStyle)) {
	$assignStyle = $gQueryUser->getPreference('theme', 'basic');
}
$gBitSmarty->assign_by_ref( 'assignStyle', $assignStyle);

// Read in this user's custom.css to display in the textarea
$lines = file($customCSSFile);
$data = '';
foreach ($lines as $line) {
	$data .= $line;
}

$gBitSmarty->assign('data', $data);

// Export success/error messages for display in the tpl.
if (isset($successMsg))
	$gBitSmarty->assign_by_ref('successMsg',$successMsg);
if (isset($errorMsg))
	$gBitSmarty->assign_by_ref('errorMsg', $errorMsg);

// Get the list of images used by this user's custom theme
$imageList = ls_a($customCSSPath.'images/');
$themeImages = array();
if( count( $imageList ) ) {
	foreach ($imageList as $image) {
		if (ereg(".JPG$|.PNG$|.GIF$|.BMP$",strtoupper($image))) {
			$themeImages[] = $image;
		}
	}
}

$gBitSmarty->assign('imagesCount', count($themeImages));
$gBitSmarty->assign_by_ref('themeImages',$themeImages);
$gBitSmarty->assign('PHP_SELF', $_SERVER['PHP_SELF']);
$gBitSmarty->assign_by_ref('gQueryUser', $gQueryUser);

$gBitSystem->display( 'bitpackage:users/user_theme.tpl');
?>
