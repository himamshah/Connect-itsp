
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

            <form id="ip_config_form">
                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label for="itsperfect_base_url">Itsperfect's base URL</label>
                        <input type="url" name="ip_base_url" class="form-control" id="itsperfect_base_url" placeholder="e.g. https://blackandgold.itsperfect.it/" value="<?php if($ip_base_url){echo $ip_base_url;} ?>" required>
                    </div>
                    <div class="form-group col-md-4">
                        <label for="itsperfect_api_token">API Token</label>
                        <input type="itsperfect_api_token" name="ip_api_token" class="form-control" id="itsperfect_api_token" placeholder="e.g. 1523a-6be18-a4918-5410c-9e85b-aa847" value="<?php if($ip_api_token){echo $ip_api_token;} ?>" required>
                    </div>
                </div>

                <button type="submit" id="save_itsperfect_config" class="btn btn-primary">Save</button>
                

            </form>
        </div>
    </div>
</div>



<!-- Modal -->
<div class="modal fade" id="notificationmodal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" style="margin-top: 20%">
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