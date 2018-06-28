<?php
$options = getopt('f:n::r::');
$filename = !empty($options['f']) ? $options['f'] : null;
$n98path = !empty($options['n']) ? $options['n'] : 'n98-magerun.phar';
$magentoRoot = !empty($options['r']) ? $options['r'] : '';

if (empty($filename) || !file_exists($filename)) {
    echo "ERROR: Invalid file\n";
    exit(1);
}


$contents = file_get_contents($filename);
$lines = explode("\n", $contents);
foreach ($lines as $line) {
    $line = trim($line);
    if (empty($line) || substr($line, 0, 1) == ';' || strpos($line, '=') === false) {
        continue;
    }

    list($key, $value) = explode('=', $line);
    if (strpos($key, 'setting.') === 0) {
        $setting = str_replace(array('setting.', '.'), array('', '/'), $key);
        $params = array();
        if(!empty($magentoRoot)){
            $params[] = '--root-dir='.$magentoRoot;
        }
        $return = shell_exec("php " . $n98path . " ". implode(' ', $params). " config:set ".$setting." ". $value);
        echo $return;
    }
}
exit(0);