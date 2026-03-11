<?php
/**
 * Test bootstrap - stubs CodeIgniter globals and constants
 * so pure-logic classes can be tested without the full framework.
 */

define('BASEPATH', __DIR__ . '/../system/');
define('APPPATH',  __DIR__ . '/../application/');
define('ENVIRONMENT', 'testing');

// Stub the CodeIgniter base controller so My_lib can be loaded standalone.
if (!class_exists('MX_Controller')) {
    class MX_Controller {
        public function __construct() {}
    }
}

// CodeIgniter helper: mdate() – used in My_lib::setDate()
if (!function_exists('mdate')) {
    function mdate(string $datestr = '', int $time = 0): string {
        if ($time === 0) {
            $time = time();
        }
        $datestr = str_replace(
            ['%Y', '%m', '%d', '%H', '%i', '%s', '%A'],
            ['Y',  'm',  'd',  'H',  'i',  's',  'l'],
            $datestr
        );
        return date($datestr, $time);
    }
}

// CodeIgniter helper: now() – returns current Unix timestamp
if (!function_exists('now')) {
    function now(): int {
        return time();
    }
}

// CodeIgniter helper: cal_days_in_month() is a PHP built-in (calendar ext).
// No stub needed.

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../application/libraries/My_lib.php';
