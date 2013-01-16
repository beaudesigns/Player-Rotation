<?php

class indexController extends Controller
{
	public $timezones = array();
	public $players = array();
	public $settings;
	public $playerSchedule;
	public $playingInRounds = array();

	public function index()
	{
		$this->buildTimeZones();

		$query = self::$database->query("SELECT * FROM users LEFT OUTER JOIN schedule ON users.id = schedule.user_id");
		while($row = $query->fetch())
		{
			$row->rounds_available = unserialize($row->rounds_available);
			$row->rounds_playing = unserialize($row->rounds_playing);
			$row->schedule_count = 0;
			$this->players[] = $row;
		}

		$query = self::$database->query("SELECT * FROM settings");
		$this->settings = $query->fetch();
		$this->settings->locked_schedule = unserialize($this->settings->locked_schedule);

		$query = self::$database->prepare("SELECT * FROM schedule WHERE user_id = :user");
		$query->bindValue(':user', self::$user->storage->id, PDO::PARAM_INT);
		$query->execute();
		$this->playerSchedule = $query->fetch();
		if(empty($this->playerSchedule))
		{
			$this->playerSchedule = new stdClass();
			$this->playerSchedule->user_id = self::$user->storage->id;
			$this->playerSchedule->rounds_available = '';
		}
		$this->playerSchedule->rounds_available = unserialize($this->playerSchedule->rounds_available);

		$this->buildPlayerSchedule();

		if(!empty($_POST['save-settings']))
		{
			$this->saveSettings();
			header('Location: ' . $this->self);
			exit;
		}
		if(!empty($_POST['save-schedule']))
		{
			$this->saveSchedule();
			header('Location: ' . $this->self);
			exit;
		}

		$this->render();
	}

	private function buildTimeZones()
	{
		$query = self::$database->query("SELECT * FROM users");
		while($row = $query->fetch())
		{
			$locale = new DateTimeZone($row->timezone);
			$offset = $locale->getOffset(new DateTime('now', new DateTimeZone('UTC')));
			$formattedOffset = gmdate('H:i', abs($locale->getOffset(new DateTime('now', new DateTimeZone('UTC')))));
			$formattedOffset = ($offset < 0) ? '-' . $formattedOffset : '+' . $formattedOffset;

			if(empty($this->timezones[$formattedOffset]))
			{

				$this->timezones[$formattedOffset] = new stdClass();
				$this->timezones[$formattedOffset]->offset = $formattedOffset;
				$this->timezones[$formattedOffset]->localTime = new DateTime('now', $locale);
				$this->timezones[$formattedOffset]->users = array();
			}
			$this->timezones[$formattedOffset]->users[] = $row->username;
		}
	}

	private function saveSettings()
	{
		$schedule = '';
		if(!empty($_POST['locked']))
		{
			$schedule = serialize($this->playingInRounds);
		}

		$query = self::$database->prepare("UPDATE settings SET rounds = :rounds, locked_schedule = :schedule");
		$query->bindValue(':rounds', $_POST['rounds'], PDO::PARAM_INT);
		$query->bindValue(':schedule', $schedule, PDO::PARAM_STR);
		$query->execute();

		self::$database->exec("UPDATE users SET all_rounds = 0");
		if(!empty($_POST['players']))
		{
			$query = self::$database->prepare("UPDATE users SET all_rounds = 1 WHERE id = :player");
			foreach($_POST['players'] AS $id => $status)
			{
				$query->bindValue(':player', $id, PDO::PARAM_INT);
				$query->execute();
			}
		}
	}

	private function saveSchedule()
	{
		$query = self::$database->prepare("DELETE FROM schedule WHERE user_id = :user");
		$query->bindValue(':user', self::$user->storage->id, PDO::PARAM_INT);
		$query->execute();

		$query = self::$database->prepare("INSERT INTO schedule (user_id, rounds_available, rounds_playing) VALUES (:user, :rounds_available, '')");
		$query->bindValue(':user', self::$user->storage->id, PDO::PARAM_INT);
		$query->bindValue(':rounds_available', serialize($_POST['playing']), PDO::PARAM_STR);
		$query->execute();
	}

	private function buildPlayerSchedule()
	{
		for($round = 1; $round <= $this->settings->rounds; ++$round)
		{
			if(isset($this->settings->locked_schedule[$round]))
			{
				$this->playingInRounds[$round] = $this->settings->locked_schedule[$round];
				continue;
			}

			$playerList = array();
			// Check for players that are playing every round.
			foreach($this->players as &$player)
			{
				if(!empty($player->all_rounds))
				{
					$playerList[] = $player->username;
					++$player->schedule_count;
				}
			}

			$iteration = 0;
			while(count($playerList) < 4 && $iteration < count($this->players))
			{
				++$iteration;

				usort($this->players, 'self::sortPlaying');
				foreach($this->players as &$player)
				{
					if(empty($player->rounds_available))
					{
						continue;
					}
					if(array_key_exists($round, $player->rounds_available) && !in_array($player->username, $playerList) && count($playerList) < 4)
					{
						$playerList[] = $player->username;
						++$player->schedule_count;
						break;
					}
				}
			}
			$this->playingInRounds[$round] = $playerList;

		}
	}

	public static function sortPlaying($a, $b)
	{
		return ($a->schedule_count < $b->schedule_count ? -1 : 1);
	}
}