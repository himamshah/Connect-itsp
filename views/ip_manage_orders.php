<div class="loading" style="display: none">Loading&#8230;</div>

<div class="wrap">
    <div class="col-md-12">
        <h4 style="margin-bottom: 10px;">Manage Orders</h4>

        <table class="wp-list-table widefat fixed striped order_manage_datatable" id="order_manage_datatable" style="width: 100%;">
            <thead>
                <tr>
                    <th>OrderID</th>
                    <th>Customer Name</th>
                    <th>Itsperfect Id</th>
                    <th>Sync Status</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($orders as $key=>$order){


                    

                    // echo "<pre>";print_r($order->get_id());die;
                    $order = $order->get_data(); 
                    if($order['status'] == 'auto-draft' || $order['parent_id'] != 0){
                        continue;
                    }
                    
                    $erp_order_status = get_post_meta($order['id'],'erp_order_status');
                    if(isset($erp_order_status[0])){
                        $erp_order_status = $erp_order_status[0];
                    }
                    $erp_order_id = get_post_meta($order['id'],'erp_order_id');
                    if(isset($erp_order_id[0])){
                        $erp_order_id = $erp_order_id[0];
                    }
                    if(empty($erp_order_id)){
                        $erp_order_id = '-';
                    }
                    if(empty($erp_order_status)){
                        $erp_order_status = 'unsynced';
                    }

                    if(isset($order['billing']['email'])){
                        $order_email = $order['billing']['email'];
                        $email = email_exists( $order_email );
                        $user = get_user_by('email',$order_email);
                        if(!empty($user) && $email !== false){
                            $order['customer_id'] = $user->data->ID;
                        }
                    }
                    ?>
                    <tr>
                        <td>
                            <a target="_blank" href="<?php echo get_site_url().'/wp-admin/post.php?post='.$order['id'].'&action=edit';  ?>" >
                                <?php echo $order['id']; ?>
                            </a>
                        </td>
                        
                        <td>
                            <a target="_blank" href="<?php echo get_site_url().'/wp-admin/user-edit.php?user_id='.$order['customer_id'].'&wp_http_referer=%2Fwp-admin%2Fusers.php';  ?>" >
                                <?php 
                                if(isset($order['billing'])){
                                    echo $order['billing']['first_name']." ".$order['billing']['last_name']; 
                                }
                                ?>
                            </a>
                        </td>
                        
                        <td> <?php echo $erp_order_id; ?>  </td>
                        <td> 
                            <?php 
                                if($erp_order_status == 'synced') {
                                    echo '<span class="btn btn-sm btn-success">Synced</span>';
                                }
                                else{
                                    echo '<span class="btn btn-sm btn-danger">'.$erp_order_status.'</span>';
                                }
                            ?>  
                        </td>
                        
                        <td><?php echo $order['total']; ?></td>
                        
                        <td>
                            <?php 
                                switch($order['status']){
                                    case "cancelled":
                                        echo '<span class="btn btn-sm btn-secondary">Cancelled</span>';
                                        break;
                                    case "completed":
                                        echo '<span class="btn btn-sm btn-success">Completed</span>';
                                        break;
                                    case "processing":
                                        echo '<span class="btn btn-sm btn-primary">Processing</span>';
                                        break;
                                    case "pending":
                                        echo '<span class="btn btn-sm btn-secondary">Pending payment</span>';
                                        break;
                                    case "on-hold":
                                        echo '<span class="btn btn-sm btn-warning">On hold</span>';
                                        break;
                                }
                            ?>
                        </td>

                        <td>
                            <?php if($erp_order_id != '' && $erp_order_status == 'synced'){ ?>
                                <button id="<?php echo $order['id']; ?>" class="button button-primary resync_order">Re-sync</button>
                            <?php }else { ?>
                                <button id="<?php echo $order['id']; ?>" class="button button-primary sync_order">sync</button>
                            <?php } ?>
                        </td>
                    </tr>
                <?php } ?>
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