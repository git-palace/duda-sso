<?php //$all_templates = duda()->getTemplates(); ?>
<?php 
  $all_templates = [[
    'thumbnail_url' => 'https://irp-cdn.multiscreensite.com/f517ba54/screenshots/Screenshot_L0FUu.png',
    'template_name' => 'KW Agent',
    'preview_url'   => 'http://websites.agentcloud.com/preview/f517ba54',
    'template_id'   => '1066304'
  ]];
?>

<h1 class="text-center text-uppercase">Choose a template to start from</h1>
<p class="text-center">Each Template is natively responsive and can be fully customized to your liking.</p>

<div class="all-templates">
  <?php foreach( $all_templates as $template ): ?>
    <div class="single-template">
      <div class="thumbnail">
        <div>
          <img class="card-img-top" src="<?php esc_attr_e( $template['thumbnail_url'] ) ?>" alt="<?php esc_attr_e( $template['template_name'] ) ?>">        
        </div>
      </div>

      <div class="summary">
        <h2 class="template-name"><?php esc_html_e( $template['template_name'] ) ?></h2>
        <a class="template-preview" href="<?php esc_attr_e( $template['preview_url']) ?>" target="_blank">Preview Template</a>
        <hr />
        <a class="template-select text-uppercase" href="?action=duda_tpl_select&id=<?php esc_attr_e( $template['template_id'] ) ?>" template-id="<?php esc_attr_e( $template['template_id'] ) ?>">Start with this template</a>
      </div>

    </div>
  <?php endforeach; ?>
</div>

<div class="duda-addons" title="Duda Subscription Product Addons" style="display: none;">
  <?php if ( function_exists( 'get_duda_subscription_addons' ) ) : ?>
    <?php foreach ( get_duda_subscription_addons() as $addon_product_id ) : $product = wc_get_product( $addon_product_id ); ?>
      <div class="single-duda-addon">
        <p>
          <input type="checkbox" id="product-<?php esc_attr_e( $addon_product_id )?>" />
          <label for="product-<?php esc_attr_e( $addon_product_id )?>"><<?php _e( $product->get_title()) )?>/label>
        </p>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>
</div>