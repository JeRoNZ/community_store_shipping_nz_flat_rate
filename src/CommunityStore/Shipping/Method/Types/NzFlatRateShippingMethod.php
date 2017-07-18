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

/**
 * @Entity
 * @Table(name="CommunityStoreNZFlatRateMethods")
 */
class NzFlatRateShippingMethod extends ShippingMethodTypeMethod
{
    public function getShippingMethodTypeName() {
        return t('NZ Flat Rate Method');
    }

    /**
     * @Column(type="float")
     */
    protected $north;

	/**
	 * @Column(type="float")
	 */
	protected $south;

    public function getSouth()
    {
        return $this->south;
    }
	public function getNorth()
	{
		return $this->north;
	}

    public function setSouth($rate)
    {
        $this->south = $rate;
    }
	public function setNorth($rate)
	{
		$this->north = $rate;
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
        if ($type == "update") {
            $sm = $this;
        } else {
            $sm = new self();
        }
        // do any saves here
        $sm->setNorth($data['north']);
        $sm->setSouth($data['south']);
		$em = \ORM::entityManager();
        $em->persist($sm);
        $em->flush();
        return $sm;
    }

    public function dashboardForm($shippingMethod = null)
    {
        $this->set('form', Core::make("helper/form"));
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
		} else {
			return false;
		}
	}

	private function getRate()
	{
		$customer = new StoreCustomer();
		// getAddres() gives us a string - but we want the postcode portion
		// so using getValue which gives us an object instead.
		$postcode = $customer->getValue('shipping_address')->postal_code;
		if (! preg_match('/[0-9]{4}/',$postcode)) {
			// because something's off so charge the max
			return $this->getSouth() > $this->getNorth() ? $this->getSouth() : $this->getNorth();
		}

		// South island postcodes are 7000 and above.
		// Hurrah for NZ.
		if ($postcode >= 7000)
			return $this->getSouth();

		return $this->getNorth();
	}


	public function getOffers() {
		$offers = array();

		$offer = new StoreShippingMethodOffer();
		$offer->setRate($this->getRate());

		$offers[] = $offer;
		return $offers;
	}

}