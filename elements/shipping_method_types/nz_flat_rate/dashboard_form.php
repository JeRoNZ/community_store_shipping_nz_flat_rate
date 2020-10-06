<?php
defined('C5_EXECUTE') or die('Access Denied.');
extract($vars); ?>
<div class="row">
	<div class="col-xs-12 col-sm-6">
		<?= $form->label('north',t('North Island')); ?>
		<div class="input-group">
			<div class="input-group-addon"><?=Config::get('community_store.symbol')?></div>
			<?= $form->text('north',$smtm->getNorth()); ?>
		</div>
	</div>
    <div class="col-xs-12 col-sm-6">
		<?= $form->label('northRD',t('North Island RD')); ?>
        <div class="input-group">
            <div class="input-group-addon"><?=Config::get('community_store.symbol')?></div>
			<?= $form->text('northRD',$smtm->getNorthRD()); ?>
        </div>
    </div>
</div>
<div class="row">
	<div class="col-xs-12 col-sm-6">
		<?= $form->label('south',t('South Island')); ?>
		<div class="input-group">
			<div class="input-group-addon"><?=Config::get('community_store.symbol')?></div>
			<?= $form->text('south',$smtm->getSouth()); ?>
		</div>
	</div>
    <div class="col-xs-12 col-sm-6">
		<?= $form->label('southRD',t('South Island RD')); ?>
        <div class="input-group">
            <div class="input-group-addon"><?=Config::get('community_store.symbol')?></div>
			<?= $form->text('southRD',$smtm->getSouthRD()); ?>
        </div>
    </div>
</div>