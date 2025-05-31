<?php

/*
 * Copyright (c) 2018 - 2025 Daniel Popiniuc.
 * All rights reserved. This program and the accompanying materials
 * are made available under the terms of the Eclipse Public License v1.0
 * which accompanies this distribution, and is available at
 * http://www.eclipse.org/legal/epl-v10.html
 *
 * Contributors:
 *    Daniel Popiniuc
 */
session_start();

$_SERVER['HTTP_USER_AGENT']      = 'Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:139.0) Gecko/20100101 Firefox/139.0';
$_SERVER['HTTP_ACCEPT']          = 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8';
$_SERVER['HTTP_ACCEPT_CHARSET']  = '';
$_SERVER['HTTP_ACCEPT_ENCODING'] = 'gzip, deflate';
$_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'en-US,en;q=0.7,ro;q=0.3';
$_SERVER['REMOTE_ADDR']          = '127.0.0.1';
$_SESSION                        = [
    'lang' => 'en_US'
];
