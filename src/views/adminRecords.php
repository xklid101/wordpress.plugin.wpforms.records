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
                        href="<?php echo $routing->getPluginCurrentUrlPage() ?>&formid=<?php echo esc_attr($value['id']) ?>"
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
</div>
