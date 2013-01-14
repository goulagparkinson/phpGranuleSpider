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

function datetime_handler_AQUARIUS_L3_SSS_SMI_7DAY($dt) {
  //echo "[DEBUG] inside ".__FUNCTION__."\n";
  $t = date_parse_from_format("Y-z H:i:s", $dt[1]."-".($dt[2]-1)." 00:00:01");
  $b_ts = mktime($t['hour'],$t['minute'],$t['second'],$t['month'],$t['day'],$t['year']);
  $t = date_parse_from_format("Y-z H:i:s", $dt[3]."-".($dt[4]-1)." 23:59:59");
  $e_ts = mktime($t['hour'],$t['minute'],$t['second'],$t['month'],$t['day'],$t['year']);
/*
  $b_str = strftime("%F %T", $b_ts);
  $e_str = strftime("%F %T", $e_ts);
  echo "[DEBUG] filename = $dt[0]\n";
  echo "[DEBUG] beginDateTime_str = $b_str\n";
  echo "[DEBUG] endDateTime_str   = $e_str\n";
*/
  return array($b_ts,$e_ts);
}

function datetime_handler_ASCAT_L2_12km($dt) {
  //echo "[DEBUG] inside ".__FUNCTION__."\n";
  $ts_coverage = 3600+41*60+57; // 1h41m57s is the avg time coverage, need to be recomputeds
  $t = date_parse_from_format("Y-m-d H:i:s", $dt[1]."-".$dt[2]."-".$dt[3]." ".$dt[4].":".$dt[5].":".$dt[6]);
  $b_ts = mktime($t['hour'],$t['minute'],$t['second'],$t['month'],$t['day'],$t['year']);
  $e_ts = $b_ts+$ts_coverage;
/*
  $b_str = strftime("%F %T", $b_ts);
  $e_str = strftime("%F %T", $e_ts);
  echo "[DEBUG] filename = $dt[0]\n";
  echo "[DEBUG] beginDateTime_str = $b_str\n";
  echo "[DEBUG] endDateTime_str   = $e_str\n";
*/
  return array($b_ts,$e_ts);
}

function datetime_handler_ASCAT_L2_Coastal($dt) {
  return datetime_handler_ASCAT_L2_12km($dt);
}

function datetime_handler_ASI_AMSRE($dt) {
  //echo "[DEBUG] inside ".__FUNCTION__."\n";
  $ts_coverage = 3600+41*60+57; // 1h41m57s is the avg time coverage, need to be recomputeds
  $t = date_parse_from_format("Y-m-d H:i:s", $dt[1]."-".$dt[2]."-".$dt[3]." 00:00:01");
  $b_ts = mktime($t['hour'],$t['minute'],$t['second'],$t['month'],$t['day'],$t['year']);
  $t = date_parse_from_format("Y-m-d H:i:s", $dt[1]."-".$dt[2]."-".$dt[3]." 23:59:59");
  $e_ts = mktime($t['hour'],$t['minute'],$t['second'],$t['month'],$t['day'],$t['year']);
/*
  $b_str = strftime("%F %T", $b_ts);
  $e_str = strftime("%F %T", $e_ts);
  echo "[DEBUG] filename = $dt[0]\n";
  echo "[DEBUG] beginDateTime_str = $b_str\n";
  echo "[DEBUG] endDateTime_str   = $e_str\n";
*/
  return array($b_ts,$e_ts);
}

function datetime_handler_AVHRR_Pathfinder_NODC_L3C_v5_2($dt) {
  //echo "[DEBUG] inside ".__FUNCTION__."\n";
  $ts_coverage = 110000;
  $t = date_parse_from_format("Y-m-d H:i:s", $dt[1]."-".$dt[2]."-".$dt[3]." 00:00:01");
  $b_ts = mktime($t['hour'],$t['minute'],$t['second'],$t['month'],$t['day'],$t['year']);
  $b_ts = $b_ts - $ts_coverage/2; // Half of the avg time coverage for this product;
  $e_ts = $b_ts + $ts_coverage;
/*
  $b_str = strftime("%F %T", $b_ts);
  $e_str = strftime("%F %T", $e_ts);
  echo "[DEBUG] filename = $dt[0]\n";
  echo "[DEBUG] beginDateTime_str = $b_str\n";
  echo "[DEBUG] endDateTime_str   = $e_str\n";
*/
  return array($b_ts,$e_ts);
}

