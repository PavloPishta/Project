<?php
/* 
	Appointment: Проверка файлов на наличие посторонних
	File: antivirus.php
 
*/

class antivirus{
	var $bad_files       = array();
	var $snap_files      = array();
	var $track_files      = array();
	var $snap      		 = false;
	var $checked_folders = array();
	var $dir_split       = '/';

	var $cache_files       = array(
		"./application/cache/application/country.php",
		"./application/cache/application/country_city_.php",
		"./application/cache/application/country_city_1.php",
		"./application/cache/application/country_city_2.php",
		"./application/cache/application/country_city_3.php",
		"./application/cache/application/country_city_4.php",
		"./application/cache/application/country_city_5.php",
		"./application/cache/application/country_city_6.php",
		"./application/cache/application/country_city_7.php",
		"./application/cache/application/country_city_8.php",
		"./application/cache/application/country_city_9.php",
		"./application/cache/application/country_city_10.php",
		"./application/cache/application/country_city_11.php",
		"./application/cache/application/country_city_12.php",
		"./application/cache/application/country_city_13.php",
		"./application/cache/application/country_city_14.php",
		"./application/cache/application/country_city_15.php",
		"./application/cache/application/country_city_16.php",
		"./application/cache/application/country_city_17.php",
		"./application/cache/application/country_city_18.php",
		"./application/cache/application/country_city_19.php",
		"./application/cache/application/country_city_20.php",
		"./application/cache/application/country_city_21.php",
		"./application/cache/application/country_city_22.php",
		"./application/cache/application/country_city_23.php",
		"./application/cache/application/country_city_24.php",
		"./application/cache/application/country_city_25.php",
	);

	var $good_files       = array(
		"./.htaccess",
		"./application/backup/.htaccess",
		"./application/cache/.htaccess",
		"./application/cache/application/.htaccess",
		"./application/data/.htaccess",
		"./lang/.htaccess",
		"./uploads/.htaccess",
		"./uploads/smiles/.htaccess",
		"./uploads/gifts/.htaccess",
		"./application/classes/antivirus.php",
		"./application/classes/id3v2.php",
		"./application/classes/images.php",
		"./application/classes/mail.php",
		"./application/classes/mysql.php",
		"./application/classes/parse.php",
		"./application/classes/tpl.php",
		"./application/classes/wall.php",
		"./application/classes/wall.public.php",
		"./application/data/config.php",
		"./application/data/db.php",
		"./application/inc/antivirus.php",
		"./application/inc/ban.php",
		"./application/inc/db.php",
		"./application/inc/dumper.php",
		"./application/inc/functions.php",
		"./application/inc/gifts.php",
		"./application/inc/groups.php",
		"./application/inc/login.php",
		"./application/inc/mail.php",
		"./application/inc/mail_tpl.php",
		"./application/inc/main.php",
		"./application/inc/massaction.php",
		"./application/inc/lib.php",
		"./application/inc/mysettings.php",
		"./application/inc/notes.php",
		"./application/inc/search.php",
		"./application/inc/static.php",
		"./application/inc/application.php",
		"./application/inc/tpl.php",
		"./application/inc/users.php",
		"./application/inc/videos.php",
		"./application/inc/albums.php",
		"./application/inc/musics.php",
		"./application/inc/stats.php",
		"./application/inc/logs.php",
		"./application/inc/country.php",
		"./application/inc/city.php",
		"./application/modules/albums.php",
		"./application/modules/attach.php",
		"./application/modules/attach_groups.php",
		"./application/modules/audio.php",
		"./application/modules/balance.php",
		"./application/modules/blog.php",
		"./application/modules/editprofile.php",
		"./application/modules/fave.php",
		"./application/modules/friends.php",
		"./application/modules/functions.php",
		"./application/modules/gifts.php",
		"./application/modules/groups.php",
		"./application/modules/gzip.php",
		"./application/modules/im.php",
		"./application/modules/loadcity.php",
		"./application/modules/login.php",
		"./application/modules/messages.php",
		"./application/modules/news.php",
		"./application/modules/notes.php",
		"./application/modules/photo.php",
		"./application/modules/profile.php",
		"./application/modules/public.php",
		"./application/modules/register.php",
		"./application/modules/register_main.php",
		"./application/modules/restore.php",
		"./application/modules/search.php",
		"./application/modules/settings.php",
		"./application/modules/status.php",
		"./application/modules/subscriptions.php",
		"./application/modules/support.php",
		"./application/modules/video.php",
		"./application/modules/videos.php",
		"./application/modules/wall.php",
		"./application/init.php",
		"./application/lib.php",
		"./badbrowser.php",
		"./nav.php",
		"./index.php",
		"./application/captcha/captcha.php",
		"./application/captcha/sec_code.php",
		"./application/modules/profile_delet.php",
		"./application/modules/profile_ban.php",
		"./application/modules/offline.php",
		"./application/classes/download.php",
		"./application/inc/report.php",
		"./application/inc/xfields.php",
		"./application/modules/distinguish.php",
		"./application/modules/doc.php",
		"./application/modules/fast_search.php",
		"./application/modules/public_audio.php",
		"./application/modules/report.php",
		"./application/modules/repost.php",
		"./application/modules/static.php",
		"./application/modules/updates.php",
		"./application/modules/votes.php",
		"./uploads/doc/.htaccess",
	);

