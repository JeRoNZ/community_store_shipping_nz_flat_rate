<?php

namespace Concrete\Package\CommunityStoreShippingNzFlatRate\Src\CommunityStore\Shipping\Method\Types;

use Concrete\Package\CommunityStore\Src\CommunityStore\Cart\Cart;
use Concrete\Package\CommunityStore\Src\CommunityStore\Product\Product;
use Concrete\Package\CommunityStore\Src\CommunityStore\Shipping\Method\ShippingMethod;
use Package;
use Core;
use Database;
use Concrete\Package\CommunityStore\Src\CommunityStore\Shipping\Method\ShippingMethodTypeMethod;
use Concrete\Package\CommunityStore\Src\CommunityStore\Cart\Cart as StoreCart;
use Concrete\Package\CommunityStore\Src\CommunityStore\Utilities\Calculator as StoreCalculator;
use Concrete\Package\CommunityStore\Src\CommunityStore\Customer\Customer as StoreCustomer;
use Concrete\Package\CommunityStore\Src\CommunityStore\Shipping\Method\ShippingMethodOffer as StoreShippingMethodOffer;
use Concrete\Core\Support\Facade\DatabaseORM as dbORM;
use Doctrine\ORM\Mapping as ORM;
use Concrete\Core\Entity\Attribute\Value\Value\AddressValue;


/**
 * @ORM\Entity
 * @ORM\Table(name="CommunityStoreNZFlatRateMethods")
 */
class NzFlatRateShippingMethod extends ShippingMethodTypeMethod {
	public function getShippingMethodTypeName () {
		return t('NZ Flat Rate Method');
	}

	/**
	 * @ORM\Column(type="float")
	 */
	protected $north;

	/**
	 * @ORM\Column(type="float")
	 */
	protected $northrd;

	/**
	 * @ORM\Column(type="boolean")
	 */
	protected $northSurcharge;

	/**
	 * @ORM\Column(type="float")
	 */
	protected $northPerKg;

	/**
	 * @ORM\Column(type="float")
	 */
	protected $northBaseKg;

	/**
	 * @ORM\Column(type="float")
	 */
	protected $south;

	/**
	 * @ORM\Column(type="float")
	 */
	protected $southrd;

	/**
	 * @ORM\Column(type="boolean")
	 */
	protected $southSurcharge;

	/**
	 * @ORM\Column(type="float")
	 */
	protected $southPerKg;

	/**
	 * @ORM\Column(type="float")
	 */
	protected $southBaseKg;


	public function getSouth () {
		return $this->south;
	}

	public function getSouthSurcharge () {
		return $this->southSurcharge;
	}

	public function getSouthBaseKg () {
		return $this->southBaseKg;
	}

	public function getSouthPerKg () {
		return $this->southPerKg;
	}

	public function getSouthRD () {
		return $this->southrd;
	}

	public function getNorth () {
		return $this->north;
	}

	public function getNorthRD () {
		return $this->northrd;
	}

	public function getNorthSurcharge () {
		return $this->northSurcharge;
	}

	public function getNorthBaseKg () {
		return $this->northBaseKg;
	}

	public function getNorthPerKg () {
		return $this->northPerKg;
	}

	public function setSouth ($rate) {
		$this->south = $rate;
	}

	public function setSouthRD ($rate) {
		$this->southrd = $rate;
	}

	public function setSouthSurcharge ($sc) {
		$this->southSurcharge = $sc;
	}

	public function setSouthPerKg ($kg) {
		$this->southPerKg = $kg;
	}

	public function setSouthBaseKg ($kg) {
		$this->southBaseKg = $kg;
	}

	public function setNorth ($rate) {
		$this->north = $rate;
	}

	public function setNorthRD ($rate) {
		$this->northrd = $rate;
	}

	public function setNorthSurcharge ($sc) {
		$this->northSurcharge = $sc;
	}

	public function setNorthPerKg ($kg) {
		$this->northPerKg = $kg;
	}

	public function setNorthBaseKg ($kg) {
		$this->northBaseKg = $kg;
	}

	public function addMethodTypeMethod ($data) {
		return $this->addOrUpdate('update', $data);
	}

	public function update ($data) {
		return $this->addOrUpdate('update', $data);
	}

	private static $isSouth;
	private static $isRural;

	private function addOrUpdate ($type, $data) {
		if ($type === "update") {
			$sm = $this;
		} else {
			$sm = new self();
		}
		// do any saves here
		$sm->setNorth((float) $data['north']);
		$sm->setNorthRD((float) $data['northRD']);
		$sm->setNorthPerKg((float) $data['northPerKg']);
		$sm->setNorthBaseKg((float) $data['northBaseKg']);
		$sm->setNorthSurcharge(isset($data['northSurcharge']) ? (int) $data['northSurcharge'] : 0);
		$sm->setSouth((float) $data['south']);
		$sm->setSouthRD((float) $data['southRD']);
		$sm->setSouthPerKg((float) $data['southPerKg']);
		$sm->setSouthBaseKg((float) $data['southBaseKg']);
		$sm->setSouthSurcharge(isset($data['southSurcharge']) ? (int) $data['southSurcharge'] : 0);
		$em = dbORM::entityManager();
		$em->persist($sm);
		$em->flush();

		return $sm;
	}

