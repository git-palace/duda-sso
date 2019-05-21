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
})(jQuery);