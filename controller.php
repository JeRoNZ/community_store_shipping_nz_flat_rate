<?php

namespace Concrete\Package\CommunityStoreShippingNzFlatRate;

use Concrete\Core\Package\Package;
use Whoops\Exception\ErrorException;
use Concrete\Package\CommunityStore\Src\CommunityStore\Shipping\Method\ShippingMethodType as StoreShippingMethodType;

defined('C5_EXECUTE') or die(_("Access Denied."));

class Controller extends Package
{
    protected $pkgHandle = 'community_store_shipping_nz_flat_rate';
    protected $appVersionRequired = '8.1';
    protected $pkgVersion = '2.0.2';

	protected $pkgAutoloaderRegistries = [
		'src/CommunityStore' => '\Concrete\Package\CommunityStoreShippingNzFlatRate\Src\CommunityStore',
	];


	public function getPackageDescription()
    {
        return t("NZ Flat Rate Shipping Method for Community Store");
    }

    public function getPackageName()
    {
        return t("NZ Flat Rate Shipping Method Type");
    }

    public function install()
    {
        $installed = Package::getInstalledHandles();
        if(!(is_array($installed) && in_array('community_store',$installed)) ) {
            throw new ErrorException(t('This package requires that Community Store be installed'));
        } else {
            $pkg = parent::install();
            StoreShippingMethodType::add('nz_flat_rate', 'NZ Flat Rate Shipping', $pkg);
            StoreShippingMethodType::add('store_pickup', 'Store Pickup', $pkg);
        }

    }
    public function uninstall()
    {
        $pm = StoreShippingMethodType::getByHandle('nz_flat_rate');
        if ($pm) {
            $pm->delete();
        }
		$pm = StoreShippingMethodType::getByHandle('store_pickup');
		if ($pm) {
			$pm->delete();
		}
        $pkg = parent::uninstall();
    }

    public function upgrade() {
		$pkg = $this->app->make('Concrete\Core\Package\PackageService')->getByHandle($this->pkgHandle);
		$pm = StoreShippingMethodType::getByHandle('store_pickup');
		if (! $pm) {
			StoreShippingMethodType::add('store_pickup', 'Store Pickup', $pkg);
		}
		parent::upgrade();
	}

}
