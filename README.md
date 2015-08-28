php_error_log_parser
=================

[![Code Climate](https://codeclimate.com/github/JBlond/php_error_log_parser/badges/gpa.svg)](https://codeclimate.com/github/JBlond/php_error_log_parser) [![SensioLabsInsight](https://insight.sensiolabs.com/projects/5dc63c08-bfaa-440b-ae7a-4d46b50b2a00/mini.png)](https://insight.sensiolabs.com/projects/5dc63c08-bfaa-440b-ae7a-4d46b50b2a00)

PHP Error log parser: Filter your log for errors

example

```PHP
<?php
require 'php_log_parser.class.php';
$log_parser = new php_log_parser("/Users/james/work/php_errors.log",false);
echo $log_parser->output();
?>
```
