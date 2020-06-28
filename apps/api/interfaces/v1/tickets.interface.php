<?php

namespace Api\Interfaces\Tickets {

  class Main
  {
    public function list($args, &$api)
    {
      $api->expect_parameters(array(), true);

      \Import::app('tickets');
      $controller = new \Tickets_Controller();

      $result = $controller->main();

      if(isset($result['errors']) && !empty($result['errors'])) {
        // something wrong
        $api->response( array_shift($result['errors']), false );
      } else {
        // all good, return token
        $api->response( array('tickets' => json_encode($result['tickets']) ));
      }
    }

    public function take($args, &$api)
    {
      $api->expect_parameters(array('test_number', 'variant_number'), true);

      if(!\FilterMaster::isNumber($args['params']['test_number'])) {
        $api->response( array('message' => 'error', 'message_t' => 'argument test_number must be number' ));
      } else if(!\FilterMaster::isNumber($args['params']['variant_number'])) {
        $api->response( array('message' => 'error', 'message_t' => 'argument variant_number must be number' ));
      }

      \Import::app('tickets');
      $controller = new \Tickets_Controller();

      $result = $controller->take([[], $args['params']['test_number'], $args['params']['variant_number']]);

      if(isset($result['errors']) && !empty($result['errors'])) {
        // something wrong
        $api->response( array_shift($result['errors']), false );
      } else {
        // all good, return token
        $api->response( array('message' => 'success' ));
      }
    }

    public function view($args, &$api)
    {
      $api->expect_parameters(array('test_number', 'variant_number'), true);

      if(!\FilterMaster::isNumber($args['params']['test_number'])) {
        $api->response( array('message' => 'error', 'message_t' => 'argument test_number must be number' ));
      } else if(!\FilterMaster::isNumber($args['params']['variant_number'])) {
        $api->response( array('message' => 'error', 'message_t' => 'argument variant_number must be number' ));
      }

      \Import::app('tickets');
      $controller = new \Tickets_Controller();

      $result = $controller->view([[], $args['params']['test_number'], $args['params']['variant_number']]);

      if(isset($result['errors']) && !empty($result['errors'])) {
        // something wrong
        $api->response( array_shift($result['errors']), false );
      } else {
        // all good, return answer
        $api->response( array('message' => $result ));
      }
    }

    public function check($args, &$api)
    {
      $api->expect_parameters(array('test_number', 'variant_number'), true);
      $api->expect_request_method("POST");

      if(!\FilterMaster::isNumber($args['params']['test_number'])) {
        $api->response( array('message' => 'error', 'message_t' => 'argument test_number must be number' ));
      } else if(!\FilterMaster::isNumber($args['params']['variant_number'])) {
        $api->response( array('message' => 'error', 'message_t' => 'argument variant_number must be number' ));
      }

      \Import::app('tickets');
      $controller = new \Tickets_Controller();

      $result = $controller->check([[], $args['params']['test_number'], $args['params']['variant_number']]);

      if(isset($result['errors']) && !empty($result['errors'])) {
        // something wrong
        $api->response( array_shift($result['errors']), false );
      } else {
        // all good, return answer
        $status_descriptions = array(
          "0" => "in process",
          "1" => "not passed, repassing allowed",
          "2" => "not passed",
          "3" => "passed"
        );

        $api->response( array('message' => 'checked', 'status' => $result->status ));
      }
    }
  }

}