(function ($) {
  var template_id = null;

  $(".duda-addons").dialog({
    autoOpen: false,
    dialogClass: "duda-addons-dlg",
    draggable: false,
    width: 750,
    minHeight: 300,
    modal: true,
    resizable: false,
    buttons: [{
      text: "Check Out",
      click: function () {
        var addon_ids = [];

        $.each($(".duda-addons input:checked"), function () {
          addon_ids.push($(this).val())
        });

        window.location.href = "?action=duda_tpl_select&id=" + template_id + (addon_ids.length ? "&addon_ids=" + addon_ids.join("|||") : "");
      }
    }]
  });

  $(".template-select").click(function (e) {
    e.preventDefault();

    $(".duda-addons").dialog("open");

    template_id = $(this).attr("template-id");
  });

  if ($("select[name^=neighborhoods]").length && marketplaces && Object.keys(marketplaces).length) {
    $("select[name=marketplace]").change(function () {
      $("select[name^=neighborhoods]").select2({
        maximumSelectionLength: 10
      });
      $(".neighborhoods-container").css('visibility', 'hidden');
      $("select[name^=neighborhoods]").val(null).trigger("change");
      $("select[name^=neighborhoods]").select2("destroy");

      if (!$(this).val()) {
        $("select[name^=neighborhoods] option").each(function () {
          $(this).remove();
        });
        return;
      }

      $.each(marketplaces[$(this).val()], function (index, neighborhood) {
        $("select[name^=neighborhoods]").append(new Option(neighborhood));
      });

      $("select[name^=neighborhoods]").select2({
        maximumSelectionLength: 10
      });
      $(".neighborhoods-container").css('visibility', 'visible');
    });

    $("select[name=marketplace]").trigger("change");

    $(".neighborhoods-select-container form").submit(function (e) {
      if (!$("select[name^=neighborhoods]").val())
        e.preventDefault();
    });
  }
})(jQuery);