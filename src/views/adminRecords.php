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
        <form method="post" action="<?php echo esc_attr($routing->getPluginCurrentUrlPage()) ?>">
            <div>
                <input
                    type="submit"
                    class="button button-default"
                    name="export-all-table"
                    value="<?php echo __('Export celé tabulky') ?>"
                />
                <input type="hidden" name="id" value="<?php echo esc_attr($_GET['formid'] ?? '') ?>">
            </div>
        </form>
        <form method="get">
            <div>
                <input type="hidden" name="page" value="<?php echo esc_attr($routing->getSlug()) ?>">
                <input type="hidden" name="subpage" value="<?php echo esc_attr($routing->getSubpage()) ?>">
                <input type="hidden" name="formid" value="<?php echo esc_attr($_GET['formid'] ?? '') ?>">
                <?php $formTableSelected->search_box(__('hledat'), 'xklid101-wprecords-search-box'); ?>
            </div>
        </form>
        <form method="post" action="<?php echo esc_attr($routing->getPluginCurrentUrlPage()) ?>">
            <div id="xklid101-form-table-selected">
                <?php $formTableSelected->display() ?>
            </div>
            <input type="hidden" name="id" value="<?php echo esc_attr($formTableSelected->getId()) ?>">
            <div style="margin: 10px; padding: 10px; border-top: 1px solid #ababab">
                <button
                    type="button"
                    class="button button-default"
                    onclick="
                        this.style.display = 'none';
                        this.nextElementSibling.style.display = '';
                        document.querySelectorAll('#xklid101-form-table-selected .read').forEach(function(item) {
                            item.style.display = 'none';
                        });
                        document.querySelectorAll('#xklid101-form-table-selected .edit').forEach(function(item) {
                            item.style.display = '';
                        });
                    "
                >
                    <?php echo __('Upravit záznamy') ?>
                </button>
                <div style="display: none">
                    <input
                        type="submit"
                        name="submit-all-changes"
                        value="<?php echo __('Uložit změny') ?>"
                        class="button button-primary"
                    >
                    &emsp;
                    <button
                        type="button"
                        class="button button-default"
                        onclick="
                            this.closest('div').style.display = 'none';
                            this.closest('div').previousElementSibling.style.display = '';
                            document.querySelectorAll('#xklid101-form-table-selected .edit').forEach(function(item) {
                                item.style.display = 'none';
                            });
                            document.querySelectorAll('#xklid101-form-table-selected .read').forEach(function(item) {
                                item.style.display = '';
                            });
                        "
                    >
                        <?php echo __('Zrušit') ?>
                    </button>
                </div>
            </div>
        </form>
    <?php endif; ?>
</div>
