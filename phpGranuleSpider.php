#!/usr/bin/php
<?php
/*******************************************************************************

    This file is part of "phpGranuleSpider" - Copyright 2013 Goulag PARKINSON
    Author(s) : Goulag PARKINSON <goulag.parkinson@gmail.com>

    "phpGranuleSpider" is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    any later version.

    "phpGranuleSpider" is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with "phpGranuleSpider".  If not, see <http://www.gnu.org/licenses/>.

*******************************************************************************/

$config_array = parse_ini_file("phpGranuleSpider.ini", TRUE);
$regex_array = array();
foreach($config_array['products'] as $key => $product_id) {
	$product_regex = $config_array[$product_id]['productRegex'];
	$regex_array[$product_regex] = $product_id;
}
$config_array['regex_array'] = $regex_array;


function match_product(&$file_array) {
	global $config_array;
	$filename = $file_array['name'];
	foreach($config_array['regex_array'] as $regex => $product_id) {
		if (preg_match("/".$regex."/",$filename, $matches)) {
			$fmt_array = explode(',',$config_array[$product_id]['productDateTimeFormatBegin']);
			foreach($fmt_array as $key => $index) {
				if (isset($mktime_cmd)) $mktime_cmd.=",";
				else $mktime_cmd = "mktime(";
				$mktime_cmd.= ($index==-1?0:$matches[$index]);
			}
			$mktime_cmd.=");";
			$filenameBeginDateTime = eval($mktime_cmd);
			unset($mktime_cmd);
			$fmt_array = explode(',',$config_array[$product_id]['productDateTimeFormatEnd']);
			foreach($fmt_array as $key => $index) {
				if (isset($mktime_cmd)) $mktime_cmd.=",";
				else $mktime_cmd = "mktime(";
				$mktime_cmd.= ($index==-1?0:$matches[$index]);
			}
			$mktime_cmd.=");";
			$filenameEndDateTime = eval($mktime_cmd);
			$file_array["product"] = $product_id;
			$file_array["begin_datetime"] = $product_id;
			$file_array["end_datetime"] = $product_id;
			return true;
		}	
	}
	return false;
}

$tree_options = array(
	'-X' => "Turn  on  XML  output. Outputs the directory tree as an XML formatted file.",
	'-s' => "Print the size of each file in bytes along with the name.",
	'-D' => "Print the date of the last modification time or if -c  is  used,  the last status change time for the file listed.",
  '--dirsfirst' => "List  directories  before  files. This is a meta-sort that alters the above sorts.  This option is disabled when -U is used.",
	'--du' => "For  each  directory  report its size as the accumulation of sizes of all its files and sub-directories (and their files, and so on).",
	'--timefmt "%Y-%m-%d %H:%M:%S"' => "Prints (implies -D) and formats the  date  according  to  the  format
              string which uses the strftime(3) syntax."
);


$options = getopt("d:");

if (!array_key_exists('d',$options)) {
  die("You must provide input directory to browse as -d option.\n");
}

$dirname = realpath($options['d']);
if (!is_dir($dirname)) die("Input directory is not a real directory path.\n"); 
if (!is_readable($dirname)) die("Unable to read input directory.\n");

echo "dirname = ".$dirname."\n";


$tree_command = "tree";
foreach ($tree_options as $option => $description) {
	$tree_command.=" ".$option;
}
$tree_output_tmpfilename = tempnam(sys_get_temp_dir(), 'granuleSpider_xml_');
$tree_command.=" ".$dirname." > ".$tree_output_tmpfilename;

echo "tree_command=".$tree_command."\n";

exec($tree_command, $output=array(), $return_val);

$tree_xml = new SimpleXMLElement(file_get_contents($tree_output_tmpfilename));

function recurse_xml($directory, &$parent_dirname) {
	foreach ($directory->file as $file) {
	  $filename = $file['name'];
		$filepath = $parent_dirname."/".$filename;
		$filesize = $file['size'];
		$filedatetime = $file['time'];
		$file_array = array(
			'name' => $filename,
		  'path' => $filepath,
			'size' => $filesize,
      'last_modification_datetime' => $filedatetime);
		if (!match_product($file_array)) {
			echo "[WARNING] Unable to match ".$filepath."\n";
		}
	}
	foreach ($directory->directory as $child_directory) {
	   $current_dirname=$parent_dirname."/".$child_directory['name'];
		 //echo $current_dirname."\n";
		 recurse_xml($child_directory,$current_dirname);
	}	
}

foreach ($tree_xml->directory as $directory) {
	 recurse_xml($directory,$directory['name']);
}

unset($tree_xml);
unlink($tree_output_tmpfilename);


?>
