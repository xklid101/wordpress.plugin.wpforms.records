<div class="wrap">

    <div>
        <?php include 'adminNavigation.php' ?>
    </div>

    <form action="<?php echo esc_attr($routing->getPluginCurrentUrlPage()) ?>" method="post">
    <?php foreach ($formsData as $value): ?>
        <div style="margin: 10px; padding: 10px; border-top: 1px solid #ababab">
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
            <div>
                <?php foreach ($value['fields'] as $value2): ?>
                    <div style="display: inline-block; width: 350px;">
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
