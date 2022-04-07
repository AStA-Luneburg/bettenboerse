<?php
namespace AStA\Bettenboerse;

abstract class AnnouncementType {
	const Request = 'request';
	const Offer = 'offer';
}

abstract class TypeOfBed {
	const Bed = 'bed';
	const Couch = 'couch';
}



abstract class IAnnouncement {
	public string $id;
	public string $type;
	public string $name;
	public string $email;
	public string $phone;
	public ?string $gender = null;

	public string $bedType;
	public int $bedCount;
	public ?string $locationHint = null;
	public ?string $wishes = null;

	public Helpers\JSONDateTime $from;
	public Helpers\JSONDateTime $until;

	protected static $text_type_map = [
		'Ich biete' => AnnouncementType::Offer,
		'Ich suche' => AnnouncementType::Request,
	];

	protected static $id_field_map = [
		0 => 'name',
		1 => 'email',
		3 => 'type',
		4 => 'phone',
		6 => 'bedType',
		8 => 'bedCount',
		9 => 'from',
		10 => 'until',
		12 => 'locationHint',
		13 => 'wishes',
		14 => 'privacy',
		15 => 'gender',
		//
	];


	function __construct() {
		$this->id = uniqid();
	}

	public function getDuration(): int {
		return $this->until->getTimestamp() - $this->from->getTimestamp();
	}

	public function toArray() {
		return [
			'id' => $this->id,
			'type' => $this->type,
			'name' => $this->name,
			'email' => $this->email,
			'phone' => $this->phone,
			'gender' => $this->gender,
			'bedType' => $this->bedType,
			'bedCount' => $this->bedCount,
			'locationHint' => $this->locationHint,
			'wishes' => $this->wishes,
			'from_date' => $this->from->format(DATE_ATOM),
			'until_date' => $this->until->format(DATE_ATOM),
		];
	}

	public static function withDates(Helpers\JSONDateTime $from, Helpers\JSONDateTime $until, ?string $name = null) {
		if ($from >= $until) {
			throw new \Exception('The start date needs to be before the end date.');
		}

		$announcement = new static();
		$announcement->from = $from;
		$announcement->until = $until;
		$announcement->name = is_null($name) ? 'Announcement ' . $announcement->id : $name;

		return $announcement;
	}

	protected static function validatedField($key, $value) {
		switch ($key) {
			case 'type':
				if (!array_key_exists($value, static::$text_type_map)) {
					throw new \Exception('Invalid type: ' . $value);
				}

				return self::$text_type_map[$value];

			case 'bedCount':
				return intval($value);

			case 'from':
			case 'until':
				return new Helpers\JSONDateTime($value);

			default:
				return $value;
		}
	}

	public static function fromDBResult($result): IAnnouncement {
		$announcement = null;

		if ($result->type === AnnouncementType::Offer) {
			$announcement = new BedOffer();
		} else {
			$announcement = new BedRequest();
		}

		$announcement->id           = $result->id;
		$announcement->name         = $result->name;
		$announcement->email        = $result->email;
		$announcement->phone        = $result->phone;
		$announcement->bedType      = $result->bedType;
		$announcement->bedCount     = $result->bedCount;
		$announcement->from         = new Helpers\JSONDateTime($result->from_date); // Column name has _date suffix!!
		$announcement->until        = new Helpers\JSONDateTime($result->until_date); // Column name has _date suffix!!

		if (!is_null($result->gender)) 
			$announcement->gender = $result->gender;

		if (!is_null($result->locationHint)) 
			$announcement->locationHint = $result->locationHint;

		if (!is_null($result->wishes)) 
			$announcement->wishes = $result->wishes;

		if ($announcement->from >= $announcement->until) {
			throw new \Exception('The start date needs to be before the end date.');
		}

		return $announcement;
	}

	public static function fromForm(array $fields) {
		$values = [];

		// Parse and validate fields
		foreach ($fields as $field) {
			$key = self::$id_field_map[$field['id']];
			$value = $field['value'];
			
			// Throw on unknown field
			if (is_null($key)) {
				throw new \Exception('Unknown field: ' . $field['id']);
			}

			$values[$key] = static::validatedField($key, $value);
		}

		$announcement = null;

		if ($values['type'] === AnnouncementType::Offer) {
			$announcement = new BedOffer();
		} else {
			$announcement = new BedRequest();
		}

		$announcement->name         = $values['name'];
		$announcement->email        = $values['email'];
		$announcement->phone        = $values['phone'];
		$announcement->bedType      = $values['bedType'];
		$announcement->bedCount     = $values['bedCount'];
		$announcement->from         = $values['from'];
		$announcement->until        = $values['until'];

		if (array_key_exists('gender', $values)) 
			$announcement->gender = $values['gender'];

		if (array_key_exists('locationHint', $values)) 
			$announcement->locationHint = $values['locationHint'];

		if (array_key_exists('wishes', $values)) 
			$announcement->wishes = $values['wishes'];

		if ($announcement->from >= $announcement->until) {
			throw new \Exception('The start date needs to be before the end date.');
		}

		return $announcement;
	}
}


class BedOffer extends IAnnouncement {
	public string $type = AnnouncementType::Offer;

}

class BedRequest extends IAnnouncement {
	public string $type = AnnouncementType::Request;

	public function durationMissingBefore(BedOffer $offer): int {
		return max(0, $offer->from->getTimestamp() - $this->from->getTimestamp());
	}

	public function durationMissingAfter(BedOffer $offer): int {
		return max(0, $this->until->getTimestamp() - $offer->until->getTimestamp());
	}
}

class AnnouncementMatch {
	public string $id;
	public BedOffer $offer;
	public BedRequest $request;
	public float $percentage;
	public float $duration;

	public function __construct(BedRequest $request, BedOffer $offer, float $duration, float $percentage) {
		$this->id = uniqid();
		$this->offer = $offer;
		$this->request = $request;
		$this->duration = $duration;
		$this->percentage = $percentage;
	}

	function ignore() {
		// Save match ack in DB
		// This match will now be hidden
	}

	function saveMatch() {
		// Save match in DB
	}

	public static function calculateMatch(BedRequest $request, BedOffer $offer): AnnouncementMatch {
		$totalDuration = $request->getDuration();
		$durationMissingBefore = $request->durationMissingBefore($offer);
		$durationMissingAfter  = $request->durationMissingAfter($offer);

		$matchingDuration = max(
			0, 
			$totalDuration - $durationMissingBefore - $durationMissingAfter
		);

		$matchPercentage = $matchingDuration / $totalDuration;

		// var_dump($totalDuration);
		// var_dump($durationMissingBefore);
		// var_dump($durationMissingAfter);
		// var_dump($matchingDuration);
		// var_dump($matchPercentage);
		// exit();

		return new static($request, $offer, $matchingDuration, $matchPercentage);
	}
}