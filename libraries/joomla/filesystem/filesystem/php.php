<?php
/**
 * @version		$Id: php.php 9764 2007-12-30 07:48:11Z ircmaxell $
 * @package		Joomla.Framework
 * @subpackage	FileSystem
 * @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

/**
 * PHP Filesystem backend class
 *
 * @static
 * @package 	Joomla.Framework
 * @subpackage	FileSystem
 * @since		1.6
 */
class JFilesystemPHP extends JFilesystem
{

	protected function __construct($options) {
		parent::__construct($options);
	}

	public static function test() {
		return true;
	}

	public function check() {
		return true;
	}

	public function copy($src, $dest) {
		return @copy($src, $dest);
	}

	public function delete($src) {
		return @unlink($src);
	}

	public function rename($src, $dest) {
		return @rename($src, $dest);
	}

	public function read($src) {
		return @file_get_contents($src);
	}

	public function write($file, $buffer) {
		return @file_put_contents($file, $buffer);
	}

	public function isWritable($path) {
		return is_writable($path);
	}

	public function isReadable($path) {
		return is_readable($path);
	}

	public function chmod($path, $hex) {
		return @chmod($path, $hex);
	}

	public function chgrp($file, $group) {
		return @chgrp($file, $group);
	}

	public function chown($file, $owner) {
		return @chown($file, $owner);
	}

	public function exists($file) {
		return file_exists($file);
	}

	public function mkdir($path) {
		return @mkdir($path);
	}

	public function rmdir($path) {
		return @rmdir($path);
	}

	public function perms($path) {
		return fileperms($path);
	}

	public function owner($path) {
		return fileowner($path);
	}
}