<div class="simplicate-flex-container">
    <div class="ss-activate-highlight activate-option">
        <form name="simplicate_activate" action="options-general.php?page=simplicate-key-config" method="POST">
            <?php settings_fields( 'simplicate-settings' ); ?>
            <?php do_settings_sections( 'simplicate-settings' ); ?>
            <div class="option-description">
                <strong style="font-size: 16px;">Simplicate connectie</strong>
                <p>Vul hier je API gegevens in die je in simplicate hebt aangemaakt</p>
            </div>
            <div class="form-group">
                <label for="simplicateDomain">Domein</label>
                <div class="input-group">
                    <input type="text" name="simplicate_domain" id="simplicateKey" placeholder="domein"
                           value="<?php echo get_option('simplicate_domain'); ?>"
                    >
                    <div class="input-value">.simplicate.nl</div>
                </div>
            </div>
            <div class="form-group">
                <label for="simplicateKey">API key</label>
                <input type="text" name="simplicate_key" id="simplicateKey" placeholder="Simplicate API key"
                       value="<?php echo get_option('simplicate_key'); ?>"
                >
            </div>
            <div class="form-group">
                <label for="simplicateSecret">API secret</label>
                <input type="text" name="simplicate_secret" id="simplicateSecret" placeholder="Simplicate API secret"
                       value="<?php echo get_option('simplicate_secret'); ?>"
                >
            </div>
            <input type="hidden" name="passback_url" value="/wp-admin/options-general.php?page=simplicate-key-config">
            <input type="hidden" name="blog" value="http://wordpress.dev">
            <input type="hidden" name="redirect" value="plugin-signup">
            <div class="option-description">
                <p>Nog geen account? <a href="">vraag een demo account aan</a>.</p>
            </div>
            <?php // submit_button('Verbinden'); ?>
            <input type="submit" class="right button button-primary" value="Verbinden">
        </form>
    </div>
    <div class="simplicate-status">
        API Status
        <?php if(1 == get_option('simplicate_active')): ?>
            <div class="current-status">
                <i class="sim-icon icon-waiting"></i> Wachten
            </div>
        <?php else: ?>
            <div class="current-status">
                <i class="sim-icon"></i> Geen API verbinding ingesteld.
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
    <?php if(1 == get_option('simplicate_active')): ?>
    jQuery(document).ready(function($) {
        $.ajax({
            url: 'https://<?php echo get_option('simplicate_domain'); ?>.simplicate.nl/api/v2/base/test.json',
            headers: {
                'Authentication-Key':       '<?php echo get_option('simplicate_key'); ?>',
                'Authentication-Secret':    '<?php echo get_option('simplicate_secret'); ?>'
            }
        }).done(function() {
            $('.current-status').html('<i class="sim-icon icon-active"></i> Operationeel');
        }).fail(function() {
            $('.current-status').html('<i class="sim-icon icon-error"></i> Kan geen verbinding maken');
        });
    });
    <?php endif; ?>
</script>