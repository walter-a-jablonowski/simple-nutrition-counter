<?php

require_once 'tools/publish_foods/Publisher.php';

/*

Ajax: publish the food data to the installation folder (dev menu > Publish).

Input:  { mode: 'plan' | 'run', delete: bool }
Output: { lines, counts, copied, deleted, errors } - 'plan' only reports, 'run'
        rebuilds the plan server side and applies it (the client just confirms).

*/
trait PublishFoodsAjaxController
{

  public function publishFoods( $request )
  {
    if( ! config::get('devMode'))
      return ['result' => 'error', 'data' => ['message' => 'Publishing is a devMode tool.']];

    $delete    = ! empty($request['delete']);
    $publisher = new Publisher( getcwd() . '/tools/publish_foods');

    if(($request['mode'] ?? 'plan') !== 'run')
    {
      $plan = $publisher->plan();

      return ['result' => 'success', 'data' => [
        'lines'  => $publisher->reportLines( $plan, $delete ),
        'counts' => $this->publishCounts( $plan ),
        'errors' => []
      ]];
    }

    $result = $publisher->run( $delete );

    return ['result' => 'success', 'data' => [
      'lines'   => $publisher->reportLines( $result['plan'], $delete ),
      'counts'  => $this->publishCounts( $result['plan'] ),
      'copied'  => $result['copied'],
      'deleted' => $result['deleted'],
      'errors'  => $result['errors']
    ]];
  }

  private function publishCounts( array $plan ) : array
  {
    return [
      'new'       => count($plan['new']),
      'changed'   => count($plan['changed']),
      'obsolete'  => count($plan['deleted']),
      'unchanged' => $plan['unchanged']
    ];
  }
}

?>
