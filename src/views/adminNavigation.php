<div>
    <?php if ($this->routing->isSubpage('records')): ?>
        <span>records</span>
    <?php else: ?>
        <a href="<?php echo $this->routing->getPluginUrl(['subpage' => 'records']) ?>">records</a>
    <?php endif; ?>
    <?php if (current_user_can('manage_options')): ?>
        &emsp;
        <?php if ($this->routing->isSubpage('config')): ?>
            <span>config</span>
        <?php else: ?>
            <a href="<?php echo $this->routing->getPluginUrl(['subpage' => 'config']) ?>">config</a>
        <?php endif; ?>
    <?php endif; ?>
</div>
