<?php 

if (!function_exists('debug_log')) {
    function debug_log($message, array $context = []) {
        if (config('app.debug')) {
            info($message, $context);
        }
    }
  }