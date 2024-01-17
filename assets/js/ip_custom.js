jQuery(document).ready( function($) {
    $('#save_itsperfect_config').on('click', function(e) {

      
        var url = $('#itsperfect_base_url').val();
        var token = $('#itsperfect_api_token').val();
        var product_status = $('#product_status').val();

        var product_title_format = [];
        $(".product_title_format:checked").each(function() { 
            product_title_format.push($(this).val()); 
        }); 
           // console.log(product_title_format);

            var webhooks = [];
            $(".webhooks:checked").each(function() { 
                webhooks.push($(this).val()); 
            }); 

          

         
            
            $('.loading').css('display', 'block');
             $(".btn").attr("disabled");

        if(url && token){
            var data = {
                action: 'kp_ip_save_config',
                ip_base_url: url,
                ip_api_token: token,
                product_status: product_status,
                product_title_format: product_title_format,
                webhooks: webhooks,
              
            };
            // the_ajax_script.ajaxurl is a variable that will contain the url to the ajax processing file
            $.post(the_ajax_script.ajaxurl, data, function(response) {
                // $("#createvariations").removeAttr('disabled');
                // $("#orderlists").css('opacity', 'unset');
                // $("#UpdateOrdersLoader").css('display','none');
                $('.loading').css('display', 'none');
                $(".btn").removeAttr("disabled");
              
                
                $("#modalcontent").html(response);
                $("#notificationmodal").modal("show");
            });
        }else{
            $.alert('Please enter valid input !');
        }
        return false;
    });




    var table = $('.create_product_datatable').DataTable({
        "order": [[ 0, "desc" ]],
        columnDefs: [
            {
                orderable: false,
                targets:   0,
                searchable:false,
                render: function (data, type, full, meta){
                    if(full[4].indexOf("Create") != -1){
                        return '<input type="checkbox" name="id[]" value="'
                            + full[1] + '">';
                    } else {
                        return "";
                    }
                }
            },
            {"width": "10%", "targets": 0},
            {"width": "13%", "targets": 1},
        ]
    });

    var update_product_table = $("#update_product_datatable").DataTable({
        "order" : [[ 0, "desc"]],
        columnDefs: [
            {"width": "5%", "targets": 0},
            {"width": "8%", "targets": 1},
            {"width": "20%", "targets": 2},
            {"width": "10%", "targets": 3},
            {"width": "13%", "targets": 4},
        ]
    });

    var order_manage_datatable = $("#order_manage_datatable").DataTable({
        "order" : [[ 0, "desc"]],
        // columnDefs: [
        //     {"width": "5%", "targets": 0},
        //     {"width": "8%", "targets": 1},
        //     {"width": "20%", "targets": 2},
        //     {"width": "10%", "targets": 3},
        //     {"width": "13%", "targets": 4},
        // ]
    });

    // Handle click on "Select all" control
    $('#example-select-all').on('click', function(){
        // Check/uncheck all checkboxes in the table
        console.log("hey We here");
        var rows = table.rows({ 'search': 'applied' }).nodes();

        $('input[type="checkbox"]', rows).prop('checked', this.checked);

        if(this.checked){
            $('.ca_create_selected_product').css('display', 'block');
            $('.create_product').prop('disabled','true');
        } else {
            $('.ca_create_selected_product').css('display', 'none');
            $('.create_product').removeAttr('disabled');
        }
    });

    // Handle click on checkbox to set state of "Select all" control
    $('#create_product_datatable tbody').on('change', 'input[type="checkbox"]', function(){
        // If checkbox is unchecked
        if(!this.checked){
            var length = $('input[type="checkbox"]:checked').length;
            if(length > 0){
                $('.ca_create_selected_product').css('display', 'block');
                $('.create_product').prop('disabled','true');
            } else {
                $('.ca_create_selected_product').css('display', 'none');
                $('.create_product').removeAttr('disabled');
            }
        } else {
            $('.ca_create_selected_product').css('display', 'block');
            $('.create_product').prop('disabled','true');
        }
    });

    //Create multiple products
    $('.ca_create_selected_product').on('click', function(){
        $('.loading').css('display', 'block');
        $(".btn").attr("disabled");

        var productId = [];
        table.$('input[type="checkbox"]:checked').each(function(){
            productId.push(this.value);
        });
        var data = {
            action: 'create_multiple_products',
            productId: JSON.stringify(productId)
        };

        $.ajax({
            type: "POST",
            url: the_ajax_script.ajaxurl,
            data: data,
            success: function(response){
                $('.loading').css('display', 'none');
                $(".btn").removeAttr("disabled");
                $("#modalcontent").html(response);
                $("#notificationmodal").modal("show");
            },
            error: function(jqXHR) {
                $('.loading').css('display', 'none');
                $(".btn").removeAttr("disabled");
                if (jqXHR.status === 400) {
                    $("#modalcontent").html("You need to activate the license key in order to use the plugin");
                      $("#notificationmodal").modal("show");
                } else {
                    $("#modalcontent").html(jqXHR);
                    $("#notificationmodal").modal("You need to activate the license key in order to use the plugin");
                }
            }
        });

    });

    /*
    * Create a single product from wordpress to channelAdvisor
    */
    jQuery(document).on('click','.create_product',function(){
        var id = this.id;
        $('.loading').css('display', 'block');
        $(".btn").attr("disabled");
        
        var data = {
            action: 'ca_create_single_product',
            post_var: id
        };

        $.ajax({
            type: "POST",
            url: the_ajax_script.ajaxurl,
            data: data,
            success: function(response){
                $('.loading').css('display', 'none');
                $(".btn").removeAttr("disabled");
                $("#modalcontent").html(response);
                $("#notificationmodal").modal("show");
                $('#td_'+id).html("Product exists");
            },
            error: function(jqXHR) {
                $('.loading').css('display', 'none');
                $(".btn").removeAttr("disabled");
                if (jqXHR.status === 400) {
                    $("#modalcontent").html("You need to activate the license key in order to use the plugin");
                      $("#notificationmodal").modal("show");
                } else {
                    $("#modalcontent").html(jqXHR);
                    $("#notificationmodal").modal("You need to activate the license key in order to use the plugin");
                }
            }
        });

        return false;
    });


    /**
     * Triggers a function which creates all products at once
     */
    jQuery(document).on("click",'#import_all',function(){
        $('.loading').css('display', 'block');
        $(".btn").attr("disabled");
        
        var data = {
            action: 'create_multiple_products',
            productId: "import_all"
        };

        $.ajax({
            type: "POST",
            url: the_ajax_script.ajaxurl,
            data: data,
            success: function(response){
                $('.loading').css('display', 'none');
                $(".btn").removeAttr("disabled");
                $("#modalcontent").html(response);
                $("#notificationmodal").modal("show");
            },
            error: function(jqXHR) {
                $('.loading').css('display', 'none');
                $(".btn").removeAttr("disabled");
                if (jqXHR.status === 400) {
                    $("#modalcontent").html("You need to activate the license key in order to use the plugin");
                      $("#notificationmodal").modal("show");
                } else {
                    $("#modalcontent").html(jqXHR);
                    $("#notificationmodal").modal("You need to activate the license key in order to use the plugin");
                }
            }
        });
        return false;
    });

    /**
     * Triggers a function which removes all products & categories at once.
     */
    jQuery(document).on("click",'#reset_all',function(){
        if (confirm('Are you sure you want to reset the full store ?')) {
            $('.loading').css('display', 'block');
            $(".btn").attr("disabled");
            
            var data = {
                action: 'reset_store',
            };

            $.ajax({
                type: "POST",
                url: the_ajax_script.ajaxurl,
                data: data,
                success: function(response){
                    $('.loading').css('display', 'none');
                    $(".btn").removeAttr("disabled");
                    $("#modalcontent").html(response);
                    $("#notificationmodal").modal("show");
                    window.location.reload();
                },
                error: function(jqXHR) {
                    $('.loading').css('display', 'none');
                    $(".btn").removeAttr("disabled");
                    if (jqXHR.status === 400) {
                        $("#modalcontent").html("You need to activate the license key in order to use the plugin");
                        $("#notificationmodal").modal("show");
                    } else {
                        $("#modalcontent").html(jqXHR);
                        $("#notificationmodal").modal("You need to activate the license key in order to use the plugin");
                    }
                }
            });
          } else {
            // Do nothing!
            console.log('We are not removing the products ! ');
          }
        
        return false;
    });

    /**
     * Triggers a function which creates all images for existing products
     */
    jQuery(document).on("click","#import_images",function(){
        $('.loading').css('display', 'block');
        $(".btn").attr("disabled");
        
        var data = {
            action: 'import_images',
        };

        $.ajax({
            type: "POST",
            url: the_ajax_script.ajaxurl,
            data: data,
            success: function(response){
                $('.loading').css('display', 'none');
                $(".btn").removeAttr("disabled");
                $("#modalcontent").html(response);
                $("#notificationmodal").modal("show");
            },
            error: function(jqXHR) {
                $('.loading').css('display', 'none');
                $(".btn").removeAttr("disabled");
                if (jqXHR.status === 400) {
                    $("#modalcontent").html("You need to activate the license key in order to use the plugin");
                      $("#notificationmodal").modal("show");
                } else {
                    $("#modalcontent").html(jqXHR);
                    $("#notificationmodal").modal("You need to activate the license key in order to use the plugin");
                }
            }
        });
        
        return false;
    });

    /**
     * Triggers a function which updates the selected product
     */
    jQuery(document).on("click",".update_product",function(){
        $('.loading').css('display', 'block');
        $(".btn").attr("disabled");
        var productid = $(this).attr('id');
        
        var data = {
            action: 'update_product',
            wp_product_id: productid
        };

        $.ajax({
            type: "POST",
            url: the_ajax_script.ajaxurl,
            data: data,
            success: function(response){
                $('.loading').css('display', 'none');
                $(".btn").removeAttr("disabled");
                $("#modalcontent").html(response);
                $("#notificationmodal").modal("show");
            },
            error: function(jqXHR) {
                $('.loading').css('display', 'none');
                $(".btn").removeAttr("disabled");
                if (jqXHR.status === 400) {
                    $("#modalcontent").html("You need to activate the license key in order to use the plugin");
                      $("#notificationmodal").modal("show");
                } else {
                    $("#modalcontent").html(jqXHR);
                    $("#notificationmodal").modal("You need to activate the license key in order to use the plugin");
                }
            }
        });
        
        return false;
    });


    /**
     * Triggers a function which sends order to itsperfect
     */
    jQuery(document).on("click",".sync_order",function(){
        $('.loading').css('display', 'block');
        $(".btn").attr("disabled");
        $(".gif").css("display","inline-block");

        var orderid = $(this).attr('id');
        
        var data = {
            action: 'send_order_to_erp',
            order_id: orderid
        };

        $.ajax({
            type: "POST",
            url: the_ajax_script.ajaxurl,
            data: data,
            success: function(response){
                $('.loading').css('display', 'none');
                $(".btn").removeAttr("disabled");
                $("#modalcontent").html(response);
                $("#notificationmodal").modal("show");
                $(".gif").css("display","none");
            },
            error: function(jqXHR) {              
                  $('.loading').css('display', 'none');
                $(".btn").removeAttr("disabled");
                if (jqXHR.status === 400) {
                    $("#modalcontent").html("You need to activate the license key in order to use the plugin");
                      $("#notificationmodal").modal("show");
                } else {
                    $("#modalcontent").html(jqXHR);
                    $("#notificationmodal").modal("You need to activate the license key in order to use the plugin");
                }
            }
        });
        
        return false;
    });

    jQuery(document).on("click",".resync_order",function(){
        $('.loading').css('display', 'block');
        $(".button").attr("disabled");
        $(".gif").css("display","inline-block");

        var orderid = $(this).attr('id');
        
        var data = {
            action: 'send_order_to_erp',
            order_id: orderid,
            resync : true
        };

        $.ajax({
            type: "POST",
            url: the_ajax_script.ajaxurl,
            data: data,
            success: function(response){
                $('.loading').css('display', 'none');
                $(".button").removeAttr("disabled");
                $(".gif").css("display","none");
                alert(response);
                $("#modalcontent").html(response);
                $("#notificationmodal").modal("show");
            },
            error: function(jqXHR) {
                $('.loading').css('display', 'none');
                $(".btn").removeAttr("disabled");
                if (jqXHR.status === 400) {
                    $("#modalcontent").html("You need to activate the license key in order to use the plugin");
                      $("#notificationmodal").modal("show");
                } else {
                    $("#modalcontent").html(jqXHR);
                    $("#notificationmodal").modal("You need to activate the license key in order to use the plugin");
                }
            }
        });
        
        return false;
    });


    /**
     * Triggers stock job manually
     */
    jQuery(document).on("click","#update_stock",function(){
        $('.loading').css('display', 'block');
        $(".btn").attr("disabled");
        
        var data = {
            action: 'update_stock',
        };

        $.ajax({
            type: "POST",
            url: the_ajax_script.ajaxurl,
            data: data,
            success: function(response){
                $('.loading').css('display', 'none');
                $(".btn").removeAttr("disabled");
                $("#modalcontent").html(response);
                $("#notificationmodal").modal("show");
            },
            error: function(jqXHR) {
                $('.loading').css('display', 'none');
                $(".btn").removeAttr("disabled");
                if (jqXHR.status === 400) {
                    $("#modalcontent").html("You need to activate the license key in order to use the plugin");
                      $("#notificationmodal").modal("show");
                } else {
                    $("#modalcontent").html(jqXHR);
                    $("#notificationmodal").modal("You need to activate the license key in order to use the plugin");
                }
            }
        });
        
        return false;
    });

    /**
     * Triggers set default variation job
     */
    jQuery(document).on("click","#set_default_variations",function(){
        $('.loading').css('display', 'block');
        $(".btn").attr("disabled");
        
        var data = {
            action: 'set_default_variations',
        };

        $.ajax({
            type: "POST",
            url: the_ajax_script.ajaxurl,
            data: data,
            success: function(response){
                $('.loading').css('display', 'none');
                $(".btn").removeAttr("disabled");
                $("#modalcontent").html(response);
                $("#notificationmodal").modal("show");
            },
            error: function(jqXHR) {
                $('.loading').css('display', 'none');
                $(".btn").removeAttr("disabled");
                if (jqXHR.status === 400) {
                    $("#modalcontent").html("You need to activate the license key in order to use the plugin");
                      $("#notificationmodal").modal("show");
                } else {
                    $("#modalcontent").html(jqXHR);
                    $("#notificationmodal").modal("You need to activate the license key in order to use the plugin");
                }
            }
        });
        
        return false;
    });

    /**
     * Triggers a function which imports & maps all product's categories
     */
    jQuery(document).on("click",'#import_categories',function(){
        $('.loading').css('display', 'block');
        $(".btn").attr("disabled");
        
        var data = {
            action: 'import_categories',
        };

        $.ajax({
            type: "POST",
            url: the_ajax_script.ajaxurl,
            data: data,
            success: function(response){
                $('.loading').css('display', 'none');
                $(".btn").removeAttr("disabled");
                $("#modalcontent").html(response);
                $("#notificationmodal").modal("show");
            },
            error: function(jqXHR) {
                $('.loading').css('display', 'none');
                $(".btn").removeAttr("disabled");
                if (jqXHR.status === 400) {
                    $("#modalcontent").html("You need to activate the license key in order to use the plugin");
                      $("#notificationmodal").modal("show");
                } else {
                    $("#modalcontent").html(jqXHR);
                    $("#notificationmodal").modal("You need to activate the license key in order to use the plugin");
                }

              
              
            }
        });
        return false;
    });
});


