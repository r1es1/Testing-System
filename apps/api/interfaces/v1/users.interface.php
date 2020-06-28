<?php

namespace Api\Interfaces\Users {

  class Main
  {
    public function signIn($args, &$api)
    {
      $api->expect_parameters(array('username', 'password'), false);

      \Import::app('users');
      $controller = new \Users_Controller();

      $_POST = array_merge($args['params'], array('signin' => true));
      $result = $controller->signin_form();

      if(isset($result['errors']) && !empty($result['errors'])) {
        // something wrong
        $api->response( array_shift($result['errors']), false );
      } else {
        // all good, return token
        $u = \R::findOne('user', '`username` = ?', array($result['data']['username']));

        \R::exec("DELETE FROM `credentials` WHERE `user_id` = ?", array($u->id));

        // token generation (temp)
        srand(\TimeManager::time());
        // $___token = password_hash($u->id . $u->username . \TimeManager::time() . rand(), PASSWORD_DEFAULT);
        
        $___token = md5($u->id . $u->username . \TimeManager::time() . rand());

        $token = \R::dispense('credentials');
        $token->token = $___token;
        $token->user_id = $u->id;

        \R::store($token);

        $api->response( array('token' => $___token ));
      }
    }

    public function signUp($args, &$api)
    {
      $api->expect_parameters(array('username', 'password'), false);

      \Import::app('users');
      $controller = new \Users_Controller();

      $args['params']['password2'] = $args['params']['password'];

      $_POST = array_merge($args['params'], array('signup' => true));
      $result = $controller->signup_form();

      if(isset($result['errors']) && !empty($result['errors'])) {
        // something wrong
        $api->response( array_shift($result['errors']), false );
      } else {
        // all good, return token
        $api->response( array('message' => 'success' ));
      }
    }
  }

}