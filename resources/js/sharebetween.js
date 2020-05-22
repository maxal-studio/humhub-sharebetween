humhub.module("sharebetween", function (module, require, $) {
  var client = require("client");
  var Widget = require("ui.widget").Widget;

  module.initOnPjaxLoad = true;

  //Format Select2
  function formatSelect2(option) {
    if (!option.id) {
      return option.text;
    }
    var optimage = option.image;
    if (!optimage) {
      return option.text;
    } else {
      var $opt = $(
        `<span class='result'>
            <div class='image' style='background-image:url(` +
          optimage +
          `)'></div>
            ` +
          option.text +
          `
          </span>`
      );
      return $opt;
    }
  }

  //Bind Select2
  function bindSelect2() {
    //Initialize select2 for SPACES
    var _$spacesSelect2 = $("#spaces_select2");
    _$spacesSelect2.select2({
      width: "100%",
      minimumInputLength: 1,
      templateResult: formatSelect2,
      templateSelection: formatSelect2,
      containerCssClass: "sharebetween",
      dropdownCssClass: "sharebetween",
      //Ajax Call
      ajax: {
        url: module.config.groups.searchGroupsUrl,
        dataType: "json",
        data: function (params) {
          var query = {
            query: params.term,
          };
          return query;
        },
        processResults: function (data) {
          return {
            results: data,
          };
        },
      },
    });

    //Initialize select2
    var _$usersSelect2 = $("#users_select2");
    _$usersSelect2.select2({
      width: "100%",
      minimumInputLength: 1,
      templateResult: formatSelect2,
      templateSelection: formatSelect2,
      containerCssClass: "sharebetween",
      dropdownCssClass: "sharebetween",
      //Ajax Call
      ajax: {
        url: module.config.groups.searchUsersUrl,
        dataType: "json",
        data: function (params) {
          var query = {
            query: params.term,
          };
          return query;
        },
        processResults: function (data) {
          return {
            results: data,
          };
        },
      },
    });
  }

  //Unload Function
  var unload = function (isPjax) {
    // humhub.modules.sharebetween = null;
    isPjax = true;
  };

  //Hide modal on success
  var hideGlobalModal = function () {
    setTimeout(function () {
      $("#globalModal").modal("hide");
    }, 4000);
  };

  var init = function (isPjax) {
    var _$shb_searchBox = $(".shb_searchBox");
    var _$searchResults = _$shb_searchBox.find(".searchResults");
    var _$input = _$shb_searchBox.find("input");

    //Bind Select2
    bindSelect2();

    //Rebind Select2 On each modal show
    $("#globalModal").on("shown.bs.modal", function (e) {
      //Bind Select2
      setTimeout(function () {
        bindSelect2();
      }, 500);
    });
  };

  var sendMessageTo = function (content_id, user_ids) {
    if (user_ids.length == 0) {
      $(".status").hide();
      $(".status.failed").show();
    } else {
      $(".status").hide();
      //Get user objects
      $.ajax({
        async: false,
        type: "GET",
        url: module.config.groups.retriveUserObjects,
        data: {
          content_id: content_id,
          user_ids: user_ids,
        },
        success: function (data) {
          for (to in data.to) {
            sendMessage(me, data.to[to], data.message);
          }
          $(".status.success").show();
          hideGlobalModal();
          //Clear Selection
          $("#users_select2").empty();
        },
      });
    }
  };

  function checkResponse() {
    var $confirmModal = $("#globalModal").find("#confirmModal");
    if ($confirmModal.length == 0) {
      //Bind Select2
      setTimeout(function () {
        bindSelect2();

        //Navigate to failed tab
        $errorElement = $(".help-block-error").first();
        if ($errorElement.length != 0) {
          $parentTab = $errorElement.parents(".tab-pane");
          parent_id = $parentTab.attr("id");
          $('.nav-tabs a[href="#' + parent_id + '"]').tab("show");
        }
      }, 500);
    } else {
      hideGlobalModal();
    }
  }

  module.export({
    init: init,
    unload: unload,
    sendMessageTo: sendMessageTo,
    hideGlobalModal: hideGlobalModal,
    bindSelect2: bindSelect2,
    checkResponse: checkResponse,
  });
});
