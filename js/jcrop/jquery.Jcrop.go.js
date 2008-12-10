		$(function(){
			jQuery("#avatar_original img.avatar").Jcrop({ onChange: showPreview,
													      setSelect: [ 0, 0, $("#avatar_original img.avatar").attr("width"), $("#avatar_original img.avatar").attr("height") ],
														  onSelect: updateCoords,
													      aspectRatio: 1,
														  boxWidth: 640,
														  boxHeight: 640,
														  bgColor: '#000',
														  bgOpacity: .4
												});
		});

		function showPreview(coords) {
			var rx = 96 / coords.w;
			var ry = 96 / coords.h;

			var img_width = $("#avatar_original img.avatar").attr("width");
			var img_height = $("#avatar_original img.avatar").attr("height");


			$('#avatar_preview img.avatar').css({
				width: Math.round(rx *img_width) + 'px',
				height: Math.round(ry * img_height) + 'px',
				marginLeft: '-' + Math.round(rx * coords.x) + 'px',
				marginTop: '-' + Math.round(ry * coords.y) + 'px'
			});
		};

		function updateCoords(c) {
			$('#avatar_crop_x').val(c.x);
			$('#avatar_crop_y').val(c.y);
			$('#avatar_crop_w').val(c.w);
			$('#avatar_crop_h').val(c.h);
		};

		function checkCoords() {
			if (parseInt($('#avatar_crop_w').val())) return true;
			alert('Please select a crop region then press submit.');
			return false;
		};

