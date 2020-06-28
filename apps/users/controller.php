<?php

class Users_Controller extends Controller {

  public function signin_form($request=[])
  {
    if( \Users\get() )
    {
      Http::redirect('/');
    }

    $errors = [];
    $data =& $_POST;

    if(isset($data['signin'])) {

      // do signin
      if(!isset($data['username']) || FilterMaster::isRegEmpty($data['username'])) {
        $errors[] = 'Не введен логин!';
      }

      if(!isset($data['password']) || FilterMaster::isRegEmpty($data['password'])) {
        $errors[] = 'Не введен пароль!';
      }

      if(empty($errors)) {

        // check for validity
        $u = R::findOne('user', "`username` = ? AND `password` IS NOT NULL", array($data['username']));

        if(!$u) {
          $errors[] = 'Не верно введён логин или пароль!';
        } else {

          if(password_verify($data['password'], $u->password)) {

            // good2go
            $u->hash = HashManager::createHash($u->id . $_SERVER['HTTP_USER_AGENT']);
            R::store($u);

            CookieManager::store('logged_user', HashManager::encodeInt($u->id));

            Http::redirect('/?signin_success=true');
          } else {
            $errors[] = 'Не верно введён логин или пароль!';
          }

        }

      }

    }

    return render('signin_form', array(
      'errors' => $errors,
      'data' => $data,
      'signup_success' => isset($_GET['signup_success'])));
  }

  public function signup_form($request=[])
  {
    if( \Users\get() )
    {
      Http::redirect('/');
    }

    $errors = [];
    $data =& $_POST;

    if(isset($data['signup'])) {

      // do signup
      if(!isset($data['username']) || FilterMaster::isRegEmpty($data['username'])) {
        $errors[] = 'Не введен логин!';
      }

      if(!isset($data['password']) || FilterMaster::isRegEmpty($data['password'])) {
        $errors[] = 'Не введен пароль!';
      } else if(!isset($data['password2']) || $data['password'] != $data['password2']) {
        $errors[] = 'Введённые пароли не совпадают!';
      }

      if(empty($errors)) {
        if(R::count('user', '`username` = ?', array($data['username']))) {
          $errors[] = 'Логин уже занят.';
        } else {
          // all clear, signup
          $u = R::dispense('user');
          $u->username = $data['username'];
          $u->password = password_hash($data['password'], PASSWORD_DEFAULT);

          R::store($u);

          Http::redirect('/signin?signup_success=true');
        }
      }


    }

    return render('signup_form', array(
      'errors' => $errors,
      'data' => $data));
  }

  public function do_logout($request)
  {
    CookieManager::delete('logged_user');
    Http::redirect('/');
  }

}