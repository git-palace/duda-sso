(function ($) {
  var template_id;

  $(".template-select").click(function (e) {
    e.preventDefault();

    $.each($(".duda-addons input:checked"), function () {
      $(this).prop("checked", false);
    });

    $(".duda-addons-container").show();

    template_id = $(this).attr("template-id");
  });

  $(".duda-addons-modal a.close").click(function () {
    $(".duda-addons-container").hide();
  });

  $(".duda-addons-modal button").click(function () {
    var addon_ids = [];

    $.each($(".duda-addons-modal .modal-content input:checked"), function () {
      addon_ids.push($(this).val())
    });

    window.location.href = "?action=duda_tpl_select&id=" + template_id + (addon_ids.length ? "&addon_ids=" + addon_ids.join("|||") : "");
  });

  if ($("select[name=marketplace]").length) {
    $("select[name=marketplace]").change(function () {
      $(".neighborhoods-container").hide();

      $("input[name^=neighborhoods]").each(function () {
        $(this).prop("checked", false);
      });

      $("input[name^=neighborhoods]").click(function (e) {
        if ($("input[name^=neighborhoods]:checked").length > 10) {
          $(this).prop("checked", false);
          alert("Only 10 neightborhoods can be selected.");
          return false;
        }
      });

      $(".neighborhoods-container[marketplace='" + $(this).val() + "']").slideDown("slow");
    });

    $(".neighborhoods-select-container form").submit(function (e) {
      if (!$("input[name^=neighborhoods]").val())
        e.preventDefault();
    });
  }
})(jQuery);