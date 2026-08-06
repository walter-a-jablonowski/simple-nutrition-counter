<?php

require_once 'lib/env.php';

/*

Sends images to a Gemini model and returns the structured answer (generateContent).

Transport only: it knows nothing about food. The caller provides the model, the
prompts and a response schema; a schema plus responseMimeType json constrains the
decoding, so the model can not emit an unknown key or a string where a number is
required and the caller never has to parse prose.

The api key stays here on the server, unlike the voice agent which needs an
ephemeral token because php can not proxy a websocket (see ajax/get_agent_token.php).

*/
class GeminiVisionClient
{

  const ENDPOINT = 'https://generativelanguage.googleapis.com/v1beta/models/%s:generateContent';


  /* Returns the model's answer, decoded from json.

     $images: [ ['mime' => 'image/jpeg', 'data' => <raw bytes>], ... ]
     $schema: response schema in gemini's OpenAPI subset

     Throws with a user-facing message on any failure. */

  public static function extract( string $model, string $systemPrompt, string $userText, array $images, array $schema ) : array
  {
    $key = env_get('GEMINI_API_KEY');

    if( empty($key) )   // empty, not null: a key without a value parses as ''
      throw new Exception('No GEMINI_API_KEY in the .env (see .env.example)');

    $parts = [ ['text' => $userText] ];

    foreach( $images as $image )
      $parts[] = ['inline_data' => ['mime_type' => $image['mime'], 'data' => base64_encode( $image['data'])]];

    $payload = [
      'contents'          => [ ['role' => 'user', 'parts' => $parts] ],
      'systemInstruction' => ['parts' => [ ['text' => $systemPrompt] ]],
      'generationConfig'  => [
        'temperature'      => 0,          // transcription, not generation
        'maxOutputTokens'  => 4096,       // long ingredient lists plus thinking tokens
        'responseMimeType' => 'application/json',
        'responseSchema'   => $schema
      ]
    ];

    $curl = curl_init( sprintf(self::ENDPOINT, $model));

    curl_setopt_array( $curl, [
      CURLOPT_POST           => true,
      CURLOPT_POSTFIELDS     => json_encode( $payload ),
      CURLOPT_HTTPHEADER     => ['Content-Type: application/json', "x-goog-api-key: $key"],
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_CONNECTTIMEOUT => 10,
      CURLOPT_TIMEOUT        => 120
    ]);

    $response = curl_exec( $curl );
    $status   = curl_getinfo( $curl, CURLINFO_HTTP_CODE);
    $curlErr  = curl_error( $curl );

    curl_close( $curl );

    if( $response === false )
      throw new Exception("Could not reach google: $curlErr");

    // Google's error body quotes the request back, images included, so it goes
    // to the log, never to the client

    if( $status >= 400 )
    {
      error_log('GeminiVisionClient: http ' . $status . ' - ' . substr($response, 0, 500));
      throw new Exception("Google refused the request (http $status), details in the php error log");
    }

    return self::readAnswer( $response );
  }


  // Unwrap the candidate text and decode it

  private static function readAnswer( string $response ) : array
  {
    $body = json_decode( $response, true) ?: [];

    if( ! empty( $body['promptFeedback']['blockReason']))
      throw new Exception('The pictures were rejected by the safety filter.');

    $candidate = $body['candidates'][0] ?? [];

    if(( $candidate['finishReason'] ?? '') === 'MAX_TOKENS')
      throw new Exception('The answer was cut off. Try fewer pictures or a closer shot of the table.');

    // Thinking models put their reasoning in a part of its own, before the answer

    $text = '';

    foreach( $candidate['content']['parts'] ?? [] as $part )
      if( empty( $part['thought']) && isset( $part['text']))
        $text .= $part['text'];

    $data = json_decode( $text, true);

    if( ! is_array($data))
    {
      error_log('GeminiVisionClient: unparsable answer - ' . substr($text ?: $response, 0, 500));
      throw new Exception('The model did not return usable data, details in the php error log');
    }

    return $data;
  }
}

?>
