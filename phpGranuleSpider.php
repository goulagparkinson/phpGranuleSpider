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

$fileset_matched_array = array();
$fileset_unmatched_array = array();

function formatBytes($bytes, $precision = 2) { 
	$units = array('B', 'KB', 'MB', 'GB', 'TB'); 

	$bytes = max($bytes, 0); 
	$pow = floor(($bytes ? log($bytes) : 0) / log(1024)); 
	$pow = min($pow, count($units) - 1); 

	// Uncomment one of the following alternatives
	$bytes /= pow(1024, $pow);
	// $bytes /= (1 << (10 * $pow)); 

	return round($bytes, $precision) . ' ' . $units[$pow]; 
} 
function match_product(&$file_array) {
	global $config_array;
	$filepath = $file_array['path'];
	foreach($config_array['regex_array'] as $regex => $product_id) {
		if (preg_match("/".$regex."/",$filepath, $matches)) {
	
		$cmd_to_eval = '$dt = array(); ';
		foreach($matches as $value) {
			$cmd_to_eval.= '$dt[] = "'.$value.'"; ';
		}
		$t = eval($cmd_to_eval.'return '.$config_array[$product_id]['dateTimeBeginCallback'].';');
		$begin_datetime = strftime("%F %T", mktime($t['hour'],$t['minute'],$t['second'],$t['month'],$t['day'],$t['year']));
		$t = eval($cmd_to_eval.'return '.$config_array[$product_id]['dateTimeEndCallback'].';');
		$end_datetime = strftime("%F %T", mktime($t['hour'],$t['minute'],$t['second'],$t['month'],$t['day'],$t['year']));

			$file_array["product_id"] = $product_id;
			$file_array["begin_datetime"] = $begin_datetime;
			$file_array["end_datetime"] = $end_datetime;
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

//echo "dirname = ".$dirname."\n";


$tree_command = "tree";
foreach ($tree_options as $option => $description) {
	$tree_command.=" ".$option;
}
$tree_output_tmpfilename = tempnam(sys_get_temp_dir(), 'granuleSpider_xml_');
$tree_command.=" ".$dirname." > ".$tree_output_tmpfilename;

//echo "tree_command=".$tree_command."\n";

exec($tree_command, $output=array(), $return_val);

$tree_xml = new SimpleXMLElement(file_get_contents($tree_output_tmpfilename));

function recurse_xml($directory, &$parent_dirname) {
	global $fileset_matched_array;
	global $fileset_unmatched_array;
	foreach ($directory->file as $file) {
	  $filename = (string)$file['name'];
		$filepath = $parent_dirname."/".$filename;
		$filesize = (string)$file['size'];
		$filedatetime = (string)$file['time'];
		$file_array = array(
			'name' => $filename,
		  'path' => $filepath,
			'size' => $filesize,
      'last_modification_datetime' => $filedatetime);
		if (!match_product($file_array)) {
			echo "[WARNING] Unable to match ".$filepath."\n";
			$fileset_unmatched_array[] = $file_array;
		} else $fileset_matched_array[] = $file_array;
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

$total_analyzed_files = count($fileset_matched_array)+count($fileset_unmatched_array);
$total_matched_files = count($fileset_matched_array);
$total_unmatched_files = count($fileset_unmatched_array);
$product_stats = array();
foreach($fileset_matched_array as $key => $value) {
  if (!array_key_exists($value['product_id'], $product_stats)) $product_stats[$value['product_id']] = array('count' => 0, 'avg_filesize' => 0, 'sum_filesize' => 0, 'begin_datetime' => 100000, 'end_datetime' => 0);
	$product_stats[$value['product_id']]['count']++;
	$product_stats[$value['product_id']]['sum_filesize']+=$value['size'];
	$product_stats[$value['product_id']]['begin_datetime']=min($value['begin_datetime'],$product_stats[$value['product_id']]['begin_datetime']);
	$product_stats[$value['product_id']]['end_datetime']=max($value['end_datetime'],$product_stats[$value['product_id']]['end_datetime']);
}
foreach($product_stats as $product_id => $value) {
	$product_stats[$product_id]['avg_filesize'] = formatBytes(round($value['sum_filesize'] / $value['count']));
}
echo "[INFO] Number of matched   file : ".count($fileset_matched_array)."\n";
echo "[INFO] Number of unmatched file : ".count($fileset_unmatched_array)."\n";

print_r($product_stats);

?>
