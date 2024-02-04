<?php

$token = 'YOUR_DISCORD_BOT_TOKEN';
$command = '!ping';

// Function to send a message to a Discord channel
function sendMessage($channelId, $message) {
    $url = "https://discord.com/api/channels/$channelId/messages";
    $data = array('content' => $message);
    $headers = array(
        'Authorization: Bot ' . $GLOBALS['token'],
        'Content-Type: application/json'
    );

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
    curl_close($ch);
    return $response;
}

// Function to process incoming Discord messages
function processMessage($data) {
    $message = $data['content'];
    $channelId = $data['channel_id'];

    if ($message === $GLOBALS['command']) {
        sendMessage($channelId, 'Pong!');
    }
}

// Start listening for incoming Discord messages
$socket = fsockopen("udp://discord.com", 443);
if ($socket) {
    fwrite($socket, "GET /api/gateway HTTP/1.0\r\nHost: discord.com\r\n\r\n");
    $response = fread($socket, 8192);
    fclose($socket);

    $response = json_decode(substr($response, strpos($response, "\r\n\r\n") + 4), true);

    $ws = new WebSocket($response['url']);
    $ws->on('message', function($ws, $data) {
        $data = json_decode($data, true);
        if (isset($data['t']) && isset($data['d'])) {
            if ($data['t'] === 'MESSAGE_CREATE') {
                processMessage($data['d']);
            }
        }
    });
    $ws->run();
}

?>
