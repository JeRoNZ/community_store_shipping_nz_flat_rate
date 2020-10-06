<?php
namespace Concrete\Package\CommunityStoreShippingNzFlatRate\Src\CommunityStore\Shipping\Method\Types;

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

class NzFlatRateShippingMethod extends ShippingMethodTypeMethod
{
    public function getShippingMethodTypeName() {
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
	 * @ORM\Column(type="float")
	 */
	protected $south;

	/**
	 * @ORM\Column(type="float")
	 */
	protected $southrd;

    public function getSouth()
    {
        return $this->south;
    }
	public function getSouthRD()
	{
		return $this->southrd;
	}
	public function getNorth()
	{
		return $this->north;
	}
	public function getNorthRD()
	{
		return $this->northrd;
	}

    public function setSouth($rate)
    {
        $this->south = $rate;
    }
	public function setSouthRD($rate)
	{
		$this->southrd = $rate;
	}

	public function setNorth($rate)
	{
		$this->north = $rate;
	}
	public function setNorthRD($rate)
	{
		$this->northrd = $rate;
	}

	public function addMethodTypeMethod($data)
    {
        return $this->addOrUpdate('update', $data);
    }

    public function update($data)
    {
        return $this->addOrUpdate('update', $data);
    }

    private function addOrUpdate($type, $data)
    {
        if ($type === "update") {
            $sm = $this;
        } else {
            $sm = new self();
        }
        // do any saves here
        $sm->setNorth($data['north']);
        $sm->setNorthRD($data['northRD']);
        $sm->setSouth($data['south']);
        $sm->setSouthRD($data['southRD']);
		$em = dbORM::entityManager();
        $em->persist($sm);
        $em->flush();
        return $sm;
    }

    public function dashboardForm($shippingMethod = null)
    {
        $this->set('form', Core::make('helper/form'));
        $this->set('smt', $this);
        if (is_object($shippingMethod)) {
            $smtm = $shippingMethod->getShippingMethodTypeMethod();
        } else {
            $smtm = new self();
        }
        $this->set("smtm", $smtm);
    }

    public function isEligible()
    {
		if (!$this->isWithinSelectedCountries()) {
			return false;
		}

        return true;
    }


	public function isWithinSelectedCountries()
	{
		$customer = new StoreCustomer();
		$custCountry = $customer->getValue('shipping_address')->country;
		$selectedCountries = array('NZ');
		if (in_array($custCountry, $selectedCountries)) {
			return true;
		}

		return false;
	}

	private function getRate()
	{
		$customer = new StoreCustomer();
		// getAddres() gives us a string - but we want the postcode portion
		// so using getValue which gives us an object instead.
		$postcode = $customer->getValue('shipping_address')->postal_code;
		if (! preg_match('/[0-9]{4}/',$postcode)) {
			// because something's off so charge the max
			#return $this->getSouth() > $this->getNorth() ? $this->getSouth() : $this->getNorth();
			return false;
		}

		$rural = $this->isRural();

		// South island postcodes are 7000 and above.
		// Hurrah for NZ.
		if ($postcode >= 7000) {
			if  ($rural) {
				return $this->getSouthRD();
			}

			return $this->getSouth();
		}

		if  ($rural) {
			return $this->getNorthRD();
		}

		return $this->getNorth();
	}


	private function isRural() {
		$customer = new StoreCustomer();
		$address = $customer->getValue('shipping_address');
		/* @var $address AddressValue | \stdClass */

		$rural = false;
		if ($address instanceof \stdClass)
			$add = $address->address1;
		else {
			$add = $address->getAddress1();
		}
		if (preg_match('|R[ .]*D[ .]*[0-9]+|i', $add)) {
			return true;
		}
		if (! $rural) {
			if ($address instanceof \stdClass)
				$add = $address->address2;
			else {
				$add = $address->getAddress2();
			}
			if (preg_match('|R[ .]*D[ .]*[0-9]+|i', $add)) {
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
			$rows = $db->fetchAssoc($sql, [$postcode]);
			if ($rows) {
				return true;
			}
		}

		return false;
    }



	public function getOffers() {
		$offers = array();

		$offer = new StoreShippingMethodOffer();
		$rate = $this->getRate();
		if ($rate !== false) {
			$offer->setRate($this->getRate());
			$offers[] = $offer;
		}

		return $offers;
	}

}