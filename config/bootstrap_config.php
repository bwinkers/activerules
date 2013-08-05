<?php  defined('AR_VERSION') or die('No direct script access.');
/**
 * All INSTALLATION specific confguration happens here.
 * 
 * Copyright 2013 - Brian Winkers
 */

/*
 * Define where the modules directory is located.
 * Use a full path or a path or relative to index.php
 * This path is used by the index file to define MODPATH
 */
$activerules = '.'.DIRECTORY_SEPARATOR.'activerules';

/**
 * Define the active config storage mechanism
 * (array|file|rackfile|mongo|mongolab|cleardb|herokupg|mysql)
 */
$activerules_storage = 'file';

/**
 * Define the active cache storage mechanism
 * (NULL|file|memcache|memcachier|ironcache|apc)
 */
$activerules_cacher = NULL;

/**
 * Define the active logging mechanism
 * (NULL|file|syslog|syslogng|snmp)
 */
$activerules_logger = NULL;


