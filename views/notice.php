<?php if ( $type == 'plugin' ) :?>
<div class="ss_updated" >
    <form name="akismet_activate" action="<?php echo esc_url( SimplicateAdmin::getPageUrl() ); ?>" method="POST">
        <div class="simplicate_activate">
            <div class="aa_button_container">
                <div class="ss_button_border">
                    <input type="submit" class="ss_button" value="<?php esc_attr_e( 'Connect with Simplicate API', 'simplicate' ); ?>" />
                </div>
            </div>
            <div class="ss_description"><?php _e('<strong>Almost done</strong> - connect Simplicate CRM with wordpress', 'simplicate');?></div>
        </div>
    </form>
</div>
<?php endif; ?>