<?php

class registerController extends Controller
{
	public $timezones = array();

	public function index()
	{
		$zones = DateTimeZone::listIdentifiers(DateTimeZone::ALL);
		foreach($zones as $zone)
		{
			$locale = new DateTimeZone($zone);
			$offset = $locale->getOffset(new DateTime('now', new DateTimeZone('UTC')));

			$object = new stdClass();
			$object->zone = $zone;
			$object->offset = abs($locale->getOffset(new DateTime('now', new DateTimeZone('UTC'))));
			$object->offset = gmdate('H:i', $object->offset);
			$object->offset = ($offset < 0) ? '-' . $object->offset : '+' . $object->offset;
			$object->realOffset = $offset;
			$this->timezones[] = $object;
		}

		usort($this->timezones, 'self::sortOffset');

		$this->render();
	}

	public static function sortOffset($a, $b)
	{
		return ($a->realOffset < $b->realOffset ? -1 : 1);
	}
}