	public function dashboardForm ($shippingMethod = null) {
		$this->set('form', Core::make('helper/form'));
		$this->set('smt', $this);
		if (is_object($shippingMethod)) {
			$smtm = $shippingMethod->getShippingMethodTypeMethod();
		} else {
			$smtm = new self();
		}
		$this->set("smtm", $smtm);
	}

	public function isEligible () {
		if (!$this->isWithinSelectedCountries()) {
			return false;
		}

		return true;
	}


	public function isWithinSelectedCountries () {
		$customer = new StoreCustomer();
		$address = $customer->getValue('shipping_address');
		if ($address) {
			$custCountry = $address->country;
			$selectedCountries = array('NZ');
			if (in_array($custCountry, $selectedCountries)) {
				return true;
			}
		}

		return false;
	}

	private function getRate () {
		$customer = new StoreCustomer();
		// getAddres() gives us a string - but we want the postcode portion
		// so using getValue which gives us an object instead.
		$postcode = $customer->getValue('shipping_address')->postal_code;
		if (!preg_match('/[0-9]{4}/', $postcode)) {
			// because something's off so charge the max
			#return $this->getSouth() > $this->getNorth() ? $this->getSouth() : $this->getNorth();
			return false;
		}

		$rural = self::isRural();

		$items = Cart::getShippableItems();

		// Find the total weight.
		$weight = 0;
		foreach ($items as $item) {
			/** @var Product $product */
			$product = $item['product']['object'];
			$qty = $item['product']['qty'];
			$w = $product->getWidth();
			$weight += $w * $qty;
		}


		// South island postcodes are 7000 and above.
		// Hurrah for Te Wai Pounamu.
		if ($postcode >= 7000) {
			self::$isSouth = true;
			$additional = $weight <= $this->getSouthBaseKg() ? 0 : (($weight - $this->getSouthBaseKg()) * $this->getSouthPerKg());
			$additional = round($additional, 2);

			if ($rural) {
				if ($this->getSouthSurcharge()) {
					return $this->getSouthRD() + $this->getSouth() + $additional;
				}

				return $this->getSouthRD() + $additional;
			}

			return $this->getSouth() + $additional;
		}

		self::$isSouth = false;

		$additional = $weight <= $this->getNorthBaseKg() ? 0 : (($weight - $this->getNorthBaseKg()) * $this->getNorthPerKg());
		$additional = round($additional, 2);

		if ($rural) {
			if ($this->getNorthSurcharge()) {
				return $this->getNorthRD() + $this->getNorth() + $additional;
			}

			return $this->getNorthRD() + $additional;
		}

		return $this->getNorth() + $additional;
	}


	public static function isRural () {
		$customer = new StoreCustomer();
		$address = $customer->getValue('shipping_address');
		/* @var $address AddressValue | \stdClass */

		if ($address === null) {
			return false;
		}

		$rural = false;
		if ($address instanceof \stdClass)
			$add = $address->address1;
		else {
			$add = $address->getAddress1();
		}
		if (preg_match('|R[ .]*D[ .]*[0-9]+|i', $add)) {
			self::$isRural = true;

			return true;
		}
		if (!$rural) {
			if ($address instanceof \stdClass)
				$add = $address->address2;
			else {
				$add = $address->getAddress2();
			}
			if (preg_match('|R[ .]*D[ .]*[0-9]+|i', $add)) {
				self::$isRural = true;

				return true;
			}
		}
		if (!$rural) {
			if ($address instanceof \stdClass)
				$add = $address->city;
			else {
				$add = $address->getAddress3();
			}
			if (preg_match('|R[ .]*D[ .]*[0-9]+|i', $add)) {
				self::$isRural = true;

				return true;
			}
		}
		if (!$rural) {
			if ($address instanceof \stdClass)
				$postcode = $address->postal_code;
			else {
				$postcode = trim($address->getPostalCode());
			}
			$db = Database::Connection();
			/* @var $db \Concrete\Core\Database\Connection\Connection */
			$sql = 'SELECT * FROM RD_Postcodes WHERE postcode=?';
			$rows = $db->fetchAssociative($sql, [$postcode]);
			if ($rows) {
				self::$isRural = true;

				return true;
			}
		}

		if (!$rural) {
			if ($address instanceof \stdClass)
				$postcode = $address->postal_code;
			else {
				$postcode = trim($address->getPostalCode());
			}
			// if digit 3 is 7,8, or 9 - it's rural
			if (preg_match('/^[0-9]{2}[789][0-9]$/', $postcode)) {
				self::$isRural = true;

				return true;
			}
		}

		self::$isRural = false;

		return false;
	}


	public function getOffers () {
		$offers = [];

		$offer = new StoreShippingMethodOffer();
		$rate = $this->getRate();
		if ($rate !== false) {
			$offer->setRate($this->getRate());

			$labelParts = [
				/** @var $method ShippingMethod */
				$method = ShippingMethod::getByID($this->getShippingMethodID())->getName(),
				self::$isSouth ? 'South Island' : 'North Island',
				self::$isRural ? 'Rural' : 'Urban'
			];
			$offer->setOfferLabel(implode(' ', $labelParts));
			$offers[] = $offer;
		}

		return $offers;
	}

}