<?php

require_once 'lib/env.php';

/*@

Session credentials for the voice agent (Gemini Live).

Php can't proxy a websocket, so the browser opens the Live connection to google itself
and needs credentials for it. It gets a single use ephemeral token that expires after
30 min instead of the api key, so the key never leaves the server.

See dev_info/Voice_Agent_Plan.md

*/
trait GetAgentTokenAjaxController
{

  public function getAgentToken( $request )
  {
    $key = env_get('GEMINI_API_KEY');

    if( empty($key) )   // empty, not null: a key without a value parses as ''
      return ['result' => 'error', 'data' => ['message' => 'No GEMINI_API_KEY in the .env (see .env.example)']];

    $agent = config::get('agent') ?: [];

    // Single use, 30 min of talking, 1 min to open the connection (google's defaults)

    $payload = [
      'uses'                 => 1,
      'expireTime'           => gmdate('Y-m-d\TH:i:s\Z', time() + 1800),
      'newSessionExpireTime' => gmdate('Y-m-d\TH:i:s\Z', time() + 60)
    ];

    // Same api version as the websocket in VoiceAgentController.connect()

    $curl = curl_init('https://generativelanguage.googleapis.com/v1beta/auth_tokens');

    curl_setopt_array( $curl, [
      CURLOPT_POST           => true,
      CURLOPT_POSTFIELDS     => json_encode( $payload ),
      CURLOPT_HTTPHEADER     => ['Content-Type: application/json', "x-goog-api-key: $key"],
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_TIMEOUT        => 10
    ]);

    $response = curl_exec( $curl );
    $status   = curl_getinfo( $curl, CURLINFO_HTTP_CODE);
    $curlErr  = curl_error( $curl );

    curl_close( $curl );

    if( $response === false )
      return ['result' => 'error', 'data' => ['message' => "Could not reach google: $curlErr"]];

    $token = json_decode( $response, true)['name'] ?? null;

    // Google's error body can quote the request back, so it goes to the log, not the client

    if( ! $token )
    {
      error_log("getAgentToken: http $status - $response");
      return ['result' => 'error', 'data' => ['message' => "Google refused the token request (http $status), details in the php error log"]];
    }

    // Today's date is the one thing the prompt file can't contain

    $prompt = file_get_contents('data/agent/prompt.md') . "\n\nToday is " . date('l, Y-m-d') . ".\n";

    return ['result' => 'success', 'data' => [
      'token'             => $token,
      'model'             => $agent['model'] ?? 'gemini-3.1-flash-live-preview',
      'voice'             => $agent['voice'] ?? 'Aoede',
      'debug'             => ! empty( $agent['debug']),
      'systemInstruction' => $prompt
    ]];
  }
}

?>
