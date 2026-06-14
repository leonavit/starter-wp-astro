/**
 * WP × Astro Starter — Theme Options admin JS.
 * - Hours repeater (add / remove rows)
 * - Media picker (WP media library)
 */
(function ($) {
	"use strict";

	// ---- Repeater (hours) ------------------------------------------------
	$(document).on("click", "[data-wpas-repeater-add]", function () {
		var $wrap = $(this).closest("[data-wpas-repeater]");
		var $list = $wrap.find("[data-wpas-repeater-list]");
		var prefix = $wrap.data("wpas-prefix");
		var index = $list.find("[data-wpas-repeater-row]").length;

		var $template = $list.find("[data-wpas-repeater-row]").first().clone();
		$template.find("input").each(function () {
			var name = $(this).attr("name") || "";
			// Replace the existing array index.
			$(this).attr("name", name.replace(/\[(\d+)\]/, "[" + index + "]"));
			$(this).val("");
		});
		$list.append($template);
	});

	$(document).on("click", "[data-wpas-repeater-remove]", function (e) {
		e.preventDefault();
		var $row = $(this).closest("[data-wpas-repeater-row]");
		var $list = $row.closest("[data-wpas-repeater-list]");
		if ($list.find("[data-wpas-repeater-row]").length > 1) {
			$row.remove();
		} else {
			$row.find("input").val("");
		}
	});

	// ---- Media picker ----------------------------------------------------
	$(document).on("click", "[data-wpas-media-select]", function (e) {
		e.preventDefault();
		var $btn = $(this);
		var $field = $btn.closest("[data-wpas-media]");
		var mediaType = $field.data("media-type") || "image";

		var frame = wp.media({
			title: "בחירת מדיה",
			button: { text: "השתמש בקובץ זה" },
			library: { type: mediaType },
			multiple: false,
		});

		frame.on("select", function () {
			var attachment = frame.state().get("selection").first().toJSON();
			$field.find("[data-wpas-media-id]").val(attachment.id);
			$field.find("[data-wpas-media-url]").val(attachment.url);

			var $thumb = $field.find("[data-wpas-media-thumb]");
			if (mediaType === "image") {
				$thumb.html('<img src="' + attachment.url + '" alt="">');
			} else {
				$thumb.html("<code>" + attachment.filename + "</code>");
			}
			$thumb.removeAttr("hidden");
		});

		frame.open();
	});

	$(document).on("click", "[data-wpas-media-clear]", function (e) {
		e.preventDefault();
		var $field = $(this).closest("[data-wpas-media]");
		$field.find("[data-wpas-media-id]").val("0");
		$field.find("[data-wpas-media-url]").val("");
		$field.find("[data-wpas-media-thumb]").empty().attr("hidden", "hidden");
	});
})(window.jQuery);