function datetime_handler_AVISO_DT_REF_MADT_MERGED_H($dt) {
  //echo "[DEBUG] inside ".__FUNCTION__."\n";
  $ts_coverage = 7*24*3600; // 7days is the time coverage
  $t = date_parse_from_format("Y-m-d H:i:s", $dt[1]."-".$dt[2]."-".$dt[3]." 00:00:01");
  $b_ts = mktime($t['hour'],$t['minute'],$t['second'],$t['month'],$t['day'],$t['year']);
  $e_ts = $b_ts+$ts_coverage-2;

/*
  $b_str = strftime("%F %T", $b_ts);
  $e_str = strftime("%F %T", $e_ts);
  echo "[DEBUG] filename = $dt[0]\n";
  echo "[DEBUG] beginDateTime_str = $b_str\n";
  echo "[DEBUG] endDateTime_str   = $e_str\n";
*/
  return array($b_ts,$e_ts);
}

function datetime_handler_AVISO_DT_REF_MADT_MERGED_UV($dt) {
  return datetime_handler_AVISO_DT_REF_MADT_MERGED_H($dt);
}

function datetime_handler_AVISO_NRT_MADT_MERGED_H($dt) {
  //echo "[DEBUG] inside ".__FUNCTION__."\n";
  $ts_coverage = 24*3600; // 1day is the time coverage
  $t = date_parse_from_format("Y-m-d H:i:s", $dt[1]."-".$dt[2]."-".$dt[3]." 00:00:01");
  $b_ts = mktime($t['hour'],$t['minute'],$t['second'],$t['month'],$t['day'],$t['year']);
  $e_ts = $b_ts+$ts_coverage-2;

/*
  $b_str = strftime("%F %T", $b_ts);
  $e_str = strftime("%F %T", $e_ts);
  echo "[DEBUG] filename = $dt[0]\n";
  echo "[DEBUG] beginDateTime_str = $b_str\n";
  echo "[DEBUG] endDateTime_str   = $e_str\n";
*/
  return array($b_ts,$e_ts);
}

function datetime_handler_AVISO_NRT_MADT_MERGED_UV($dt) {
  return datetime_handler_AVISO_NRT_MADT_MERGED_H($dt);
}

function datetime_handler_AVISO_NRT_MSWH_MERGED($dt) {
  return datetime_handler_AVISO_NRT_MADT_MERGED_H($dt);
}

function datetime_handler_AVISO_NRT_MWIND_MERGED($dt) {
  return datetime_handler_AVISO_NRT_MADT_MERGED_H($dt);
}

function datetime_handler_ERSST_V3B($dt) {
  $t = date_parse_from_format("Y-m-d H:i:s", $dt[1]."-".$dt[2]."-01 00:00:01");
  $b_ts = mktime($t['hour'],$t['minute'],$t['second'],$t['month'],$t['day'],$t['year']);
  $b_str = strftime("%F %T", $b_ts);
  $date = date_create($b_str);
  $date->modify('+1 month -2 secs');
  $e_ts = $date->getTimestamp();
  $e_str = date_format($date, 'Y-m-d H:i:s');
/*
  echo "[DEBUG] filename = $dt[0]\n";
  echo "[DEBUG] beginDateTime_str = $b_str\n";
  echo "[DEBUG] endDateTime_str   = $e_str\n";
*/
  return array($b_ts,$e_ts);
}

function datetime_handler_EUR_L2P_AVHRR_METOP_A($dt) {
  //echo "[DEBUG] inside ".__FUNCTION__."\n";
  $ts_coverage = 60*3; // 3m is the time coverage
  $t = date_parse_from_format("Y-m-d H:i:s", $dt[1]."-".$dt[2]."-".$dt[3]." ".$dt[4].":".$dt[5].":".$dt[6]);
  $b_ts = mktime($t['hour'],$t['minute'],$t['second'],$t['month'],$t['day'],$t['year']);
  $e_ts = $b_ts+$ts_coverage;
/*
  $b_str = strftime("%F %T", $b_ts);
  $e_str = strftime("%F %T", $e_ts);
  echo "[DEBUG] filename = $dt[0]\n";
  echo "[DEBUG] beginDateTime_str = $b_str\n";
  echo "[DEBUG] endDateTime_str   = $e_str\n";
*/
  return array($b_ts,$e_ts);
}

function datetime_handler_IFR_L4_SSTfnd_ODYSSEA_GLOB_010($dt) {
  //echo "[DEBUG] inside ".__FUNCTION__."\n";
  $ts_coverage = 24*3600; // 24h is the time coverage
  $t = date_parse_from_format("Y-m-d H:i:s", $dt[1]."-".$dt[2]."-".$dt[3]." 12:00:00");
  $e_ts = mktime($t['hour'],$t['minute'],$t['second'],$t['month'],$t['day'],$t['year']);
  $b_ts = $e_ts-$ts_coverage;
/*
  $b_str = strftime("%F %T", $b_ts);
  $e_str = strftime("%F %T", $e_ts);
  echo "[DEBUG] filename = $dt[0]\n";
  echo "[DEBUG] beginDateTime_str = $b_str\n";
  echo "[DEBUG] endDateTime_str   = $e_str\n";
*/
  return array($b_ts,$e_ts);
}

