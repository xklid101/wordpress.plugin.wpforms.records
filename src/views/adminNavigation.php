<div>
    <ul class="subsubsub">
        <li>
            <a
                href="<?php echo $routing->getPluginUrl(['subpage' => 'records']) ?>"
                <?php if ($routing->isSubpage('records')): ?>
                    class="current"
                <?php endif; ?>
            >
                <?php echo __('Záznamy') ?>
            </a>
        </li>
        <?php if (current_user_can('manage_options')): ?>
            <li>
                |
                <a
                    href="<?php echo $routing->getPluginUrl(['subpage' => 'config']) ?>"
                    <?php if ($routing->isSubpage('config')): ?>
                        class="current"
                    <?php endif; ?>
                >
                    <?php echo __('Nastavení') ?>
                </a>
            </li>
        <?php endif; ?>
    </ul>
    <div style="clear: both"></div>
</div>
