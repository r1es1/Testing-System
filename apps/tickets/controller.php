<?php

class Tickets_Controller extends Controller {

    public function __construct()
    {
        @session_start();
        $u = \Users\get();

        if(!$u) {
            Http::redirect('/signin');
        }
    }


	/**
	 * ВЫВОД ВСЕХ БИЛЕТОВ.
	 */
	public function main($args=[]) {

		$settings = \App::settings();

		$u = \Users\get();

		$out = [];
		$tickets = R::getAll("SELECT `t1`.*, `t2`.`status`, `t2`.`take_date`, `t2`.`attempts` FROM `tickets` AS `t1` LEFT JOIN `taken` AS `t2` ON `t2`.`user_id` = ? AND `t2`.`test_number` = `t1`.`test_number` AND `t2`.`variant_number` = `t1`.`variant_number` ORDER BY `t1`.`test_number`, `t1`.`variant_number`", array($u['id']));

		foreach($tickets as $t) {

			// compute time left
			$tleft = ($t['take_date'] + 600) - TimeManager::time();
			$t['time_left'] = TimeManager::humanizeTime($tleft);

			$t['diff'] = ($t['take_date'] + 600) - TimeManager::time();

			if($tleft < 0 && $t['status'] == 0) {
				// no time left

				$taken = R::findOne("taken", "`user_id` = ? AND `test_number` = ? AND `variant_number` = ? AND (`status` = 0 OR `status` = 1)", array($u['id'], $t['test_number'], $t['variant_number']));

				if($taken) {
					// increase attempts ammount
					$taken->attempts = (int) $taken->attempts + 1;

					$taken->take_date = TimeManager::time() + 600; // repass after 10 minutes
					$taken->status = 1; // not passed, repassing allowed

					if( $taken->attempts >= $settings['tickets']['maximum_check_attempts'] ) {
						// no attempts left
						$taken->status = 2; // not passed
					}

					R::store($taken);

					// refresh the page
					Http::redirect('/');
				}
			}

			// multidimensional array
			$out[$t['test_number']][$t['variant_number']] = $t;
		}

		unset($tickets);

        return render('main', array_merge(array(
        	'tickets' => $out), $args));
	}


	/**
	 * ВЗЯТИЕ/ПЕРЕВЗЯТИЕ БИЛЕТА.
	 */
	public function take($args) {

		$errors = [];

		$test_number = (int) $args['1'];
		$variant_number = (int) $args['2'];

		$ticket = R::getRow("SELECT * FROM `tickets` WHERE `test_number` = ? AND `variant_number` = ?", array($test_number, $variant_number));

		if(!$ticket) {
			// фейк запрос, просто выбрасываем
			Http::redirect('/');

			if(defined("API_MODE")) return array('errors' => array('fake request'));
		}

		$u = \Users\get();
		$taken = R::getRow("SELECT * FROM `taken` WHERE `user_id` = ? AND `test_number` = ? AND `variant_number` = ?", array($u['id'], $test_number, $variant_number));

		if($taken['status'] == 1) {
			// this is retake

			if(TimeManager::time() > $taken['take_date']) {

				// enough time passed
				R::exec("UPDATE `taken` SET `status` = 0 AND `take_date` = ? WHERE `id` = ?", array(TimeManager::time(), $taken['id']));

				// refresh the page
				Http::redirect('/');
				if(defined("API_MODE")) return $this->take($args);
			} else {
				$errors[] = "Пересдать можно не раньше чем через <span class='back-to-future' data-diff='".($taken['take_date'] - TimeManager::time())."'>" . TimeManager::humanizeTime($taken['take_date'] - TimeManager::time()).'</span>';
			}

		} else if($taken) {
			$errors[] = 'Данный билет ранее уже был взят!';
		} else if(R::count('taken', "`user_id` = ? AND (`status` = 0 OR `status` = 1)", array($u['id']))) {
			$errors[] = 'Сначала сдайте текущий билет, потом берите новый!';
		} else if(R::count('taken', "`user_id` = ? AND (`test_number` = ? AND `status` = 3)", array($u['id'], $test_number))) {
			$errors[] = 'Вы уже сдали этот билет.';
		} else {
			// take otherwise
			$t = R::dispense('taken');
			$t->user_id = $u['id'];
			
			$t->test_number = $test_number;
			$t->variant_number = $variant_number;
			$t->take_date = TimeManager::time();

			$t->status = 0; // inwork

			R::store($t);

			Http::redirect('/');
		}

		if(defined("API_MODE")) return array(
			'errors' => $errors,
			'ctime' => TimeManager::time());

		$this->main(array(
			'errors' => $errors,
			'ctime' => TimeManager::time()));
	}