function datetime_handler_IFR_L4_SSTfnd_ODYSSEA_MED_002($dt) {
  return datetime_handler_IFR_L4_SSTfnd_ODYSSEA_GLOB_010($dt);
}

function datetime_handler_JPL_L4UHfnd_GLOB_MUR($dt) {
  //echo "[DEBUG] inside ".__FUNCTION__."\n";
  $ts_coverage = 24*3600; // 24h is the time coverage
  $t = date_parse_from_format("Y-m-d H:i:s", $dt[1]."-".$dt[2]."-".$dt[3]." 09:00:00");
  $b_ts = mktime($t['hour'],$t['minute'],$t['second'],$t['month'],$t['day'],$t['year']);
  $e_ts = $b_ts+$ts_coverage;
/*
  $b_str = strftime("%F %T", $b_ts);
  $e_str = strftime("%F %T", $e_ts);
  echo "[DEBUG] filename = $dt[0]\n";
  echo "[DEBUG] beginDateTime_str = $b_str\n";
  echo "[DEBUG] endDateTime_str   = $e_str\n";
*/
  return array($b_ts,$e_ts);
}

function datetime_handler_JPL_OUROCEAN_L4UHfnd_GLOB_G1SST($dt) {
  //echo "[DEBUG] inside ".__FUNCTION__."\n";
  $ts_coverage = 24*3600; // 24h is the time coverage
  $t = date_parse_from_format("Y-m-d H:i:s", $dt[1]."-".$dt[2]."-".$dt[3]." 00:00:01");
  $b_ts = mktime($t['hour'],$t['minute'],$t['second'],$t['month'],$t['day'],$t['year']);
  $e_ts = $b_ts+$ts_coverage-2;
/*
  $b_str = strftime("%F %T", $b_ts);
  $e_str = strftime("%F %T", $e_ts);
  echo "[DEBUG] filename = $dt[0]\n";
  echo "[DEBUG] beginDateTime_str = $b_str\n";
  echo "[DEBUG] endDateTime_str   = $e_str\n";
*/
  return array($b_ts,$e_ts);
}

function datetime_handler_MERCI_MER_RR_1P($dt) {
  //echo "[DEBUG] inside ".__FUNCTION__."\n";
  $ts_coverage = 44*60; // 44m is the time coverage (avg but not sure)
  $t = date_parse_from_format("Y-m-d H:i:s", $dt[1]."-".$dt[2]."-".$dt[3]." ".$dt[4].":".$dt[5].":".$dt[6]);
  $b_ts = mktime($t['hour'],$t['minute'],$t['second'],$t['month'],$t['day'],$t['year']);
  $e_ts = $b_ts+$ts_coverage;
/*
  $b_str = strftime("%F %T", $b_ts);
  $e_str = strftime("%F %T", $e_ts);
  echo "[DEBUG] filename = $dt[0]\n";
  echo "[DEBUG] beginDateTime_str = $b_str\n";
  echo "[DEBUG] endDateTime_str   = $e_str\n";
*/
  return array($b_ts,$e_ts);
}

function datetime_handler_NCDC_SEAWINDS_OW_6hr($dt) {
  //echo "[DEBUG] inside ".__FUNCTION__."\n";
  $ts_coverage = 24*3600; // 24h is the time coverage
  $t = date_parse_from_format("Y-m-d H:i:s", $dt[1]."-".$dt[2]."-".$dt[3]." 00:00:01");
  $b_ts = mktime($t['hour'],$t['minute'],$t['second'],$t['month'],$t['day'],$t['year']);
  $e_ts = $b_ts+$ts_coverage-2;
/*
  $b_str = strftime("%F %T", $b_ts);
  $e_str = strftime("%F %T", $e_ts);
  echo "[DEBUG] filename = $dt[0]\n";
  echo "[DEBUG] beginDateTime_str = $b_str\n";
  echo "[DEBUG] endDateTime_str   = $e_str\n";
*/
  return array($b_ts,$e_ts);
}

function datetime_handler_NCEP_CFSR1HR($dt) {
  //echo "[DEBUG] inside ".__FUNCTION__."\n";
  $t = date_parse_from_format("Y-m-d H:i:s", $dt[1]."-".$dt[2]."-01 00:00:01");
  $b_ts = mktime($t['hour'],$t['minute'],$t['second'],$t['month'],$t['day'],$t['year']);
  $b_str = strftime("%F %T", $b_ts);
  $date = date_create($b_str);
  $date->modify('+1 month -2 secs');
  $e_ts = $date->getTimestamp();
  $e_str = date_format($date, 'Y-m-d H:i:s');
/*
  echo "[DEBUG] filename = $dt[0]\n";
  echo "[DEBUG] beginDateTime_str = $b_str\n";
  echo "[DEBUG] endDateTime_str   = $e_str\n";
*/
  return array($b_ts,$e_ts);
}

