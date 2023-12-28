<div class="loading" style="display: none">Loading&#8230;</div>

<div class="wrap">
    <div class="col-md-12">
        <h4 style="margin-bottom: 10px;">Update Products</h4>

        <table class="wp-list-table widefat fixed striped update_product_datatable" id="update_product_datatable" style="width: 100%;">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>SKU</th>
                    <th>Product Name</th>
                    <th>Itsperfect ID</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($products as $key=>$product){
                    $erpid = $product->get_attribute('erp_product_id');
                    if(!empty($erpid))
                    { 
                     ?>
                    <tr>
                        <td><a target="_blank" href="<?php echo get_site_url()."/wp-admin/post.php?post=".$product->get_id()."&action=edit"; ?>"><?php echo $product->get_id(); ?></a></td>
                        <td><?php echo $product->get_sku(); ?></td>
                        <td><?php echo $product->get_title(); ?></td>
                        <td><a target="_blank" href="<?php echo "https://blackandgold.itsperfect.it/producten/details/p_id=".$erpid  ?>"><?php echo $erpid; ?></a></td>
                        <td><button id="<?php echo $product->get_id(); ?>" class="button button-primary update_product">Update</button></td>
                    </tr>
                <?php } } ?>
            </tbody>
        </table>
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

