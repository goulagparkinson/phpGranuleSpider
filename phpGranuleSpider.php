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

require_once("productDateTimeHandler.inc.php");

date_default_timezone_set('GMT');

$now_time = time();
$output_basedir = sys_get_temp_dir();
$output_dirname = 'phpGranuleSpider_'.strftime("%F_%T", $now_time);

$options_array = array(
  'output_basedir' => $output_basedir,
  'output_dirname' => $output_dirname,
  'input_dir_array' => array()
);

$config_array = parse_ini_file("phpGranuleSpider.ini", TRUE);
$compress_suffix_array = explode(',', $config_array['compressSuffix']);
$compress_suffix_regex = '';
foreach($compress_suffix_array as $value) {
  $compress_suffix_regex.=(!empty($compress_suffix_regex)?"|":"");
  $suffix = strtolower($value);
  $compress_suffix_regex.=$suffix."|".strtoupper($suffix);
}
$compress_suffix_regex = "($|\.(".$compress_suffix_regex.")$)";

$regex_array = array();
foreach($config_array['products'] as $key => $product_id) {
  $product_regex = $config_array[$product_id]['productRegex'].$compress_suffix_regex;
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
      $fileset_unmatched_array[] = $file_array;
    } else $fileset_matched_array[] = $file_array;
  }
  foreach ($directory->directory as $child_directory) {
     $current_dirname=$parent_dirname."/".$child_directory['name'];
     //echo $current_dirname."\n";
     recurse_xml($child_directory,$current_dirname);
  }
}
function match_product(&$file_array) {
  global $config_array;
  $filepath = $file_array['path'];
  foreach($config_array['regex_array'] as $regex => $product_id) {
    if (preg_match("/".$regex."/",$filepath, $matches)) {
      $functionHandlerName = "datetime_handler_".str_replace(array('-','.'),"_",$product_id);
      $datetime_ts_array = call_user_func($functionHandlerName, $matches);

/*
      $cmd_to_eval = '$dt = array(); ';
      foreach($matches as $value) {
        $cmd_to_eval.= '$dt[] = "'.$value.'"; ';
      }
      $t = eval($cmd_to_eval.'return '.$config_array[$product_id]['dateTimeBeginCallback'].';');
      $begin_datetime = strftime("%F %T", mktime($t['hour'],$t['minute'],$t['second'],$t['month'],$t['day'],$t['year']));
      $t = eval($cmd_to_eval.'return '.$config_array[$product_id]['dateTimeEndCallback'].';');
      $end_datetime = strftime("%F %T", mktime($t['hour'],$t['minute'],$t['second'],$t['month'],$t['day'],$t['year']));
*/

      $file_array["product_id"] = $product_id;
      $file_array["begin_datetime"] = strftime("%F %T", $datetime_ts_array[0]);
      $file_array["end_datetime"] = strftime("%F %T", $datetime_ts_array[1]);
      $file_array["begin_datetime_ts"] = $datetime_ts_array[0];
      $file_array["end_datetime_ts"] = $datetime_ts_array[1];
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

$shortopts  = "d:o:n:";
//$shortopts .= "f:";  // Valeur requise
//$shortopts .= "v::"; // Valeur optionnelle
//$shortopts .= "abc"; // Ces options n'acceptent pas de valeur

$longopts  = array(
//    "required:",     // Valeur requise
    "debug::",    // Valeur optionnelle
//    "option",        // Aucune valeur
//    "opt",           // Aucune valeur
);
$options = getopt($shortopts, $longopts);

if (!array_key_exists('d',$options)) {
  echo "[CRITICAL] You must provide at least one input directory to browse as -d option !\n";
  exit(1);
} else {
  if (!is_array($options['d'])) $options['d'] = array($options['d']);
  foreach($options['d'] as $key => $value) {
    $input_dirname = realpath($value);
    if (!is_dir($input_dirname)) {
      echo "[WARNING] Input directory $input_dirname is not a real directory path.\n";
      continue;
    }
    if (!is_readable($input_dirname)) {
      echo "[WARNING] Unable to read input directory $input_dirname.\n";
      continue;
    }
    array_push($options_array['input_dir_array'],$input_dirname);
  }
}
$options_array['input_dir_array'] = array_unique($options_array['input_dir_array']);

if (!count($options_array['input_dir_array'])) {
  echo "[CRITICAL] You must provide at least one input directory to browse as -d option !\n";
  exit(1);
}

if (array_key_exists('n',$options)) {
  $options_array['output_dirname'] = $options['n'];
}

if (array_key_exists('o',$options)) {
  if (!is_dir($options['o'])) {
    echo "[CRITICAL] Output base directory is not a writable directory !\n";
    exit(1);
  }
  if (is_dir($options['o']."/".$options_array['output_dirname'])) {
    echo "[WARNING] Output directory already exist !\n";
  } else {
    if (!mkdir($options['o']."/".$options_array['output_dirname'])) {
      echo "[CRITICAL] Unable to create the output directory in this base directory !\n";
      exit(1);
    }
    rmdir($options_array['output_basedir'].'/'.$options_array['output_dirname']);
  }
  $options_array['output_basedir'] = $options['o'];
}

$options_array['output_dirpath'] = $options_array['output_basedir'].'/'.$options_array['output_dirname'];

foreach ($options_array['input_dir_array'] as $input_dirname) {
  echo "[DEBUG] Starting with input directory \"$input_dirname\"\n";
  $tree_command = "tree";
  foreach ($tree_options as $option => $description) {
    $tree_command.=" ".$option;
  }
  $tree_output_tmpfilename = tempnam(sys_get_temp_dir(), 'granuleSpider_xml_');
  $tree_command.=" ".$input_dirname." > ".$tree_output_tmpfilename;
  echo "[DEBUG] Unix tree command used is :\n$tree_command\n";
  exec($tree_command, $output=array(), $return_val);
  $tree_xml = new SimpleXMLElement(file_get_contents($tree_output_tmpfilename));

  foreach ($tree_xml->directory as $directory) {
     recurse_xml($directory,$directory['name']);
  }
  unset($tree_xml);
  unlink($tree_output_tmpfilename);
}

$total_analyzed_files = count($fileset_matched_array)+count($fileset_unmatched_array);
$total_matched_files = count($fileset_matched_array);
$total_unmatched_files = count($fileset_unmatched_array);
$product_stats = array();

foreach($fileset_matched_array as $key => $value) {
  if (!array_key_exists($value['product_id'], $product_stats)) {
    $product_stats[$value['product_id']] = array(
      'count' => 0,
      'avg_filesize' => 0,
      'sum_filesize' => 0,
      'begin_datetime' => 100000,
      'end_datetime' => 0,
      'per_day_count' => array(),
      'files' => array()
      );
  }
  $product_stats[$value['product_id']]['count']++;
  $product_stats[$value['product_id']]['sum_filesize']+=$value['size'];
  $product_stats[$value['product_id']]['begin_datetime']=min($value['begin_datetime'],$product_stats[$value['product_id']]['begin_datetime']);
  $product_stats[$value['product_id']]['end_datetime']=max($value['end_datetime'],$product_stats[$value['product_id']]['end_datetime']);
  $product_stats[$value['product_id']]['files'][sha1($value['path'])] = $value;

/*
  $begin_ts = strtotime(substr($value['begin_datetime'],0,9));
  $end_ts = strtotime(substr($value['end_datetime'],0,9));
  $date_ts = $begin_ts;
  $date_counter = 0;
  while($date_ts <= $end_ts) {
    echo $date_counter." date = ".date("Y-m-d", $date_ts)."\n";
    //write your code here
    $date_ts = strtotime("+1 day", $date_ts);
    $date_counter++;
  }
*/
}

/***********************************************************************
 * Building stats                                                      *
 **********************************************************************/

$product_count = count($product_stats);
$output_stats = "

;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;
Output stats for last phpGranuleSpider execution.
Datetime is : ".strftime("%F %T", time())."
;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;

Product(s) : ".$product_count."\n";

$product_counter = 1;
$all_products_file_count = 0;
$all_products_file_size = 0;

foreach($product_stats as $product_id => $value) {
  $output_stats.="Product ".$product_counter."/".$product_count." ".$product_id."\n\n";
  $output_stats.="\t"."Total number of file(s) : ".$value['count']."\n";
  $all_products_file_count+=$value['count'];
  $output_stats.="\t"."Total size of file(s)   : ".formatBytes($value['sum_filesize'])."\n";
  $all_products_file_size+=$value['sum_filesize'];
  $avg_filesize = round($value['sum_filesize'] / $value['count']);
  $product_stats[$product_id]['avg_filesize'] = $avg_filesize;
  $output_stats.="\t"."Average size of file    : ".formatBytes($avg_filesize)."\n";
  $output_stats.="\t"."First seen datetime     : ".$value['begin_datetime']."\n";
  $output_stats.="\t"."Last  seen datetime     : ".$value['end_datetime']."\n";
  $output_stats.="\n";
  $product_counter++;
}

$output_stats.="\n;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;
Total file count : ".$all_products_file_count."
Total file size  : ".formatBytes($all_products_file_size)."
;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;\n";

/***********************************************************************
 * Writing outputs / File serialization                                *
 **********************************************************************/

if (is_dir($options_array['output_dirpath']) || mkdir($options_array['output_dirpath'])) {

  // Serialization of "summary" products
  $output_filename = $options_array['output_dirpath'].'/summary.txt';
  if (!$output_handle = fopen($output_filename, 'w')) {
    echo "[ERROR] Unable to open output file ($output_filename)\n";
    continue;
  }
  if (fwrite($output_handle, $output_stats) === FALSE) {
    echo "[ERROR] Unable to write into file ($output_filename)\n";
  }
  fclose($output_handle);

  // Serialization of "unmatched" files
  $output_unmatched = "";
  foreach($fileset_unmatched_array as $key => $value) {
    echo "[DEBUG] Unmatched file ".$value['path']."\n";
    $output_unmatched.= $value['path']."\n";
  }
  $output_filename = $options_array['output_dirpath'].'/unmatched.txt';
  if (!$output_handle = fopen($output_filename, 'w+')) {
    echo "[ERROR] Unable to open output file ($output_filename)\n";
    continue;
  }
  if (fwrite($output_handle, $output_unmatched) === FALSE) {
    echo "[ERROR] Unable to write into file ($output_filename)\n";
  }
  fclose($output_handle);

  // Serialization of each "product" files
  foreach($product_stats as $product_id => $value) {
    $output_product = "";
    foreach($value['files'] as $key => $value) {
      $output_product.=$key.";".$value['path'].";".$value['name'].";".$value['size'].";".$value['begin_datetime'].";".$value['end_datetime']."\n";
    }
    $output_filename = $options_array['output_dirpath'].'/product_'.$product_id.'.txt';
    if (!$output_handle = fopen($output_filename, 'w+')) {
      echo "[ERROR] Unable to open output file ($output_filename)\n";
      continue;
    }
    if (fwrite($output_handle, $output_product) === FALSE) {
      echo "[ERROR] Unable to write into file ($output_filename)\n";
    }
    fclose($output_handle);
  }

}
?>