	function antivirus ()
	{
		if(@file_exists(APPLICATION_DIR.'/data/snap.db')) {
  			$filecontents = file(APPLICATION_DIR.'/data/snap.db');

		    foreach ($filecontents as $name => $value) {
	    	  $filecontents[$name] = explode("|", trim($value));
	    	    $this->track_files[$filecontents[$name][0]] = $filecontents[$name][1];
		    }
			$this->snap = true;

		}

	}
	
	function scan_files( $dir, $snap = false, $access = false )
	{
		$this->checked_folders[] = $dir . $this->dir_split . $file;
	
		if ( $dh = @opendir( $dir ) )
		{
			while ( false !== ( $file = readdir($dh) ) )
			{
				if ( $file == '.' or $file == '..' or $file == '.svn' or $file == '.DS_store' )
				{
					continue;
				}
		
				if ( is_dir( $dir . $this->dir_split . $file ) )
				{

					if ($dir != ROOT_DIR)
					$this->scan_files( $dir . $this->dir_split . $file, $snap, $access );
				}
				else
				{

					if ($this->snap OR $snap) $tpl = "|tpl|js|lng|htaccess"; elseif($access) $tpl = "|htaccess"; else $tpl = "";

					if ( preg_match( "#.*\.(php|cgi|pl|perl|php3|php4|php5|php6".$tpl.")#i", $file ) )
					{

					  $folder = str_replace(ROOT_DIR, ".",$dir);
					  $file_size = filesize($dir . $this->dir_split . $file);
					  $file_crc = md5_file($dir . $this->dir_split . $file);
					  $file_date = date("d.m.Y H:i:s", filectime($dir . $this->dir_split . $file));

					  if ($snap) {

						$this->snap_files[] = array( 'file_path' => $folder . $this->dir_split . $file,
													 'file_crc' => $file_crc );


                      } else {

						if ($this->snap) {


							if ($this->track_files[$folder . $this->dir_split . $file] != $file_crc AND !in_array($folder . $this->dir_split . $file, $this->cache_files))
							$this->bad_files[] = array( 'file_path' => $folder . $this->dir_split . $file,
													'file_name' => $file,
													'file_date' => $file_date,
													'type' => 1,
													'file_size' => $file_size );

					    } else { 

						 if (!in_array($folder . $this->dir_split . $file, $this->good_files))
						 $this->bad_files[] = array( 'file_path' => $folder . $this->dir_split . $file,
													'file_name' => $file,
													'file_date' => $file_date,
													'type' => 0,
													'file_size' => $file_size ); 

						}

					  }
					}
				}
			}
		}
	}
}

?>