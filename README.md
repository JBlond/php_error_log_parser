php_error_log_parser
=================

[![Code Climate](https://codeclimate.com/github/JBlond/apache_log_parser/badges/gpa.svg)](https://codeclimate.com/github/JBlond/apache_log_parser)

PHP Error log parser: Filter your log for errors

example

```PHP
<?php
require 'php_log_parser.class.php';
$log_parser = new php_log_parser("/Users/james/work/php_errors.log",false);
echo $log_parser->output();
?>
```
