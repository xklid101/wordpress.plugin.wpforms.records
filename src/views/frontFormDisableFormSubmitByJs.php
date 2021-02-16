<?php if ($doDisable): ?>

    <script>
        (function() {
            /**
             * info above form
             */
            let form = document.getElementById('wpforms-form-<?php echo esc_js($formId) ?>');
            let infoNode = document.createElement("div");
            infoNode.innerHTML = '<span style="color: #990000"> \
                                    <?php echo esc_html($message) ?> \
                                </span>';
            form.insertBefore(infoNode, form.firstChild);

            /**
             * info above submit elem
             */
            document.querySelectorAll('#wpforms-form-<?php echo esc_js($formId) ?> *[type="submit"]').forEach(function(item) {
                item.disabled = true;
                item.style = item.style + ';background-color: #cdcdcd';
                let newNode = infoNode.cloneNode(true);
                item.parentNode.insertBefore(newNode, item);
            });
        })()
    </script>

<? endif; ?>
