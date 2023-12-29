<div class="loading" style="display: none">Loading&#8230;</div>

<form id="ip_config_form">
    <div class="wrap">
        <div class="row">
            <div class="col-md-12">
                <h4>Itsperfect Configuration</h4>
                <br>

                <?php if($message){ ?>
                <div class="invalid-feedback" style="display: block;">
                    <?php echo $message; ?>
                </div>
                <br>
                <?php } ?>


                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label for="itsperfect_base_url"><b>Itsperfect's base URL</b></label>
                        <input type="url" name="ip_base_url" class="form-control" id="itsperfect_base_url"
                            placeholder="e.g. https://blackandgold.itsperfect.it/"
                            value="<?php if($ip_base_url){echo $ip_base_url;} ?>" required>

                    </div>
                    <div class="form-group col-md-4">
                        <label for="itsperfect_api_token"><b>API Token</b></label>
                        <input type="itsperfect_api_token" name="ip_api_token" class="form-control"
                            id="itsperfect_api_token" placeholder="e.g. 1523a-6be18-a4918-5410c-9e85b-aa847"
                            value="<?php if($ip_api_token){echo $ip_api_token;} ?>" required>
                    </div>
                </div>


            </div>
        </div>
    </div>
    <div class="wrap">
    <div class="mb-4 main-content-label"><h4> Settings</h4></div>

                            <div class="form-group" style="margin-bottom:40px">
                                <div class="row row-sm">
                                    <div class="col-md-4">
                                    <label class="form-label" for="product_status"><b>Product Status</b></label>
                                    </div>
                                    <div class="col-md-4">
                                    <select class="form-control" name="product_status" id="product_status" style="width:80%">

                                        <option value='publish'
                                            <?php if($product_status == '' || $product_status == 'publish') echo 'selected'; else echo '';  ?>>
                                            Published</option>
                                        <option value='draft'
                                            <?php if( $product_status == 'draft') echo 'selected'; else echo  '';  ?>>Draft</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group"  style="margin-bottom:35px">
                                <div class="row row-sm">
                                    <div class="col-md-4">
                                    <label class="form-label" for="product_title_format" style="margin-bottom:0px !important"><b>Product Title Format</b></label>
                                    <br><p class="mb-3"> <b>ex.</b>  Brand Name - Product Name - Category Name</p>
                                    </div>
                                    <div class="col-md-4">
                                    <div class="checkbox">
                                    <label for="checkbox-1" class="control control--checkbox">
                                        <input type="checkbox" class="product_title_format" value="brand_name"
                                            name="product_title_format[]"
                                            <?php if(in_array('brand_name',$product_title_format)) echo  'checked' ; else echo ''; ?>
                                            id="checkbox-1">
                                        Brand Name
                                    </label>
                                </div>

                                    <div class="checkbox">
                                        <label for="checkbox-2" class="control control--checkbox">
                                            <input type="checkbox" class="product_title_format" value="item_group"
                                                name="product_title_format[]"
                                                <?php if(in_array('item_group',$product_title_format)) echo  'checked' ; else echo ''; ?>
                                                id="checkbox-2">
                                            Item Group
                                        </label>
                                    </div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group"  style="margin-bottom:40px">
                                <div class="row row-sm">
                                    <div class="col-md-4">
                                    <label class="form-label" for="webhooks"><b>Webhooks</b></label>

                                    </div>
                                    <div class="col-md-4">
                                    <div class="checkbox">
                                    <label for="checkbox-3" class="control control--checkbox">
                                        <input type="checkbox" class="webhooks" value="item" name="webhooks[]"
                                            <?php if(in_array('item',$webhooks)) echo  'checked' ; else echo ''; ?>
                                            id="checkbox-3">
                                        Item
                                    </label>
                                 </div>

                                    <div class="checkbox">
                                        <label for="checkbox-4" class="control control--checkbox">
                                            <input type="checkbox" class="webhooks" value="order" name="webhooks[]"
                                                <?php if(in_array('order',$webhooks)) echo  'checked' ; else echo ''; ?>
                                                id="checkbox-4">
                                            Order
                                        </label>
                                    </div>

                                    <div class="checkbox">
                                        <label for="checkbox-5" class="control control--checkbox">
                                            <input type="checkbox" class="webhooks" value="pick" name="webhooks[]"
                                                <?php if(in_array('pick',$webhooks)) echo  'checked' ; else echo ''; ?>
                                                id="checkbox-5">
                                            Pick
                                        </label>
                                    </div>

                                    <div class="checkbox">
                                        <label for="checkbox-6" class="control control--checkbox">
                                            <input type="checkbox" class="webhooks" value="return" name="webhooks[]"
                                                <?php if(in_array('return',$webhooks)) echo  'checked' ; else echo ''; ?>
                                                id="checkbox-6">
                                            Return
                                        </label>
                                    </div>
                                </div>
                            </div>
                            </div>

                            
                      
                            
              
         </div>
    </div>
    <button type="submit" id="save_itsperfect_config" class="btn btn-primary">Save</button>

    
</form>

<!-- Modal -->
<div class="modal fade" id="notificationmodal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true" style="margin-top: 20%">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-body" id="modalcontent"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Ok</button>
            </div>
        </div>
    </div>
</div>
<!-- Modal -->