	/**
	 * СКАЧИВАНИЕ ВОПРОСА.
	 */
	public function view($args) {

		$errors = [];

		$test_number = (int) $args['1'];
		$variant_number = (int) $args['2'];

		$ticket = R::getRow("SELECT * FROM `tickets` WHERE `test_number` = ? AND `variant_number` = ?", array($test_number, $variant_number));

		if(!$ticket) {
			// фейк запрос, просто выбрасываем
			Http::redirect('/');
		}

		$u = \Users\get();
		$taken = R::getRow("SELECT * FROM `taken` WHERE `user_id` = ? AND `test_number` = ? AND `variant_number` = ?", array($u['id'], $test_number, $variant_number));

		if(!$taken) {
			$errors[] = 'Вы еще не взяли билет!';
		} else {
			// show question

			if(defined("API_MODE")) return $ticket['question'];

			header('Content-Description: File Transfer');
			header('Content-Disposition: attachment; filename=question.txt');
			header('Expires: 0');
			header('Cache-Control: must-revalidate');
			header('Pragma: public');
			header('Content-Length: ' . mb_strlen($ticket['question'], '8bit'));
			header("Content-Type: text/plain");

			echo $ticket['question'];
			exit();
		}

		$this->main(array(
			'errors' => $errors));
	}

	/**
	 * ПРОВЕРКА ОТВЕТА + СОХРАНЕНИЕ ИСТОРИИ ОТВЕТОВ
	 */
	public function check($args) {

		$settings = \App::settings();

		$errors = [];

		$test_number = (int) $args['1'];
		$variant_number = (int) $args['2'];

		$ticket = R::getRow("SELECT * FROM `tickets` WHERE `test_number` = ? AND `variant_number` = ?", array($test_number, $variant_number));

		if(!$ticket) {
			// фейк запрос, просто выбрасываем
			Http::redirect('/');
		}

		$u = \Users\get();
		$taken = R::findOne("taken", "`user_id` = ? AND `test_number` = ? AND `variant_number` = ? AND (`status` = 0 OR `status` = 1)", array($u['id'], $test_number, $variant_number));

		if(!$taken) {
			// фейк запрос, просто выбрасываем
			Http::redirect('/');
		} else {

			// everything OK, check for the form data

			if(!isset($_FILES['answer']) || filesize($_FILES['answer']['tmp_name']) <= 0 || filesize($_FILES['answer']['tmp_name']) > 8192) {
				$errors[] = 'Файл не загружен или имеет размер больше 1 килобайта!';
			}

			if(empty($errors)) {
				// process
				
				$answer = file_get_contents($_FILES['answer']['tmp_name']);
				$ticket = R::getRow("SELECT * FROM `tickets` WHERE `test_number` = ? AND `variant_number` = ?", array($test_number, $variant_number));

				if(!$ticket) {
					// no ticket? O_o
					// do something
					// cancel check i.e.
					
					exit();
				}

				/*
					0 - in process

					1 - not passed, repassing allowed

					2 - not passed
					3 - passed
				 */

				if(trim($answer) != trim($ticket['answer'])) {
					// wrong

					// increase attempts ammount
					$taken->attempts = (int) $taken->attempts + 1;

					$taken->take_date = TimeManager::time() + 600; // repass after 10 minutes
					$taken->status = 1; // not passed, repassing allowed

					if( $taken->attempts >= $settings['tickets']['maximum_check_attempts'] ) {
						// no attempts left
						$taken->status = 2; // not passed
					}
					
				} else {
					// valid
					$taken->status = 3; // passed
				}

				// save answer to history
				$answerHistoryRecord = R::dispense('answers');

				$answerHistoryRecord->user_id = $u['id'];
				$answerHistoryRecord->test_number = $test_number;
				$answerHistoryRecord->variant_number = $variant_number;
				$answerHistoryRecord->answer = $answer;

				$answerHistoryRecord->attempt = $taken->attempts;
				$answerHistoryRecord->status = $taken->status;

				R::store($answerHistoryRecord);

				// save & return
				R::store($taken);
				// R::exec("UPDATE `taken` SET `status` = ?, `attempts` = ? WHERE `id` = ?", array($taken->status, $taken->attempts, $taken->id));

				if(defined("API_MODE")) return $taken;

				Http::redirect('/');
			}
		}

		if(defined("API_MODE")) return array('errors' => $errors);

		$this->main(array(
			'errors' => $errors));
	}


}