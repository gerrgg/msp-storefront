jQuery((function(t){var e={init:function(){t("#resource_tab").on("click","button.add_input_line",e.add_line_item),t("#msp-product-video").on("click","button.add",e.add_video_line),t("#msp-specifications").on("click","button.add",e.add_spec_row),t("#msp-specifications").on("click","button.remove",e.delete_spec_row),t("#menu-to-edit").on("click","input.upload-btn",e.upload_media),t("#resource_tab").on("click","input.upload-btn",e.upload_media)},upload_media(t){t.preventDefault();let e=t.target.previousElementSibling;image=wp.media({title:"Upload Image",multiple:!1}).open().on("select",(function(t){var n=image.state().get("selection").first();console.log(n);var a=n.toJSON().url;e.value=a}))},delete_spec_row(e){let n=t(e.target.parentElement.parentElement),a={action:"msp_delete_specification",label:n.find("td:first-child input").val(),post_id:t("#post_ID").val()};t.post(ajaxurl,a),n.remove()},submit_promo_option(n){inputs=t(n.delegateTarget).find("input");for(var a=inputs.length/2-1,o={action:"msp_create_promo_line",options:{}},i=0;i<=a;i++){var l=t('input[name="msp_promo['+i+'][image_id]"]').val(),p=t('input[name="msp_promo['+i+'][permalink]"]').val();l.length>0&&p.length>0&&(o.options[l]=p)}t.post(ajaxurl,o,(function(t){e.add_promo_line(n,a+1)}))},add_spec_row(e){let n=t(e.delegateTarget).find("table"),a=Number(n.find("tr").last().attr("class"))+1;isNaN(a)&&(a=0),n.append(t("<tr/>",{class:a}).append('<td><input style="width: 100%" type="text" name="specification['+a+'][label]"></td>','<td><input style="width: 100%" type="text" name="specification['+a+'][value]"></td>','<td><button class="remove" type="button" role="button">&times;</button></td>'))},add_video_line:function(e){let n=t(e.target),a=t("#msp_product_video_input_table");count=isNaN(n.attr("data-count"))?0:+n.attr("data-count")+1,a.append(t("<input />",{name:"product_video["+count+"]"})),n.attr("data-count",count++)},add_line_item:function(e){let n=t(e.target);count=+n.attr("data-count")+1;let a=t("#resource_input_wrapper"),o={label:t("#resource_label").clone(),url:t("#resource_url").clone()};Object.keys(o).forEach((function(t){o[t].attr({id:"",name:"resource_"+t+"["+count+"]"}),a.append(o[t].val(""))})),a.append('<input type="button" name="upload-btn" class="button-secondary upload-btn" value="Upload Image"></br>'),t(e.target).attr("data-count",count++)}};e.init(),t("#title-length").length&&(t("#title-length").html(t("#gsf_title").val().length),t("#gsf_title").keyup((function(){t("#title-length").html(t(this).val().length)})))}));