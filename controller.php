<?php

namespace Concrete\Package\CommunityStoreShippingNzFlatRate;

use Package;
use Whoops\Exception\ErrorException;
use Concrete\Package\CommunityStore\Src\CommunityStore\Shipping\Method\ShippingMethodType as StoreShippingMethodType;

defined('C5_EXECUTE') or die(_("Access Denied."));

class Controller extends Package
{
    protected $pkgHandle = 'community_store_shipping_nz_flat_rate';
    protected $appVersionRequired = '5.7.2';
    protected $pkgVersion = '0.1';

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
        }

    }
    public function uninstall()
    {
        $pm = StoreShippingMethodType::getByHandle('nz_flat_rate');
        if ($pm) {
            $pm->delete();
        }
        $pkg = parent::uninstall();
    }

}
