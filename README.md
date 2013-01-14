PHP Granule Spider
======================

A script to assist browsing filesystem and matching Earth Observation satellite dataset.

Requirements

 * PHP >= 5.3
 * tree unix command line >= 1.6 (with -X XML output opt flag)


Usage
-----

See `phpGranuleSpider_sample.php` for examples.


Todo
----

 * Expand this README
 * Add doc blocks to the code
 * Add something like https://github.com/c9s/GetOptionKit to parse argv opts
 * For most of products it's impossible to read the beginDateTimeCoverage and endDateTimeCoverage from the filename.
   For example with QSCAT_L2B12, the avg time coverage is about 1h42m (multiple files for same day !).
   For the next generation tool we have to read into file (not just the pathname) inside global attribute but it's file format dependant.
