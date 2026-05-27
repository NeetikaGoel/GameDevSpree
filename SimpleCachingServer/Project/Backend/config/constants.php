<?php

declare(strict_types=1);

const CACHE_KEY_LENGTH_MAX = 255;
const CACHE_VALUE_STRING_LENGTH_MAX = 1024;
const CACHE_TTL_SECONDS_DEFAULT = 7200;
const CACHE_TTL_SECONDS_MAX = 604800;

const CACHE_LIST_LIMIT_DEFAULT = 50;
const CACHE_LIST_LIMIT_MAX = 1000;

const KEY_REGEX= '/^[A-Za-z0-9._:-]+$/';


const VALUE_LENGTH_MAX = 1024;
const KEY_LENGTH_MAX = 255;
const HOST_DEFAULT = '127.0.0.1';
const PORT_DEFAULT = 8080;
const LOG_FILE_DEFAULT = 'logs/cache-server.log';