(function ($) {
  $(".duda-addons").dialog({
    dialogClass: "no-close",
    draggable: false,
    width: 500,
    modal: true,
    resizable: false,
    buttons: [{
      text: "Add addon(s)",
      click: function () {
        $(this).dialog("close");
      }
    }, {
      text: "Skip",
      click: function () {
        $(this).dialog("close");
      }
    }]
  });

  $(".template-select").click(function () {

  });
})(jQuery);
