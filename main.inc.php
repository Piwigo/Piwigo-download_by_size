<?php
/*
Plugin Name: Download by Size
Version: auto
Description: Select a photo size before download
Plugin URI: http://piwigo.org/ext/extension_view.php?eid=
Author: plg
Author URI: http://le-gall.net/pierrick
*/

if (!defined('PHPWG_ROOT_PATH'))
{
  die('Hacking attempt!');
}

define('DLSIZE_PATH' , PHPWG_PLUGINS_PATH.basename(dirname(__FILE__)).'/');

add_event_handler('loc_end_picture', 'dlsize_picture');
function dlsize_picture()
{
  global $conf, $template, $picture;

  // in case of file with a pwg_representative, we simply fallback to the
  // standard button (which downloads the original file)
  if (!$picture['current']['src_image']->is_original())
  {
    return;
  }
  
  $template->set_prefilter('picture', 'dlsize_picture_prefilter');

  $params = array(
    'id' => $picture['current']['id'],
    'part' => 'e',
    'download' => null,
    );
  $base_dl_url = add_url_params(get_root_url().PHPWG_PLUGINS_PATH.'download_by_size/action.php', $params);
  
  $template->assign(
    array(
      'DLSIZE_URL' => $base_dl_url.'&amp;size=',
      )
    );

  if ($conf['picture_download_icon'])
  {
    // even if original can't be downloaded, we set U_DOWNLOAD so that
    // visitor can download smaller sizes
    $template->append('current', array('U_DOWNLOAD' => '#'), true);
    
    if (!empty($picture['current']['download_url']))
    {
      $template->assign('DLSIZE_ORIGINAL', $picture['current']['download_url']);
    }
  }

  $template->set_filename('dlsize_picture', realpath(DLSIZE_PATH.'picture.tpl'));
  $template->parse('dlsize_picture');
}

function dlsize_picture_prefilter($content, &$smarty)
{
  $pattern = '#\{if isset\(\$current\.U_DOWNLOAD\)\}\s*<a #';
  $replacement = '{if isset($current.U_DOWNLOAD)}<a id="downloadSizeLink" ';
  $content = preg_replace($pattern, $replacement, $content);

  return $content;
}

?>
