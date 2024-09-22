<?php
defined('C5_EXECUTE') or die('Access Denied.');

use Concrete\Package\CommunityStoreShippingNzFlatRate\Src\CommunityStore\Shipping\Method\Types\NzFlatRateShippingMethod;

extract($vars);
/** @var $smtm NzFlatRateShippingMethod */
?>
<h4>North Island</h4>
<div class="row mb-3">
    <div class="col-md-3 col-sm-6">
		<?= $form->label('north', t('Urban')); ?>
        <div class="input-group">
            <span class="input-group-text input-group-addon"><?= Config::get('community_store.symbol') ?></span>
			<?= $form->text('north', $smtm->getNorth()); ?>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
		<?= $form->label('northRD', t('Rural')); ?>
        &nbsp;-&nbsp;&nbsp;<?= $form->label('northSurcharge', t('Surcharge')); ?>&nbsp;&nbsp;
	    <?= $form->checkbox('northSurcharge', 1, $smtm->getNorthSurcharge()); ?>
        <div class="input-group">
            <span class="input-group-text input-group-addon"><?= Config::get('community_store.symbol') ?></span>
			<?= $form->text('northRD', $smtm->getNorthRD()); ?>

        </div>
    </div>
    <div class="col-md-3 col-sm-6">
		<?= $form->label('northBaseKg', t('Base Weight')); ?>
        <div class="input-group">
            <span class="input-group-text input-group-addon"><?= Config::get('community_store.symbol') ?></span>
			<?= $form->text('northBaseKg', $smtm->getNorthBaseKg()); ?>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
		<?= $form->label('northPerKg', t('Price Per Additional Kg')); ?>
        <div class="input-group">
            <span class="input-group-text input-group-addon"><?= Config::get('community_store.symbol') ?></span>
			<?= $form->text('northPerKg', $smtm->getNorthPerKg()); ?>
        </div>
    </div>

</div>

<h4>South Island</h4>
<div class="row mb-3">
    <div class="col-md-3 col-sm-6">
		<?= $form->label('south', t('Urban')); ?>
        <div class="input-group">
            <span class="input-group-text input-group-addon"><?= Config::get('community_store.symbol') ?></span>
			<?= $form->text('south', $smtm->getSouth()); ?>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
		<?= $form->label('southRD', t('Rural') ); ?>
        &nbsp;-&nbsp;&nbsp;<?= $form->label('southSurcharge', t('Surcharge')); ?>&nbsp;&nbsp;
	    <?= $form->checkbox('southSurcharge', 1, $smtm->getSouthSurcharge()); ?>
        <div class="input-group">
            <span class="input-group-text input-group-addon"><?= Config::get('community_store.symbol') ?></span>
			<?= $form->text('southRD', $smtm->getSouthRD()); ?>

        </div>
    </div>
    <div class="col-md-3 col-sm-6">
		<?= $form->label('southBaseKg', t('Base Weight')); ?>
        <div class="input-group">
            <span class="input-group-text input-group-addon"><?= Config::get('community_store.symbol') ?></span>
			<?= $form->text('southBaseKg', $smtm->getSouthBaseKg()); ?>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
		<?= $form->label('southPerKg', t('Price Per Additional Kg')); ?>
        <div class="input-group">
            <span class="input-group-text input-group-addon"><?= Config::get('community_store.symbol') ?></span>
			<?= $form->text('southPerKg', $smtm->getSouthPerKg()); ?>
        </div>
    </div>
</div>