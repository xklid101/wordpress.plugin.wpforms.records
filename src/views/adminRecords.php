<div class="wrap">

    <?php include 'adminNavigation.php' ?>

    <div>
        <ul class="subsubsub">
            <?php $i = 0; ?>
            <?php foreach ($formsData as $value): ?>
                <li>
                    <?php if ($i): ?>
                        |
                    <?php endif; ?>
                    <a
                        href="<?php echo $routing->getPluginCurrentUrlPage(['formid' => $value['id']]) ?>"
                        <?php if (($_GET['formid'] ?? '') == $value['id']): ?>
                            class="current"
                        <?php endif; ?>
                    >
                        <?php echo esc_html($value['title'])?>
                        <sup>(ID <?php  echo esc_html($value['id'])?>)</sup>
                    </a>
                </li>
                <?php $i++; ?>
            <?php endforeach; ?>
        </ul>
        <div style="clear: both"></div>
    </div>

    <?php if ($formTableSelected): ?>
        <form method="get">
            <div>
                <input type="hidden" name="page" value="<?php echo esc_attr($routing->getSlug()) ?>">
                <input type="hidden" name="subpage" value="<?php echo esc_attr($routing->getSubpage()) ?>">
                <input type="hidden" name="formid" value="<?php echo esc_attr($_GET['formid'] ?? '') ?>">
                <?php $formTableSelected->search_box(__('hledat'), 'xklid101-wprecords-search-box'); ?>
            </div>
        </form>
        <?
        /**
         * @todo  - implement the form with bulk actions and fast row edit etc..?
         *            @see  https://codingbin.com/display-custom-table-data-wordpress-admin/ for hints
         */
        ?>
        <form method="post" action="<?php echo esc_attr($routing->getPluginCurrentUrlPage()) ?>">
            <div>
                <?php $formTableSelected->display() ?>
            </div>
            <input type="hidden" name="id" value="<?php echo esc_attr($formTableSelected->getId()) ?>">
            <div style="margin: 10px; padding: 10px; border-top: 1px solid #ababab">
                <button type="submit" class="button button-primary">
                    <?php echo __('Uložit změny') ?>
                </button>
            </div>
        </form>
    <?php endif; ?>
</div>
