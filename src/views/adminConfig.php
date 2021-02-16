<?
use Xklid101\Wprecords\Services\Config;
?>
<div class="wrap">

    <div>
        <?php include 'adminNavigation.php' ?>
    </div>

    <form action="<?php echo esc_attr($routing->getPluginCurrentUrlPage()) ?>" method="post">
    <?php foreach ($formsData as $value): ?>
        <div style="margin: 10px; padding: 10px; border-top: 1px solid #ababab">
            <div style="margin-bottom: 10px">
                <h3>
                    <?php echo esc_html($value['title'])?>
                    <sup>(ID <?php  echo esc_html($value['id'])?>)</sup>
                </h3>
                <div class="form-group">
                    <label for="config[<?php echo esc_attr($value['id']) ?>][maxcount]">
                        <?php echo __('Maximální počet záznamů') ?>
                    </label>
                    <input
                        style="display: block"
                        id="config[<?php echo esc_attr($value['id']) ?>][maxcount]"
                        name="config[<?php echo esc_attr($value['id']) ?>][maxcount]"
                        type="number"
                        value="<?php echo esc_attr($config[$value['id']]['maxcount'] ?? '-1') ?>"
                        min="-1"
                        step="1"
                        size="5"
                    >
                    <small style="color: #ababab"><?php echo __('-1 pro neomezený počet') ?></small>
                </div>
                <div class="form-group">
                    <label for="config[<?php echo esc_attr($value['id']) ?>][maxcount-message]">
                        <?php echo __('Chybová hláška při překročení maximálního počtu záznamů') ?>
                    </label>
                    <input
                        style="display: block"
                        id="config[<?php echo esc_attr($value['id']) ?>][maxcount-message]"
                        name="config[<?php echo esc_attr($value['id']) ?>][maxcount-message]"
                        type="text"
                        value="<?php
                            echo esc_attr(
                                $config[$value['id']]['maxcount-message']
                                ?? Config::ERRORMSG_FORM_MAXCOUNT_DEFAULT
                            )
                        ?>"
                    >
                </div>
            </div>
            <div>
                <?php foreach ($value['fields'] as $value2): ?>
                    <div style="display: inline-block; width: 350px; border-top: 1px solid #cdcdcd; margin-bottom: 10px;">
                        <h4>
                            <?php echo __('Formulářové pole') ?>
                            "<?php echo esc_html($value2['label'])?>"
                            <sup>(ID <?php  echo esc_html($value2['id'])?>)</sup>
                        </h4>
                        <div class="form-group">
                            <label for="config[<?php echo esc_attr($value['id']) ?>][fields][<?php echo esc_attr($value2['id']) ?>][maxcount]">
                                <?php echo __('Maximální počet unikátních záznamů') ?>
                            </label>
                            <input
                                style="display: block"
                                id="config[<?php echo esc_attr($value['id']) ?>][fields][<?php echo esc_attr($value2['id']) ?>][maxcount]"
                                name="config[<?php echo esc_attr($value['id']) ?>][fields][<?php echo esc_attr($value2['id']) ?>][maxcount]"
                                type="number"
                                value="<?php echo esc_attr($config[$value['id']]['fields'][$value2['id']]['maxcount'] ?? '-1') ?>"
                                min="-1"
                                step="1"
                                size="5"
                            >
                            <small style="color: #ababab"><?php echo __('-1 pro neomezený počet') ?></small>
                        </div>
                        <div class="form-group">
                            <label for="config[<?php echo esc_attr($value['id']) ?>][fields][<?php echo esc_attr($value2['id']) ?>][maxcount-message]">
                                <?php echo __('Chybová hláška při překročení maximálního počtu záznamů') ?>
                            </label>
                            <input
                                style="display: block"
                                id="config[<?php echo esc_attr($value['id']) ?>][fields][<?php echo esc_attr($value2['id']) ?>][maxcount-message]"
                                name="config[<?php echo esc_attr($value['id']) ?>][fields][<?php echo esc_attr($value2['id']) ?>][maxcount-message]"
                                type="text"
                                value="<?php
                                    echo esc_attr(
                                        $config[$value['id']]['fields'][$value2['id']]['maxcount-message']
                                        ?? Config::ERRORMSG_FIELD_MAXCOUNT_DEFAULT
                                    )
                                ?>"
                            >
                        </div>
                        <div class="form-group" style="margin-top: 10px">
                            <label for="config[<?php echo esc_attr($value['id']) ?>][fields][<?php echo esc_attr($value2['id']) ?>][reqgroup]">
                                <?php echo __('Název skupiny povinných polí') ?>
                            </label>
                            <input
                                style="display: block"
                                id="config[<?php echo esc_attr($value['id']) ?>][fields][<?php echo esc_attr($value2['id']) ?>][reqgroup]"
                                name="config[<?php echo esc_attr($value['id']) ?>][fields][<?php echo esc_attr($value2['id']) ?>][reqgroup]"
                                type="text"
                                value="<?php echo esc_attr($config[$value['id']]['fields'][$value2['id']]['reqgroup'] ?? '') ?>"
                                size="5"
                            >
                            <small style="color: #ababab"><?php echo __('Alespoň jedno pole se stejným názvem skupiny musí být vyplněno') ?></small>
                        </div>
                        <div class="form-group">
                            <label for="config[<?php echo esc_attr($value['id']) ?>][fields][<?php echo esc_attr($value2['id']) ?>][reqgroup-message]">
                                <?php echo __('Chybová hláška při nevyplnění povinného pole skupiny') ?>
                            </label>
                            <input
                                style="display: block"
                                id="config[<?php echo esc_attr($value['id']) ?>][fields][<?php echo esc_attr($value2['id']) ?>][reqgroup-message]"
                                name="config[<?php echo esc_attr($value['id']) ?>][fields][<?php echo esc_attr($value2['id']) ?>][reqgroup-message]"
                                type="text"
                                value="<?php
                                    echo esc_attr(
                                        $config[$value['id']]['fields'][$value2['id']]['reqgroup-message']
                                        ?? Config::ERRORMSG_FIELD_REQGROUP_DEFAULT
                                    )
                                ?>"
                            >
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endforeach; ?>
        <div style="margin: 10px; padding: 10px; border-top: 1px solid #ababab">
            <button type="submit" class="button button-primary">
                <?php echo __('Uložit změny') ?>
            </button>
        </div>
    </form>
</div>
