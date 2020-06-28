<?php

namespace Users {

	function get($override = false) {

		static $user = null;

        if($override) {
            $user = $override;
            return true;
        }

        if (!is_null($user)) {
            return $user;
        }

		$user_cookie = \CookieManager::read('logged_user');

		if($user_cookie) {
            $hashRecovered = \HashManager::createHash(\HashManager::decodeInt($user_cookie) . $_SERVER['HTTP_USER_AGENT']);
            $user = \R::findOne('user', 'id = ? AND hash = ?', array(\HashManager::decodeInt($user_cookie), $hashRecovered));

            if (!$user) {
                \CookieManager::delete('logged_user');
                \Http::redirect('/');
            }
		}


        if (is_null($user)) {
            return false;
        }

        return $user->export();
	}

}