function datetime_handler_NCEP_GFS($dt) {
  //echo "[DEBUG] inside ".__FUNCTION__."\n";
  $ts_coverage = 3*3600; // 3h is the time coverage
  $t = date_parse_from_format("Y-m-d H:i:s", $dt[1]."-".$dt[2]."-".$dt[3]." ".$dt[4].":00:01");
  $b_ts = mktime($t['hour'],$t['minute'],$t['second'],$t['month'],$t['day'],$t['year']);
  $e_ts = $b_ts+$ts_coverage-2;
/*
  $b_str = strftime("%F %T", $b_ts);
  $e_str = strftime("%F %T", $e_ts);
  echo "[DEBUG] filename = $dt[0]\n";
  echo "[DEBUG] beginDateTime_str = $b_str\n";
  echo "[DEBUG] endDateTime_str   = $e_str\n";
*/
  return array($b_ts,$e_ts);
}

function datetime_handler_OISST_AVHRR_AMSR_V2($dt) {
  //echo "[DEBUG] inside ".__FUNCTION__."\n";
  $ts_coverage = 24*3600; // 24h is the time coverage
  $t = date_parse_from_format("Y-m-d H:i:s", $dt[1]."-".$dt[2]."-".$dt[3]." 00:00:01");
  $b_ts = mktime($t['hour'],$t['minute'],$t['second'],$t['month'],$t['day'],$t['year']);
  $e_ts = $b_ts+$ts_coverage-2;
/*
  $b_str = strftime("%F %T", $b_ts);
  $e_str = strftime("%F %T", $e_ts);
  echo "[DEBUG] filename = $dt[0]\n";
  echo "[DEBUG] beginDateTime_str = $b_str\n";
  echo "[DEBUG] endDateTime_str   = $e_str\n";
*/
  return array($b_ts,$e_ts);
}

function datetime_handler_OISST_AVHRR_V2($dt) {
  return datetime_handler_OISST_AVHRR_AMSR_V2($dt);
}

function datetime_handler_OSTIA($dt) {
  //echo "[DEBUG] inside ".__FUNCTION__."\n";
  $ts_coverage = 24*3600; // 24h is the time coverage
  $t = date_parse_from_format("Y-m-d H:i:s", $dt[1]."-".$dt[2]."-".$dt[3]." 00:00:01");
  $b_ts = mktime($t['hour'],$t['minute'],$t['second'],$t['month'],$t['day'],$t['year']);
  $e_ts = $b_ts+$ts_coverage-2;
/*
  $b_str = strftime("%F %T", $b_ts);
  $e_str = strftime("%F %T", $e_ts);
  echo "[DEBUG] filename = $dt[0]\n";
  echo "[DEBUG] beginDateTime_str = $b_str\n";
  echo "[DEBUG] endDateTime_str   = $e_str\n";
*/
  return array($b_ts,$e_ts);
}

function datetime_handler_SSMI($dt) {
  //echo "[DEBUG] inside ".__FUNCTION__."\n";
  $ts_coverage = 24*3600; // 24h is the time coverage
  $t = date_parse_from_format("Y-m-d H:i:s", $dt[1]."-".$dt[2]."-".$dt[3]." 00:00:01");
  $b_ts = mktime($t['hour'],$t['minute'],$t['second'],$t['month'],$t['day'],$t['year']);
  $e_ts = $b_ts+$ts_coverage-2;

  $b_str = strftime("%F %T", $b_ts);
  $e_str = strftime("%F %T", $e_ts);
/*
  echo "[DEBUG] filename = $dt[0]\n";
  echo "[DEBUG] beginDateTime_str = $b_str\n";
  echo "[DEBUG] endDateTime_str   = $e_str\n";
*/
  return array($b_ts,$e_ts);
}

function datetime_handler_QSCAT_L2B12($dt) {
  //echo "[DEBUG] inside ".__FUNCTION__."\n";
  $t = date_parse_from_format("Y-z H:i:s", $dt[1]."-".($dt[2]-1)." 00:00:01");
  $b_ts = mktime($t['hour'],$t['minute'],$t['second'],$t['month'],$t['day'],$t['year']);
  $t = date_parse_from_format("Y-z H:i:s", $dt[1]."-".($dt[2]-1)." 23:59:59");
  $e_ts = mktime($t['hour'],$t['minute'],$t['second'],$t['month'],$t['day'],$t['year']);
/*
  $b_str = strftime("%F %T", $b_ts);
  $e_str = strftime("%F %T", $e_ts);
  echo "[DEBUG] filename = $dt[0]\n";
  echo "[DEBUG] beginDateTime_str = $b_str\n";
  echo "[DEBUG] endDateTime_str   = $e_str\n";
*/
  return array($b_ts,$e_ts);
}

?>
