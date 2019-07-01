<?php $all_templates = duda()->getTemplates(); ?>

<h1 class="text-center text-uppercase">Choose a template to start from</h1>
<p class="text-center">Each Template is natively responsive and can be fully customized to your liking.</p>

<div class="all-templates">
  <?php foreach( $all_templates as $template ): if ( $template['template_id'] != '1071598' ) continue; ?>
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
        <a class="template-select text-uppercase" href="javascript:void(0)" template-id="<?php esc_attr_e( $template['template_id'] ) ?>">Start with this template</a>
      </div>

    </div>
  <?php endforeach; ?>
</div>

<div class="duda-addons-container" style="display: none;">
  <div class="duda-addons-modal">
    <a href="#" class="close">× <span class="screen-reader-text"></span></a>

    <header class="modal-header">Premium Website Add-ons</header>

    <div class="modal-content">
      <?php if ( function_exists( 'get_duda_subscription_addons' ) ) : ?>
        <?php foreach ( get_duda_subscription_addons() as $addon_key => $addon_product_id ) : $product = wc_get_product( $addon_product_id ); ?>
          <div class="single-duda-addon">
            <p>
              <input type="checkbox" id="product-<?php esc_attr_e( $addon_product_id )?>" value="<?php esc_attr_e( $addon_product_id )?>" />
              <label for="product-<?php esc_attr_e( $addon_product_id )?>">
                <?php _e( $product->get_title() ); ?> <small>(<?php _e( $product->get_price_html() ); ?>)</small>
              </label>
            </p>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
    
    <footer class="modal-footer"><button type="submit" class="button-primary">Check out</button></footer>
  </div>
</div>