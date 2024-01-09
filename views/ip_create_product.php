<div class="loading" style="display: none">Loading&#8230;</div>

<div class="wrap">
    <div class="col-md-12">
        <div class="">
            <h4 style="margin-bottom: 10px;">Import Products</h4>
            
            <img style="display:none;" src="<?php echo $gif; ?>" id="store_erp_items_data_loader" class="loading">

            <button class="button button-primary ca_create_selected_product" style="float: right; display: none;margin-right: 10px">Import Products</button>
          

            <nav>
                <div class="nav nav-tabs" id="nav-tab" role="tablist">
                    <a class="nav-item nav-link active" id="nav-home-tab" data-toggle="tab" href="#nav-home" role="tab" aria-controls="nav-home" aria-selected="true">Product Jobs</a>
                    <a class="nav-item nav-link" id="nav-profile-tab" data-toggle="tab" href="#nav-profile" role="tab" aria-controls="nav-profile" aria-selected="false">Create Specific Products</a>
                </div>
            </nav>
            
            <div class="tab-content" id="nav-tabContent">
                <div class="tab-pane fade show active" id="nav-home" role="tabpanel" aria-labelledby="nav-home-tab">
                    <div style="margin-top:2%">
                        <h5>Account Name : <strong>Black and Gold</strong></h5>


                        <div class="custom-mt">
                            <span>Import Categories</span>
                            <br><span><sub>We advise you to sync categories before creating products so that categories can be mapped with products.</sub></span>

                            <button style="margin-left:9.5%" class="btn btn-primary btn-sm" id="import_categories">Import</button>
                        </div>

                        <div class="custom-mt">
                            <span style="margin-top:20px">There are total <?php echo $count; ?> products on your itsperfect account. Import all at once without images</span>
                            <br><span><sub>Products will be created without images for job's performance.</sub></span>
                            
                            <button style="margin-left:30%" class="btn btn-primary btn-sm" id="import_all">Import All</button>
                        </div>

                        <div class="custom-mt">
                            <span>Import images</span>
                            <br><span><sub>This might take a while so it will run in parts, you will get notified once its complete.</sub></span>

                            <button style="margin-left:20%" class="btn btn-primary btn-sm" id="import_images">Start Import</button>
                        </div>


                        <div class="custom-mt">
                            <span>Reset products & product's categories (If needed ask the dev to enable this) </span>
                            <br><span><sub>We advise you to use this carefully as this will wipe out all products & categories permenantly.</sub></span>

                            <button disabled style="margin-left:17%" class="btn btn-danger btn-sm" id="reset_all">Reset</button>
                        </div>

                        <div class="custom-mt">
                            <span>Update stock quantities</span>
                            <br><span><sub>This will refresh all stock quantities for all exisitng products.</sub></span>

                            <button style="margin-left:31%" class="btn btn-primary btn-sm" id="update_stock">Refresh Stock</button>
                        </div>

                        <div class="custom-mt">
                            <span>Set default variations</span>
                            <br><span><sub>This will set default variation in all variable products.</sub></span>

                            <button style="margin-left:37%" class="btn btn-primary btn-sm" id="set_default_variations">Set</button>
                        </div>


                    </div>

                    
                </div>
                <div class="tab-pane fade" id="nav-profile" role="tabpanel" aria-labelledby="nav-profile-tab">
                    <p></p>
                    <?php if(!empty($data)){ ?>
                        <table class="wp-list-table widefat fixed striped create_product_datatable" id="create_product_datatable" style="width: 100%;">
                            <thead>
                            <tr>
                                <th><input name="select_all" value="1" id="example-select-all" type="checkbox" /></th>
                                <th>ID</th>
                                <th>SKU</th>
                                <th>Product Name</th>
                                <th>Actions</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php
                            $part_productids = array();
                            foreach($data as $key=>$value){

                                // The query
                                $products = new WP_Query( array(
                                    'post_type'      => array('product'),
                                    'post_status'    => 'publish,draft',
                                    'posts_per_page' => -1,
                                    'meta_query'     => array( array(
                                        'key' => 'meta_erp_product_id',
                                        'value' => array($value->id),
                                        'compare' => 'IN',
                                    ) )
                                ) );

                                $product_ids = array();
                                
                                // The Loop
                                if ( $products->have_posts() ){
                                    while ( $products->have_posts() ){
                                        $product = $products->the_post();
                                        $meta_erp = get_post_meta($products->post->ID,'meta_erp_product_id');
                                        if(isset($meta_erp[0])){
                                            $product_ids[$products->post->ID] = $meta_erp[0];
                                        }
                                    }
                                    wp_reset_postdata();
                                } 

                                // if($value->id == 510){
                                //     echo "<pre>";print_r($product_ids);die;
                                // }

                                foreach($value->colors as $key=>$color){
                                    $check = 0;
                                    if(!empty($product_ids)){
                                        
                                        foreach($product_ids as $wp_id=>$erp_item_id){
                                            $product = wc_get_product($wp_id);
                                            $existing_color = $product->get_attribute('color');
                                            
                                            $existing_color = strtolower(str_replace(" ","",$existing_color));
                                            $existing_color = str_replace("-","",$existing_color);
                                            $existing_color = str_replace("_","",$existing_color);
                                            $existing_color = str_replace("'","",$existing_color);
                    
                                            $check_color = $color->color;

                                            $check_color = strtolower(str_replace(" ","",$check_color));
                                            $check_color = str_replace("-","",$check_color);
                                            $check_color = str_replace("_","",$check_color);
                                            $check_color = str_replace("'","",$check_color);

                                            if($check_color == $existing_color){
                                                $check = 1;
                                            }
                                        }
                                    }
                    
                                    if($check == 0){
                                        $part_productids = $product_ids;
                                    }
                                }

                                // TEST: Output the Products IDs
                                // print_r($product_ids);die;

                                if(!in_array($value->id,$product_ids) || empty($product_ids) || in_array($value->id,$part_productids)){
                                    ?>
                                    <tr>
                                        <td></td>
                                        <td><?php echo $value->id; ?></td>
                                        <td><?php echo $value->itemNumber; ?></td>
                                        <td><?php echo $value->item; ?></td>
                                        <td>
                                            <button id="<?php echo $value->id; ?>" class="button button-primary create_product">Create</button>
                                            <img style="display:none;" src="<?php echo $gif; ?>" id="loader_<?php echo $value->id; ?>">
                                        </td>
                                    </tr>
                                <?php 
                                }
                            }
                            ?>
                            </tbody>
                        </table>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
</div>


<!-- Modal -->
<div class="modal fade" id="notificationmodal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Notification</h5>
            </div>
            <div class="modal-body" id="modalcontent">
                ...
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>