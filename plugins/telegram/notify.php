<?php

// This entry point is launched by rTorrent and must never make the daemon
// wait for a web request or a Telegram retry.
if(!chdir(dirname(__FILE__)))
	exit(0);

$event = isset($argv[1]) ? $argv[1] : '';
$torrent = isset($argv[2]) ? $argv[2] : '';
$hash = isset($argv[3]) ? $argv[3] : '';
$comment = isset($argv[4]) ? $argv[4] : '';
if(isset($argv[5]))
	$_SERVER['REMOTE_USER'] = $argv[5];
// Absent when the handler was registered by an older version of the plugin
$details = array(
	'size' => isset($argv[6]) ? $argv[6] : '',
	'label' => isset($argv[7]) ? $argv[7] : '',
	'ratio' => isset($argv[8]) ? $argv[8] : '',
);

require_once(dirname(__FILE__).'/telegram.php');

$config = rTelegram::load();
if(!$config->canNotify($event))
	exit(0);
// hash_done runs after every check: only a check that failed is reported
if($event === 'checkfailed' && !rTelegram::isFailedCheck(isset($argv[9]) ? $argv[9] : '', isset($argv[10]) ? $argv[10] : ''))
	exit(0);

$message = $config->render($event, $torrent, $hash, $comment, $details);
$client = new rTelegramClient();
$result = $client->send($config->token, $config->chatId, $message, 'MarkdownV2');
if(empty($result['ok']))
{
	$error = isset($result['error']) ? $result['error'] : 'Telegram request failed';
	error_log('telegram: '.rTelegram::sanitizeError($error, $config->token));
}
exit(0);
