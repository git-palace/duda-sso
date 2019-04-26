<?php $all_templates = duda()->getTemplates(); ?>

<div class="card-columns">
  <?php foreach( $all_templates as $template ): ?>
    <div class="card">
      <div class="card-thumbnail">
        <img class="card-img-top" src="<?php esc_attr_e( $template['thumbnail_url'] ) ?>" alt="<?php esc_attr_e( $template['template_name'] ) ?>">
        <a href="<?php esc_attr_e( $template['preview_url']) ?>" target="_blank">
          <span>Preview <i class="fa fa-search" aria-hidden="true"></i></span>
        </a>

        <a href="?action=duda_tpl_select&id=<?php esc_attr_e( $template['template_id'] ) ?>"><span>Choose <i class="fa fa-check" aria-hidden="true"></i></span></a>
      </div>

      <div class="card-body">
        <h2 class="text-center my-0"><?php esc_html_e( $template['template_name'] ) ?></h2>
      </div>
    </div>
  <?php endforeach; ?>
</div>